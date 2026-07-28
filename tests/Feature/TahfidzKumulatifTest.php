<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\HafalanTahfidz;
use App\Models\JuzSurat;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahfidzKumulatifTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semesterLama;

    private Semester $semesterBaru;

    private Kelas $kelasTahfidz;

    private Siswa $siswa;

    private Surat $suratJuz1;

    private Surat $suratJuz30;

    protected function setUp(): void
    {
        parent::setUp();

        // Tahun ajaran & semester
        TahunAjaran::create([
            'nama' => '2024/2025',
            'tanggal_mulai' => '2024-07-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'ditutup',
        ]);

        TahunAjaran::create([
            'nama' => '2025/2026',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2026-06-30',
            'status' => 'aktif',
        ]);

        $this->semesterLama = Semester::create([
            'tahun_ajaran' => '2024/2025',
            'jenis' => 'ganjil',
            'tanggal_mulai' => '2024-07-01',
            'tanggal_selesai' => '2024-12-31',
            'is_aktif' => false,
            'status' => 'ditutup',
        ]);

        $this->semesterBaru = Semester::create([
            'tahun_ajaran' => '2025/2026',
            'jenis' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
            'status' => 'aktif',
        ]);

        // Kelas reguler (FK untuk siswa)
        KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);

        // Guru & kelas Tahfidz
        $guru = GuruTartil::create([
            'nama' => 'Ust. Test',
            'nip' => 'GT001',
            'email' => 'test@tartil.id',
            'no_hp' => '081000000001',
            'jenis_kelamin' => 'L',
            'is_aktif' => true,
        ]);

        $this->kelasTahfidz = Kelas::create([
            'nama' => 'Tahfidz',
            'jenis' => 'Tahfidz',
            'mata_pelajaran' => 'Tahfidz',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id,
            'status' => 'aktif',
        ]);

        // Siswa
        $this->siswa = Siswa::create([
            'nis' => '2526001',
            'nama' => 'Siswa A',
            'no_hp' => '081111111111',
            'password' => bcrypt('password'),
            'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $this->kelasTahfidz->id,
            'tanggal_masuk' => '2024-07-01',
            'status' => 'aktif',
        ]);

        // Surat & mapping JuzSurat
        $this->suratJuz1 = Surat::create([
            'nama' => 'Al-Fatihah',
            'nama_latin' => 'Al-Fatihah',
            'jumlah_ayat' => 7,
            'jenis' => 'Makkiyah',
            'urutan' => 1,
        ]);

        $this->suratJuz30 = Surat::create([
            'nama' => 'An-Nas',
            'nama_latin' => 'An-Nas',
            'jumlah_ayat' => 6,
            'jenis' => 'Makkiyah',
            'urutan' => 114,
        ]);

        JuzSurat::create([
            'juz' => 1,
            'surat_id' => $this->suratJuz1->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'total_ayat' => 7,
        ]);

        JuzSurat::create([
            'juz' => 30,
            'surat_id' => $this->suratJuz30->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 6,
            'total_ayat' => 6,
        ]);
    }

    private function buatHafalan(int $juz, Surat $surat, int $mulai, ?int $selesai, Semester $semester, string $tanggal): HafalanTahfidz
    {
        return HafalanTahfidz::create([
            'siswa_id' => $this->siswa->id,
            'semester_id' => $semester->id,
            'kelas_id' => $this->kelasTahfidz->id,
            'surat_id' => $surat->id,
            'juz' => $juz,
            'ayat_mulai' => $mulai,
            'ayat_selesai' => $selesai,
            'status' => 'hafal',
            'kualitas' => 'mumtaz',
            'tanggal_hafalan' => $tanggal,
        ]);
    }

    public function test_hafalan_semester_lama_masih_dihitung_di_semester_baru(): void
    {
        $this->buatHafalan(1, $this->suratJuz1, 1, 7, $this->semesterLama, '2024-09-15');

        $this->assertEquals(1, HafalanTahfidz::totalJuzHafal($this->siswa->id));
        $this->assertEquals(1, HafalanTahfidz::totalJuzHafalSampaiSemester($this->siswa->id, $this->semesterLama));
        $this->assertEquals(1, HafalanTahfidz::totalJuzHafalSampaiSemester($this->siswa->id, $this->semesterBaru));

        $this->assertTrue(HafalanTahfidz::punyaHafalanSebelumSemester($this->siswa->id, $this->semesterBaru));

        $persenLama = HafalanTahfidz::hitungPersentaseJuzSampaiSemester($this->siswa->id, 1, $this->semesterLama);
        $this->assertEquals(100.0, $persenLama['persentase']);
        $this->assertEquals('selesai', $persenLama['status']);

        $persenBaru = HafalanTahfidz::hitungPersentaseJuzSampaiSemester($this->siswa->id, 1, $this->semesterBaru);
        $this->assertEquals(100.0, $persenBaru['persentase']);
        $this->assertEquals('selesai', $persenBaru['status']);
    }

    public function test_hafalan_tidak_berurutan_didukung(): void
    {
        $this->buatHafalan(30, $this->suratJuz30, 1, 6, $this->semesterBaru, '2025-08-10');

        $persen = HafalanTahfidz::hitungPersentaseJuzSampaiSemester($this->siswa->id, 30, $this->semesterBaru);
        $this->assertEquals(100.0, $persen['persentase']);
        $this->assertEquals('selesai', $persen['status']);

        $this->assertEquals(1, HafalanTahfidz::totalJuzHafalSampaiSemester($this->siswa->id, $this->semesterBaru));
    }

    public function test_rekap_juz_per_kelas_memisahkan_total_sudah_hafal_dan_tuntas(): void
    {
        $this->buatHafalan(1, $this->suratJuz1, 1, 7, $this->semesterLama, '2024-09-15');

        $rekap = HafalanTahfidz::rekapJuzPerKelas($this->kelasTahfidz->id, $this->semesterBaru);

        $juz1 = collect($rekap)->firstWhere('juz', 1);
        $this->assertEquals(1, $juz1['totalSiswa']);
        $this->assertEquals(1, $juz1['sudahHafal']);
        $this->assertEquals(1, $juz1['tuntas']);
        $this->assertCount(1, $juz1['siswaTuntas']);
        $this->assertEquals($this->siswa->id, $juz1['siswaTuntas'][0]['id']);

        $juz30 = collect($rekap)->firstWhere('juz', 30);
        $this->assertEquals(1, $juz30['totalSiswa']);
        $this->assertEquals(0, $juz30['sudahHafal']);
        $this->assertEquals(0, $juz30['tuntas']);
        $this->assertCount(0, $juz30['siswaTuntas']);
    }

    public function test_rekap_kelas_mendeteksi_hafalan_dari_semester_sebelumnya(): void
    {
        $this->buatHafalan(1, $this->suratJuz1, 1, 7, $this->semesterLama, '2024-09-15');

        $rekap = HafalanTahfidz::rekapPerKelasSampaiSemester($this->kelasTahfidz->id, $this->semesterBaru);

        $this->assertCount(1, $rekap['perSiswa']);
        $this->assertEquals(1, $rekap['perSiswa'][0]['juzHafal']);
        $this->assertTrue($rekap['perSiswa'][0]['punyaHafalanLama']);
    }

    public function test_data_hafalan_tidak_hilang_saat_semester_ditutup(): void
    {
        $this->buatHafalan(1, $this->suratJuz1, 1, 7, $this->semesterLama, '2024-09-15');
        $this->assertEquals(1, HafalanTahfidz::count());

        // Simulasi penutupan semester: hafalan tidak boleh dihapus/diubah
        $this->semesterLama->update(['status' => 'ditutup', 'is_aktif' => false]);
        $this->semesterBaru->update(['is_aktif' => true, 'status' => 'aktif']);

        $this->assertEquals(1, HafalanTahfidz::count());
        $this->assertEquals(1, HafalanTahfidz::totalJuzHafalSampaiSemester($this->siswa->id, $this->semesterBaru));
    }

    public function test_snapshot_rekap_tahfidz_semester_tidak_menghapus_hafalan_asli(): void
    {
        $this->buatHafalan(1, $this->suratJuz1, 1, 7, $this->semesterLama, '2024-09-15');

        // Simulasi snapshot yang dibuat saat semester ditutup
        \DB::table('rekap_tahfidz_semesters')->insert([
            'semester_id' => $this->semesterLama->id,
            'kelas_id' => $this->kelasTahfidz->id,
            'siswa_id' => $this->siswa->id,
            'total_juz_dihafal' => 1,
            'total_entry' => 1,
            'juz_terakhir' => 1,
            'surat_terakhir' => 'Al-Fatihah',
            'kualitas_rata' => 'mumtaz',
            'detail_juz' => json_encode([]),
            'locked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertEquals(1, HafalanTahfidz::count());
        $this->assertEquals(1, \DB::table('rekap_tahfidz_semesters')->count());

        // Meski snapshot tetap, hafalan asli harus tetap bisa dihitung di semester berikutnya
        $this->assertEquals(1, HafalanTahfidz::totalJuzHafalSampaiSemester($this->siswa->id, $this->semesterBaru));
    }
}
