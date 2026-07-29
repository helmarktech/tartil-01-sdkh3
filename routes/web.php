<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ImportExcelGuruController;
use App\Http\Controllers\ImportExcelSiswaController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\MunaqosyahController;
use App\Http\Controllers\PenempatanTartilController;
use App\Http\Controllers\PengaturanKelasController;
use App\Http\Controllers\PenilaianRaporInternalController;
use App\Http\Controllers\PerpindahanTartilController;
use App\Http\Controllers\ProgressJurnalController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SystemSetupController;
use App\Http\Controllers\TahfidzController;
use App\Http\Controllers\TrackRecordController;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIC ====================
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('guru.dashboard');
    }

    return view('landing');
});

// Fallback: redirect /login ke landing page (menangani session timeout & bookmark lama)
Route::get('/login', function () {
    return redirect('/');
})->name('login');

// ==================== AUTH ADMIN/GURU (via landing page) ====================
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
    Route::get('/siswa/{siswa}/edit', [AdminController::class, 'siswaEdit'])->name('siswa.edit');
    Route::put('/siswa/{siswa}', [AdminController::class, 'siswaUpdate'])->name('siswa.update');

    // ===== IMPORT SISWA & PENEMPATAN TARTIL (statis HARUS sebelum {siswa}) =====
    Route::get('/siswa/import', [ImportExcelSiswaController::class, 'index'])->name('siswa.import');
    Route::post('/siswa/import', [ImportExcelSiswaController::class, 'proses'])->name('siswa.import.proses');
    Route::get('/siswa/import/template', [ImportExcelSiswaController::class, 'template'])->name('siswa.import.template');
    Route::get('/siswa/penempatan', [PenempatanTartilController::class, 'index'])->name('siswa.penempatan');
    Route::post('/siswa/penempatan', [PenempatanTartilController::class, 'tempatkan'])->name('siswa.penempatan.proses');

    // ===== IMPORT GURU (statis HARUS sebelum {guru}) =====
    Route::get('/guru/import', [ImportExcelGuruController::class, 'index'])->name('guru.import');
    Route::post('/guru/import', [ImportExcelGuruController::class, 'proses'])->name('guru.import.proses');
    Route::get('/guru/import/template', [ImportExcelGuruController::class, 'template'])->name('guru.import.template');

    Route::get('/siswa/{siswa}', [AdminController::class, 'siswaShow'])->name('siswa.show');

    // Kelas
    Route::get('/kelas', [AdminController::class, 'kelasIndex'])->name('kelas.index');
    Route::get('/kelas/create', [AdminController::class, 'kelasCreate'])->name('kelas.create');
    Route::post('/kelas', [AdminController::class, 'kelasStore'])->name('kelas.store');
    Route::get('/kelas/{kelas}/edit', [AdminController::class, 'kelasEdit'])->name('kelas.edit');
    Route::put('/kelas/{kelas}', [AdminController::class, 'kelasUpdate'])->name('kelas.update');

    // List Kelas Tartil (dedicated view under Kelas Tartil menu)
    Route::get('/kelas-tartil', fn () => redirect()->route('admin.kelas.index'))->name('kelastartil.index');

    // Kelas Reguler (static routes first to avoid parameter conflict)
    Route::get('/kelas-reguler/daftar', [AdminController::class, 'kelasRegulerIndex'])->name('kelas-reguler.daftar');
    Route::post('/kelas-reguler/daftar', [AdminController::class, 'kelasRegulerStore'])->name('kelas-reguler.store');
    Route::put('/kelas-reguler/daftar/{kelasReguler}', [AdminController::class, 'kelasRegulerUpdate'])->name('kelas-reguler.update');
    Route::get('/kelas-reguler/keterangan', [AdminController::class, 'kelasRegulerSiswa'])->name('kelas-reguler.keterangan');
    Route::get('/kelas-reguler/keterangan/export', [AdminController::class, 'kelasRegulerExport'])->name('kelas-reguler.keterangan.export');
    Route::get('/kelas-reguler/pindah-kelas', [AdminController::class, 'kelasRegulerPindahIndex'])->name('kelas-reguler.pindah-index');
    Route::post('/kelas-reguler/pindah-kelas', [AdminController::class, 'kelasRegulerPindah'])->name('kelas-reguler.pindah');
    // Parameter routes must be LAST (otherwise "pindah-kelas" matches as {kelasReguler} parameter)
    Route::get('/kelas-reguler/{kelasReguler}', [AdminController::class, 'kelasRegulerDetail'])->name('kelas-reguler.detail');
    Route::post('/kelas-reguler/{kelasReguler}/daftarkan-siswa', [AdminController::class, 'kelasRegulerDaftarkanSiswa'])->name('kelas-reguler.daftarkan-siswa');

    // Tahun Ajaran (auto buat ganjil+genap + kenaikan kelas + snapshot)
    Route::get('/tahun-ajaran', [AdminController::class, 'tahunAjaranIndex'])->name('tahun-ajaran.index');
    Route::post('/tahun-ajaran', [AdminController::class, 'tahunAjaranStore'])->name('tahun-ajaran.store');
    Route::post('/tahun-ajaran/{tahunAjaran}/tutup', [AdminController::class, 'tahunAjaranTutup'])->name('tahun-ajaran.tutup');

    // Semester (hanya daftar + detail + tutup, tidak bisa tambah manual)
    Route::get('/semester', [AdminController::class, 'semesterIndex'])->name('semester.index');
    Route::get('/semester/{semester}', [AdminController::class, 'semesterDetail'])->name('semester.detail');
    Route::post('/semester/{semester}/aktifkan', [AdminController::class, 'semesterAktifkan'])->name('semester.aktifkan');
    Route::post('/semester/{semester}/tutup', [AdminController::class, 'semesterTutup'])->name('semester.tutup');

    // Audit & Rekap Semester (track record terkunci)
    // ===== AUDIT: TAHUN AJARAN (track record terkunci) =====
    Route::get('/audit-semester', [AdminController::class, 'auditPilihTahunAjaran'])->name('audit-semester.pilih-ta');
    Route::get('/audit-semester/list', [AdminController::class, 'auditSemesterIndex'])->name('audit-semester.index');
    Route::get('/audit-semester/{semester}/detail', [AdminController::class, 'auditSemesterDetail'])->name('audit-semester.detail');
    Route::get('/audit-semester/{semester}/export-pdf', [AdminController::class, 'auditSemesterExportPdf'])->name('audit-semester.export-pdf');
    Route::get('/audit-semester/{semester}/export-excel', [AdminController::class, 'auditSemesterExportExcel'])->name('audit-semester.export-excel');

    // ===== STATISTIK: Dashboard grafik perkembangan =====
    Route::get('/statistik', [AdminController::class, 'statistikDashboard'])->name('statistik.index');
    Route::get('/statistik/data', [AdminController::class, 'statistikData'])->name('statistik.data');

    // Munaqosyah: Approval Pendaftaran
    Route::get('/munaqosyah-approval', [AdminController::class, 'munaqosyahApprovalIndex'])->name('munaqosyah.approval.index');
    Route::post('/munaqosyah-approval/{approval}/setuju', [AdminController::class, 'munaqosyahApprovalSetuju'])->name('munaqosyah.approval.setuju');
    Route::post('/munaqosyah-approval/{approval}/tolak', [AdminController::class, 'munaqosyahApprovalTolak'])->name('munaqosyah.approval.tolak');
    Route::post('/munaqosyah-approval/setuju-massal', [AdminController::class, 'munaqosyahApprovalSetujuMassal'])->name('munaqosyah.approval.setuju-massal');
    Route::post('/munaqosyah-approval/tolak-massal', [AdminController::class, 'munaqosyahApprovalTolakMassal'])->name('munaqosyah.approval.tolak-massal');

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
    Route::get('/guru-reguler/{guruReguler}/edit', [AdminController::class, 'guruRegulerEdit'])->name('guru-reguler.edit');
    Route::put('/guru-reguler/{guruReguler}', [AdminController::class, 'guruRegulerUpdate'])->name('guru-reguler.update');

    // ===== MANAJEMEN USER (CRUD + PASSWORD + MUTASI) =====
    Route::get('/manajemen/guru', [ManajemenController::class, 'guruIndex'])->name('manajemen.guru');
    Route::post('/manajemen/guru', [ManajemenController::class, 'guruStore'])->name('manajemen.guru.store');
    Route::get('/manajemen/guru/{guru}/edit', [ManajemenController::class, 'guruEdit'])->name('manajemen.guru.edit');
    Route::put('/manajemen/guru/{guru}', [ManajemenController::class, 'guruUpdate'])->name('manajemen.guru.update');
    Route::post('/manajemen/guru/{guru}/reset-password', [ManajemenController::class, 'guruResetPassword'])->name('manajemen.guru.resetpw');
    Route::post('/manajemen/guru/{guru}/nonaktifkan', [ManajemenController::class, 'guruNonaktifkan'])->name('manajemen.guru.nonaktif');
    Route::post('/manajemen/guru/{guru}/hapus', [ManajemenController::class, 'guruHapus'])->name('manajemen.guru.hapus');
    Route::post('/manajemen/guru/{guru}/aktifkan', [ManajemenController::class, 'guruAktifkan'])->name('manajemen.guru.aktif');

    Route::get('/manajemen/siswa', [ManajemenController::class, 'siswaIndex'])->name('manajemen.siswa');
    Route::post('/manajemen/siswa', [ManajemenController::class, 'siswaStore'])->name('manajemen.siswa.store');
    Route::get('/manajemen/siswa/{siswa}/edit', [ManajemenController::class, 'siswaEdit'])->name('manajemen.siswa.edit');
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
    Route::post('/perpindahan-tartil/ajukan-massal', [PerpindahanTartilController::class, 'adminAjukanMassal'])->name('perpindahan-tartil.ajukan-massal');
    Route::post('/perpindahan-tartil/{perpindahan}/approve', [PerpindahanTartilController::class, 'adminApprove'])->name('perpindahan-tartil.approve');
    Route::post('/perpindahan-tartil/{perpindahan}/tolak', [PerpindahanTartilController::class, 'adminTolak'])->name('perpindahan-tartil.tolak');
    Route::post('/perpindahan-tartil/approve-all', [PerpindahanTartilController::class, 'adminApproveAll'])->name('perpindahan-tartil.approve-all');
    Route::post('/perpindahan-tartil/tolak-all', [PerpindahanTartilController::class, 'adminTolakAll'])->name('perpindahan-tartil.tolak-all');
    Route::get('/rekap-kelas-tartil', [AdminController::class, 'rekapKelasTartil'])->name('rekap-kelas-tartil');
    Route::get('/rekap-kelas-tartil/export', [AdminController::class, 'keteranganKelasTartilExport'])->name('keterangan-kelas-tartil.export');
    Route::get('/rekap-jurnal', [JurnalController::class, 'adminRekap'])->name('rekap-jurnal');
    Route::get('/daftar-jurnal', [JurnalController::class, 'adminDaftarJurnal'])->name('daftar-jurnal');
    Route::get('/jurnal-bulanan', [JurnalController::class, 'adminJurnalBulanan'])->name('jurnal-bulanan');

    // Progress Jurnal & Absensi (monitoring)
    Route::get('/progress-jurnal', [ProgressJurnalController::class, 'progressJurnal'])->name('progress.jurnal');
    Route::get('/progress-absensi', [ProgressJurnalController::class, 'progressAbsensi'])->name('progress.absensi');

    Route::get('/track-record', [TrackRecordController::class, 'adminIndex'])->name('track-record.index');
    Route::get('/track-record/{siswa}/detail', [TrackRecordController::class, 'detail'])->name('track-record.detail');

    // ===== PENGATURAN KELAS (Indikator Penilaian + Aktivasi Semester) =====
    Route::get('/pengaturan-kelas', [PengaturanKelasController::class, 'index'])->name('pengaturan-kelas.index');
    Route::post('/pengaturan-kelas/indikator', [PengaturanKelasController::class, 'storeIndikator'])->name('pengaturan-kelas.indikator.store');
    Route::put('/pengaturan-kelas/indikator/{id}', [PengaturanKelasController::class, 'updateIndikator'])->name('pengaturan-kelas.indikator.update');
    Route::delete('/pengaturan-kelas/indikator/{id}', [PengaturanKelasController::class, 'destroyIndikator'])->name('pengaturan-kelas.indikator.destroy');

    // ===== UJIAN MUNAQOSYAH =====
    Route::get('/munaqosyah', [MunaqosyahController::class, 'adminIndex'])->name('munaqosyah.index');
    Route::post('/munaqosyah', [MunaqosyahController::class, 'adminStore'])->name('munaqosyah.store');
    Route::get('/munaqosyah/daftar', [MunaqosyahController::class, 'adminDaftar'])->name('munaqosyah.daftar');
    Route::post('/munaqosyah/daftar', [MunaqosyahController::class, 'adminDaftarSimpan'])->name('munaqosyah.daftar.simpan');
    Route::get('/munaqosyah/{munaqosyah}', [MunaqosyahController::class, 'adminDetail'])->name('munaqosyah.detail');
    Route::get('/munaqosyah/{munaqosyah}/export-excel', [MunaqosyahController::class, 'adminExportPesertaExcel'])->name('munaqosyah.export-excel');
    Route::post('/munaqosyah/{munaqosyah}/nilai', [MunaqosyahController::class, 'adminNilaiBatch'])->name('munaqosyah.nilai.admin');
    Route::delete('/munaqosyah/{munaqosyah}/peserta/{pendaftaran}', [MunaqosyahController::class, 'adminBatalPendaftaran'])->name('munaqosyah.peserta.batal');
    Route::post('/munaqosyah/{munaqosyah}/approve', [MunaqosyahController::class, 'adminApprove'])->name('munaqosyah.approve');
    Route::post('/munaqosyah/{munaqosyah}/tolak', [MunaqosyahController::class, 'adminTolak'])->name('munaqosyah.tolak');
    Route::post('/munaqosyah/{munaqosyah}/buka-pendaftaran', [MunaqosyahController::class, 'adminBukaPendaftaran'])->name('munaqosyah.buka-pendaftaran');
    Route::post('/munaqosyah/{munaqosyah}/tutup-pendaftaran', [MunaqosyahController::class, 'adminTutupPendaftaran'])->name('munaqosyah.tutup-pendaftaran');
    Route::post('/munaqosyah/{munaqosyah}/daftarkan', [MunaqosyahController::class, 'adminDaftarkan'])->name('munaqosyah.daftarkan');
    Route::post('/munaqosyah/{munaqosyah}/lulus-semua', [MunaqosyahController::class, 'guruLulusSemua'])->name('munaqosyah.lulussemua');
    Route::post('/munaqosyah/{munaqosyah}/tidak-lulus-semua', [MunaqosyahController::class, 'guruTidakLulusSemua'])->name('munaqosyah.tidaklulussemua');
    // Rekap History Munaqosyah (admin)
    Route::get('/munaqosyah-rekap', [MunaqosyahController::class, 'adminRekapHistory'])->name('munaqosyah.rekap');
    Route::get('/munaqosyah-rekap/siswa/{siswa}', [MunaqosyahController::class, 'adminRekapPerSiswa'])->name('munaqosyah.rekap.siswa');

    // ===== PENILAIAN RAPOR INTERNAL (admin buat, guru isi nilai B/C/K) =====
    Route::get('/penilaian-rapor-internal', [PenilaianRaporInternalController::class, 'adminIndex'])->name('penilaian-rapor-internal.index');
    Route::post('/penilaian-rapor-internal', [PenilaianRaporInternalController::class, 'adminStore'])->name('penilaian-rapor-internal.store');
    Route::delete('/penilaian-rapor-internal/{penilaian}', [PenilaianRaporInternalController::class, 'adminDestroy'])->name('penilaian-rapor-internal.destroy');
    Route::get('/penilaian-rapor-internal-rekap', [PenilaianRaporInternalController::class, 'adminRekapProgress'])->name('penilaian-rapor-internal.rekap');

    // ===== MONITORING GURU BELUM MENGISI JURNAL =====
    Route::get('/monitoring-guru', [ProgressJurnalController::class, 'monitoringGuru'])->name('monitoring.guru');

    // ===== MANAJEMEN HARI LIBUR PER KELAS =====
    Route::post('/kelas-libur', [ProgressJurnalController::class, 'liburStore'])->name('kelas-libur.store');
    Route::delete('/kelas-libur/{libur}', [ProgressJurnalController::class, 'liburDestroy'])->name('kelas-libur.destroy');

    // ===== TAHFIDZ: TRACKING HAFALAN =====
    Route::get('/tahfidz', [TahfidzController::class, 'adminIndex'])->name('tahfidz.index');
    Route::get('/tahfidz/rekap-semester', [TahfidzController::class, 'adminRekapSemester'])->name('tahfidz.rekap-semester');
    Route::get('/tahfidz/siswa/{siswa}', [TahfidzController::class, 'adminDetailSiswa'])->name('tahfidz.detail-siswa');
    Route::get('/tahfidz/hafalan/create', [TahfidzController::class, 'adminCreate'])->name('tahfidz.hafalan.create');
    Route::post('/tahfidz/hafalan', [TahfidzController::class, 'adminStore'])->name('tahfidz.hafalan.store');
    Route::delete('/tahfidz/hafalan/{hafalan}', [TahfidzController::class, 'adminDestroy'])->name('tahfidz.hafalan.destroy');

    // ===== KOP SURAT RAPOR (pengaturan cetak) =====
    Route::get('/kop-surat-rapor', [AdminController::class, 'kopSuratRaporIndex'])->name('kop-surat-rapor.index');
    Route::post('/kop-surat-rapor/update', [AdminController::class, 'kopSuratRaporUpdate'])->name('kop-surat-rapor.update');

    // ===== CETAK RAPOR PDF =====
    Route::get('/cetak-rapor', [PenilaianRaporInternalController::class, 'adminCetakRaporPilih'])->name('cetak-rapor.pilih');
    Route::get('/cetak-rapor/pdf/{siswa}', [PenilaianRaporInternalController::class, 'adminCetakRaporPdf'])->name('cetak-rapor.pdf');
    Route::get('/cetak-rapor/kelas-tartil/pdf', [PenilaianRaporInternalController::class, 'adminCetakRaporKelasPdf'])->name('cetak-rapor.kelas.pdf');
    Route::get('/cetak-rapor/kelas-reguler/pdf', [PenilaianRaporInternalController::class, 'adminCetakRaporKelasRegulerPdf'])->name('cetak-rapor.kelas-reguler.pdf');

    // ===== SYSTEM SETUP & MAINTENANCE =====
    Route::get('/system/setup', [SystemSetupController::class, 'index'])->name('system.setup');
    Route::post('/system/setup/run', [SystemSetupController::class, 'runSetup'])->name('system.setup.run');
    Route::post('/system/r2-precalculate', [SystemSetupController::class, 'runR2Precalculate'])->name('system.r2-precalculate');
    Route::post('/system/r2-reset', [SystemSetupController::class, 'resetR2Cache'])->name('system.r2-reset');
    Route::post('/system/artisan', [SystemSetupController::class, 'runArtisan'])->name('system.artisan');
    Route::post('/system/clear-cache', [SystemSetupController::class, 'clearAllCache'])->name('system.clear-cache');
    Route::post('/system/optimize', [SystemSetupController::class, 'optimize'])->name('system.optimize');
    Route::get('/system/status', [SystemSetupController::class, 'checkStatus'])->name('system.status');
    Route::post('/system/precalculate-dismiss', [SystemSetupController::class, 'dismissPrecalculate'])->name('system.precalculate-dismiss');
});

