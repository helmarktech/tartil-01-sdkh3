<!-- AGENTS.md — Panduan Pengembangan TARTIL -->

File ini ditujukan untuk AI coding agent yang bekerja pada proyek **TARTIL (Sistem Penilaian Tartil Online)**. Bacalah seluruh bagian sebelum melakukan perubahan kode. Informasi di bawah ini disusun berdasarkan isi repositori saat ini.

---

## 1. Ikhtisar Proyek

TARTIL adalah aplikasi web berbasis **Laravel 13.x** untuk manajemen penilaian bacaan tartil Al-Quran di lembaga pendidikan. Aplikasi mendukung tiga peran pengguna:

| Peran | Cara Login | Akses Utama |
|-------|------------|-------------|
| **Admin** | Email + Password | Manajemen guru, siswa, kelas, semester, approval perpindahan, cetak rapor, monitoring, system setup |
| **Guru** | Email + Password | Input jurnal harian, absensi, nilai rapor, munaqosyah, pengajuan pindah kelas |
| **Siswa** | NIS + No HP | Melihat nilai, absensi, riwayat kelas, rapor, hafalan |

Fitur inti meliputi:

- Jurnal harian dengan penilaian **B (Baik)**, **C (Cukup)**, **K (Kurang)**.
- Absensi otomatis dari jurnal harian.
- Perpindahan kelas tartil dengan alur approval (guru mengajukan → admin atau guru tujuan approve/tolak).
- Kenaikan kelas reguler otomatis saat pembuatan tahun ajaran baru.
- Rapor PDF per siswa dan per kelas.
- Ujian munaqosyah dengan pendaftaran dan approval.
- Manajemen tahun ajaran dan semester (ganjil/genap).
- Hari libur per kelas.
- Tahfidz: tracking hafalan siswa per juz 1–30 (tidak harus berurutan), dengan rekap per semester yang membedakan total siswa, siswa sudah hafal, dan siswa tuntas per juz.
- Notifikasi siswa (database + push Web VAPID): siswa menerima notifikasi saat guru menginput jurnal harian, menambahkan setoran hafalan, dan mengkonfirmasi laporan pendampingan orangtua. Lonceng notifikasi di topbar siswa + halaman `/siswa/notifikasi`.
- PWA: installable (manifest + service worker `public/sw.js`), caching cache-first hanya untuk asset statis (`/build/`, `/icons/`, `/images/`, `/css/`), halaman dinamis network-only.
- Audit trail perubahan data via `activity_logs`.

---

## 2. Teknologi dan Stack

- **Framework Backend:** Laravel 13.x (PHP ^8.3).
- **Frontend:** Blade templates, Tailwind CSS v4, Vite.
  - File entry Vite (`resources/css/app.css` dan `resources/js/app.js`) sudah tersedia; hasil build (`public/build`) di-generate saat `npm run build` dan di-gitignore.
- **Database:** MySQL/MariaDB (default `.env`), SQLite untuk testing.
- **Dependensi Utama:**
  - `laravel/framework` ^13.0
  - `barryvdh/laravel-dompdf` ^3.0 — cetak PDF
  - `phpoffice/phpspreadsheet` ^2.0 — import/export Excel
  - `laravel-notification-channels/webpush` ^12.1 — push notification Web (VAPID) untuk siswa
  - `laravel/tinker`
- **Dev Tools:**
  - `phpunit/phpunit` ^11.5
  - `laravel/pint` ^1.20 — code style
  - `nunomaduro/collision` ^8.7
  - `fakerphp/faker`
  - `mockery/mockery`
- **Monitoring:** Laravel Pulse (`/pulse`, dilindungi Gate `viewPulse`).

---

## 3. Struktur Direktori dan Modul

### Ringkasan Ukuran Kode

Berdasarkan eksplorasi repositori:

| Komponen | Jumlah |
|----------|--------|
| Model | 41 |
| Controller | 21 |
| Migration | 75 |
| View Blade | 112 |
| Console Command | 5 (+ command `session:fix` di `routes/console.php`) |

### Direktori Penting

