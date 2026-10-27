<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TasmiComment;
use App\Models\TasmiLike;
use App\Models\TasmiVideo;
use App\Models\User;
use App\Notifications\SiswaNotifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TasmiController extends Controller
{
    // ═══════════════════════════════════════════════
    // ADMIN: KELOLA DOKUMENTASI TASMI'
    // ═══════════════════════════════════════════════
    public function adminIndex()
    {
        $videos = TasmiVideo::with('siswa')
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(20);

        return view('admin.tasmi.index', compact('videos'));
    }

    public function adminCreate()
    {
        $siswas = Siswa::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nis']);
        $tasmi = null;

        return view('admin.tasmi.form', compact('siswas', 'tasmi'));
    }

    public function adminStore(Request $request)
    {
        $data = $this->validasiTasmi($request);

        $tasmi = TasmiVideo::create([
            ...$data,
            'diupload_oleh' => auth()->id(),
        ]);

        // Kabari siswa yang bersangkutan (database + push bila tersedia)
        $tasmi->siswa->notify(new SiswaNotifikasi(
            tipe: 'tasmi',
            judul: 'Dokumentasi Tasmi\' Baru',
            pesan: "Dokumentasi tasmi' Anda (Juz {$tasmi->juz}) telah diunggah: {$tasmi->judul}",
            url: route('siswa.tasmi.show', $tasmi->id),
        ));

        return redirect()->route('admin.tasmi.index')
            ->with('success', 'Dokumentasi tasmi\' berhasil ditambahkan.');
    }

    public function adminEdit(TasmiVideo $tasmi)
    {
        $siswas = Siswa::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nis']);

        return view('admin.tasmi.form', compact('siswas', 'tasmi'));
    }

    public function adminUpdate(Request $request, TasmiVideo $tasmi)
    {
        $tasmi->update($this->validasiTasmi($request));

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
            'siswa_id' => 'required|exists:siswas,id',
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
        $query = TasmiVideo::with('siswa')
            ->withCount(['likes', 'comments'])
            ->latest();

        if ($request->filled('juz')) {
            $query->where('juz', (int) $request->juz);
        }
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('judul', 'like', "%{$cari}%")
                    ->orWhereHas('siswa', fn ($s) => $s->where('nama', 'like', "%{$cari}%"));
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

        $tasmi->load(['siswa', 'comments.pengomentar'])->loadCount('likes');

        $aktor = TasmiLike::aktor();
        $sudahLike = $tasmi->isLikedOleh($aktor);
        $bisaModerasi = $this->userWeb()?->isAdmin() ?? false;
        $rp = $this->routePrefix();
        // Link share selalu mengarah ke jalur siswa (target share umumnya orang tua/siswa)
        $shareWa = 'https://wa.me/?text='.rawurlencode(
            "Dokumentasi Tasmi' {$tasmi->siswa->nama} (Juz {$tasmi->juz}): {$tasmi->judul}\n"
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
    public function toggleLike(TasmiVideo $tasmi)
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
        } else {
            TasmiLike::create($where);
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

        TasmiComment::create([
            'tasmi_video_id' => $tasmi->id,
            'pengomentar_type' => $aktor->getMorphClass(),
            'pengomentar_id' => $aktor->getKey(),
            'isi' => $data['isi'],
        ]);

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

        return back()->with('success', 'Komentar dihapus.');
    }
}
