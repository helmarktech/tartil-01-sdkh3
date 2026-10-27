<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\IndikatorPenilaian;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\RekapR2Akhir;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan baru: nilai rapor (r2_akhir) MURNI dari Penilaian Rapor Internal.
 * Nilai B/C/K jurnal harian (r2_harian) hanya info rekap mengaji dan
 * tidak boleh memengaruhi nilai rapor.
 */
class RaporMurniPenilaianTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Siswa $siswa;

    private GuruTartil $guru;

    private User $userGuru;

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

        $this->userGuru = User::create([
            'nama' => 'Ust. Test',
            'email' => 'test@tartil.id',
            'password' => bcrypt('guru123'),
            'role' => 'guru',
            'guru_id' => $this->guru->id,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'Tartil A',
            'jenis' => 'Tartil',
            'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $this->guru->id,
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
    }

    /**
     * Buat penilaian internal aktif + 2 indikator + nilai untuk siswa.
     *
     * @return array{0: PenilaianRaporInternal, 1: array<int, int>} [penilaian, map indikator_id => nilai]
     */
    private function buatPenilaianDenganNilai(array $nilais = [80, 90]): array
    {
        $penilaian = PenilaianRaporInternal::create([
            'nama' => 'Penilaian Semester Ganjil',
            'semester_id' => $this->semester->id,
            'status' => 'aktif',
        ]);

        $map = [];
        foreach (array_values($nilais) as $i => $nilai) {
            $indikator = IndikatorPenilaian::create([
                'jenis_kelas' => 'Tartil',
                'nama_indikator' => 'Indikator '.($i + 1),
                'urutan' => $i + 1,
                'is_default' => true,
            ]);
            PenilaianRaporNilai::create([
                'penilaian_id' => $penilaian->id,
                'siswa_id' => $this->siswa->id,
                'indikator_penilaian_id' => $indikator->id,
                'nilai' => $nilai,
                'diisi_oleh' => $this->guru->id,
                'tanggal_diisi' => now(),
            ]);
            $map[$indikator->id] = $nilai;
        }

        return [$penilaian, $map];
    }

    private function buatJurnal(string $penilaian): void
    {
        JurnalHarian::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'siswa_id' => $this->siswa->id,
            'tanggal' => now()->startOfWeek(Carbon::MONDAY)->subWeek()->toDateString(),
            'penilaian' => $penilaian,
        ]);
    }

    public function test_nilai_rapor_murni_dari_penilaian_internal(): void
    {
        [$penilaian, $map] = $this->buatPenilaianDenganNilai([80, 90]);

        // Jurnal harian B (r2_harian = 100) — tidak boleh memengaruhi nilai rapor
        $this->buatJurnal('B');

        $rekap = RekapR2Akhir::calculateAndSave($this->siswa, $this->semester, $this->kelas);

        $this->assertEquals(100, $rekap->r2_harian); // info mengaji tetap dihitung
        $this->assertEquals(85, $rekap->r2_penilaian); // (80+90)/2
        $this->assertEquals(85, $rekap->r2_akhir); // murni penilaian, bukan (100+85)/2 = 93
    }

    public function test_recalc_rapor_murni_memperbaiki_snapshot_semester_ditutup(): void
    {
        $this->semester->update(['status' => 'ditutup', 'is_aktif' => false]);

        // Snapshot lama dengan rumus 50:50: (100 + 80) / 2 = 90
        $rekapLama = RekapR2Akhir::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $this->siswa->id,
            'r2_harian' => 100,
            'r2_penilaian' => 80,
            'r2_akhir' => 90,
            'jumlah_indikator' => 2,
            'jumlah_terisi' => 2,
            'is_mutasi' => false,
            'last_calculated' => now()->subDays(30),
        ]);

        $this->artisan('r2:recalc-rapor-murni', ['--force' => true])->assertSuccessful();

        $this->assertEquals(80, $rekapLama->fresh()->r2_akhir);
        $this->assertEquals(100, $rekapLama->fresh()->r2_harian); // info harian tidak diubah
    }

    public function test_recalc_tidak_menyentuh_semester_aktif(): void
    {
        // Semester masih aktif — snapshot/cache-nya tidak boleh diubah command recalc
        $rekapAktif = RekapR2Akhir::create([
            'semester_id' => $this->semester->id,
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $this->siswa->id,
            'r2_harian' => 100,
            'r2_penilaian' => 80,
            'r2_akhir' => 90,
            'jumlah_indikator' => 2,
            'jumlah_terisi' => 2,
            'is_mutasi' => false,
            'last_calculated' => now(),
        ]);

        // Tidak ada snapshot semester ditutup → command menolak jalan (exit 1)
        // dan cache semester aktif tetap utuh.
        $this->artisan('r2:recalc-rapor-murni', ['--force' => true])->assertExitCode(1);

        $this->assertEquals(90, $rekapAktif->fresh()->r2_akhir);
    }

    public function test_guru_simpan_nilai_menginvalidate_cache_r2(): void
    {
        [$penilaian, $map] = $this->buatPenilaianDenganNilai([80, 90]);

        // Cache R2 terbentuk
        RekapR2Akhir::calculateAndSave($this->siswa, $this->semester, $this->kelas);
        $this->assertDatabaseCount('rekap_r2_akhirs', 1);

        // Guru menyimpan ulang nilai → cache harus dibuang agar tidak basi
        $payload = ['nilai' => [$this->siswa->id => []]];
        foreach ($map as $indikatorId => $nilai) {
            $payload['nilai'][$this->siswa->id][$indikatorId] = 95;
        }

        $this->actingAs($this->userGuru)
            ->post(route('guru.penilaian-rapor.simpan-nilai', [$penilaian->id, $this->kelas->id]), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('rekap_r2_akhirs', 0);

        // Hitung ulang: nilai rapor mengikuti nilai terbaru (95, 95) → 95
        $rekap = RekapR2Akhir::calculateAndSave($this->siswa, $this->semester, $this->kelas);
        $this->assertEquals(95, $rekap->r2_akhir);
    }
}
