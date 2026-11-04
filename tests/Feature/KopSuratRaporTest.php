<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\KopSuratRapor;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KopSuratRaporTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@tartil.id',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_dapat_upload_logo_kiri_dan_kanan(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->post(route('admin.kop-surat-rapor.update'), [
            'judul' => 'LAPORAN HASIL BELAJAR',
            'sub_judul' => 'Program Tartil',
            'nama_sekolah' => 'SD Khadijah 3',
            'logo' => UploadedFile::fake()->image('logo-kiri.png', 100, 100),
            'logo_kanan' => UploadedFile::fake()->image('logo-kanan.png', 100, 100),
        ])->assertRedirect(route('admin.kop-surat-rapor.index'));

        $kop = KopSuratRapor::getOrCreate();
        $this->assertNotNull($kop->logo_path);
        $this->assertNotNull($kop->logo_kanan_path);
        Storage::disk('public')->assertExists($kop->logo_path);
        Storage::disk('public')->assertExists($kop->logo_kanan_path);
    }

    public function test_upload_logo_kanan_baru_menghapus_file_lama(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->post(route('admin.kop-surat-rapor.update'), [
            'judul' => 'A', 'sub_judul' => 'B', 'nama_sekolah' => 'C',
            'logo_kanan' => UploadedFile::fake()->image('lama.png'),
        ]);
        $pathLama = KopSuratRapor::getOrCreate()->logo_kanan_path;
        Storage::disk('public')->assertExists($pathLama);

        $this->post(route('admin.kop-surat-rapor.update'), [
            'judul' => 'A', 'sub_judul' => 'B', 'nama_sekolah' => 'C',
            'logo_kanan' => UploadedFile::fake()->image('baru.png'),
        ]);
        $kop = KopSuratRapor::getOrCreate();
        $this->assertNotEquals($pathLama, $kop->logo_kanan_path);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($kop->logo_kanan_path);
    }

    public function test_snapshot_semester_menduplikat_logo_kanan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('kop-surat/logo.png', 'isi-logo-kiri');
        Storage::disk('public')->put('kop-surat/logo-kanan.png', 'isi-logo-kanan');

        $kop = KopSuratRapor::getOrCreate();
        $kop->update([
            'logo_path' => 'kop-surat/logo.png',
            'logo_kanan_path' => 'kop-surat/logo-kanan.png',
        ]);

        $semester = Semester::create([
            'tahun_ajaran' => now()->year.'/'.(now()->year + 1),
            'jenis' => 'ganjil',
            'tanggal_mulai' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'is_aktif' => true,
            'status' => 'aktif',
        ]);

        $snapshot = KopSuratRapor::snapshotSemester($semester->id);

        $this->assertNotNull($snapshot->logo_kanan_path);
        $this->assertNotEquals('kop-surat/logo-kanan.png', $snapshot->logo_kanan_path);
        Storage::disk('public')->assertExists($snapshot->logo_kanan_path);
        // File asli tetap utuh
        Storage::disk('public')->assertExists('kop-surat/logo-kanan.png');
    }

    public function test_rapor_pdf_tanpa_r2_harian_dan_kesimpulan_jenjang_tartil(): void
    {
        [$siswa, $kelas, $semester] = $this->buatSiswaKelasSemester();
        $kop = KopSuratRapor::nullSafe();
        $penilaian = (object) ['semester' => $semester];

        $rekap = [
            'is_mutasi' => false,
            'tanggal_masuk_kelas_tartil' => null,
            'nilai_per_indikator' => [1 => ['nama' => 'Indikator 1', 'nilai' => 80]],
            'r2_harian' => 50,
            'r2_penilaian' => 80,
            'r2_akhir' => 75,
        ];

        // Tidak lulus: tetap di jenjang yang sama; R2 harian tidak ditampilkan
        $html = view('pdf.rapor-tartil', compact('siswa', 'penilaian', 'kelas', 'kop', 'rekap'))->render();
        $this->assertStringNotContainsString('Rekap Mengaji Harian', $html);
        $this->assertStringNotContainsString('jilid yang sama', $html);
        $this->assertStringContainsString('tetap melanjutkan pembelajaran pada jenjang pembelajaran tartil yang sama untuk pemantapan', $html);

        // Lulus: lanjut ke jenjang berikutnya
        $rekap['r2_akhir'] = 85;
        $html = view('pdf.rapor-tartil', compact('siswa', 'penilaian', 'kelas', 'kop', 'rekap'))->render();
        $this->assertStringContainsString('berhak melanjutkan pembelajaran pada jenjang pembelajaran tartil berikutnya untuk peningkatan pembelajaran siswa', $html);
    }

    public function test_rapor_pdf_menampilkan_logo_kiri_dan_kanan(): void
    {
        [$siswa, $kelas, $semester] = $this->buatSiswaKelasSemester();

        // Tulis file logo 1x1 PNG asli ke storage agar accessor base64 bekerja
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $dir = storage_path('app/public/kop-surat');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/test-kiri.png', $png);
        file_put_contents($dir.'/test-kanan.png', $png);

        try {
            $kop = KopSuratRapor::nullSafe();
            $kop->logo_path = 'kop-surat/test-kiri.png';
            $kop->logo_kanan_path = 'kop-surat/test-kanan.png';
            $penilaian = (object) ['semester' => $semester];
            $rekap = [
                'is_mutasi' => false,
                'tanggal_masuk_kelas_tartil' => null,
                'nilai_per_indikator' => [],
                'r2_harian' => 0,
                'r2_penilaian' => 80,
                'r2_akhir' => 80,
            ];

            $this->assertNotEmpty($kop->logo_kanan_base64);

            $html = view('pdf.rapor-tartil', compact('siswa', 'penilaian', 'kelas', 'kop', 'rekap'))->render();
            // Dua logo ter-render sebagai base64 (kiri & kanan)
            $this->assertEquals(2, substr_count($html, 'data:image/png;base64,'));
        } finally {
            @unlink($dir.'/test-kiri.png');
            @unlink($dir.'/test-kanan.png');
        }
    }

    public function test_rapor_siswa_memiliki_ttd_mualim_dan_kepala_sekolah(): void
    {
        [$siswa, $kelas, $semester] = $this->buatSiswaKelasSemester();
        $kop = KopSuratRapor::nullSafe();
        $rapor = [
            'r2_harian' => 0,
            'r2_penilaian' => 80,
            'jumlah_terisi' => 3,
            'jumlah_indikator' => 4,
            'r2_akhir' => 80,
            'predikat' => 'Baik',
        ];

        $html = view('pdf.rapor-siswa', compact('siswa', 'kelas', 'semester', 'rapor', 'kop'))->render();

        $this->assertStringContainsString("Mu'alim", $html);
        $this->assertStringContainsString('Kepala Sekolah', $html);
        // Nama guru tartil tampil pada blok TTD Mu'alim
        $this->assertStringContainsString('Ust. Test', $html);
    }

    private function buatSiswaKelasSemester(): array
    {
        TahunAjaran::create([
            'nama' => now()->year.'/'.(now()->year + 1),
            'tanggal_mulai' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonths(9)->endOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $semester = Semester::create([
            'tahun_ajaran' => now()->year.'/'.(now()->year + 1),
            'jenis' => 'ganjil',
            'tanggal_mulai' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'is_aktif' => true,
            'status' => 'aktif',
        ]);

        KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);

        $guru = GuruTartil::create([
            'nama' => 'Ust. Test',
            'nip' => 'GT001',
            'email' => 'test@tartil.id',
            'no_hp' => '081000000001',
            'jenis_kelamin' => 'L',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'Tartil A',
            'jenis' => 'Tartil',
            'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id,
            'status' => 'aktif',
        ]);

        $siswa = Siswa::create([
            'nis' => '2526001',
            'nama' => 'Siswa A',
            'no_hp' => '081111111111',
            'password' => bcrypt('password'),
            'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        return [$siswa, $kelas, $semester];
    }
}
