# Audit: Skenario Pembuatan & Penutupan Tahun Ajaran

## Ringkasan Status: STABIL ✅

---

## SKENARIO 1: Pembuatan TA Baru (`tahunAjaranStore`)

### Flow Lengkap

```
┌─────────────────────────────────────────────────────────────────────────┐
│  VALIDASI                                                               │
│  1. Nama TA belum pernah dibuat                                         │
│  2. Tidak ada TA aktif dengan semester aktif                            │
│  3. Tidak ada semester aktif                                            │
│  4. Semua kelas jenjang 1-5 yang punya siswa punya kelas tujuan       │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│  STEP 1: Tutup TA Lama                                                  │
│  - Semester aktif → status='ditutup', is_aktif=false                   │
│  - TA aktif → status='ditutup'                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│  STEP 2: Kenaikan Kelas                                                 │
│  - Kelas 6 → siswa status='lulus', kelas_reguler_id=null,              │
│              kelas_tartil_id=null, tanggal_masuk_kelas_tartil=null     │
│  - Kelas 1-5 → siswa kelas_reguler_id = kelas tujuan (jenjang+1)      │
│                tanggal_masuk_kelas_tartil = null (reset mutasi)        │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│  STEP 3: Buat TA Baru                                                   │
│  - tanggal_mulai = input admin                                          │
│  - tanggal_selesai = tanggal_mulai + 1 tahun - 1 hari                  │
│  - status = 'aktif'                                                     │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│  STEP 4: Buat Semester                                                  │
│  - Ganjil: tanggal_mulai s/d +6 bulan, is_aktif=true                   │
│  - Genap: +6 bulan s/tanggal_selesai, is_aktif=false                   │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│  STEP 5: Snapshot ke Semester Ganjil                                    │
│  - semester_kelas: semua kelas tartil aktif + jumlah siswa             │
│  - semester_siswa: semua siswa aktif + kelas_tartil + kelas_reguler    │
└─────────────────────────────────────────────────────────────────────────┘
```

### Data yang Terpengaruh

| Entitas | Perubahan | Keterangan |
|---------|-----------|------------|
| `siswas.status` | 'aktif' → 'lulus' | Kelas 6 |
| `siswas.kelas_reguler_id` | null | Kelas 6 (lulus) |
| `siswas.kelas_tartil_id` | null | Kelas 6 (lulus) |
| `siswas.tanggal_masuk_kelas_tartil` | null | Kelas 6 (lulus) |
| `siswas.kelas_reguler_id` | naik | Kelas 1-5 |
| `siswas.tanggal_masuk_kelas_tartil` | null | Kelas 1-5 (reset mutasi) |
| `semesters.status` | 'ditutup' | TA lama |
| `semesters.is_aktif` | false | TA lama |
| `tahun_ajaran.status` | 'ditutup' | TA lama |
| `semester_kelas` | CREATE | Snapshot TA baru |
| `semester_siswa` | CREATE | Snapshot TA baru |

### Data yang TIDAK Terpengaruh (AMAN) ✅

| Entitas | Status | Alasan |
|---------|--------|--------|
| `jurnal_harians` | Tetap | Hanya flag status semester |
| `rekap_jurnal_bulanans` | Tetap | Hanya flag status semester |
| `munaqosyah_pendaftarans` | Tetap | Tidak terhubung ke semester status |
| `penilaian_rapor_nilais` | Tetap | Tidak terhubung ke semester status |
| `kelas` (tartil) | Tetap | Status tetap aktif |
| `siswas` (non-kelas 6) | Tetap aktif | Hanya kelas_reguler_id berubah |

---

## SKENARIO 2: Penutupan Semester (`semesterTutup`)

### Flow

```
1. Semester.status = 'ditutup', is_aktif = false
2. Cek: semua semester di TA sudah ditutup?
   Ya → TA.status = 'ditutup'
   Tidak → TA tetap aktif
```

### Data yang Terpengaruh

| Entitas | Perubahan |
|---------|-----------|
| `semesters.status` | 'ditutup' |
| `semesters.is_aktif` | false |
| `tahun_ajaran.status` | 'ditutup' (kalau semua semester tutup) |

