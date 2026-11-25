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
 * Fitur import nilai via Excel:
 * - Munaqosyah: template prefill peserta terdaftar + import status/nilai/catatan.
 * - Penilaian rapor: template prefill siswa per kelas tartil + import nilai per indikator.
 */
class ImportNilaiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Semester $semester;

    private Kelas $kelas;

    private Siswa $siswaA;

    private Siswa $siswaB;

    private UjianMunaqosyah $ujian;

    private MunaqosyahPendaftaran $pendaftaranA;

    private MunaqosyahPendaftaran $pendaftaranB;

    private PenilaianRaporInternal $penilaian;

    private IndikatorPenilaian $indikator1;

    private IndikatorPenilaian $indikator2;

    protected function setUp(): void
    {
        parent::setUp();

        TahunAjaran::create([
            'nama' => now()->year.'/'.(now()->year + 1),
            'tanggal_mulai' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonths(9)->endOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $this->semester = Semester::create([
            'tahun_ajaran' => now()->year.'/'.(now()->year + 1),
            'jenis' => 'ganjil',
            'tanggal_mulai' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'is_aktif' => true,
            'status' => 'aktif',
        ]);

        KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);

        $this->admin = User::create([
            'nama' => 'Admin', 'email' => 'admin@tartil.id',
            'password' => bcrypt('admin123'), 'role' => 'admin',
        ]);

        $guru = GuruTartil::create([
            'nama' => 'Ust. Test', 'nip' => 'GT001', 'email' => 'guru@tartil.id',
            'no_hp' => '081000000001', 'jenis_kelamin' => 'L', 'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'Tartil A', 'jenis' => 'Tartil', 'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id, 'status' => 'aktif',
        ]);

        $this->siswaA = $this->buatSiswa('2526001', 'Siswa A');
        $this->siswaB = $this->buatSiswa('2526002', 'Siswa B');

        // Ujian munaqosyah tingkat pesantren + 2 peserta:
        // A didaftarkan admin (tanpa approval), B didaftarkan guru (approval pending)
        $this->ujian = UjianMunaqosyah::create([
            'nama' => 'Munaqosyah Akhir Semester',
            'tingkat' => 'pesantren',
            'tanggal_ujian' => now()->addWeek()->toDateString(),
            'semester_id' => $this->semester->id,
            'status' => 'disetujui',
        ]);

        $this->pendaftaranA = MunaqosyahPendaftaran::create([
            'munaqosyah_id' => $this->ujian->id,
            'siswa_id' => $this->siswaA->id,
            'diajukan_oleh' => $this->admin->id,
            'pengaju_type' => 'admin',
            'status' => 'T',
        ]);

        $this->pendaftaranB = MunaqosyahPendaftaran::create([
            'munaqosyah_id' => $this->ujian->id,
            'siswa_id' => $this->siswaB->id,
            'diajukan_oleh' => $this->admin->id,
            'pengaju_type' => 'guru',
            'status' => 'T',
        ]);
        MunaqosyahApproval::create([
            'pendaftaran_id' => $this->pendaftaranB->id,
            'status' => 'pending',
        ]);

        // Penilaian rapor + 2 indikator Tartil
        $this->penilaian = PenilaianRaporInternal::create([
            'nama' => 'Penilaian Semester Ganjil',
            'semester_id' => $this->semester->id,
            'status' => 'aktif',
        ]);

        $this->indikator1 = IndikatorPenilaian::create([
            'jenis_kelas' => 'Tartil', 'nama_indikator' => 'Tajwid', 'urutan' => 1, 'is_default' => true,
        ]);
        $this->indikator2 = IndikatorPenilaian::create([
            'jenis_kelas' => 'Tartil', 'nama_indikator' => 'Kelancaran', 'urutan' => 2, 'is_default' => true,
        ]);
    }

    private function buatSiswa(string $nis, string $nama): Siswa
    {
        return Siswa::create([
            'nis' => $nis, 'nama' => $nama, 'no_hp' => '081'.substr($nis, -8),
            'password' => bcrypt('password'), 'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $this->kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->toDateString(), 'status' => 'aktif',
        ]);
    }

    /**
     * Buat file Excel upload dari array baris (0-based).
     */
    private function buatFileExcel(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        return new UploadedFile(
            $path,
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Parse konten xlsx hasil download (StreamedResponse) menjadi array baris.
     */
    private function parseExcelResponse($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'template').'.xlsx';
        file_put_contents($path, $response->streamedContent());

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    // ==================== MUNAQOSYAH ====================

    public function test_template_munaqosyah_berisi_peserta_terdaftar(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.munaqosyah.import.template', $this->ujian))
            ->assertOk();

        $rows = $this->parseExcelResponse($response);

        // Header di baris ke-4
        $this->assertSame('ID Pendaftaran', $rows[3][0]);
        $this->assertSame('Status (T/L/TL)', $rows[3][4]);

        // Seluruh peserta terdaftar masuk template (prefill id, nis, nama, status)
        $dataRows = array_slice($rows, 4);
        $this->assertCount(2, $dataRows);
        $this->assertEquals($this->pendaftaranA->id, $dataRows[0][0]);
        $this->assertSame('Siswa A', $dataRows[0][2]);
        $this->assertSame('T', $dataRows[0][4]);
        $this->assertEquals($this->pendaftaranB->id, $dataRows[1][0]);
    }

    public function test_import_nilai_munaqosyah_berhasil_dan_pending_ditolak(): void
    {
        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI MUNAQOSYAH'],
            ['Ujian Test'],
            ['Petunjuk'],
            ['ID Pendaftaran', 'NIS', 'Nama Siswa', 'Kelas Tartil', 'Status (T/L/TL)', 'Nilai (0-100)', 'Catatan'],
            [$this->pendaftaranA->id, '2526001', 'Siswa A', 'Tartil A', 'L', 90, 'Baik'],
            [$this->pendaftaranB->id, '2526002', 'Siswa B', 'Tartil A', 'L', 80, null], // approval pending → dilewati
            [999999, '0000', 'Hantu', '-', 'L', 70, null], // ID tidak dikenal → dilewati
            [$this->pendaftaranA->id, '2526001', 'Siswa A', 'Tartil A', 'X', 90, null], // status invalid → dilewati
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.import.proses', $this->ujian), ['file' => $file])
            ->assertRedirect(route('admin.munaqosyah.detail', $this->ujian))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 3);

        // Peserta approved ternilai
        $this->pendaftaranA->refresh();
        $this->assertSame('L', $this->pendaftaranA->status);
        $this->assertSame(90, $this->pendaftaranA->nilai);
        $this->assertSame('Baik', $this->pendaftaranA->catatan);

        // Peserta pending approval tidak tersentuh
        $this->pendaftaranB->refresh();
        $this->assertSame('T', $this->pendaftaranB->status);
        $this->assertNull($this->pendaftaranB->nilai);
    }

    public function test_import_munaqosyah_menolak_file_bukan_template(): void
    {
        $file = $this->buatFileExcel([
            ['NIS', 'NAMA'],
            ['2526001', 'Siswa A'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.import.proses', $this->ujian), ['file' => $file])
            ->assertSessionHas('error');

        $this->assertSame('T', $this->pendaftaranA->fresh()->status);
    }

    // ==================== PENILAIAN RAPOR ====================

    public function test_template_rapor_berisi_siswa_dan_kolom_indikator(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.penilaian-rapor-internal.import.template', [$this->penilaian, $this->kelas]))
            ->assertOk();

        $rows = $this->parseExcelResponse($response);

        // Header: kolom identitas + kolom indikator dengan ID
        $this->assertSame('ID Siswa', $rows[3][0]);
        $this->assertSame('Tajwid (ID:'.$this->indikator1->id.')', $rows[3][3]);
        $this->assertSame('Kelancaran (ID:'.$this->indikator2->id.')', $rows[3][4]);

        // Seluruh siswa aktif kelas masuk template
        $dataRows = array_slice($rows, 4);
        $this->assertCount(2, $dataRows);
        $this->assertEquals($this->siswaA->id, $dataRows[0][0]);
        $this->assertSame('Siswa A', $dataRows[0][2]);
        $this->assertEquals($this->siswaB->id, $dataRows[1][0]);
    }

    public function test_import_nilai_rapor_berhasil_dan_invalidate_cache_r2(): void
    {
        // Cache R2 terbentuk sebelum import — harus dibuang setelah import
        RekapR2Akhir::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $this->siswaA->id,
            'r2_harian' => 100,
            'r2_penilaian' => 50,
            'r2_akhir' => 50,
            'jumlah_indikator' => 2,
            'jumlah_terisi' => 2,
            'is_mutasi' => false,
            'last_calculated' => now(),
        ]);

        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI RAPOR TARTIL'],
            ['Penilaian Test - Kelas Tartil A'],
            ['Petunjuk'],
            ['ID Siswa', 'NIS', 'Nama Siswa', 'Tajwid (ID:'.$this->indikator1->id.')', 'Kelancaran (ID:'.$this->indikator2->id.')'],
            [$this->siswaA->id, '2526001', 'Siswa A', 80, 90],
            [$this->siswaB->id, '2526002', 'Siswa B', 'abc', 70], // nilai invalid → baris dilewati
            [999999, '0000', 'Hantu', 80, 80], // siswa tidak dikenal → dilewati
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.penilaian-rapor-internal.import.proses', [$this->penilaian, $this->kelas]), ['file' => $file])
            ->assertRedirect(route('admin.penilaian-rapor-internal.import', [
                'penilaian_id' => $this->penilaian->id,
                'kelas_id' => $this->kelas->id,
            ]))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 2);

        // Nilai siswa A tersimpan per indikator
        $this->assertDatabaseHas('penilaian_rapor_nilais', [
            'penilaian_id' => $this->penilaian->id,
            'siswa_id' => $this->siswaA->id,
            'indikator_penilaian_id' => $this->indikator1->id,
            'nilai' => 80,
        ]);
        $this->assertDatabaseHas('penilaian_rapor_nilais', [
            'penilaian_id' => $this->penilaian->id,
            'siswa_id' => $this->siswaA->id,
            'indikator_penilaian_id' => $this->indikator2->id,
            'nilai' => 90,
        ]);

        // Siswa B (baris invalid) tidak tersimpan sama sekali
        $this->assertDatabaseMissing('penilaian_rapor_nilais', [
            'penilaian_id' => $this->penilaian->id,
            'siswa_id' => $this->siswaB->id,
        ]);

        // Cache R2 siswa yang terpengaruh dibuang agar nilai rapor tidak basi
        $this->assertDatabaseMissing('rekap_r2_akhirs', ['siswa_id' => $this->siswaA->id]);
    }

    public function test_import_nilai_rapor_update_nilai_yang_sudah_ada(): void
    {
        PenilaianRaporNilai::create([
            'penilaian_id' => $this->penilaian->id,
            'siswa_id' => $this->siswaA->id,
            'indikator_penilaian_id' => $this->indikator1->id,
            'nilai' => 60,
            'tanggal_diisi' => now(),
        ]);

        $file = $this->buatFileExcel([
            ['TEMPLATE IMPORT NILAI RAPOR TARTIL'],
            ['Penilaian Test - Kelas Tartil A'],
            ['Petunjuk'],
            ['ID Siswa', 'NIS', 'Nama Siswa', 'Tajwid (ID:'.$this->indikator1->id.')', 'Kelancaran (ID:'.$this->indikator2->id.')'],
            [$this->siswaA->id, '2526001', 'Siswa A', 95, null], // sel kosong tidak mengubah apa pun
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.penilaian-rapor-internal.import.proses', [$this->penilaian, $this->kelas]), ['file' => $file])
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 0);

        // Nilai lama ter-update, tidak dobel (unique penilaian+siswa+indikator)
        $this->assertSame(95, PenilaianRaporNilai::where('siswa_id', $this->siswaA->id)
            ->where('indikator_penilaian_id', $this->indikator1->id)->first()->nilai);
        $this->assertDatabaseCount('penilaian_rapor_nilais', 1);
    }

    public function test_import_rapor_menolak_file_bukan_template(): void
    {
        $file = $this->buatFileExcel([
            ['ID', 'NILAI'],
            [1, 80],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.penilaian-rapor-internal.import.proses', [$this->penilaian, $this->kelas]), ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('penilaian_rapor_nilais', 0);
    }
}
