# TARTIL - Sistem Penilaian Tartil Online (Full Version)

Sistem manajemen penilaian bacaan tartil Al-Quran lengkap dengan multi-role (Admin, Guru, Siswa), jurnal harian dengan penilaian B/C/K, absensi otomatis, perpindahan kelas dengan approval, dan rapor PDF.

---

## Fitur Lengkap

### Multi-Role Authentication
| Role | Login | Akses |
|------|-------|-------|
| **Admin** | Email + Password | Full access: guru, siswa, kelas, semester, approval perpindahan |
| **Guru** | Email + Password | Input jurnal, absensi, rapor, pengajuan pindah kelas |
| **Siswa** | NIS + No HP | Lihat nilai, absensi, riwayat kelas |

### Jurnal Harian (Mobile-Friendly)
- Input nilai **B (Bacaan)**, **C (Catatan)**, **K (Keterampilan)** per siswa
- Batch action: set semua nilai sekaligus
- Absensi otomatis tercatat saat jurnal disimpan
- Desain kartu per siswa (optimal untuk HP)

### Absensi Otomatis
- Siswa yang dinilai = **Hadir**
- Siswa tidak dinilai bisa di-set **Sakit/Izin/Alpha**
- Terintegrasi dengan jurnal harian

### Perpindahan Kelas
- Guru mengajukan perpindahan kelas siswa
- Admin approve/tolak dengan catatan
- Track record perpindahan tersimpan

### Rapor PDF
- Rapor per **siswa** (individual)
- Rapor per **kelas** (rekap semua siswa)
- Per **semester** (Ganjil/Genap)
- Per **tengah semester** atau **akhir semester**
- Predikat otomatis (A/B/C/D/E)

### Manajemen Lengkap
- **Guru**: CRUD, auto-create login
- **Siswa**: NIS, kelas reguler, kelas tartil, status
- **Kelas**: Tartil + reguler, jadwal, guru pengampu
- **Semester**: Ganjil/Genap, aktif/nonaktif

---

## Cara Install di Laragon

### 1. Persyaratan
- PHP 8.1+
- Composer
- Node.js & NPM
- MySQL/MariaDB

### 2. Setup Project

```bash
# Buat project Laravel baru di Laragon
cd C:\laragon\www
composer create-project laravel/laravel tartil-full
cd tartil-full

# Copy semua file dari ZIP ini ke folder project
# (replace semua file yang ada)

# Install dependencies
composer install
npm install

# Copy environment
copy .env.example .env

# Generate key
php artisan key:generate

# Setup database di .env
# DB_DATABASE=tartil
# DB_USERNAME=root
# DB_PASSWORD=

# Jalankan migration + seeder
php artisan migrate --seed

# Build assets
npm run build

# Jalankan
php artisan serve
# atau buka http://tartil-full.test di Laragon
```

### 3. Default Login (setelah seed)

| Role | Username/ID | Password |
|------|-------------|----------|
| **Admin** | admin@tartil.id | admin123 |
| **Guru** | ahmad.ridwan@tartil.id | guru123 |
| **Siswa** | NIS: 2024001 | No HP dari database |

---

## Struktur Database

```
semesters          - Semester (ganjil/genap, tahun ajaran)
kelas_regulers     - Kelas reguler (7A, 8B, dst.)
kelas              - Kelas tartil (Tahsin, Tartil, dst.)
gurus              - Data guru
siswas             - Data siswa (login: NIS + No HP)
users              - Admin & Guru (login: Email + Password)
perpindahan_kelas  - Riwayat perpindahan (pending/disetujui/ditolak)
jurnals            - Jurnal harian
jurnal_details     - Penilaian B/C/K per siswa
absensis           - Absensi (auto dari jurnal)
```

## Flow Sistem

1. **Admin** membuat semester, kelas, guru, dan siswa
2. **Guru** login → input jurnal harian (nilai B/C/K + absensi)
3. **Siswa** login dengan NIS + No HP → lihat nilai & absensi
4. **Guru** bisa ajukan perpindahan kelas → **Admin** approve
5. **Guru** generate rapor PDF per siswa atau per kelas

---

## Keterangan Penilaian

| Kode | Keterangan | Range |
|------|------------|-------|
| **B** | Bacaan | 0-100 |
| **C** | Catatan / Pengetahuan | 0-100 |
| **K** | Keterampilan / Sikap | 0-100 |
| **Rata-rata** | (B + C + K) / 3 | Auto |
| **Predikat** | A/B/C/D/E | Auto dari rata-rata |

---

## License
Open source untuk penggunaan pribadi dan institusi pendidikan.
