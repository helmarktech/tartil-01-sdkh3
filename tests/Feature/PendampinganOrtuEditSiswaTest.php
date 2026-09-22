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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendampinganOrtuEditSiswaTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $siswa;

    private Siswa $siswaLain;

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

        $this->siswaLain = Siswa::create([
            'nis' => '2526002',
            'nama' => 'Siswa B',
            'no_hp' => '082222222222',
            'password' => bcrypt('password'),
            'jenis_kelamin' => 'P',
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

    private function buatLaporan(string $status = 'pengajuan_konfirmasi'): LaporanPendampinganOrtu
    {
        return LaporanPendampinganOrtu::create([
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $this->kelas->id,
            'semester_id' => Semester::first()->id,
            'guru_id' => $this->kelas->guru_id,
            'jenis' => 'tadarus',
            'surat_id' => Surat::first()->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 3,
            'is_jilid' => false,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Catatan awal',
            'status' => $status,
            'dikonfirmasi_oleh' => $status === 'telah_dikonfirmasi' ? $this->kelas->guru_id : null,
            'tanggal_konfirmasi' => $status === 'telah_dikonfirmasi' ? now() : null,
        ]);
    }

    private function payloadEdit(): array
    {
        return [
            'jenis' => 'murajaah',
            'surat_id' => Surat::first()->id,
            'ayat_mulai' => 2,
            'ayat_selesai' => 5,
            'tanggal' => now()->subDay()->toDateString(),
            'catatan' => 'Catatan hasil edit',
        ];
    }

    public function test_siswa_bisa_update_laporan_miliknya(): void
    {
        $laporan = $this->buatLaporan();

        $this->actingAs($this->siswa, 'siswa');
        $this->put(route('siswa.pendampingan-ortu.update', $laporan), $this->payloadEdit())
            ->assertRedirect(route('siswa.pendampingan-ortu.index'));

        $laporan->refresh();
        $this->assertEquals('murajaah', $laporan->jenis);
        $this->assertEquals(2, $laporan->ayat_mulai);
        $this->assertEquals(5, $laporan->ayat_selesai);
        $this->assertEquals('Catatan hasil edit', $laporan->catatan);
    }

    public function test_edit_laporan_terkonfirmasi_mereset_status_ke_pengajuan(): void
    {
        $laporan = $this->buatLaporan('telah_dikonfirmasi');

        $this->actingAs($this->siswa, 'siswa');
        $this->put(route('siswa.pendampingan-ortu.update', $laporan), $this->payloadEdit())
            ->assertRedirect(route('siswa.pendampingan-ortu.index'));

        $laporan->refresh();
        $this->assertEquals('pengajuan_konfirmasi', $laporan->status);
        $this->assertNull($laporan->dikonfirmasi_oleh);
        $this->assertNull($laporan->tanggal_konfirmasi);
    }

    public function test_siswa_tidak_bisa_edit_atau_hapus_laporan_siswa_lain(): void
    {
        $laporan = LaporanPendampinganOrtu::create([
            'siswa_id' => $this->siswaLain->id,
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->kelas->guru_id,
            'jenis' => 'tadarus',
            'surat_id' => Surat::first()->id,
            'ayat_mulai' => 1,
            'is_jilid' => false,
            'tanggal' => now()->toDateString(),
            'status' => 'pengajuan_konfirmasi',
        ]);

        $this->actingAs($this->siswa, 'siswa');

        $this->get(route('siswa.pendampingan-ortu.edit', $laporan))->assertForbidden();
        $this->put(route('siswa.pendampingan-ortu.update', $laporan), $this->payloadEdit())->assertForbidden();
        $this->delete(route('siswa.pendampingan-ortu.destroy', $laporan))->assertForbidden();

        $this->assertDatabaseHas('laporan_pendampingan_ortus', ['id' => $laporan->id]);
    }

    public function test_siswa_bisa_hapus_laporan_yang_belum_dikonfirmasi(): void
    {
        $laporan = $this->buatLaporan();

        $this->actingAs($this->siswa, 'siswa');
        $this->delete(route('siswa.pendampingan-ortu.destroy', $laporan))
            ->assertRedirect(route('siswa.pendampingan-ortu.index'));

        $this->assertDatabaseMissing('laporan_pendampingan_ortus', ['id' => $laporan->id]);
    }

    public function test_siswa_tidak_bisa_hapus_laporan_yang_sudah_dikonfirmasi(): void
    {
        $laporan = $this->buatLaporan('telah_dikonfirmasi');

        $this->actingAs($this->siswa, 'siswa');
        $this->delete(route('siswa.pendampingan-ortu.destroy', $laporan))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('laporan_pendampingan_ortus', ['id' => $laporan->id]);
    }
}
