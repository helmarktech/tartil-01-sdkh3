<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\HafalanTahfidz;
use App\Models\IndikatorPenilaian;
use App\Models\JurnalHarian;
use App\Models\JuzSurat;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\MunaqosyahPendaftaran;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use App\Models\UjianMunaqosyah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataAmanTutupSemesterTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester1;

    private Semester $semester2;

    private Kelas $kelas;

    private Siswa $siswa;

    private GuruTartil $guru;

    protected function setUp(): void
    {
        parent::setUp();

        TahunAjaran::create([
            'nama' => '2024/2025',
            'tanggal_mulai' => '2024-07-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'ditutup',
        ]);

        $this->semester1 = Semester::create([
            'tahun_ajaran' => '2024/2025',
            'jenis' => 'ganjil',
            'tanggal_mulai' => '2024-07-01',
            'tanggal_selesai' => '2024-12-31',
            'is_aktif' => true,
            'status' => 'aktif',
        ]);

        $this->semester2 = Semester::create([
            'tahun_ajaran' => '2024/2025',
            'jenis' => 'genap',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-06-30',
            'is_aktif' => false,
            'status' => 'nonaktif',
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
            'tanggal_masuk' => '2024-07-01',
            'status' => 'aktif',
        ]);

        $surat = Surat::create([
            'nama' => 'Al-Fatihah',
            'nama_latin' => 'Al-Fatihah',
            'jumlah_ayat' => 7,
            'jenis' => 'Makkiyah',
            'urutan' => 1,
        ]);

        JuzSurat::create([
            'juz' => 1,
            'surat_id' => $surat->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'total_ayat' => 7,
        ]);

        IndikatorPenilaian::create([
            'jenis_kelas' => 'Tartil',
            'nama_indikator' => 'Kelancaran Bacaan',
            'urutan' => 1,
            'is_default' => true,
        ]);
    }

    public function test_semua_data_inti_tidak_hilang_saat_semester_ditutup(): void
    {
        // 1. Jurnal harian
        JurnalHarian::create([
            'semester_id' => $this->semester1->id,
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'siswa_id' => $this->siswa->id,
            'tanggal' => '2024-09-15',
            'kehadiran' => 1,
            'penilaian' => 'B',
        ]);

        // 2. Munaqosyah
        $ujian = UjianMunaqosyah::create([
            'nama' => 'Munaqosyah Unit 1',
            'tingkat' => 'unit',
            'tanggal_ujian' => '2024-10-10',
            'semester_id' => $this->semester1->id,
            'status' => 'selesai',
        ]);

        MunaqosyahPendaftaran::create([
            'munaqosyah_id' => $ujian->id,
            'siswa_id' => $this->siswa->id,
            'status' => MunaqosyahPendaftaran::STATUS_LULUS,
            'nilai' => 85,
        ]);

        // 3. Penilaian rapor internal
        $penilaian = PenilaianRaporInternal::create([
            'nama' => 'Penilaian Ganjil 2024/2025',
            'semester_id' => $this->semester1->id,
            'status' => 'aktif',
        ]);

        PenilaianRaporNilai::create([
            'penilaian_id' => $penilaian->id,
            'siswa_id' => $this->siswa->id,
            'indikator_penilaian_id' => IndikatorPenilaian::first()->id,
            'nilai' => 80,
            'diisi_oleh' => $this->guru->id,
            'tanggal_diisi' => now(),
        ]);

        // 4. Tahfidz
        HafalanTahfidz::create([
            'siswa_id' => $this->siswa->id,
            'semester_id' => $this->semester1->id,
            'kelas_id' => $this->kelas->id,
            'surat_id' => Surat::first()->id,
            'juz' => 1,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'status' => 'hafal',
            'kualitas' => 'mumtaz',
            'tanggal_hafalan' => '2024-09-15',
        ]);

        // Rekam jumlah awal
        $counts = [
            'jurnal' => JurnalHarian::count(),
            'munaqosyah' => UjianMunaqosyah::count(),
            'munaqosyah_pendaftaran' => MunaqosyahPendaftaran::count(),
            'penilaian' => PenilaianRaporInternal::count(),
            'penilaian_nilai' => PenilaianRaporNilai::count(),
            'hafalan' => HafalanTahfidz::count(),
        ];

        // Simulasi penutupan semester 1 dan aktifkan semester 2
        $this->semester1->update(['status' => 'ditutup', 'is_aktif' => false]);
        $this->semester2->update(['status' => 'aktif', 'is_aktif' => true]);

        // Data transaksional harus tetap ada
        $this->assertEquals($counts['jurnal'], JurnalHarian::count());
        $this->assertEquals($counts['munaqosyah'], UjianMunaqosyah::count());
        $this->assertEquals($counts['munaqosyah_pendaftaran'], MunaqosyahPendaftaran::count());
        $this->assertEquals($counts['penilaian'], PenilaianRaporInternal::count());
        $this->assertEquals($counts['penilaian_nilai'], PenilaianRaporNilai::count());
        $this->assertEquals($counts['hafalan'], HafalanTahfidz::count());

        // Data masih bisa diquery berdasarkan semester lama
        $this->assertEquals(1, JurnalHarian::where('semester_id', $this->semester1->id)->count());
        $this->assertEquals(1, UjianMunaqosyah::where('semester_id', $this->semester1->id)->count());
        $this->assertEquals(1, PenilaianRaporInternal::where('semester_id', $this->semester1->id)->count());

        // Snapshot per modul bisa ditulis tanpa menghapus data asli
        \DB::table('rekap_jurnal_semesters')->insert([
            'semester_id' => $this->semester1->id,
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $this->siswa->id,
            'guru_id' => $this->guru->id,
            'total_hari' => 1,
            'count_b' => 1,
            'r2_harian' => 100,
            'persentase_b' => 100,
            'locked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('rekap_munaqosyah_semesters')->insert([
            'semester_id' => $this->semester1->id,
            'siswa_id' => $this->siswa->id,
            'total_ujian' => 1,
            'total_lulus' => 1,
            'locked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('rekap_tahfidz_semesters')->insert([
            'semester_id' => $this->semester1->id,
            'siswa_id' => $this->siswa->id,
            'total_juz_dihafal' => 1,
            'total_entry' => 1,
            'locked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertEquals(1, \DB::table('rekap_jurnal_semesters')->count());
        $this->assertEquals(1, \DB::table('rekap_munaqosyah_semesters')->count());
        $this->assertEquals(1, \DB::table('rekap_tahfidz_semesters')->count());

        // Setelah snapshot, data asli tetap utuh
        $this->assertEquals($counts['jurnal'], JurnalHarian::count());
        $this->assertEquals($counts['hafalan'], HafalanTahfidz::count());
    }
}
