<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\PerpindahanTartilController;
use App\Http\Controllers\MunaqosyahController;

// ==================== PUBLIC ====================
Route::get('/', fn() => redirect()->route('login'));

// ==================== AUTH ADMIN/GURU ====================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== AUTH SISWA ====================
Route::get('/siswa/login', [AuthController::class, 'showSiswaLogin'])->name('siswa.login');
Route::post('/siswa/login', [AuthController::class, 'siswaLogin'])->name('siswa.login.post');
Route::post('/siswa/logout', [AuthController::class, 'siswaLogout'])->name('siswa.logout');

// ==================== ADMIN ====================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Guru
    Route::get('/guru', [AdminController::class, 'guruIndex'])->name('guru.index');
    Route::get('/guru/create', [AdminController::class, 'guruCreate'])->name('guru.create');
    Route::post('/guru', [AdminController::class, 'guruStore'])->name('guru.store');
    Route::get('/guru/{guru}/edit', [AdminController::class, 'guruEdit'])->name('guru.edit');
    Route::put('/guru/{guru}', [AdminController::class, 'guruUpdate'])->name('guru.update');
    
    // Siswa
    Route::get('/siswa', [AdminController::class, 'siswaIndex'])->name('siswa.index');
    Route::get('/siswa/create', [AdminController::class, 'siswaCreate'])->name('siswa.create');
    Route::post('/siswa', [AdminController::class, 'siswaStore'])->name('siswa.store');
    Route::get('/siswa/{siswa}', [AdminController::class, 'siswaShow'])->name('siswa.show');
    
    // Kelas
    Route::get('/kelas', [AdminController::class, 'kelasIndex'])->name('kelas.index');
    Route::get('/kelas/create', [AdminController::class, 'kelasCreate'])->name('kelas.create');
    Route::post('/kelas', [AdminController::class, 'kelasStore'])->name('kelas.store');
    Route::get('/kelas/{kelas}/edit', [AdminController::class, 'kelasEdit'])->name('kelas.edit');
    Route::put('/kelas/{kelas}', [AdminController::class, 'kelasUpdate'])->name('kelas.update');

    // List Kelas Tartil (dedicated view under Kelas Tartil menu)
    Route::get('/kelas-tartil', fn() => redirect()->route('admin.kelas.index'))->name('kelastartil.index');

    // Kelas Reguler (static routes first to avoid parameter conflict)
    Route::get('/kelas-reguler/daftar', [AdminController::class, 'kelasRegulerIndex'])->name('kelas-reguler.daftar');
    Route::post('/kelas-reguler/daftar', [AdminController::class, 'kelasRegulerStore'])->name('kelas-reguler.store');
    Route::put('/kelas-reguler/daftar/{kelasReguler}', [AdminController::class, 'kelasRegulerUpdate'])->name('kelas-reguler.update');
    Route::get('/kelas-reguler/keterangan', [AdminController::class, 'kelasRegulerSiswa'])->name('kelas-reguler.keterangan');
    Route::get('/kelas-reguler/pindah-kelas', [AdminController::class, 'kelasRegulerPindahIndex'])->name('kelas-reguler.pindah-index');
    Route::post('/kelas-reguler/pindah-kelas', [AdminController::class, 'kelasRegulerPindah'])->name('kelas-reguler.pindah');
    // Parameter routes must be LAST (otherwise "pindah-kelas" matches as {kelasReguler} parameter)
    Route::get('/kelas-reguler/{kelasReguler}', [AdminController::class, 'kelasRegulerDetail'])->name('kelas-reguler.detail');
    Route::post('/kelas-reguler/{kelasReguler}/daftarkan-siswa', [AdminController::class, 'kelasRegulerDaftarkanSiswa'])->name('kelas-reguler.daftarkan-siswa');
    
    // Tahun Ajaran (auto buat ganjil+genap + kenaikan kelas + snapshot)
    Route::get('/tahun-ajaran', [AdminController::class, 'tahunAjaranIndex'])->name('tahun-ajaran.index');
    Route::post('/tahun-ajaran', [AdminController::class, 'tahunAjaranStore'])->name('tahun-ajaran.store');

    // Semester (hanya daftar + detail + tutup, tidak bisa tambah manual)
    Route::get('/semester', [AdminController::class, 'semesterIndex'])->name('semester.index');
    Route::get('/semester/{semester}', [AdminController::class, 'semesterDetail'])->name('semester.detail');
    Route::post('/semester/{semester}/aktifkan', [AdminController::class, 'semesterAktifkan'])->name('semester.aktifkan');
    Route::post('/semester/{semester}/tutup', [AdminController::class, 'semesterTutup'])->name('semester.tutup');
    
    // Munaqosyah: Approval Pendaftaran
    Route::get('/munaqosyah-approval', [AdminController::class, 'munaqosyahApprovalIndex'])->name('munaqosyah.approval.index');
    Route::post('/munaqosyah-approval/{approval}/setuju', [AdminController::class, 'munaqosyahApprovalSetuju'])->name('munaqosyah.approval.setuju');
    Route::post('/munaqosyah-approval/{approval}/tolak', [AdminController::class, 'munaqosyahApprovalTolak'])->name('munaqosyah.approval.tolak');
    
    // Perpindahan Kelas
    Route::get('/perpindahan', [AdminController::class, 'perpindahanIndex'])->name('perpindahan.index');
    Route::post('/perpindahan/{perpindahan}/approve', [AdminController::class, 'perpindahanApprove'])->name('perpindahan.approve');
    Route::post('/perpindahan/{perpindahan}/tolak', [AdminController::class, 'perpindahanTolak'])->name('perpindahan.tolak');

    // Riwayat Siswa Per Semester
    Route::get('/riwayat-siswa', [AdminController::class, 'riwayatSiswaIndex'])->name('riwayat-siswa.index');
    Route::get('/riwayat-siswa/{siswa}', [AdminController::class, 'riwayatSiswaDetail'])->name('riwayat-siswa.detail');

    // Guru Reguler
    Route::get('/guru-reguler', [AdminController::class, 'guruRegulerIndex'])->name('guru-reguler.index');
    Route::post('/guru-reguler', [AdminController::class, 'guruRegulerStore'])->name('guru-reguler.store');
    Route::put('/guru-reguler/{guruReguler}', [AdminController::class, 'guruRegulerUpdate'])->name('guru-reguler.update');
    
    // ===== MANAJEMEN USER (CRUD + PASSWORD + MUTASI) =====
    Route::get('/manajemen/guru', [ManajemenController::class, 'guruIndex'])->name('manajemen.guru');
    Route::post('/manajemen/guru', [ManajemenController::class, 'guruStore'])->name('manajemen.guru.store');
    Route::put('/manajemen/guru/{guru}', [ManajemenController::class, 'guruUpdate'])->name('manajemen.guru.update');
    Route::post('/manajemen/guru/{guru}/reset-password', [ManajemenController::class, 'guruResetPassword'])->name('manajemen.guru.resetpw');
    Route::post('/manajemen/guru/{guru}/nonaktifkan', [ManajemenController::class, 'guruNonaktifkan'])->name('manajemen.guru.nonaktif');
    Route::post('/manajemen/guru/{guru}/hapus', [ManajemenController::class, 'guruHapus'])->name('manajemen.guru.hapus');
    Route::post('/manajemen/guru/{guru}/aktifkan', [ManajemenController::class, 'guruAktifkan'])->name('manajemen.guru.aktif');
    
    Route::get('/manajemen/siswa', [ManajemenController::class, 'siswaIndex'])->name('manajemen.siswa');
    Route::post('/manajemen/siswa', [ManajemenController::class, 'siswaStore'])->name('manajemen.siswa.store');
    Route::put('/manajemen/siswa/{siswa}', [ManajemenController::class, 'siswaUpdate'])->name('manajemen.siswa.update');
    Route::post('/manajemen/siswa/{siswa}/reset-password', [ManajemenController::class, 'siswaResetPassword'])->name('manajemen.siswa.resetpw');
    Route::post('/manajemen/siswa/{siswa}/nonaktifkan', [ManajemenController::class, 'siswaNonaktifkan'])->name('manajemen.siswa.nonaktif');
    Route::post('/manajemen/siswa/{siswa}/hapus', [ManajemenController::class, 'siswaHapus'])->name('manajemen.siswa.hapus');
    Route::post('/manajemen/siswa/{siswa}/aktifkan', [ManajemenController::class, 'siswaAktifkan'])->name('manajemen.siswa.aktif');
    
    // ===== KENAIKAN KELAS REGULER MASSAL =====
    Route::get('/kenaikan-kelas', [KenaikanKelasController::class, 'index'])->name('kenaikan.index');
    Route::post('/kenaikan-kelas/proses', [KenaikanKelasController::class, 'prosesMassal'])->name('kenaikan.proses');
    Route::post('/kenaikan-kelas/mutasi', [KenaikanKelasController::class, 'prosesMutasi'])->name('kenaikan.mutasi');
    
    // ===== PERPINDAHAN KELAS TARTIL (ADMIN VIEW) =====
    Route::get('/perpindahan-tartil', [PerpindahanTartilController::class, 'adminIndex'])->name('perpindahan-tartil.admin');
    Route::post('/perpindahan-tartil/ajukan', [PerpindahanTartilController::class, 'adminAjukan'])->name('perpindahan-tartil.ajukan');
    Route::post('/perpindahan-tartil/{perpindahan}/approve', [PerpindahanTartilController::class, 'adminApprove'])->name('perpindahan-tartil.approve');
    Route::post('/perpindahan-tartil/{perpindahan}/tolak', [PerpindahanTartilController::class, 'adminTolak'])->name('perpindahan-tartil.tolak');
    Route::get('/rekap-kelas-tartil', [AdminController::class, 'rekapKelasTartil'])->name('rekap-kelas-tartil');
    
    // ===== UJIAN MUNAQOSYAH =====
    Route::get('/munaqosyah', [MunaqosyahController::class, 'adminIndex'])->name('munaqosyah.index');
    Route::post('/munaqosyah', [MunaqosyahController::class, 'adminStore'])->name('munaqosyah.store');
    Route::get('/munaqosyah/{munaqosyah}', [MunaqosyahController::class, 'adminDetail'])->name('munaqosyah.detail');
    Route::post('/munaqosyah/{munaqosyah}/approve', [MunaqosyahController::class, 'adminApprove'])->name('munaqosyah.approve');
    Route::post('/munaqosyah/{munaqosyah}/tolak', [MunaqosyahController::class, 'adminTolak'])->name('munaqosyah.tolak');
    Route::post('/munaqosyah/{munaqosyah}/daftarkan', [MunaqosyahController::class, 'adminDaftarkan'])->name('munaqosyah.daftarkan');
    Route::post('/munaqosyah/{munaqosyah}/lulus-semua', [MunaqosyahController::class, 'guruLulusSemua'])->name('munaqosyah.lulussemua');
    Route::post('/munaqosyah/{munaqosyah}/tidak-lulus-semua', [MunaqosyahController::class, 'guruTidakLulusSemua'])->name('munaqosyah.tidaklulussemua');
});

