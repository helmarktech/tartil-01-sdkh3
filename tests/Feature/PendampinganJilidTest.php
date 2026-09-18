<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\LaporanPendampinganOrtu;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendampinganJilidTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $siswa;

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

        Semester::create([
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

        $this->userGuru = User::create([
            'nama' => 'Ust. Test',
            'email' => 'test@tartil.id',
            'password' => bcrypt('guru123'),
            'role' => 'guru',
            'guru_id' => $guru->id,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'BQ 1A',
            'jenis' => 'BQ 1',
            'mata_pelajaran' => 'Bilqolam',
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

        Surat::create([
            'nama' => 'Al-Fatihah',
            'nama_latin' => 'Al-Fatihah',
            'jumlah_ayat' => 7,
            'jenis' => 'Makkiyah',
            'urutan' => 1,
        ]);
    }

    public function test_siswa_jilid_bisa_lapor_tanpa_surat_dan_ayat(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $this->post(route('siswa.pendampingan-ortu.store'), [
            'jenis' => 'tadarus',
            'is_jilid' => '1',
            'tanggal' => now()->toDateString(),
            'catatan' => 'Membaca jilid 2 halaman 5-8.',
        ])->assertRedirect();

        $laporan = LaporanPendampinganOrtu::first();
        $this->assertNotNull($laporan);
        $this->assertTrue($laporan->is_jilid);
        $this->assertNull($laporan->surat_id);
        $this->assertNull($laporan->ayat_mulai);
        $this->assertNull($laporan->ayat_selesai);
        $this->assertEquals('Jilid / Bilqolam', $laporan->labelBacaan());
        $this->assertEquals('Bacaan jilid / bilqolam', $laporan->labelAyat());

        // Halaman konfirmasi guru menampilkan keterangan default dari sistem
        $this->actingAs($this->userGuru);
        $this->get(route('guru.pendampingan-ortu.index'))
            ->assertOk()
            ->assertSee('Jilid / Bilqolam');
    }

    public function test_tanpa_centang_jilid_surat_dan_ayat_tetap_wajib(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $this->post(route('siswa.pendampingan-ortu.store'), [
            'jenis' => 'tadarus',
            'tanggal' => now()->toDateString(),
            'catatan' => 'Tanpa surat.',
        ])->assertSessionHasErrors(['surat_id', 'ayat_mulai']);

        $this->assertEquals(0, LaporanPendampinganOrtu::count());
    }

    public function test_laporan_biasa_tidak_ternandai_jilid(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $this->post(route('siswa.pendampingan-ortu.store'), [
            'jenis' => 'murajaah',
            'surat_id' => Surat::first()->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'tanggal' => now()->toDateString(),
        ])->assertRedirect();

        $laporan = LaporanPendampinganOrtu::first();
        $this->assertFalse($laporan->is_jilid);
        $this->assertEquals('Al-Fatihah', $laporan->labelBacaan());
        $this->assertEquals('Ayat 1-7', $laporan->labelAyat());
    }
}
