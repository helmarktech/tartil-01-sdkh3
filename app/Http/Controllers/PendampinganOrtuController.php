<?php

namespace App\Http\Controllers;

use App\Models\LaporanPendampinganOrtu;
use App\Models\Semester;
use App\Models\Surat;
use Illuminate\Http\Request;

class PendampinganOrtuController extends Controller
{
    // ==================== SISWA: INPUT & RIWAYAT ====================
    public function siswaIndex()
    {
        $siswa = auth('siswa')->user();
        $semester = Semester::aktif()->first();

        $riwayat = LaporanPendampinganOrtu::where('siswa_id', $siswa->id)
            ->with(['surat', 'guru', 'guruKonfirmasi', 'semester'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $suratList = Surat::orderBy('urutan')->get();

        return view('siswa.pendampingan-ortu.index', compact(
            'siswa', 'semester', 'riwayat', 'suratList'
        ));
    }

    public function siswaStore(Request $request)
    {
        $siswa = auth('siswa')->user();

        $validated = $request->validate([
            'jenis' => 'required|in:tadarus,murajaah',
            'surat_id' => 'required|exists:surats,id',
            'ayat_mulai' => 'required|integer|min:1',
            'ayat_selesai' => 'nullable|integer|min:1|gte:ayat_mulai',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        $kelas = $siswa->kelasTartil;
        if (! $kelas) {
            return back()->with('error', 'Anda belum tergabung di kelas tartil.');
        }

        $semester = Semester::aktif()->first();

        LaporanPendampinganOrtu::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'semester_id' => $semester?->id,
            'guru_id' => $kelas->guru_id,
            'jenis' => $validated['jenis'],
            'surat_id' => $validated['surat_id'],
            'ayat_mulai' => $validated['ayat_mulai'],
            'ayat_selesai' => $validated['ayat_selesai'],
            'tanggal' => $validated['tanggal'],
            'catatan' => $validated['catatan'],
            'status' => 'pengajuan_konfirmasi',
        ]);

        return redirect()->route('siswa.pendampingan-ortu.index')
            ->with('success', 'Laporan pendampingan berhasil dikirim. Menunggu konfirmasi guru.');
    }

    // ==================== GURU: KONFIRMASI LAPORAN ====================
    public function guruIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $status = $request->get('status', 'semua');
        $query = LaporanPendampinganOrtu::where('guru_id', $guru->id)
            ->with(['siswa', 'surat', 'semester', 'guruKonfirmasi']);

        if ($status === 'pengajuan') {
            $query->pengajuan();
        } elseif ($status === 'dikonfirmasi') {
            $query->dikonfirmasi();
        }

        $laporan = $query->orderByRaw("status = 'pengajuan_konfirmasi' DESC")
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('guru.pendampingan-ortu.index', compact('laporan', 'status', 'guru'));
    }

    public function guruConfirm(LaporanPendampinganOrtu $laporan)
    {
        $guru = auth()->user()?->guru;
        if (! $guru || $laporan->guru_id !== $guru->id) {
            return back()->with('error', 'Laporan ini bukan untuk kelas Anda.');
        }

        $laporan->update([
            'status' => 'telah_dikonfirmasi',
            'dikonfirmasi_oleh' => $guru->id,
            'tanggal_konfirmasi' => now(),
        ]);

        return back()->with('success', 'Laporan pendampingan berhasil dikonfirmasi.');
    }

    public function guruConfirmBulk(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $validated = $request->validate([
            'laporan_ids' => 'required|array|min:1',
            'laporan_ids.*' => 'integer|exists:laporan_pendampingan_ortus,id',
        ]);

        $updated = LaporanPendampinganOrtu::where('guru_id', $guru->id)
            ->where('status', 'pengajuan_konfirmasi')
            ->whereIn('id', $validated['laporan_ids'])
            ->update([
                'status' => 'telah_dikonfirmasi',
                'dikonfirmasi_oleh' => $guru->id,
                'tanggal_konfirmasi' => now(),
            ]);

        return back()->with('success', "{$updated} laporan pendampingan berhasil dikonfirmasi.");
    }

    // ==================== ADMIN: MONITORING SEMUA LAPORAN ====================
    public function adminIndex(Request $request)
    {
        $status = $request->get('status', 'semua');
        $query = LaporanPendampinganOrtu::with(['siswa', 'surat', 'semester', 'guru', 'guruKonfirmasi']);

        if ($status === 'pengajuan') {
            $query->pengajuan();
        } elseif ($status === 'dikonfirmasi') {
            $query->dikonfirmasi();
        }

        $laporan = $query->orderByRaw("status = 'pengajuan_konfirmasi' DESC")
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.pendampingan-ortu.index', compact('laporan', 'status'));
    }

    public function adminConfirm(LaporanPendampinganOrtu $laporan)
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $guruId = $user->guru_id ?? $laporan->guru_id;

        $laporan->update([
            'status' => 'telah_dikonfirmasi',
            'dikonfirmasi_oleh' => $guruId,
            'tanggal_konfirmasi' => now(),
        ]);

        return back()->with('success', 'Laporan pendampingan berhasil dikonfirmasi.');
    }

    public function adminConfirmBulk(Request $request)
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'laporan_ids' => 'required|array|min:1',
            'laporan_ids.*' => 'integer|exists:laporan_pendampingan_ortus,id',
        ]);

        $guruId = $user->guru_id;

        $updated = LaporanPendampinganOrtu::where('status', 'pengajuan_konfirmasi')
            ->whereIn('id', $validated['laporan_ids'])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->update([
                'status' => 'telah_dikonfirmasi',
                'dikonfirmasi_oleh' => $guruId,
                'tanggal_konfirmasi' => now(),
            ]);

        return back()->with('success', "{$updated} laporan pendampingan berhasil dikonfirmasi.");
    }
}
