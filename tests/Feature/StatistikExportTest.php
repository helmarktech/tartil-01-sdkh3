<?php

namespace Tests\Feature;

use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Export statistik Excel: multi-sheet futuristik dengan sheet khusus
 * perbandingan antar tahun ajaran (maks 3 TA ke belakang, min 1 TA).
 */
class StatistikExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin', 'email' => 'admin@tartil.id',
            'password' => bcrypt('admin123'), 'role' => 'admin',
        ]);
    }

    private function buatTahunAjaran(string $nama, array $jenisSemester): void
    {
        TahunAjaran::create([
            'nama' => $nama,
            'tanggal_mulai' => $nama ? '2023-07-01' : '2023-07-01',
            'tanggal_selesai' => '2024-06-30',
            'status' => 'ditutup',
        ]);

        foreach ($jenisSemester as $jenis) {
            Semester::create([
                'tahun_ajaran' => $nama,
                'jenis' => $jenis,
                'tanggal_mulai' => $jenis === 'ganjil' ? '2023-07-01' : '2024-01-01',
                'tanggal_selesai' => $jenis === 'ganjil' ? '2023-12-31' : '2024-06-30',
                'is_aktif' => false,
                'status' => 'ditutup',
            ]);
        }
    }

    private function unduhDanParse(): array
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.statistik.export'))
            ->assertOk();

        $path = tempnam(sys_get_temp_dir(), 'statistik').'.xlsx';
        file_put_contents($path, $response->streamedContent());

        return IOFactory::load($path)->getAllSheets();
    }

    private function cariTeksDiKolomA($sheet, string $teks): bool
    {
        foreach ($sheet->toArray() as $row) {
            if (isset($row[0]) && str_contains((string) $row[0], $teks)) {
                return true;
            }
        }

        return false;
    }

    public function test_export_memuat_5_sheet_dengan_sheet_perbandingan_ta(): void
    {
        $this->buatTahunAjaran('2023/2024', ['ganjil', 'genap']);
        $this->buatTahunAjaran('2024/2025', ['ganjil']);

        $sheets = $this->unduhDanParse();
        $namaSheets = array_map(fn ($s) => $s->getTitle(), $sheets);

        $this->assertSame(
            ['Ringkasan', 'Perbandingan TA', 'Per Semester', 'Munaqosyah', 'Tahfidz'],
            $namaSheets
        );

        // Sheet Perbandingan TA: kolom metrik + per TA + kolom delta
        $perbandingan = $sheets[1]->toArray();
        $this->assertSame('Metrik', $perbandingan[3][0]);
        $this->assertSame('TA 2023/2024', $perbandingan[3][1]);
        $this->assertSame('TA 2024/2025', $perbandingan[3][2]);
        $this->assertStringStartsWith('Δ 2024/2025 vs 2023/2024', (string) $perbandingan[3][3]);

        // Metrik kunci tersedia sebagai baris
        $this->assertTrue($this->cariTeksDiKolomA($sheets[1], 'Total Siswa'));
        $this->assertTrue($this->cariTeksDiKolomA($sheets[1], 'Kelulusan Munaqosyah (%)'));

        // Dengan 2 TA, band insight otomatis tampil
        $this->assertTrue($this->cariTeksDiKolomA($sheets[1], 'INSIGHT OTOMATIS'));
    }

    public function test_export_dengan_satu_ta_tanpa_kolom_delta(): void
    {
        $this->buatTahunAjaran('2024/2025', ['ganjil']);

        $sheets = $this->unduhDanParse();
        $perbandingan = $sheets[1]->toArray();

        // Hanya 1 kolom TA, tidak ada kolom Δ maupun band insight
        $this->assertSame('Metrik', $perbandingan[3][0]);
        $this->assertSame('TA 2024/2025', $perbandingan[3][1]);
        $this->assertEmpty($perbandingan[3][2] ?? null);
        $this->assertFalse($this->cariTeksDiKolomA($sheets[1], 'INSIGHT OTOMATIS'));
    }

    public function test_export_tanpa_data_ta_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.statistik.export'))
            ->assertSessionHas('error');
    }
}
