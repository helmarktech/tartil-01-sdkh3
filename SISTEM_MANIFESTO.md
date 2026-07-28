# MANIFESTO ARSITEKTUR TARTIL
## Panduan Kritis untuk Pengembangan Sistem

### Prinsip Fundamental
1. **Tidak ada perubahan tanpa analisis dampak** — Setiap fitur baru dipertanyakan: "Apa konsekuensinya ke data yang sudah ada? Apa yang rusak kalau ini salah?"
2. **Data itu sakral** — Validasi harus di controller SEBELUM transaksi. Tidak ada "toleransi" untuk data korup.
3. **Satu sumber kebenaran** — Status siswa ada di tabel `siswas`. Semester snapshot hanya REKAMAN, bukan sumber data aktif.
4. **Automasi itu pedang bermata dua** — Automasik bagus kalau ada safety net. Tanpa safety net, automasi = bug otomatis.

### Aturan Validasi
- Penambahan siswa: WAJIB semester aktif
- Pembuatan TA: WAJIB semua kelas berpenghuni punya tujuan
- Kenaikan kelas: Hanya terjadi OTOMATIS saat buat TA. Tidak ada jalur manual.
- Mutasi siswa: Hanya `aktif` -> `mutasi_keluar`. Tidak boleh mutasi sembarangan.

### Anti-Pattern yang Harus Dihindari
- Hardcoded ID atau value
- Query N+1 di dalam loop
- Mass assignment tanpa fillable yang eksplisit
- Blade logic yang terlalu kompleks (jangan query DB dari view)
- Mengabaikan error handling di transaction

### Checklist Sebelum Rilis Perubahan
[ ] Apakah migration backward-compatible?
[ ] Apakah validasi cukup kuat untuk edge case?
[ ] Apakah tidak ada query ke tabel yang sudah di-rename?
[ ] Apakah pesan error informatif untuk user?
[ ] Apakah tidak ada duplikasi logika antara controller?