```
app/
  Console/Commands/        # Artisan command custom (r2:precalculate, semester:retroactive-lock, dsb.)
  Exports/                 # Export Excel (RekapNilaiExport)
  Http/Controllers/        # Semua controller (Admin, Guru, Siswa, Jurnal, Rapor, dsb.)
  Http/Middleware/         # RoleMiddleware, CheckSemesterAktif, SiswaMiddleware
  Jobs/                    # Queue job (HitungR2Job)
  Models/                  # ~41 model Eloquent
  Providers/               # AppServiceProvider, SessionFallbackServiceProvider
  Services/                # AutoSetupService, PrecalculateReminderService
  Traits/                  # Auditable
database/
  migrations/              # Semua migration
  seeders/                 # DatabaseSeeder dan seeder spesifik
resources/
  views/                   # Blade templates, dikelompokkan per peran (admin, guru, siswa, pdf, ...)
routes/
  web.php                  # Seluruh routing web aplikasi
  console.php              # Command custom sederhana (session:fix)
  api.php                  # Endpoint publik /ping
```

### Modul Controller Utama

- `AdminController` — dashboard, CRUD guru/siswa/kelas, tahun ajaran, semester, audit, statistik (file terbesar, ~2.290 baris).
- `GuruController` — dashboard guru, API siswa per kelas.
- `SiswaDashboardController` — dashboard siswa, nilai, perpindahan, hafalan.
- `JurnalController` — input batch jurnal harian, rekap, daftar jurnal, progress.
- `RaporController` — preview dan cetak PDF rapor (model lama).
- `PenilaianRaporInternalController` — penilaian rapor internal (admin buat, guru isi nilai) dan cetak rapor.
- `MunaqosyahController` — ujian munaqosyah (admin & guru).
- `PerpindahanTartilController` — alur perpindahan kelas tartil.
- `KenaikanKelasController` — kenaikan kelas reguler massal & mutasi.
- `TahfidzController` — tracking hafalan Al-Quran.
- `ProgressJurnalController` — monitoring guru, progress jurnal/absensi, hari libur.
- `TrackRecordController` — riwayat siswa per semester.
- `ManajemenController` — manajemen user guru/siswa (reset password, aktif/nonaktif, hapus).
- `SystemSetupController` — setup sistem, cache, R2 precalculate, artisan dari web.
- `ImportExcelSiswaController` — import data siswa dari Excel.
- `PenempatanTartilController` — penempatan massal siswa ke kelas tartil.
- `PengaturanKelasController` — indikator penilaian dan aktivasi semester kelas.

---

## 4. Autentikasi dan Otorisasi

Aplikasi menggunakan **dua guard session**:

- `web` — untuk Admin dan Guru (model `App\Models\User`, tabel `users`).
- `siswa` — untuk Siswa (model `App\Models\Siswa`, tabel `siswas`).

### Role

Role disimpan di kolom `users.role` dengan nilai `admin` atau `guru`. Method bantu pada `User`:

```php
$user->isAdmin();
$user->isGuru();
```

### Middleware

- `role:admin` / `role:guru` — `App\Http\Middleware\RoleMiddleware`. Admin otomatis bisa mengakses rute guru.
- `semester` — `App\Http\Middleware\CheckSemesterAktif`. Memastikan ada semester aktif dan dalam periode berlaku. Menyediakan `$request->semester_aktif`.
- `auth:siswa` — `App\Http\Middleware\SiswaMiddleware`.

### Default Login Setelah Seed

| Peran | Username | Password |
|-------|----------|----------|
| Admin | `admin@tartil.id` | `admin123` |
| Guru | tergantung seeder | `guru123` |
| Siswa | NIS `2526001` s/d `2526030` | `password` |

---

## 5. Perintah Build, Development, dan Testing

### Instalasi Awal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Atur DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
php artisan migrate --seed
npm run build
php artisan serve
```

Di lingkungan Laragon, aplikasi umumnya diakses via `http://tartil-assessment.test` (sesuaikan nama folder).

> **Peringatan environment saat ini:** repositori mensyaratkan PHP >= 8.3.0, sedangkan environment eksplorasi menjalankan PHP 8.2.20, sehingga `php artisan` gagal saat platform check. Pastikan environment aktif menggunakan PHP 8.3+.

### Development

```bash
# Hot reload Vite
npm run dev

# Jalankan server Laravel
php artisan serve
```

> File entry Vite (`resources/css/app.css`, `resources/js/app.js`) dan hasil build (`public/build`) belum ada. Sebelum `npm run build` berhasil, buat file entry tersebut atau sesuaikan `vite.config.js`.

### Testing

```bash
# Jalankan seluruh test PHPUnit
php artisan test

# Atau langsung vendor/bin/phpunit
./vendor/bin/phpunit
```

