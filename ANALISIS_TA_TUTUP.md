# Analisis: Perlakuan Data saat Tahun Ajaran Ditutup

## Ringkasan

Ketika TA ditutup, sistem mengubah **status** record menjadi 'ditutup' tanpa menghapus data. Data historis tetap ada dan bisa diakses. Namun ada beberapa risiko dan hal yang perlu diperhatikan.

---

## Flow Penutupan TA

```
1. Admin klik "Tutup Semester"
   -> Semester.status = 'ditutup', is_aktif = false

2. Jika semua semester di TA sudah ditutup
   -> TA.status = 'ditutup' (otomatis)

3. Admin bisa juga klik "Tutup TA" manual
   -> Semua semester di TA jadi 'ditutup'
   -> TA.status = 'ditutup'
```

**Tidak ada data yang dihapus** saat penutupan. Hanya flag status.

---

## Perlakuan per Modul

### 1. SISWA

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Status siswa | Tetap 'aktif' (tidak berubah) |
| Kelas reguler | Tetap (sudah naik saat buat TA baru) |
| Kelas tartil | Tetap |
| `tanggal_masuk_kelas_tartil` | Tetap (untuk arsip mutasi) |
| Data login | Tetap bisa login |

### 2. JURNAL HARIAN

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Data jurnal | **Tetap ada** — tidak dihapus |
| Akses guru | Guru masih bisa lihat jurnal semester lama via filter semester |
| Input jurnal baru | Tidak bisa (tidak ada semester aktif) |

**RISIKO**: Migration pakai `ON DELETE CASCADE` pada `semester_id`. Kalau semester **dihapus** (bukan ditutup), semua jurnal ikut terhapus.

### 3. REKAP JURNAL BULANAN

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Data rekap | **Tetap ada** — tidak dihapus |
| Track record siswa | Bisa diakses (TrackRecordController ambil semua semester) |

**RISIKO**: Sama seperti jurnal — `ON DELETE CASCADE`.

### 4. MUNAQOSYAH

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Data ujian | Tetap ada |
| Data pendaftaran | Tetap ada |
| Data approval | Tetap ada |
| Data nilai (L/TL) | Tetap ada |

### 5. PENILAIAN RAPOR INTERNAL

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Data penilaian | Tetap ada |
| Data nilai per indikator | Tetap ada |
| Cetak rapor PDF | Masih bisa (dari data semester yang sudah ditutup) |

### 6. SNAPSHOT SEMESTER (semester_kelas, semester_siswa)

| Aspek | Perlakuan saat TA Tutup |
|-------|------------------------|
| Data snapshot | **Tetap ada** — tidak dihapus saat tutup |

**Catatan**: Snapshot dihapus hanya saat semester diaktifkan ulang (agar tidak duplikat).

---

## Risiko Kritis: CASCADE DELETE

Migration ini berbahaya — kalau semester record dihapus:

```php
// jurnal_harians.semester_id -> ON DELETE CASCADE
// rekap_jurnal_bulanans.semester_id -> ON DELETE CASCADE
// semester_kelas.semester_id -> ON DELETE CASCADE
// semester_siswa.semester_id -> ON DELETE CASCADE
```

**Solusi**: Ganti CASCADE jadi RESTRICT agar semester tidak bisa dihapus kalau masih punya data.

---

## Apa yang TIDAK BISA dilakukan setelah TA Tutup?

| Aksi | Status |
|------|--------|
| Input jurnal harian | Tidak bisa (tidak ada semester aktif) |
| Input nilai munaqosyah | Tidak bisa (guru perlu semester aktif) |
| Input nilai rapor | Tidak bisa (penilaian aktif tidak ada) |
| Daftar siswa ke munaqosyah | Tidak bisa (perlu semester aktif) |
| Cetak rapor PDF | **Bisa** — admin bisa pilih semester lama |
| Lihat track record | **Bisa** — semua semester ditampilkan |
| Lihat progress jurnal | **Bisa** — via filter semester lama |

---

## Rekomendasi Perbaikan

### 1. Ganti CASCADE jadi RESTRICT
```php
// Sebelum (berbahaya):
$table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');

// Sesudah (aman):
$table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
```

### 2. Buat middleware/validasi: semester yang sudah ditutup tidak bisa dihapus

### 3. Admin hanya bisa "tutup" semester, tidak bisa "hapus"
```