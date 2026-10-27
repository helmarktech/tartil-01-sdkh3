<?php

namespace Tests\Feature;

use App\Models\GuruTartil;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Siswa;
use App\Models\TasmiComment;
use App\Models\TasmiLike;
use App\Models\TasmiVideo;
use App\Models\User;
use App\Notifications\SiswaNotifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TasmiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $userGuru;

    private Siswa $siswa;

    private TasmiVideo $video;

    protected function setUp(): void
    {
        parent::setUp();

        KelasReguler::create(['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A']);

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

        $kelas = Kelas::create([
            'nama' => 'Tartil A', 'jenis' => 'Tartil', 'mata_pelajaran' => 'Tartil',
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '09:00:00',
            'guru_id' => $guru->id, 'status' => 'aktif',
        ]);

        $this->siswa = Siswa::create([
            'nis' => '2526001', 'nama' => 'Siswa A', 'no_hp' => '081111111111',
            'password' => bcrypt('password'), 'jenis_kelamin' => 'L',
            'kelas_reguler_id' => KelasReguler::first()->id,
            'kelas_tartil_id' => $kelas->id,
            'tanggal_masuk' => now()->subMonths(3)->toDateString(), 'status' => 'aktif',
        ]);

        $this->video = TasmiVideo::create([
            'siswa_id' => $this->siswa->id, 'juz' => 30,
            'judul' => "Tasmi' Juz 30", 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ', 'diupload_oleh' => $this->admin->id,
        ]);
    }

    public function test_admin_store_memparse_berbagai_format_url_youtube(): void
    {
        Notification::fake();

        $formatValid = [
            'https://www.youtube.com/watch?v=abc123XYZ89' => 'abc123XYZ89',
            'https://youtu.be/abc123XYZ89' => 'abc123XYZ89',
            'https://www.youtube.com/shorts/abc123XYZ89' => 'abc123XYZ89',
            'https://www.youtube.com/embed/abc123XYZ89' => 'abc123XYZ89',
        ];

        foreach ($formatValid as $url => $expectedId) {
            $this->actingAs($this->admin)->post(route('admin.tasmi.store'), [
                'siswa_id' => $this->siswa->id,
                'juz' => 29,
                'judul' => 'Uji '.$url,
                'youtube_url' => $url,
            ])->assertRedirect(route('admin.tasmi.index'));

            $this->assertDatabaseHas('tasmi_videos', ['youtube_id' => $expectedId, 'juz' => 29]);
        }
    }

    public function test_admin_store_menolak_url_bukan_youtube(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.tasmi.store'), [
            'siswa_id' => $this->siswa->id,
            'juz' => 30,
            'judul' => 'Uji invalid',
            'youtube_url' => 'https://example.com/video',
        ])->assertSessionHasErrors('youtube_url');

        $this->assertDatabaseMissing('tasmi_videos', ['judul' => 'Uji invalid']);
    }

    public function test_admin_store_mengirim_notifikasi_ke_siswa(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.tasmi.store'), [
            'siswa_id' => $this->siswa->id,
            'juz' => 1,
            'judul' => "Tasmi' Juz 1",
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        Notification::assertSentTo($this->siswa, SiswaNotifikasi::class);
    }

    public function test_semua_peran_bisa_membuka_galeri_dan_detail(): void
    {
        // Siswa
        $this->actingAs($this->siswa, 'siswa')->get(route('siswa.tasmi.galeri'))->assertOk();
        $this->actingAs($this->siswa, 'siswa')->get(route('siswa.tasmi.show', $this->video))
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);

        // Guru
        $this->actingAs($this->userGuru)->get(route('guru.tasmi.galeri'))->assertOk();
        $this->actingAs($this->userGuru)->get(route('guru.tasmi.show', $this->video))->assertOk();

        // Admin
        $this->actingAs($this->admin)->get(route('admin.tasmi.galeri'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.tasmi.show', $this->video))->assertOk();
    }

    public function test_toggle_like_multi_guard_dan_unik_per_aktor(): void
    {
        // Siswa like
        $this->actingAs($this->siswa, 'siswa')
            ->post(route('siswa.tasmi.like', $this->video))->assertRedirect();
        $this->assertEquals(1, $this->video->likes()->count());

        // Guru like (aktor berbeda, tabel users)
        $this->actingAs($this->userGuru)
            ->post(route('guru.tasmi.like', $this->video))->assertRedirect();
        $this->assertEquals(2, $this->video->likes()->count());

        // Toggle ulang oleh siswa → unlike
        $this->actingAs($this->siswa, 'siswa')
            ->post(route('siswa.tasmi.like', $this->video))->assertRedirect();
        $this->assertEquals(1, $this->video->likes()->count());
        $this->assertDatabaseHas('tasmi_likes', [
            'tasmi_video_id' => $this->video->id,
            'penyuka_type' => (new User)->getMorphClass(),
            'penyuka_id' => $this->userGuru->id,
        ]);
    }

    public function test_komentar_moderasi_dan_kepemilikan(): void
    {
        // Siswa & guru bisa berkomentar
        $this->actingAs($this->siswa, 'siswa')
            ->post(route('siswa.tasmi.komentar', $this->video), ['isi' => 'MasyaAllah!'])->assertRedirect();
        $this->actingAs($this->userGuru)
            ->post(route('guru.tasmi.komentar', $this->video), ['isi' => 'Bagus sekali'])->assertRedirect();

        $komentarSiswa = TasmiComment::where('isi', 'MasyaAllah!')->first();
        $komentarGuru = TasmiComment::where('isi', 'Bagus sekali')->first();

        // Siswa TIDAK bisa hapus komentar guru (403)
        $this->actingAs($this->siswa, 'siswa')
            ->delete(route('siswa.tasmi.komentar.hapus', $komentarGuru))->assertForbidden();

        // Siswa bisa hapus komentarnya sendiri
        $this->actingAs($this->siswa, 'siswa')
            ->delete(route('siswa.tasmi.komentar.hapus', $komentarSiswa))->assertRedirect();
        $this->assertDatabaseMissing('tasmi_comments', ['id' => $komentarSiswa->id]);

        // Admin bisa hapus komentar siapa pun (moderasi)
        $this->actingAs($this->admin)
            ->delete(route('admin.tasmi.komentar.hapus', $komentarGuru))->assertRedirect();
        $this->assertDatabaseMissing('tasmi_comments', ['id' => $komentarGuru->id]);
    }

    public function test_penayangan_dihitung_sekali_per_sesi(): void
    {
        // Kunjungan pertama menambah penghitung
        $this->actingAs($this->siswa, 'siswa')->get(route('siswa.tasmi.show', $this->video))->assertOk();
        $this->assertEquals(1, $this->video->fresh()->jumlah_ditonton);

        // Kunjungan ulang di sesi yang sama tidak menambah
        $this->actingAs($this->siswa, 'siswa')->get(route('siswa.tasmi.show', $this->video))->assertOk();
        $this->assertEquals(1, $this->video->fresh()->jumlah_ditonton);

        // Pengguna lain (guru) dengan sesi berbeda menambah penghitung
        $this->flushSession();
        $this->actingAs($this->userGuru)->get(route('guru.tasmi.show', $this->video))->assertOk();
        $this->assertEquals(2, $this->video->fresh()->jumlah_ditonton);
    }

    public function test_landing_menampilkan_tasmi_terbaru_inframe_dan_galeri(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dokumentasi', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Tasmi&#039; Juz 30', false) // apostrof di-escape Blade
            ->assertSee('Masuk dulu di sini', false) // ajakan login untuk like/komentar
            ->assertSee('id="login"', false); // anchor ke kartu login
    }

    public function test_landing_paginasi_galeri_maksimal_10_per_halaman(): void
    {
        // Total 12 video (1 dari setUp + 11 baru) → halaman 1 berisi 10, halaman 2 berisi 2
        for ($i = 1; $i <= 11; $i++) {
            TasmiVideo::create([
                'siswa_id' => $this->siswa->id, 'juz' => 1,
                'judul' => 'Video P'.str_pad($i, 2, '0', STR_PAD_LEFT), 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'youtube_id' => 'dQw4w9WgXcQ', 'diupload_oleh' => $this->admin->id,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        // Urutan latest(): video setUp (baru), lalu P01 (terbaru dari 11) ... P11 (terlama)
        // Halaman 1: setUp + P01..P09 | Halaman 2: P10, P11
        $this->get('/')
            ->assertOk()
            ->assertSee('Video P01', false)
            ->assertDontSee('Video P11', false);

        $this->get('/?page=2')
            ->assertOk()
            ->assertSee('Video P11', false)
            ->assertDontSee('Video P01', false);
    }

    public function test_hapus_video_ikut_menghapus_like_dan_komentar(): void
    {
        TasmiLike::create([
            'tasmi_video_id' => $this->video->id,
            'penyuka_type' => (new Siswa)->getMorphClass(),
            'penyuka_id' => $this->siswa->id,
        ]);
        TasmiComment::create([
            'tasmi_video_id' => $this->video->id,
            'pengomentar_type' => (new Siswa)->getMorphClass(),
            'pengomentar_id' => $this->siswa->id,
            'isi' => 'Keren!',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.tasmi.destroy', $this->video))->assertRedirect();

        $this->assertDatabaseMissing('tasmi_videos', ['id' => $this->video->id]);
        $this->assertEquals(0, TasmiLike::count());
        $this->assertEquals(0, TasmiComment::count());
    }
}