Konfigurasi PHPUnit ada di `phpunit.xml`. Saat test, aplikasi menggunakan SQLite in-memory (`:memory:`) dengan environment `testing`.

Test suite saat ini mencakup:

- `tests/Unit/ExampleTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/TahfidzKumulatifTest.php` — memastikan hafalan Tahfidz kumulatif antar semester/TA dan pemilihan juz 1–30 berfungsi.
- `tests/Feature/DataAmanTutupSemesterTest.php` — memastikan data jurnal, munaqosyah, penilaian rapor, dan tahfidz tidak hilang saat semester ditutup.
- `tests/Feature/NotifikasiSiswaTest.php` — memastikan notifikasi siswa terkirim saat jurnal diinput, setoran hafalan ditambahkan, pendampingan dikonfirmasi, dan subscription push tersimpan.

> Migration yang mengandung raw SQL khusus MySQL (`SHOW COLUMNS`, `ALTER TABLE ... MODIFY`, dsb.) sudah dibungkus dengan pengecekan driver agar test SQLite bisa berjalan, tanpa mengubah perilaku di MySQL/MariaDB.

### Code Style

```bash
# Cek style
./vendor/bin/pint --test

# Perbaiki style otomatis
./vendor/bin/pint
```

`.editorconfig` menetapkan indentasi 4 spasi untuk PHP/Blade, 2 spasi untuk YAML.

### Artisan Commands Custom

| Command | Fungsi |
|---------|--------|
| `php artisan r2:precalculate` | Hitung ulang cache R2 akhir |
| `php artisan r2:precalculate --semester_id=1 --kelas_id=2` | Hitung untuk semester/kelas tertentu |
| `php artisan r2:sync-semester {semester_id?}` | Hitung rekap R2 untuk semester yang sudah ditutup |
| `php artisan semester:retroactive-lock {semester_id} \| --all` | Kunci/snapshot data semester lama secara retroaktif |
| `php artisan jurnal:sync-surat` | Sinkronisasi surat/ayat/materi dari `jurnal_kelas` ke `jurnal_harians` |
| `php artisan pulse:token` | Generate token akses Laravel Pulse |
| `php artisan webpush:vapid` | Generate VAPID keys untuk push notification (butuh `OPENSSL_CONF` di Windows) |
| `php artisan session:fix` | Diagnosa dan perbaiki konfigurasi session (di `routes/console.php`) |

---

## 6. Konvensi Kode dan Architecture

Proyek ini memiliki dokumen arsitektur tersendiri:

- `SISTEM_MANIFESTO.md` — prinsip fundamental pengembangan.
- `ANALISIS_SISTEM_TARTIL.md` — analisis kekuatan, risiko, dan rekomendasi.
- `AUDIT_TA_LIFECYCLE.md` — skenario pembuatan & penutupan tahun ajaran.
- `ANALISIS_TA_TUTUP.md` — perlakuan data saat tahun ajaran ditutup.

### Prinsip Penting dari SISTEM_MANIFESTO.md

1. **Tidak ada perubahan tanpa analisis dampak** — tanyakan konsekuensi ke data lama sebelum menambah fitur.
2. **Data itu sakral** — validasi harus dilakukan di controller **sebelum** transaksi.
3. **Satu sumber kebenaran** — status siswa ada di tabel `siswas`; snapshot semester (`semester_siswa`, `semester_kelas`) hanya REKAMAN.
4. **Automasi butuh safety net** — jangan membuat automasi tanpa validasi dan rollback yang aman.

### Aturan Validasi Khusus

- Penambahan siswa: WAJIB semester aktif.
- Pembuatan tahun ajaran baru: WAJIB semua kelas jenjang 1-5 yang berpenghuni punya kelas tujuan.
- Kenaikan kelas: hanya terjadi otomatis saat buat tahun ajaran baru; tidak ada jalur manual.
- Mutasi siswa: hanya `aktif` → `mutasi_keluar`.

### Anti-Pattern yang Dilarang

- Hardcoded ID atau value.
- Query N+1 di dalam loop (gunakan eager load `with()`).
- Mass assignment tanpa `$fillable` yang eksplisit.
- Logic database di dalam Blade (jangan query dari view).
- Mengabaikan error handling di dalam transaction.

### Checklist Sebelum Rilis Perubahan

- [ ] Apakah migration backward-compatible?
- [ ] Apakah validasi cukup kuat untuk edge case?
- [ ] Apakah tidak ada query ke tabel yang sudah di-rename?
- [ ] Apakah pesan error informatif untuk user?
- [ ] Apakah tidak ada duplikasi logika antara controller?

