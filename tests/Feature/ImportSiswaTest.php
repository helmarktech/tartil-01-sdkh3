<?php

namespace Tests\Feature;

use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ImportSiswaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Administrator',
            'email' => 'admin@tartil.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        Semester::create([
            'tahun_ajaran' => '2026/2027',
            'jenis' => 'ganjil',
            'tanggal_mulai' => now()->subMonths(3),
            'tanggal_selesai' => now()->addMonths(3),
            'is_aktif' => true,
        ]);
    }

    private function createExcelFile(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');

        $path = storage_path('app/testing-import-siswa.xlsx');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        return $path;
    }

    public function test_import_siswa_berhasil_dengan_kolom_wajib_baru(): void
    {
        $this->actingAs($this->admin);

        $path = $this->createExcelFile([
            ['NIS', 'NAMA', 'JENIS_KELAMIN', 'NO_HP', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_MASUK', 'TANGGAL_LAHIR', 'TEMPAT_LAHIR', 'NAMA_AYAH'],
            ['2026001', 'Ahmad Fauzi', 'L', '08123456789', '1A', '1', 'A', '2026-07-31', '2015-01-15', 'Surabaya', 'Bapak Fauzi'],
            ['2026002', 'Siti Aminah', 'P', '08123456788', '1A', '1', 'A', '2026-07-31', '2015-03-22', 'Sidoarjo', 'Bapak Amin'],
        ]);

        $file = new UploadedFile($path, 'siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.siswa.import.proses'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.siswa.import'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('siswas', [
            'nis' => '2026001',
            'nama' => 'Ahmad Fauzi',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2015-01-15',
            'tempat_lahir' => 'Surabaya',
            'nama_ayah' => 'Bapak Fauzi',
        ]);

        @unlink($path);
    }

    public function test_import_siswa_gagal_jika_kolom_wajib_baru_kosong(): void
    {
        $this->actingAs($this->admin);

        $path = $this->createExcelFile([
            ['NIS', 'NAMA', 'JENIS_KELAMIN', 'NO_HP', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_MASUK', 'TANGGAL_LAHIR', 'TEMPAT_LAHIR', 'NAMA_AYAH'],
            ['2026001', 'Ahmad Fauzi', 'L', '08123456789', '1A', '1', 'A', '2026-07-31', '', 'Surabaya', 'Bapak Fauzi'],
        ]);

        $file = new UploadedFile($path, 'siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.siswa.import.proses'), [
            'file' => $file,
        ]);

        // Jika semua baris gagal validasi, job melempar exception sehingga
        // controller menganggap proses gagal dan redirect back dengan error.
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('siswas', ['nis' => '2026001']);

        @unlink($path);
    }

    public function test_import_siswa_gagal_jika_header_kolom_wajib_tidak_ada(): void
    {
        $this->actingAs($this->admin);

        $path = $this->createExcelFile([
            ['NIS', 'NAMA', 'JENIS_KELAMIN', 'NO_HP', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_MASUK'],
            ['2026001', 'Ahmad Fauzi', 'L', '08123456789', '1A', '1', 'A', '2026-07-31'],
        ]);

        $file = new UploadedFile($path, 'siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.siswa.import.proses'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        @unlink($path);
    }
}
