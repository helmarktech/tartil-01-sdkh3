<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\MunaqosyahPendaftaran;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\UjianMunaqosyah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MunaqosyahEditBatalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $userGuru;

    private Semester $semester;

    private UjianMunaqosyah $ujian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin', 'email' => 'admin@tartil.id',
            'password' => bcrypt('admin123'), 'role' => 'admin',
        ]);

        $guru = GuruTartil::create([
            'nama' => 'Ust. Test', 'nip' => 'GT001', 'email' => 'guru@tartil.id',
            'no_hp' => '081000000001', 'jenis_kelamin' => 'L', 'is_aktif' => true,
        ]);

        $this->userGuru = User::create([
            'nama' => 'Ust. Test', 'email' => 'guru@tartil.id',
            'password' => bcrypt('guru123'), 'role' => 'guru', 'guru_id' => $guru->id,
        ]);

        $this->semester = Semester::create([
            'tahun_ajaran' => '2026/2027', 'jenis' => 'ganjil',
            'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true, 'status' => 'aktif',
        ]);

        $this->ujian = UjianMunaqosyah::create([
            'nama' => 'Munaqosyah Test', 'tingkat' => 'unit',
            'tanggal_ujian' => '2026-11-18', 'semester_id' => $this->semester->id,
            'status' => 'disetujui', 'approved_by' => $this->admin->id, 'approved_at' => now(),
        ]);
    }

    private function dataEdit(array $override = []): array
    {
        return array_merge([
            'nama' => 'Munaqosyah Revisi',
            'tingkat' => 'yayasan',
            'tanggal_ujian' => '2026-11-20',
            'semester_id' => $this->semester->id,
            'catatan' => 'Jadwal dimajukan',
        ], $override);
    }

    public function test_admin_dapat_mengedit_detail_ujian(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.munaqosyah.update', $this->ujian), $this->dataEdit())
            ->assertRedirect(route('admin.munaqosyah.index'))
            ->assertSessionHas('success');

        $this->ujian->refresh();
        $this->assertSame('Munaqosyah Revisi', $this->ujian->nama);
        $this->assertSame('yayasan', $this->ujian->tingkat);
        $this->assertSame('2026-11-20', $this->ujian->tanggal_ujian->format('Y-m-d'));
        $this->assertSame('Jadwal dimajukan', $this->ujian->catatan);
    }

    public function test_admin_dapat_membatalkan_ujian_dengan_alasan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.batal', $this->ujian), ['catatan' => 'Bentrok agenda yayasan'])
            ->assertRedirect(route('admin.munaqosyah.index'))
            ->assertSessionHas('success');

        $this->ujian->refresh();
        $this->assertSame('dibatalkan', $this->ujian->status);
        $this->assertSame('Bentrok agenda yayasan', $this->ujian->catatan);
        $this->assertSame($this->admin->id, $this->ujian->approved_by);
        $this->assertNotNull($this->ujian->approved_at);
    }

    public function test_ujian_selesai_tidak_bisa_diedit_atau_dibatalkan(): void
    {
        $this->ujian->update(['status' => 'selesai']);

        $this->actingAs($this->admin)
            ->put(route('admin.munaqosyah.update', $this->ujian), $this->dataEdit())
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.batal', $this->ujian))
            ->assertSessionHas('error');

        $this->ujian->refresh();
        $this->assertSame('selesai', $this->ujian->status);
        $this->assertSame('Munaqosyah Test', $this->ujian->nama);
    }

    public function test_ujian_dibatalkan_tidak_bisa_dibatalkan_ulang(): void
    {
        $this->ujian->update(['status' => 'dibatalkan']);

        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.batal', $this->ujian))
            ->assertSessionHas('error');
    }

    public function test_edit_ditolak_jika_validasi_gagal(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.munaqosyah.update', $this->ujian), $this->dataEdit(['tingkat' => 'tidak-valid']))
            ->assertSessionHasErrors('tingkat');

        $this->actingAs($this->admin)
            ->put(route('admin.munaqosyah.update', $this->ujian), $this->dataEdit(['nama' => '']))
            ->assertSessionHasErrors('nama');
    }

    public function test_guru_tidak_bisa_edit_atau_batal(): void
    {
        $this->actingAs($this->userGuru)
            ->put(route('admin.munaqosyah.update', $this->ujian), $this->dataEdit())
            ->assertForbidden();

        $this->actingAs($this->userGuru)
            ->post(route('admin.munaqosyah.batal', $this->ujian))
            ->assertForbidden();
    }

    public function test_halaman_index_menampilkan_tombol_edit_dan_batal(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.munaqosyah.index'))
            ->assertOk()
            ->assertSee('formEditUjian')
            ->assertSee('Batalkan');
    }

    public function test_halaman_index_menyembunyikan_tombol_untuk_ujian_dibatalkan(): void
    {
        $this->ujian->update(['status' => 'dibatalkan']);

        $this->actingAs($this->admin)
            ->get(route('admin.munaqosyah.index'))
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertDontSee('batal-'.$this->ujian->id);
    }

    public function test_ujian_dibatalkan_tidak_muncul_di_riwayat_siswa(): void
    {
        $kelasReguler = KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);
        $kelas = Kelas::create([
            'nama' => 'Tartil A', 'jenis' => 'Tartil', 'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '09:00:00',
            'status' => 'aktif',
        ]);
        $siswa = Siswa::create([
            'nis' => '2526001', 'nama' => 'Siswa A', 'no_hp' => '081111111111',
            'password' => bcrypt('password'), 'jenis_kelamin' => 'L',
            'kelas_reguler_id' => $kelasReguler->id, 'kelas_tartil_id' => $kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->toDateString(), 'status' => 'aktif',
        ]);

        $ujianLain = UjianMunaqosyah::create([
            'nama' => 'Munaqosyah Kedua', 'tingkat' => 'unit',
            'tanggal_ujian' => '2026-11-25', 'semester_id' => $this->semester->id,
            'status' => 'disetujui', 'approved_by' => $this->admin->id, 'approved_at' => now(),
        ]);

        foreach ([$this->ujian, $ujianLain] as $u) {
            MunaqosyahPendaftaran::create([
                'munaqosyah_id' => $u->id, 'siswa_id' => $siswa->id,
                'diajukan_oleh' => $this->admin->id, 'pengaju_type' => 'admin',
                'status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR,
            ]);
        }

        // Batalkan ujian pertama (urutan penting: guard web dulu, baru guard siswa)
        $this->actingAs($this->admin)
            ->post(route('admin.munaqosyah.batal', $this->ujian))
            ->assertSessionHas('success');
        $this->assertSame('dibatalkan', $this->ujian->fresh()->status);

        // Halaman siswa: ujian dibatalkan hilang, ujian lain tetap tampil
        // (assertSee nama ujian pertama tidak dipakai karena nama muncul di flash message pembatalan)
        $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.munaqosyah'))
            ->assertViewHas('riwayat', fn ($riwayat) => $riwayat->total() === 1
                && $riwayat->first()->munaqosyah->id === $ujianLain->id)
            ->assertSee('Munaqosyah Kedua');
    }
}