### Gaya Kode

- Bahasa komentar dan variabel utama: **Bahasa Indonesia**.
- Gunakan `camelCase` untuk method/property, `PascalCase` untuk class, `snake_case` untuk kolom database.
- Gunakan Laravel Pint untuk menjaga konsistensi style.
- Controller cenderung besar; jika menambah fitur baru, pertimbangkan memisahkan ke service atau action class untuk mengurangi duplikasi.

---

## 7. Database dan Migration

### Aturan Migration

- Migration disimpan di `database/migrations/` dengan prefix tanggal.
- Jangan hapus atau ubah migration yang sudah pernah dijalankan di production; buat migration baru.
- Foreign key umumnya menggunakan `restrictOnDelete()` untuk melindungi data transaksional (terutama pada `semesters`).
- Soft delete digunakan pada beberapa tabel utama (`siswas`, `gurus`, `kelas_regulers`).

### Tabel Utama

| Tabel | Fungsi |
|-------|--------|
| `tahun_ajaran` | Tahun ajaran aktif/ditutup |
| `semesters` | Semester ganjil/genap, status aktif/ditutup |
| `semester_kelas` / `semester_siswa` | Snapshot komposisi kelas & siswa per semester |
| `kelas_regulers` | Kelas reguler (jenjang + rombel) |
| `kelas` | Kelas tartil |
| `gurus` / `users` | Data guru dan akun login admin/guru |
| `siswas` | Data siswa + akun login siswa |
| `jurnal_harians` / `jurnal_details` (legacy) | Jurnal harian dan penilaian B/C/K |
| `jurnal_kelas` | Info umum jurnal per kelas per tanggal (surat, ayat, halaman, materi, topik, rencana) |
| `absensis` | Absensi (otomatis dari jurnal) |
| `rekap_jurnal_bulanans` | Rekap jurnal per bulan |
| `rekap_r2_akhirs` | Cache R2 akhir per siswa/semester |
| `penilaian_rapor_internals` / `penilaian_rapor_nilais` | Penilaian rapor internal |
| `ujian_munaqosyahs` / `munaqosyah_pendaftarans` | Ujian munaqosyah |
| `perpindahan_kelas` | Riwayat perpindahan kelas tartil |
| `kelas_liburs` | Hari libur per kelas |
| `hafalan_tahfidzs` / `rekap_tahfidz_semesters` | Tracking hafalan |
| `juz_surats` | Mapping surat-ayat per juz untuk perhitungan persentase hafalan |
| `activity_logs` | Audit trail perubahan data |
| `notifications` | Notifikasi siswa (morph ke `siswas`, channel database) |
| `push_subscriptions` | Subscription push Web per siswa (package webpush) |
| `kop_surat_rapors` | Pengaturan kop surat rapor PDF |
| `semester_audit_logs` | Log proses lock/snapshot semester |

### Seeding

:heavy_check_mark: Data transaksional (jurnal, munaqosyah, penilaian rapor, tahfidz) **tidak dihapus** saat semester/TA ditutup. Hanya flag status semester yang berubah.

```bash
php artisan migrate --seed
```

`DatabaseSeeder` menjalankan urutan:

1. `TahunAjaranSemesterSeeder`
2. `KelasGuruSeeder`
3. `AdminSeeder`
4. `SuratSeeder`
5. `Siswa30Seeder`
6. `JurnalPenilaianMunaqosyahSeeder`

### Seeder Minimal (Deploy Production Baru)

Untuk deploy aplikasi baru tanpa data dummy, gunakan `DatabaseSeederMinimal`:

```bash
php artisan db:seed --class=DatabaseSeederMinimal
```

Yang di-seed:

1. `KelasRegulerOnlySeeder` — kelas reguler 1A–6B saja.
2. `AdminOnlySeeder` — satu akun admin `admin@tartil.id` / `admin123`.
3. `SuratSeeder` — data surat Al-Quran.
4. `KopSuratRaporSeeder` — kop surat default.
5. `IndikatorSeeder` — indikator penilaian default per jenis kelas.
6. `JuzSuratSeeder` — mapping surat-ayat per juz untuk perhitungan persentase hafalan.

Yang **tidak** di-seed (admin buat sendiri via dashboard):
- Tahun Ajaran & Semester
- Guru & Kelas Tartil
- Siswa
- Jurnal, Penilaian, Munaqosyah, Tahfidz

