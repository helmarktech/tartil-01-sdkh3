<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasLibur;
use App\Models\KelasReguler;
use App\Models\RekapJurnalSemester;
use App\Models\RekapR2Akhir;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\JurnalSiswaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memastikan hitungan hari jurnal konsisten di semua tampilan:
 * dashboard siswa, track record, snapshot tutup semester, dan R2 —
 * semuanya melalui satu sumber kebenaran (JurnalSiswaService).
 */
class KonsistensiJurnalTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Kelas $kelasLain;

    private Siswa $siswa;

    private GuruTartil $guru;

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

        $this->guru = GuruTartil::create([
            'nama' => 'Ust. Test',
            'nip' => 'GT001',
            'email' => 'test@tartil.id',
            'no_hp' => '081000000001',
            'jenis_kelamin' => 'L',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'Marhalah 1',
            'jenis' => 'Tartil',
            'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $this->guru->id,
            'status' => 'aktif',
        ]);

        $this->kelasLain = Kelas::create([
            'nama' => 'Marhalah 2',
            'jenis' => 'Tartil',
            'mata_pelajaran' => 'Tartil',
            'hari' => 'Selasa',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $this->guru->id,
            'status' => 'aktif',
        ]);

        $this->siswa = Siswa::create([
            'nis' => '2152',
            'nama' => 'Siswa Uji',
            'no_hp' => '081111111111',
            'password' => bcrypt('password'),
            'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $this->kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    private function buatJurnal(Carbon $tanggal, ?string $penilaian = 'B', ?int $kelasId = null): JurnalHarian
    {
        return JurnalHarian::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $kelasId ?? $this->kelas->id,
            'guru_id' => $this->guru->id,
            'siswa_id' => $this->siswa->id,
            'tanggal' => $tanggal->toDateString(),
            'penilaian' => $penilaian,
        ]);
    }

    /**
     * Data uji: 3 hari efektif (Sen, Sel, Rab), 1 Jumat, 1 hari libur kelas,
     * 1 tanggal yang sama tercatat juga di kelas lain (kasus pindah kelas).
     * Yang sah terhitung: Sen, Sel, Rab = 3 hari.
     */
    private function isiDataJurnal(): array
    {
        $senin = now()->startOfWeek(Carbon::MONDAY)->subWeek();
        $selasa = $senin->copy()->addDay();
        $rabu = $senin->copy()->addDays(2);
        $jumat = $senin->copy()->addDays(4);
        $kamisLibur = $senin->copy()->addDays(3);

        $this->buatJurnal($senin, 'B');
        $this->buatJurnal($selasa, 'C');
        $this->buatJurnal($rabu, 'K');
        $this->buatJurnal($jumat, 'B'); // bukan hari aktif
        $this->buatJurnal($kamisLibur, 'B'); // hari libur kelas
        $admin = User::create([
            'nama' => 'Admin Uji',
            'email' => 'admin-uji@tartil.id',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);
        KelasLibur::create([
            'kelas_id' => $this->kelas->id,
            'tanggal' => $kamisLibur->toDateString(),
            'keterangan' => 'Libur uji',
            'created_by' => $admin->id,
        ]);

        // Tanggal yang sama juga tercatat di kelas lain (pindah kelas di hari yang sama)
        $this->buatJurnal($senin, 'B', $this->kelasLain->id);

        return [$senin, $selasa, $rabu];
    }

    public function test_ssot_satu_tanggal_satu_hari_dan_filter_hari_efektif(): void
    {
        $this->isiDataJurnal();

        $ringkasan = JurnalSiswaService::ringkasan($this->siswa->id, $this->semester);

        // 6 baris jurnal_harians, tetapi hanya 3 hari sah
        $this->assertEquals(6, JurnalHarian::count());
        $this->assertEquals(3, $ringkasan['total']);
        $this->assertEquals(1, $ringkasan['b']);
        $this->assertEquals(1, $ringkasan['c']);
        $this->assertEquals(1, $ringkasan['k']);
        $this->assertEquals(3, $ringkasan['dinilai']);
    }

    public function test_dashboard_siswa_konsisten_dengan_ssot(): void
    {
        $this->isiDataJurnal();

        $response = $this->actingAs($this->siswa, 'siswa')->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalJurnal', 3);
        $response->assertViewHas('bCount', 1);
        $response->assertViewHas('cCount', 1);
        $response->assertViewHas('kCount', 1);

        // Progress bulanan berbasis poin B/C/K (B=2, C=1, K=0).
        // Data uji: Senin=B, Selasa=C, Rabu=K — bisa terbelah dua bulan
        // bila minggunya melintasi batas bulan.
        $senin = now()->startOfWeek(Carbon::MONDAY)->subWeek();
        $selasa = $senin->copy()->addDay();
        $response->assertViewHas('bulanData', function ($bulanData) use ($senin, $selasa) {
            $data = collect($bulanData)->keyBy('label');

            if ($senin->isSameMonth($selasa)) {
                $bulan = $data->get($senin->format('M Y'));

                return $bulan && (int) $bulan['pct'] === 50 && $bulan['total'] === 3;
            }

            $mSenin = $data->get($senin->format('M Y'));
            $mSelasa = $data->get($selasa->format('M Y'));

            return $mSenin && $mSelasa
                && (int) $mSenin['pct'] === 100 && $mSenin['total'] === 1
                && (int) $mSelasa['pct'] === 25 && $mSelasa['total'] === 2;
        });
    }

    public function test_track_record_konsisten_dengan_ssot(): void
    {
        $this->isiDataJurnal();

        $response = $this->actingAs($this->siswa, 'siswa')
            ->get(route('siswa.track-record.detail', ['siswa' => $this->siswa->id]));

        $response->assertOk();
        $rekap = $response->viewData('rekapPerSemester');
        $this->assertCount(1, $rekap);
        $this->assertEquals(3, $rekap[0]['total_hadir']);
        $this->assertEquals(1, $rekap[0]['count_b']);
        $this->assertEquals(1, $rekap[0]['count_c']);
        $this->assertEquals(1, $rekap[0]['count_k']);
    }

    public function test_snapshot_tutup_semester_konsisten_dengan_ssot(): void
    {
        $this->isiDataJurnal();

        $snapshot = RekapJurnalSemester::snapshot($this->siswa, $this->semester, $this->kelas);

        // Snapshot per kelas: baris di kelas lain tidak ikut; Jumat & libur tersaring
        $this->assertEquals(3, $snapshot->total_hari);
        $this->assertEquals(1, $snapshot->count_b);
        $this->assertEquals(1, $snapshot->count_c);
        $this->assertEquals(1, $snapshot->count_k);
    }

    public function test_r2_harian_konsisten_dengan_jalur_rapor(): void
    {
        $this->isiDataJurnal();

        // Poin: B=2, C=1, K=0 → (2+1+0)/(3×2) = 50
        $this->assertEquals(50, JurnalSiswaService::r2Harian($this->siswa->id, $this->semester, $this->kelas->id));

        $rekap = RekapR2Akhir::calculateAndSave($this->siswa, $this->semester, $this->kelas);
        $this->assertEquals(50, $rekap->r2_harian);
    }

    public function test_semester_ditutup_tetap_menghitung_sampai_akhir_semester(): void
    {
        $this->isiDataJurnal();

        // Tutup semester — perhitungan harus tetap membaca rentang penuh semester
        $this->semester->update(['status' => 'ditutup', 'is_aktif' => false]);

        $ringkasan = JurnalSiswaService::ringkasan($this->siswa->id, $this->semester->fresh());
        $this->assertEquals(3, $ringkasan['total']);
    }

    public function test_jurnal_sebelum_tanggal_mulai_resmi_kelas_tidak_dihitung(): void
    {
        $senin = now()->startOfWeek(Carbon::MONDAY)->subWeek();
        $selasa = $senin->copy()->addDay();
        $rabu = $senin->copy()->addDays(2);
        $this->buatJurnal($senin, 'B');
        $this->buatJurnal($selasa, 'C');
        $this->buatJurnal($rabu, 'K');

        // Kelas resmi dimulai Rabu — jurnal Senin & Selasa sebelum itu diabaikan,
        // selaras dengan monitoring admin/guru (getAwalHitungHari)
        $this->kelas->update(['tanggal_dimulai' => $rabu->toDateString()]);

        $ringkasan = JurnalSiswaService::ringkasan($this->siswa->id, $this->semester);
        $this->assertEquals(1, $ringkasan['total']);
        $this->assertEquals(1, $ringkasan['k']); // Rabu penilaian K
    }
}
