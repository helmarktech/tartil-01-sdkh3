<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ImportGuruTest extends TestCase
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
    }

    private function createExcelFile(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');

        $path = storage_path('app/testing-import-guru.xlsx');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        return $path;
    }

    public function test_import_guru_tartil_berhasil_dan_membuat_akun_login(): void
    {
        $this->actingAs($this->admin);

        $path = $this->createExcelFile([
            ['NIP', 'NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN', 'ALAMAT'],
            ['GT001', 'Ust. Ahmad', 'ahmad@tartil.id', '08123456789', 'L', 'Jl. Mawar'],
            ['GT002', 'Ust. Siti', 'siti@tartil.id', '08123456788', 'P', 'Jl. Melati'],
        ]);

        $file = new UploadedFile($path, 'guru-tartil.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->postJson(route('admin.guru.import.proses'), [
            'file' => $file,
            'jenis' => 'tartil',
        ]);

        $response->assertRedirect(route('admin.guru.import', ['jenis' => 'tartil']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guru_tartils', ['email' => 'ahmad@tartil.id', 'nama' => 'Ust. Ahmad']);
        $this->assertDatabaseHas('guru_tartils', ['email' => 'siti@tartil.id', 'nama' => 'Ust. Siti']);
        $this->assertDatabaseHas('users', ['email' => 'ahmad@tartil.id', 'role' => 'guru']);
        $this->assertDatabaseHas('users', ['email' => 'siti@tartil.id', 'role' => 'guru']);

        $user = User::where('email', 'ahmad@tartil.id')->first();
        $this->assertTrue(Hash::check('guru123', $user->password));
        $this->assertEquals($user->guru_id, GuruTartil::where('email', 'ahmad@tartil.id')->first()->id);

        @unlink($path);
    }

    public function test_import_guru_reguler_berhasil(): void
    {
        $this->actingAs($this->admin);

        $path = $this->createExcelFile([
            ['NIP', 'NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN', 'ALAMAT'],
            ['GR001', 'Pak Budi', 'budi.reguler@tartil.id', '08123456787', 'L', 'Jl. Anggrek'],
        ]);

        $file = new UploadedFile($path, 'guru-reguler.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->postJson(route('admin.guru.import.proses'), [
            'file' => $file,
            'jenis' => 'reguler',
        ]);

        $response->assertRedirect(route('admin.guru.import', ['jenis' => 'reguler']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guru_regulers', ['email' => 'budi.reguler@tartil.id', 'nama' => 'Pak Budi']);
        $this->assertDatabaseMissing('users', ['email' => 'budi.reguler@tartil.id']);

        @unlink($path);
    }

    public function test_import_guru_menolak_email_duplikat(): void
    {
        $this->actingAs($this->admin);

        GuruTartil::create([
            'nama' => 'Ust. Lama',
            'email' => 'ahmad@tartil.id',
            'no_hp' => '08100000000',
            'jenis_kelamin' => 'L',
        ]);

        $path = $this->createExcelFile([
            ['NIP', 'NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN', 'ALAMAT'],
            ['GT001', 'Ust. Ahmad', 'ahmad@tartil.id', '08123456789', 'L', 'Jl. Mawar'],
        ]);

        $file = new UploadedFile($path, 'guru-duplikat.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->postJson(route('admin.guru.import.proses'), [
            'file' => $file,
            'jenis' => 'tartil',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');
        $response->assertSessionHas('import_errors');

        $this->assertEquals(1, GuruTartil::where('email', 'ahmad@tartil.id')->count());

        @unlink($path);
    }
}
