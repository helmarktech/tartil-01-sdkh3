<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\IndikatorPenilaian;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\MunaqosyahApproval;
use App\Models\MunaqosyahPendaftaran;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\RekapR2Akhir;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\UjianMunaqosyah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/**
 * Pembatasan akses guru ke siswa kelas tartilnya sendiri:
 * - Detail/nilai/lulus-semua munaqosyah hanya siswa kelas sendiri.
 * - Import nilai munaqosyah & rapor internal via Excel (jalur guru).
 */
class GuruImportDanAksesKelasTest extends TestCase
{
    use RefreshDatabase;

    private User $userGuruA;

    private User $userGuruB;

    private GuruTartil $guruA;

    private GuruTartil $guruB;

    private Kelas $kelasA;

    private Kelas $kelasB;

    private Siswa $siswaA1;

    private Siswa $siswaB1;

    private Semester $semester;

    private UjianMunaqosyah $ujian;

    private MunaqosyahPendaftaran $pendaftaranA;

    private MunaqosyahPendaftaran $pendaftaranB;

    private PenilaianRaporInternal $penilaian;

    private IndikatorPenilaian $indikator1;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->semester = $semester;

        KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);

        // Dua guru dengan kelas tartil masing-masing
        [$this->guruA, $this->userGuruA, $this->kelasA] = $this->buatGuruKelas('Ust. A', 'gurua@tartil.id', 'Tartil A');
        [$this->guruB, $this->userGuruB, $this->kelasB] = $this->buatGuruKelas('Ust. B', 'gurub@tartil.id', 'Tartil B');

        $this->siswaA1 = $this->buatSiswa('2526001', 'Siswa A1', $this->kelasA);
        $this->siswaB1 = $this->buatSiswa('2526002', 'Siswa B1', $this->kelasB);

        $this->ujian = UjianMunaqosyah::create([
            'nama' => 'Munaqosyah Unit',
            'tingkat' => 'unit',
            'tanggal_ujian' => now()->addWeek()->toDateString(),
            'semester_id' => $semester->id,
            'status' => 'disetujui',
        ]);

        // Keduanya didaftarkan admin (tanpa approval pending)
        $this->pendaftaranA = MunaqosyahPendaftaran::create([
            'munaqosyah_id' => $this->ujian->id,
            'siswa_id' => $this->siswaA1->id,
            'pengaju_type' => 'admin',
            'status' => 'T',
        ]);
        $this->pendaftaranB = MunaqosyahPendaftaran::create([
            'munaqosyah_id' => $this->ujian->id,
            'siswa_id' => $this->siswaB1->id,
            'pengaju_type' => 'admin',
            'status' => 'T',
        ]);

        $this->penilaian = PenilaianRaporInternal::create([
            'nama' => 'Penilaian Ganjil',
            'semester_id' => $semester->id,
            'status' => 'aktif',
        ]);

        $this->indikator1 = IndikatorPenilaian::create([
            'jenis_kelas' => 'Tartil', 'nama_indikator' => 'Tajwid', 'urutan' => 1, 'is_default' => true,
        ]);
    }

    private function buatGuruKelas(string $nama, string $email, string $namaKelas): array
    {
        $guru = GuruTartil::create([
            'nama' => $nama, 'nip' => 'GT'.rand(1000, 9999), 'email' => $email,
            'no_hp' => '081'.rand(10000000, 99999999), 'jenis_kelamin' => 'L', 'is_aktif' => true,
        ]);
        $user = User::create([
            'nama' => $nama, 'email' => $email,
            'password' => bcrypt('guru123'), 'role' => 'guru', 'guru_id' => $guru->id,
        ]);
        $kelas = Kelas::create([
            'nama' => $namaKelas, 'jenis' => 'Tartil', 'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id, 'status' => 'aktif',
        ]);

        return [$guru, $user, $kelas];
    }

    private function buatSiswa(string $nis, string $nama, Kelas $kelas): Siswa
    {
        return Siswa::create([
            'nis' => $nis, 'nama' => $nama, 'no_hp' => '081'.substr($nis, -8),
            'password' => bcrypt('password'), 'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->toDateString(), 'status' => 'aktif',
        ]);
    }

    private function buatFileExcel(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        return new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function parseExcelResponse($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'template').'.xlsx';
        file_put_contents($path, $response->streamedContent());

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    // ==================== AKSES KELAS: MUNAQOSYAH ====================

    public function test_guru_detail_munaqosyah_hanya_menampilkan_siswa_kelas_sendiri(): void
    {
        $this->actingAs($this->userGuruA)
            ->get(route('guru.munaqosyah.detail', $this->ujian))
            ->assertOk()
            ->assertSee('Siswa A1')
            ->assertDontSee('Siswa B1');
    }

    public function test_guru_nilai_batch_menolak_pendaftaran_kelas_lain(): void
    {
        $this->actingAs($this->userGuruA)
            ->post(route('guru.munaqosyah.nilai', $this->ujian), [
                'nilai' => [
                    ['pendaftaran_id' => $this->pendaftaranB->id, 'status' => 'L', 'nilai' => 90, 'catatan' => null],
                ],
            ])
            ->assertSessionHas('error');

        $this->assertSame('T', $this->pendaftaranB->fresh()->status);
    }

    public function test_guru_lulus_semua_hanya_mempengaruhi_kelas_sendiri(): void
    {
        $this->actingAs($this->userGuruA)
            ->post(route('guru.munaqosyah.lulussemua', $this->ujian))
            ->assertSessionHas('success');

        $this->assertSame('L', $this->pendaftaranA->fresh()->status);
        $this->assertSame('T', $this->pendaftaranB->fresh()->status);
    }

    // ==================== IMPORT MUNAQOSYAH (GURU) ====================

    public function test_guru_template_import_munaqosyah_hanya_berisi_kelas_sendiri(): void
    {
        $response = $this->actingAs($this->userGuruA)
            ->get(route('guru.munaqosyah.import.template', $this->ujian))
            ->assertOk();

        $rows = array_slice($this->parseExcelResponse($response), 4);

        $this->assertCount(1, $rows);
        $this->assertEquals($this->pendaftaranA->id, $rows[0][0]);
        $this->assertSame('Siswa A1', $rows[0][2]);
    }

    public function test_guru_import_munaqosyah_melewati_pendaftaran_kelas_lain(): void
    {
        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI MUNAQOSYAH'],
            ['Ujian Test'],
            ['Petunjuk'],
            ['ID Pendaftaran', 'NIS', 'Nama Siswa', 'Kelas Tartil', 'Status (T/L/TL)', 'Nilai (0-100)', 'Catatan'],
            [$this->pendaftaranA->id, '2526001', 'Siswa A1', 'Tartil A', 'L', 88, null],
            [$this->pendaftaranB->id, '2526002', 'Siswa B1', 'Tartil B', 'L', 70, null], // bukan kelas guru → dilewati
        ]);

        $this->actingAs($this->userGuruA)
            ->post(route('guru.munaqosyah.import.proses', $this->ujian), ['file' => $file])
            ->assertRedirect(route('guru.munaqosyah.detail', $this->ujian))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 1);

        $this->pendaftaranA->refresh();
        $this->assertSame('L', $this->pendaftaranA->status);
        $this->assertSame(88, $this->pendaftaranA->nilai);

        $this->assertSame('T', $this->pendaftaranB->fresh()->status);
    }

    public function test_guru_import_munaqosyah_menolak_peserta_approval_pending(): void
    {
        MunaqosyahApproval::create(['pendaftaran_id' => $this->pendaftaranA->id, 'status' => 'pending']);

        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI MUNAQOSYAH'],
            ['Ujian Test'],
            ['Petunjuk'],
            ['ID Pendaftaran', 'NIS', 'Nama Siswa', 'Kelas Tartil', 'Status (T/L/TL)', 'Nilai (0-100)', 'Catatan'],
            [$this->pendaftaranA->id, '2526001', 'Siswa A1', 'Tartil A', 'L', 88, null],
        ]);

        $this->actingAs($this->userGuruA)
            ->post(route('guru.munaqosyah.import.proses', $this->ujian), ['file' => $file])
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 1);

        $this->assertSame('T', $this->pendaftaranA->fresh()->status);
    }

    // ==================== IMPORT RAPOR INTERNAL (GURU) ====================

    public function test_guru_template_rapor_menolak_kelas_guru_lain(): void
    {
        $this->actingAs($this->userGuruA)
            ->get(route('guru.penilaian-rapor.import.template', [$this->penilaian->id, $this->kelasB->id]))
            ->assertSessionHas('error');
    }

    public function test_guru_import_rapor_berhasil_dan_invalidate_cache_r2(): void
    {
        RekapR2Akhir::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $this->kelasA->id,
            'siswa_id' => $this->siswaA1->id,
            'r2_harian' => 100,
            'r2_penilaian' => 50,
            'r2_akhir' => 50,
            'jumlah_indikator' => 1,
            'jumlah_terisi' => 1,
            'is_mutasi' => false,
            'last_calculated' => now(),
        ]);

        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI RAPOR TARTIL'],
            ['Penilaian Test'],
            ['Petunjuk'],
            ['ID Siswa', 'NIS', 'Nama Siswa', 'Tajwid (ID:'.$this->indikator1->id.')'],
            [$this->siswaA1->id, '2526001', 'Siswa A1', 85],
        ]);

        $this->actingAs($this->userGuruA)
            ->post(route('guru.penilaian-rapor.import.proses', [$this->penilaian->id, $this->kelasA->id]), ['file' => $file])
            ->assertRedirect(route('guru.penilaian-rapor.isi-nilai', [$this->penilaian->id, $this->kelasA->id]))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 0);

        // Nilai tersimpan dengan diisi_oleh = guru yang mengimport
        $nilai = PenilaianRaporNilai::where('penilaian_id', $this->penilaian->id)
            ->where('siswa_id', $this->siswaA1->id)
            ->where('indikator_penilaian_id', $this->indikator1->id)
            ->first();
        $this->assertSame(85, $nilai->nilai);
        $this->assertSame($this->guruA->id, $nilai->diisi_oleh);

        // Cache R2 dibuang
        $this->assertDatabaseMissing('rekap_r2_akhirs', ['siswa_id' => $this->siswaA1->id]);
    }
}