### Snapshot Semester

Saat semester ditutup, sistem dapat membuat snapshot di tabel berikut (data asli tetap utuh):

| Tabel Snapshot | Sumber Data |
|------------------|-------------|
| `rekap_jurnal_semesters` | `jurnal_harians` |
| `rekap_munaqosyah_semesters` | `ujian_munaqosyahs` + `munaqosyah_pendaftarans` |
| `rekap_tahfidz_semesters` | `hafalan_tahfidzs` |
| `rekap_riwayat_semesters` | `perpindahan_kelas`, `kenaikan_kelas_regulers` |

---

## 8. Fitur Kritis yang Perlu Dipahami

### 8.1 Tahun Ajaran dan Semester

- Satu tahun ajaran terdiri dari dua semester: ganjil dan genap.
- Pembuatan tahun ajaran baru akan:
  - Menutup tahun ajaran lama.
  - Melakukan kenaikan kelas reguler otomatis (kelas 6 → lulus, kelas 1-5 → jenjang+1).
  - Me-reset `tanggal_masuk_kelas_tartil` siswa.
  - Membuat snapshot ke `semester_kelas` dan `semester_siswa`.
- Saat semester ditutup, data transaksional **tidak dihapus** — hanya flag status yang berubah.
- Semester tidak bisa dihapus jika masih punya data (RESTRICT FK, migration `2026_06_18_000003_change_cascade_to_restrict_on_semesters`).

### 8.2 Jurnal Harian

- Guru mengisi nilai B, C, K untuk setiap siswa di kelasnya.
- Absensi otomatis: siswa yang dinilai = hadir; siswa tidak dinilai bisa di-set sakit/izin/alpha.
- Batch action tersedia: copy dari kemarin, set hadir semua, batch store.
- Middleware `semester` melindungi rute ini — hanya bisa diakses saat semester aktif.
- Informasi umum per kelas per tanggal disimpan di `jurnal_kelas` (surat, ayat, halaman, materi, topik, rencana).

### 8.3 R2 (Rata-Rata Akhir)

- R2 akhir merupakan kombinasi R2 harian dan R2 penilaian rapor.
- Dihitung via command `r2:precalculate` dan disimpan di `rekap_r2_akhirs`.
- `HitungR2Job` bisa dipakai untuk menghitung R2 via queue.
- `PrecalculateReminderService` mengingatkan admin saat cache R2 sudah lebih dari 6 jam (popup) atau 72 jam (badge sidebar).

### 8.4 Munaqosyah

- Admin membuat ujian munaqosyah.
- Guru mendaftarkan siswa kelas sendiri.
- Admin melakukan approval pendaftaran.
- Guru memberi nilai lulus/tidak lulus.

### 8.5 Perpindahan Kelas

- Guru asal mengajukan perpindahan siswa ke kelas tartil lain.
- Admin (atau guru kelas tujuan pada alur tertentu) approve/tolak.
- Riwayat perpindahan disimpan di `perpindahan_kelas`.

### 8.6 Audit Trail

- Model yang menggunakan trait `App\Traits\Auditable` akan otomatis mencatat `create`, `update`, `delete` ke `activity_logs`.
- `ActivityLog::logCustom()` bisa dipakai untuk event manual.

### 8.7 Session Fallback

- `SessionFallbackServiceProvider` otomatis mengalihkan `SESSION_DRIVER=database` ke `file` jika tabel `sessions` belum ada.
- Command `session:fix` tersedia untuk diagnosa manual.

### 8.8 Tahfidz & Hafalan

- Tracking hafalan per siswa per juz (1–30), tidak harus berurutan (misal: Juz 30 bisa didahulukan).
- Tersedia untuk **semua jenis kelas tartil** (BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz), bukan hanya kelas berjenis Tahfidz.
- Persentase hafalan dihitung berdasarkan mapping `juz_surats` (total ayat per juz).
- Hafalan dengan status `hafal` atau `murajaah` dihitung sebagai ayat yang sudah dikuasai.
- Perhitungan kumulatif menggunakan `tanggal_hafalan <= tanggal_selesai semester`, sehingga hafalan semester/TA lalu tetap masuk di semester berikutnya.
- Rekap admin per semester membedakan:
  - Total siswa di kelas tartil.
  - Siswa yang sudah hafal juz tertentu (punya setoran `hafal`).
  - Siswa yang tuntas (persentase ≥ 100%).
