<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\JurnalSiswaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackRecordPersentaseTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Siswa $siswa;

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

        $guru = GuruTartil::create([
            'nama' => 'Ust. Test',
            'nip' => 'GT001',
            'email' => 'test@tartil.id',
            'no_hp' => '081000000001',
            'jenis_kelamin' => 'L',
            'is_aktif' => true,
        ]);

        User::create([
            'nama' => 'Ust. Test',
            'email' => 'test@tartil.id',
            'password' => bcrypt('guru123'),
            'role' => 'guru',
            'guru_id' => $guru->id,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'Tartil A',
            'jenis' => 'Tartil',
            'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id,
            'status' => 'aktif',
        ]);

        $this->siswa = Siswa::create([
            'nis' => '2526001',
            'nama' => 'Siswa A',
            'no_hp' => '081111111111',
            'password' => bcrypt('password'),
            'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $this->kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        // 4 hari aktif (Senin–Kamis pekan ini): 2 B, 1 C, 1 K
        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY);
        foreach (['B', 'C', 'K', 'B'] as $i => $penilaian) {
            JurnalHarian::create([
                'semester_id' => $this->semester->id,
                'kelas_id' => $this->kelas->id,
                'guru_id' => $guru->id,
                'siswa_id' => $this->siswa->id,
                'tanggal' => $senin->copy()->addDays($i)->toDateString(),
                'penilaian' => $penilaian,
            ]);
        }
    }

    public function test_persentase_track_record_dan_dashboard_mengikuti_ssot(): void
    {
        // SSOT: (2*2 + 1) / (4*2) = 62.5% → 63%
        $expected = JurnalSiswaService::r2Harian($this->siswa->id, $this->semester);
        $this->assertEquals(63, $expected);

        $this->actingAs($this->siswa, 'siswa');

        $this->get(route('siswa.track-record.detail', $this->siswa))
            ->assertOk()
            ->assertViewHas('rekapPerSemester', fn ($rekap) => collect($rekap)->first()['rata_rata'] === $expected);

        $this->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertViewHas('r2Harian', $expected);
    }
}