// ==================== GURU ====================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
    
    // Jurnal (semester aktif required for input/update)
    Route::middleware('semester')->group(function () {
        Route::get('/jurnal', [GuruController::class, 'jurnalIndex'])->name('jurnal.index');
        Route::get('/jurnal/create', [GuruController::class, 'jurnalCreate'])->name('jurnal.create');
        Route::post('/jurnal', [GuruController::class, 'jurnalStore'])->name('jurnal.store');
        Route::get('/jurnal/{jurnal}', [GuruController::class, 'jurnalShow'])->name('jurnal.show');
    });

    // Jurnal view (without semester check - for viewing closed semester data)
    Route::get('/jurnal/{jurnal}/view', [GuruController::class, 'jurnalShow'])->name('jurnal.view');
    
    // Absensi
    Route::get('/absensi', [GuruController::class, 'absensiIndex'])->name('absensi.index');
    
    // API: Get siswa by kelas
    Route::get('/api/siswa-by-kelas', [GuruController::class, 'getSiswaByKelas'])->name('api.siswa');
    Route::get('/api/siswa-by-kelas-reguler', [KenaikanKelasController::class, 'getSiswaByKelasReguler'])->name('api.siswa.reguler');
    
    // Perpindahan
    Route::get('/perpindahan/create', [PerpindahanTartilController::class, 'guruCreate'])->name('perpindahan.create');
    Route::post('/perpindahan', [PerpindahanTartilController::class, 'guruStore'])->name('perpindahan.store');
    
    // Approval perpindahan (hanya guru kelas tujuan)
    Route::get('/perpindahan/approval', [PerpindahanTartilController::class, 'guruApprovalIndex'])->name('perpindahan.approval');
    Route::post('/perpindahan/{perpindahan}/guru-approve', [PerpindahanTartilController::class, 'guruApprove'])->name('perpindahan.guru.approve');
    Route::post('/perpindahan/{perpindahan}/guru-tolak', [PerpindahanTartilController::class, 'guruTolak'])->name('perpindahan.guru.tolak');
    
    // Rapor
    Route::get('/rapor', [RaporController::class, 'pilihKelas'])->name('rapor.pilih');
    Route::post('/rapor/preview', [RaporController::class, 'previewRaporKelas'])->name('rapor.preview');
    Route::get('/rapor/pdf/siswa', [RaporController::class, 'pdfRaporSiswa'])->name('rapor.pdf.siswa');
    Route::get('/rapor/pdf/kelas', [RaporController::class, 'pdfRaporKelas'])->name('rapor.pdf.kelas');
    
    // ===== UJIAN MUNAQOSYAH (guru hanya daftarkan siswa kelas sendiri, semester aktif required) =====
    Route::middleware('semester')->group(function () {
        Route::get('/munaqosyah', [MunaqosyahController::class, 'guruIndex'])->name('munaqosyah.index');
        Route::get('/munaqosyah/{munaqosyah}', [MunaqosyahController::class, 'guruDetail'])->name('munaqosyah.detail');
        Route::post('/munaqosyah/{munaqosyah}/daftarkan', [MunaqosyahController::class, 'guruDaftarkan'])->name('munaqosyah.daftarkan');
        Route::post('/munaqosyah/{munaqosyah}/nilai', [MunaqosyahController::class, 'guruNilaiBatch'])->name('munaqosyah.nilai');
        Route::post('/munaqosyah/{munaqosyah}/lulus-semua', [MunaqosyahController::class, 'guruLulusSemua'])->name('munaqosyah.lulussemua');
        Route::post('/munaqosyah/{munaqosyah}/tidak-lulus-semua', [MunaqosyahController::class, 'guruTidakLulusSemua'])->name('munaqosyah.tidaklulussemua');
    });
});

// ==================== SISWA ====================
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/nilai', [SiswaDashboardController::class, 'nilai'])->name('nilai');
    Route::get('/absensi', [SiswaDashboardController::class, 'absensi'])->name('absensi');
    Route::get('/perpindahan', [SiswaDashboardController::class, 'perpindahan'])->name('perpindahan');
    Route::get('/munaqosyah', [MunaqosyahController::class, 'siswaIndex'])->name('munaqosyah');
});
