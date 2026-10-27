# DAFTAR TINJAUAN ULANG — Dead Code & Fitur Orphaned

> Disusun dari audit statis seluruh repo (controller vs routes, view vs renderer, model vs pemakaian, migration vs tabel).
> Setiap item mencantumkan **tingkat keyakinan** dan **bukti**. Item "Pasti dead" aman dihapus; item "Perlu konfirmasi" jangan dihapus sebelum dicek ke pemakaian production.
> Tanggal audit: 2026 (pasca perubahan nilai rapor murni penilaian internal).

---

## 1. Pasti Dead — Aman Dihapus (setelah backup/git history cukup)

### 1.1 Klaster fitur penilaian rapor lama (non-internal) — ✅ SUDAH DIHAPUS

Sudah digantikan sepenuhnya oleh `PenilaianRaporInternalController`.

- `app/Http/Controllers/PenilaianRaporController.php` — tidak dirujuk di `routes/web.php`/`api.php`/`console.php`; satu-satunya referensi nama class adalah file-nya sendiri.
- `app/Http/Controllers/PenilaianRaporToggleController.php` — nol referensi eksternal; tabel `penilaian_rapor_toggles` sudah di-drop migration `2026_06_10_000003`.
- View (8 file), hanya dirender kedua controller dead di atas:
  - `resources/views/guru/penilaian-rapor/index.blade.php`
  - `resources/views/guru/penilaian-rapor/pilih-kelas.blade.php`
  - `resources/views/guru/penilaian-rapor/isi-nilai.blade.php`
  - `resources/views/guru/penilaian-rapor/rekap-nilai.blade.php` — **masih memakai rumus lama `(R2 Rapor + R2 Harian) / 2` di dalam Blade** (baris ~127, ~227); karena orphaned, tidak disesuaikan pada perubahan rapor terbaru.
  - `resources/views/guru/penilaian-rapor/track-record.blade.php`
  - `resources/views/guru/penilaian-rapor-toggle/index.blade.php`
  - `resources/views/guru/penilaian-rapor-toggle/pilih-kelas.blade.php`
  - `resources/views/guru/penilaian-rapor-toggle/isi-nilai.blade.php`
- Model terkait (tabelnya sudah di-drop migration 2026_06_10):
  - `app/Models/PenilaianRapor.php`
  - `app/Models/SemesterPenilaianRapor.php`
  - `app/Models/PenilaianRaporToggle.php`
  - `app/Models/PenilaianRaporInternalPeserta.php`

### 1.2 Method tidak dirutekan di `GuruController` — ✅ SUDAH DIHAPUS

Jurnal sudah pindah ke `JurnalController`, perpindahan ke `PerpindahanTartilController`.

- `GuruController::jurnalIndex` (l.46), `jurnalCreate` (l.68), `jurnalStore` (l.91 — masih menulis ke tabel legacy `jurnals`/`jurnal_details`), `jurnalShow` (l.160), `perpindahanCreate` (l.167), `perpindahanStore` (l.186).
- View pasangan: `resources/views/guru/jurnal/create.blade.php`, `resources/views/guru/jurnal/show.blade.php`.

### 1.3 View orphaned (tidak dirender / route perujuk sudah tidak ada) — ✅ SUDAH DIHAPUS

- `resources/views/admin/penilaian-rapor-internal/detail.blade.php`
- `resources/views/guru/penilaian-rapor-internal/detail.blade.php`
- `resources/views/guru/penilaian-rapor-internal/track-record.blade.php`
- `resources/views/pdf/rapor-kelas.blade.php`
- `resources/views/guru/absensi/index.blade.php` — memanggil `route('guru.absensi.index')` yang sudah tidak ada (broken jika dirender).
- `resources/views/siswa/absensi.blade.php` — memanggil `route('siswa.absensi')` yang sudah tidak ada.
- `resources/views/admin/pengaturan-kelas/aktifkan.blade.php` dan `monitoring.blade.php` — route `admin.pengaturan-kelas.aktifkan(.proses)` / `.monitoring` sudah tidak ada; kedua view hanya saling link satu sama lain.

### 1.4 File sampah / arsip — ✅ SUDAH DIHAPUS

- `name('daftar-jurnal')` (root, 0 byte) — hasil salah redirect shell.
- `tartil-full.zip`, `tartil-patch.zip` — arsip deployment lama (Jun 2025).

### 1.5 Method tak terpanggil — ✅ SUDAH DIHAPUS

