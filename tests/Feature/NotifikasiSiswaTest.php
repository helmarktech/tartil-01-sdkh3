<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\JuzSurat;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\LaporanPendampinganOrtu;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Notifications\SiswaNotifikasi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiSiswaTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Siswa $siswa;

    private User $userGuru;

    protected function setUp(): void
    {
        parent::setUp();

        // Semester aktif yang mencakup hari ini (syarat middleware semester)
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

        $this->userGuru = User::create([
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
    }

    public function test_guru_input_jurnal_mengirim_notifikasi_tipe_jurnal(): void
    {
        $this->actingAs($this->userGuru);

        $response = $this->postJson(route('guru.jurnal.batch-store'), [
            'tanggal' => Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            'kelas_id' => $this->kelas->id,
            'entries' => [
                ['siswa_id' => $this->siswa->id, 'penilaian' => 'B'],
            ],
        ]);

        $response->assertOk();

        $notifikasi = $this->siswa->notifications()->get();
        $this->assertCount(1, $notifikasi);
        $this->assertEquals('jurnal', $notifikasi->first()->data['tipe']);
        $this->assertEquals('Jurnal Harian Diperbarui', $notifikasi->first()->data['judul']);
        $this->assertEquals('/siswa/nilai', $notifikasi->first()->data['url']);
    }

    public function test_guru_tambah_setoran_hafalan_mengirim_notifikasi_tipe_hafalan(): void
    {
        $this->actingAs($this->userGuru);

        $response = $this->post(route('guru.tahfidz.hafalan.store'), [
            'siswa_id' => $this->siswa->id,
            'semester_id' => $this->semester->id,
            'surat_id' => Surat::first()->id,
            'juz' => 1,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'status' => 'hafal',
            'kualitas' => 'mumtaz',
            'tanggal_hafalan' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        $notifikasi = $this->siswa->notifications()->get();
        $this->assertCount(1, $notifikasi);
        $this->assertEquals('hafalan', $notifikasi->first()->data['tipe']);
        $this->assertEquals('Setoran Hafalan Ditambahkan', $notifikasi->first()->data['judul']);
        $this->assertEquals('/siswa/hafalan', $notifikasi->first()->data['url']);
    }

    public function test_guru_konfirmasi_pendampingan_mengirim_notifikasi_tipe_pendampingan(): void
    {
        // Siswa buat laporan pendampingan
        $this->actingAs($this->siswa, 'siswa');
        $this->post(route('siswa.pendampingan-ortu.store'), [
            'jenis' => 'tadarus',
            'surat_id' => Surat::first()->id,
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Setoran tadarus bersama orang tua.',
        ])->assertRedirect();

        $laporan = LaporanPendampinganOrtu::first();
        $this->assertNotNull($laporan);

        // Guru konfirmasi laporan
        $this->actingAs($this->userGuru);
        $this->post(route('guru.pendampingan-ortu.konfirmasi', $laporan))
            ->assertRedirect();

        $laporan->refresh();
        $this->assertEquals('telah_dikonfirmasi', $laporan->status);

        $notifikasi = $this->siswa->notifications()->get();
        $this->assertCount(1, $notifikasi);
        $this->assertEquals('pendampingan', $notifikasi->first()->data['tipe']);
        $this->assertEquals('Pendampingan Dikonfirmasi', $notifikasi->first()->data['judul']);
        $this->assertEquals('/siswa/pendampingan-ortu', $notifikasi->first()->data['url']);
    }

    public function test_siswa_subscribe_push_menyimpan_subscription(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $endpoint = 'https://push.example.com/sub/abc123';

        $this->postJson(route('siswa.notifikasi.subscribe'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'BPdiWfjWR0bDIBLk1zXWf0oXq0dPdFnX6Gv8Qz8xQ1c',
                'auth' => 'tEstAuThSeCrEt123',
            ],
        ])->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => $endpoint,
            'subscribable_id' => $this->siswa->id,
            'subscribable_type' => Siswa::class,
            'public_key' => 'BPdiWfjWR0bDIBLk1zXWf0oXq0dPdFnX6Gv8Qz8xQ1c',
            'auth_token' => 'tEstAuThSeCrEt123',
        ]);

        // Unsubscribe menghapus kembali subscription
        $this->deleteJson(route('siswa.notifikasi.unsubscribe'), [
            'endpoint' => $endpoint,
        ])->assertOk();

        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => $endpoint]);
    }

    public function test_siswa_buka_halaman_notifikasi_dan_unread_count(): void
    {
        $this->siswa->notify(new SiswaNotifikasi('jurnal', 'Judul Tes', 'Pesan tes', '/siswa/nilai'));

        $this->actingAs($this->siswa, 'siswa');

        // Halaman daftar notifikasi ter-render dan tidak menandai baca otomatis
        $this->get(route('siswa.notifikasi'))
            ->assertOk()
            ->assertSee('Judul Tes')
            ->assertSee('Baru');
        $this->assertEquals(1, $this->siswa->unreadNotifications()->count());

        // Endpoint JSON unread count
        $this->getJson(route('siswa.notifikasi.unread'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('latest.0.tipe', 'jurnal')
            ->assertJsonPath('latest.0.judul', 'Judul Tes');

        // Read-all menandai semua dibaca
        $this->postJson(route('siswa.notifikasi.read-all'))->assertOk();
        $this->assertEquals(0, $this->siswa->unreadNotifications()->count());
    }
}
