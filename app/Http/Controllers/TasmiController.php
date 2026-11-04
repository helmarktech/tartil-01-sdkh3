<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TasmiComment;
use App\Models\TasmiLike;
use App\Models\TasmiVideo;
use App\Models\User;
use App\Notifications\GuruNotifikasi;
use App\Notifications\SiswaNotifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TasmiController extends Controller
{
    // ═══════════════════════════════════════════════
    // ADMIN: KELOLA DOKUMENTASI TASMI'
    // ═══════════════════════════════════════════════
    public function adminIndex()
    {
        $videos = TasmiVideo::with('siswas')
            ->withCount(['likes', 'comments', 'siswas'])
            ->latest()
            ->paginate(20);

        // Rekap detail: jumlah video & siswa per juz, beserta nama siswanya
        $rekapJuz = TasmiVideo::with('siswas:id,nama,nis')->get(['id', 'juz'])
            ->groupBy('juz')
            ->map(fn ($items, $juz) => [
                'juz' => $juz,
                'video' => $items->count(),
                'siswaUnik' => $items->flatMap->siswas->unique('id')
                    ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            ])
            ->sortKeys();

        $statistik = [
            'video' => TasmiVideo::count(),
            'siswa' => DB::table('tasmi_video_siswa')->distinct()->count('siswa_id'),
            'ditonton' => (int) TasmiVideo::sum('jumlah_ditonton'),
            'juz' => TasmiVideo::distinct()->count('juz'),
        ];

        return view('admin.tasmi.index', compact('videos', 'rekapJuz', 'statistik'));
    }

    public function adminCreate()
    {
        $siswas = Siswa::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nis']);
        $tasmi = null;
        $terpilih = [];

        return view('admin.tasmi.form', compact('siswas', 'tasmi', 'terpilih'));
    }

    public function adminStore(Request $request)
    {
        $data = $this->validasiTasmi($request);
        $siswaIds = $data['siswa_ids'];
        unset($data['siswa_ids']);

        $tasmi = TasmiVideo::create([
            ...$data,
            'diupload_oleh' => auth()->id(),
        ]);
        $tasmi->siswas()->sync($siswaIds);
        $namaSiswa = $tasmi->ringkasNamaSiswa();

        // Kabari SEMUA siswa aktif: ada dokumentasi tasmi' baru
        // (database + push bila tersedia). Chunk agar hemat memori.
        Siswa::where('status', 'aktif')->select('id')->chunk(200, function ($chunk) use ($tasmi, $namaSiswa) {
            $notifikasi = new SiswaNotifikasi(
                tipe: 'tasmi',
                judul: 'Dokumentasi Tasmi\' Baru 🎬',
                pesan: "Ada dokumentasi tasmi' baru: {$tasmi->judul} — {$namaSiswa} (Juz {$tasmi->juz})",
                url: route('siswa.tasmi.show', $tasmi->id),
            );
            Siswa::whereIn('id', $chunk->pluck('id'))->get()->each->notify($notifikasi);
        });

        // Kabari semua guru (channel database; push khusus siswa)
        $notifikasiGuru = new GuruNotifikasi(
            tipe: 'tasmi',
            judul: 'Dokumentasi Tasmi\' Baru 🎬',
            pesan: "Admin mengunggah dokumentasi tasmi': {$tasmi->judul} — {$namaSiswa} (Juz {$tasmi->juz})",
            url: route('guru.tasmi.show', $tasmi->id),
        );
        User::where('role', 'guru')->get()->each->notify($notifikasiGuru);

        return redirect()->route('admin.tasmi.index')
            ->with('success', 'Dokumentasi tasmi\' berhasil ditambahkan.');
    }

    public function adminEdit(TasmiVideo $tasmi)
    {
        $siswas = Siswa::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nis']);
        $terpilih = $tasmi->siswas()->pluck('siswas.id')->all();

        return view('admin.tasmi.form', compact('siswas', 'tasmi', 'terpilih'));
    }

    public function adminUpdate(Request $request, TasmiVideo $tasmi)
    {
        $data = $this->validasiTasmi($request);
        $siswaIds = $data['siswa_ids'];
        unset($data['siswa_ids']);

        $tasmi->update($data);
        $tasmi->siswas()->sync($siswaIds);

        return redirect()->route('admin.tasmi.index')
            ->with('success', 'Dokumentasi tasmi\' berhasil diperbarui.');
    }

    public function adminDestroy(TasmiVideo $tasmi)
    {
        $tasmi->delete(); // likes & comments ikut terhapus via cascade FK

        return redirect()->route('admin.tasmi.index')
            ->with('success', 'Dokumentasi tasmi\' berhasil dihapus.');
    }

    /**
     * Validasi input + parsing YouTube ID. Gagal parse → validation error.
     */
    private function validasiTasmi(Request $request): array
    {
        $data = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|distinct|exists:siswas,id',
            'juz' => 'required|integer|min:1|max:30',
            'judul' => 'required|string|max:150',
            'youtube_url' => 'required|string|max:255',
            'tanggal_tasmi' => 'nullable|date',
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        $youtubeId = TasmiVideo::extractYoutubeId($data['youtube_url']);
        if (! $youtubeId) {
            throw ValidationException::withMessages([
                'youtube_url' => 'Link YouTube tidak valid. Gunakan format watch, youtu.be, atau shorts.',
            ]);
        }

        $data['youtube_id'] = $youtubeId;

        return $data;
    }

    // ═══════════════════════════════════════════════
    // GALERI & DETAIL (semua peran: admin, guru, siswa)
    // ═══════════════════════════════════════════════
    public function galeri(Request $request)
    {
        $query = TasmiVideo::with('siswas')
            ->withCount(['likes', 'comments'])
            ->latest();

        if ($request->filled('juz')) {
            $query->where('juz', (int) $request->juz);
        }
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('judul', 'like', "%{$cari}%")
                    ->orWhereHas('siswas', fn ($s) => $s->where('nama', 'like', "%{$cari}%"));
            });
        }

        $videos = $query->paginate(12)->withQueryString();

        return view('tasmi.galeri', ['videos' => $videos, 'rp' => $this->routePrefix()]);
    }

    public function show(TasmiVideo $tasmi)
    {
        // Hitung penayangan: sekali per video per sesi pengguna
        // (refresh berulang tidak menggelembungkan angka).
        // incrementQuietly: penayangan bukan peristiwa audit — jangan
        // memicu trait Auditable untuk setiap kali ditonton.
        $sessionKey = "tasmi_ditonton.{$tasmi->id}";
        if (! session()->has($sessionKey)) {
            $tasmi->incrementQuietly('jumlah_ditonton');
            session()->put($sessionKey, true);
            $tasmi->refresh();
        }

        $tasmi->load(['siswas', 'comments.pengomentar'])->loadCount('likes');

        $aktor = TasmiLike::aktor();
        $sudahLike = $tasmi->isLikedOleh($aktor);
        $bisaModerasi = $this->userWeb()?->isAdmin() ?? false;
        $rp = $this->routePrefix();
        // Link share selalu mengarah ke jalur siswa (target share umumnya orang tua/siswa)
        $shareWa = 'https://wa.me/?text='.rawurlencode(
            "Dokumentasi Tasmi' {$tasmi->ringkasNamaSiswa()} (Juz {$tasmi->juz}): {$tasmi->judul}\n"
            .route('siswa.tasmi.show', $tasmi)
        );

        return view('tasmi.show', compact('tasmi', 'aktor', 'sudahLike', 'bisaModerasi', 'shareWa', 'rp'));
    }

    /**
     * Prefix nama route sesuai guard/peran aktor saat ini,
     * agar view bersama menghasilkan link ke grup route yang benar.
     */
    private function routePrefix(): string
    {
        if (auth('siswa')->check()) {
            return 'siswa.tasmi';
        }

        return $this->userWeb()?->isAdmin() ? 'admin.tasmi' : 'guru.tasmi';
    }

    /**
     * Ambil user admin/guru hanya jika aktor guard default memang instance User.
     * Di request siswa, middleware auth:siswa mengalihkan guard default ke
     * guard siswa (shouldUse), sehingga auth()->user() bisa berisi model Siswa.
     */
    private function userWeb(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    // ═══════════════════════════════════════════════
    // INTERAKSI: LIKE & KOMENTAR (multi-guard)
    // ═══════════════════════════════════════════════
    public function toggleLike(Request $request, TasmiVideo $tasmi)
    {
        $aktor = TasmiLike::aktor();
        if (! $aktor) {
            abort(403);
        }

        $where = [
            'tasmi_video_id' => $tasmi->id,
            'penyuka_type' => $aktor->getMorphClass(),
            'penyuka_id' => $aktor->getKey(),
        ];

        $like = TasmiLike::where($where)->first();
        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            TasmiLike::create($where);
            $liked = true;
        }

        // Permintaan AJAX: balas JSON agar halaman (dan video yang sedang
        // diputar) tidak perlu di-refresh
        if ($request->expectsJson()) {
            return response()->json([
                'liked' => $liked,
                'count' => $tasmi->likes()->count(),
            ]);
        }

        return back();
    }

    public function storeComment(Request $request, TasmiVideo $tasmi)
    {
        $aktor = TasmiLike::aktor();
        if (! $aktor) {
            abort(403);
        }

        $data = $request->validate([
            'isi' => 'required|string|max:500',
        ]);

        $komentar = TasmiComment::create([
            'tasmi_video_id' => $tasmi->id,
            'pengomentar_type' => $aktor->getMorphClass(),
            'pengomentar_id' => $aktor->getKey(),
            'isi' => $data['isi'],
        ]);

        // Permintaan AJAX: balas data komentar agar halaman (dan video yang
        // sedang diputar) tidak perlu di-refresh
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $komentar->id,
                'nama' => $komentar->nama_pengomentar,
                'peran' => $komentar->peran_pengomentar,
                'inisial' => strtoupper(mb_substr($komentar->nama_pengomentar, 0, 1)),
                'waktu' => $komentar->created_at->diffForHumans(),
                'isi' => $komentar->isi,
                'count' => $tasmi->comments()->count(),
                'hapus_url' => route($this->routePrefix().'.komentar.hapus', $komentar),
            ]);
        }

        return back();
    }

    public function destroyComment(TasmiComment $comment)
    {
        $aktor = TasmiLike::aktor();
        $isAdmin = $this->userWeb()?->isAdmin() ?? false;

        // Admin (pemilik konten) boleh moderasi semua komentar;
        // pengguna lain hanya boleh menghapus komentarnya sendiri.
        if (! $isAdmin && ! $comment->milikAktor($aktor)) {
            abort(403);
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Komentar dihapus.');
    }
}