- `RekapR2Akhir::invalidate()` (`app/Models/RekapR2Akhir.php`) — tidak ada pemanggil produksi; `guruSimpanNilai` memakai `whereIn()->delete()` langsung. Hapus atau pertahankan sebagai public API — keputusan gaya.

---

## 2. Broken — Perlu Perbaikan (bukan hapus)

| Item | Masalah | Opsi tindakan |
|------|---------|---------------|
| ~~`routes/web.php:300` — `GET /api/siswa-by-kelas-reguler`~~ ✅ | Method tidak ada → route **sudah dihapus** (tidak ada pemanggil di view/JS mana pun) | Selesai |
| ~~`tests/Feature/TrackRecordPersentaseTest.php:108`~~ ✅ | **Test-nya yang keliru**: memakai jurnal pekan berjalan (tanggal masa depan tersaring SSOT "sampai hari ini"), sehingga hasil bergantung hari eksekusi. Sudah diperbaiki memakai pekan lalu | Selesai |
| ~~Dashboard guru baca tabel legacy `jurnals`~~ ✅ | Sudah dimigrasi ke `JurnalKelas` (1 baris per kelas per tanggal) + filter tahun; view `guru/dashboard.blade.php` menyesuaikan relasi `surat` | Selesai — tabel legacy `jurnals`/`jurnal_details` kini tidak dibaca kode hidup; penghapusannya tinggal menunggu §1.2 (method dead `GuruController`) |
| `pdf/rapor-siswa.blade.php` | Blok absensi & detail penilaian dihapus saat perbaikan bug (controller tidak menyediakan datanya) | Jika ingin blok itu tampil lagi, tambahkan field-nya di `RaporController::ambilDataRapor` |

---

## 3. Perlu Konfirmasi Sebelum Hapus/Ubah — ✅ SUDAH DIEKSEKUSI (keputusan tercatat per baris)

| Item | Keputusan |
|------|-----------|
| Tabel legacy `jurnals` / `jurnal_details` | **Kode dihapus, tabel dibiarkan.** Model `Jurnal`/`JurnalDetail` + semua relasi matinya (`GuruTartil::jurnals`, `Kelas::jurnals`, `Semester::jurnals`, `Absensi::jurnal`, `Siswa::jurnalDetails`) sudah dihapus; dashboard guru sudah migrasi ke `jurnal_kelas`. Drop tabel hanya manual setelah cek data historis production — sengaja tidak dibuat migration drop demi keamanan data |
| Direktori `tartil-patch/` | ✅ Dihapus (padanan sudah ada di root dengan versi lebih baru) |
| `import.xlsx`, `template-import-siswa.xlsx`, `template-import-jk.xlsx`, `update_jenis_kelamin.sql` | ✅ Dihapus (file satu kali; template import kini di-generate dinamis oleh `ImportExcelSiswaController`/`ImportExcelGuruController`) |
| Seeder demo tak terpanggil: `DemoSeeder`, `JurnalHarianSeeder`, `TambahSiswaSeeder`, `PenilaianRaporInternalSeeder` | ✅ Dihapus (demo data sudah dicakup rantai `DatabaseSeeder`) |
| Toolchain import CLI: `ImportSiswaSeeder`+`import:siswa`, `PenempatanKelasTartilSeeder`+`import:penempatan`+`PenempatanKelasTartilService`, `jurnal:fix-pertemuan-ke` | **DIPERTAHANKAN** — utilitas ops fungsional untuk import data awal/perbaikan data, bukan dead code. Kini terdokumentasi di AGENTS.md |
| Tooling generator | ✅ `generate_icons.php`, `generate_panduan.py`, `generate_slide_tartilpro.py` dipindah ke `tools/`; slide deck `.pptx` dipindah ke `panduan/`; `.venv-docs/` dibiarkan di root (venv Python) |

---

## 4. Sudah Diverifikasi HIDUP — Jangan Dihapus

- `RaporController.php` — dipakai route `siswa.rapor` (`routes/web.php:361`), satu-satunya jalur PDF rapor siswa untuk semester ditutup.
- `guru/penilaian-rapor-internal/rekap-nilai.blade.php` — dipakai `guruRekapNilai` (route `guru.penilaian-rapor.rekap`).
- `Buku_Panduan_Siswa_TARTIL.pdf` — dipakai route `panduan.buku-siswa` (`routes/web.php:46`).
- `resources/views/vendor/**` — published vendor views (pagination, Pulse).
- 21 controller lain di `app/Http/Controllers/` — semuanya terutekan di `routes/web.php`.