- Halaman rekap semester berada di `/admin/tahfidz/rekap-semester` (`TahfidzController::adminRekapSemester`).
- Guru dari kelas tartil manapun dapat mencatat setoran hafalan siswa di kelasnya masing-masing.

---

## 9. Keamanan

- Autentikasi session dengan guard terpisah untuk siswa.
- Password di-hash menggunakan Laravel default (Bcrypt).
- Otorisasi peran melalui `RoleMiddleware`.
- CSRF token bawaan Laravel untuk semua form.
- Akses Laravel Pulse dibatasi: di local bisa semua, di production hanya email yang dikonfigurasi (`PULSE_AUTHORIZED_EMAIL`) atau via token `pulse_token`.
- File `.env` berisi credential; jangan pernah di-commit.

---

## 10. Deployment

### Hal yang Harus Dilakukan di Production

1. Salin `.env.example` ke `.env` dan isi dengan konfigurasi production.
2. Generate key: `php artisan key:generate`.
3. Jalankan migrasi: `php artisan migrate --force`.
4. Jalankan seeder pertama kali jika database kosong:
   - Untuk development/demo: `php artisan db:seed` (menggunakan `DatabaseSeeder`).
   - Untuk production/deploy baru: `php artisan db:seed --class=DatabaseSeederMinimal` (hanya admin, kelas reguler, surat, indikator, juz mapping).
5. Jalankan setup sistem dari admin panel (`/admin/system/setup`) atau via command `php artisan migrate --force` + `php artisan r2:precalculate`.
6. Buat symbolic link storage: `php artisan storage:link`.
7. Jalankan build: `npm install && npm run build` (file entry Vite sudah tersedia).
8. Cache config, route, view: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
9. Atur queue worker jika menggunakan queue untuk PDF/R2: `php artisan queue:work`.

### PWA & Push Notification

- Push Web (VAPID) **wajib HTTPS** (atau `localhost` saat development — `http://tartil-assessment.test` di Laragon tidak mengaktifkan push karena bukan secure context).
- Generate VAPID keys saat deploy: `php artisan webpush:vapid`, lalu isi `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` di `.env` (`VAPID_SUBJECT` wajib untuk Safari/iOS, format `mailto:...` atau URL aplikasi).
- Service worker (`public/sw.js`) hanya cache asset statis; halaman dinamis sengaja network-only karena bergantung pada session.
- Ikon PWA ada di `public/icons/` (192/512/maskable) — regenerasi dari logo jika logo berubah.

### Catatan Vite

File entry Vite (`resources/css/app.css` dan `resources/js/app.js`) sudah tersedia. Jalankan `npm install && npm run build` saat deploy; hasil build (`public/build`) di-generate dan di-gitignore.

---

## 11. Known Issues & Catatan Penting

- **Test suite sudah berkembang** — kini terdapat `TahfidzKumulatifTest` dan `DataAmanTutupSemesterTest`. Tambahkan test baru saat mengubah logika inti.
- **Performance jurnal** — perhatikan query N+1 saat data siswa/jurnal besar. Gunakan `cursor()`, `chunk()`, dan eager load sesuai rekomendasi di `ANALISIS_SISTEM_TARTIL.md`.
- **Mobile responsiveness** — beberapa form panjang masih perlu disesuaikan untuk layar kecil.
- **File `name('daftar-jurnal')`** ada di root repositori dan tampaknya merupakan file tidak sengaja yang tidak berfungsi; bisa dihapus.
- **Direktori `tartil-patch/`** berisi patch lama (migration, CSS, route) dan tidak aktif dalam autoload Laravel. Biasakan memeriksa apakah isinya sudah termigrasi ke root sebelum merujuknya.
- **PHP 8.3 diperlukan** — environment pengembangan harus menjalankan PHP >= 8.3.0 agar Composer autoload dan Artisan berfungsi.

---

## 12. Sumber Dokumentasi Internal

Jika butuh detail lebih lanjut, baca file berikut:

- `README.md` — petunjuk install singkat dan fitur.
- `SISTEM_MANIFESTO.md` — prinsip arsitektur dan aturan pengembangan.
- `ANALISIS_SISTEM_TARTIL.md` — analisis komprehensif kekuatan, risiko, rekomendasi.
- `ANALISIS_TA_TUTUP.md` — perlakuan data saat tahun ajaran ditutup.
- `AUDIT_TA_LIFECYCLE.md` — skenario pembuatan/penutupan tahun ajaran dan semester.