### Data yang TIDAK Terpengaruh (AMAN) ✅

Semua data transaksional tetap ada:
- `jurnal_harians`
- `rekap_jurnal_bulanans`
- `munaqosyah_pendaftarans`
- `penilaian_rapor_nilais`
- `kelas_liburs`

---

## SKENARIO 3: Penutupan TA Manual (`tahunAjaranTutup`)

### Flow

```
1. Semua semester di TA → status='ditutup', is_aktif=false
2. TA → status='ditutup'
```

### Data yang Terpengaruh

Sama dengan skenario 2 — hanya flag status.

---

## SKENARIO 4: Hapus Semester (`semesterDestroy`)

### Proteksi

```
Cek jurnal_harians → kalau ada, BLOKIR
Cek ujian_munaqosyahs → kalau ada, BLOKIR
Cek penilaian_rapor_internals → kalau ada, BLOKIR
Hanya hapus kalau benar-benar kosong
```

### Foreign Key: CASCADE → RESTRICT ✅

Migration `2026_06_18_000003_change_cascade_to_restrict_on_semesters.php` sudah mengubah:
- `jurnal_harians.semester_id`: CASCADE → RESTRICT
- `rekap_jurnal_bulanans.semester_id`: CASCADE → RESTRICT
- `semester_kelas.semester_id`: CASCADE → RESTRICT
- `semester_siswa.semester_id`: CASCADE → RESTRICT

Artinya: **Semester tidak bisa dihapus dari database kalau masih punya data**.

---

## SKENARIO 5: Kelas Baru di Pertengahan Semester

### Logika Target Hari

```
Kelas Lama (tanpa tanggal_dibuat):
  target = hari_kerja(semester.tanggal_mulai, hari_ini) − hari_libur

Kelas Baru (dengan tanggal_dibuat):
  target = hari_kerja(kelas.tanggal_dibuat, hari_ini) − hari_libur
```

### Referensi Tanggal

```
1. $semester->tanggal_mulai (prioritas)
2. $semester->tahunAjaran->tanggal_mulai (fallback)
3. now()->startOfYear() (fallback aman)
```

---

## SKENARIO 6: Siswa Mutasi

### Logika

```
Siswa Reguler (tanggal_masuk_kelas_tartil = null):
  R2 Harian = persentase B dari semua jurnal
  Target = hari kerja semester

Siswa Mutasi (tanggal_masuk_kelas_tartil = tanggal):
  R2 Harian = persentase B dari jurnal sejak tanggal masuk
  Target = hari kerja sejak tanggal masuk
```

### Reset saat TA Baru

```
tanggal_masuk_kelas_tartil = null
→ Siswa mutasi di TA lama menjadi reguler di TA baru
```

---

## SKENARIO 7: Hari Libur Per Kelas

### Logika

```
Target = hari_kerja(awal, hari_ini) − hari_libur(kelas, awal, hari_ini)

Admin tandai: POST /kelas-libur {kelas_id, tanggal, keterangan}
Admin hapus: DELETE /kelas-libur/{id}
```

---

## KESIMPULAN

| Aspek | Status |
|-------|--------|
| Data jurnal saat TA tutup | ✅ AMAN — tidak dihapus |
| Data munaqosyah saat TA tutup | ✅ AMAN — tidak dihapus |
| Data rapor saat TA tutup | ✅ AMAN — tidak dihapus |
| Semester hapus (proteksi) | ✅ DIBLOKIR — RESTRICT FK |
| Kenaikan kelas | ✅ BENAR — jenjang+1, rombel tetap |
| Kelas 6 lulus | ✅ BENAR — status='lulus', kelas=null |
| Reset mutasi siswa | ✅ BENAR — tanggal_masuk_kelas_tartil=null |
| Kelas baru pertengahan | ✅ BENAR — tanggal_dibuat |
| Siswa mutasi | ✅ BENAR — tanggal_masuk_kelas_tartil |
| Hari libur per kelas | ✅ BENAR — kelas_liburs |
| Foreign key | ✅ RESTRICT — tidak bisa hapus semester dengan data |