// ==================== GURU ====================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');

    // JURNAL HARIAN (batch grid, keyboard shortcuts, 7.000+ entri/hari)
    Route::middleware('semester')->group(function () {
        Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
        Route::post('/jurnal/batch-store', [JurnalController::class, 'batchStore'])->name('jurnal.batch-store');
        Route::post('/jurnal/copy-yesterday', [JurnalController::class, 'copyFromYesterday'])->name('jurnal.copy-yesterday');
        Route::post('/jurnal/hadir-semua', [JurnalController::class, 'hadirSemua'])->name('jurnal.hadir-semua');
    });
    // Rekap bulanan (tanpa semester check)
    Route::get('/jurnal/rekap', [JurnalController::class, 'rekapBulanan'])->name('jurnal.rekap');
    Route::get('/jurnal/bulanan', [JurnalController::class, 'guruJurnalBulanan'])->name('jurnal.bulanan');

    // Track Record Siswa
    Route::get('/track-record', [TrackRecordController::class, 'guruIndex'])->name('track-record.index');
    Route::get('/track-record/{siswa}/detail', [TrackRecordController::class, 'detail'])->name('track-record.detail');

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

    // Perpindahan massal 3-step (guru)
    Route::get('/perpindahan/massal', [PerpindahanTartilController::class, 'guruMassalIndex'])->name('perpindahan.massal');
    Route::post('/perpindahan/massal', [PerpindahanTartilController::class, 'guruMassalStore'])->name('perpindahan.massal.store');

    // Rapor
    Route::get('/rapor', [RaporController::class, 'pilihKelas'])->name('rapor.pilih');
    Route::post('/rapor/preview', [RaporController::class, 'previewRaporKelas'])->name('rapor.preview');
    Route::get('/rapor/pdf/siswa', [RaporController::class, 'pdfRaporSiswa'])->name('rapor.pdf.siswa');
    Route::get('/rapor/pdf/kelas', [RaporController::class, 'pdfRaporKelas'])->name('rapor.pdf.kelas');

    // ===== UJIAN MUNAQOSYAH (guru hanya daftarkan siswa kelas sendiri, semester aktif required) =====
    Route::middleware('semester')->group(function () {
        Route::get('/munaqosyah', [MunaqosyahController::class, 'guruIndex'])->name('munaqosyah.index');
        Route::get('/munaqosyah/approval-rekap', [MunaqosyahController::class, 'guruApprovalRekap'])->name('munaqosyah.approval-rekap');
        Route::get('/munaqosyah/{munaqosyah}', [MunaqosyahController::class, 'guruDetail'])->name('munaqosyah.detail');
        Route::post('/munaqosyah/{munaqosyah}/daftarkan', [MunaqosyahController::class, 'guruDaftarkan'])->name('munaqosyah.daftarkan');
        Route::post('/munaqosyah/{munaqosyah}/nilai', [MunaqosyahController::class, 'guruNilaiBatch'])->name('munaqosyah.nilai');
        Route::post('/munaqosyah/{munaqosyah}/lulus-semua', [MunaqosyahController::class, 'guruLulusSemua'])->name('munaqosyah.lulussemua');
        Route::post('/munaqosyah/{munaqosyah}/tidak-lulus-semua', [MunaqosyahController::class, 'guruTidakLulusSemua'])->name('munaqosyah.tidaklulussemua');
    });
    // ===== TAHFIDZ (guru kelas Tahfidz) =====
    Route::middleware('semester')->group(function () {
        Route::get('/tahfidz', [TahfidzController::class, 'guruIndex'])->name('tahfidz.index');
        Route::post('/tahfidz/hafalan', [TahfidzController::class, 'guruStore'])->name('tahfidz.hafalan.store');
    });

    // ===== PENILAIAN RAPOR INTERNAL (guru: pilih penilaian → pilih kelas → isi nilai per indikator) =====
    Route::get('/penilaian-rapor', [PenilaianRaporInternalController::class, 'guruIndex'])->name('penilaian-rapor.index');
    Route::get('/penilaian-rapor/{penilaian}/kelas', [PenilaianRaporInternalController::class, 'guruPilihKelas'])->name('penilaian-rapor.pilih-kelas');
    Route::get('/penilaian-rapor/{penilaian}/kelas/{kelasId}/nilai', [PenilaianRaporInternalController::class, 'guruIsiNilai'])->name('penilaian-rapor.isi-nilai');
    Route::post('/penilaian-rapor/{penilaian}/kelas/{kelasId}/nilai', [PenilaianRaporInternalController::class, 'guruSimpanNilai'])->name('penilaian-rapor.simpan-nilai');
    Route::get('/rekap-nilai-rapor', [PenilaianRaporInternalController::class, 'guruRekapNilai'])->name('penilaian-rapor.rekap');
});

// ==================== SISWA ====================
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/nilai', [SiswaDashboardController::class, 'nilai'])->name('nilai');
    Route::get('/perpindahan', [SiswaDashboardController::class, 'perpindahan'])->name('perpindahan');
    Route::get('/track-record', [TrackRecordController::class, 'siswaIndex'])->name('track-record');
    Route::get('/track-record/{siswa}', [TrackRecordController::class, 'detail'])->name('track-record.detail');
    Route::get('/munaqosyah', [MunaqosyahController::class, 'siswaIndex'])->name('munaqosyah');
    Route::get('/rapor', [RaporController::class, 'pdfRaporSiswaSendiri'])->name('rapor');
    Route::get('/hafalan', [SiswaDashboardController::class, 'hafalan'])->name('hafalan');
});

// ════════════════════════════════════════════
// LARAVEL PULSE — Monitoring (protected by Gate)
// ════════════════════════════════════════════
// Akses via: /pulse (production) atau /pulse?pulse_token=<token>
// Generate token: php artisan pulse:token
// ════════════════════════════════════════════
