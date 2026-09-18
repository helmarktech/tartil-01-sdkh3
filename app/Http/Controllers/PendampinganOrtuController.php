<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\LaporanPendampinganOrtu;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Surat;
use App\Notifications\SiswaNotifikasi;
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

        $mode = null;
        $siswaList = collect();
        $filterSiswa = null;
        $semesterAktif = null;

        if ($status === 'pengajuan') {
            $query->pengajuan();
        } elseif ($status === 'dikonfirmasi') {
            $query->dikonfirmasi();

            $mode = $request->get('mode');
            $semesterAktif = Semester::aktif()->first();

            $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id');
            $siswaList = Siswa::whereIn('kelas_tartil_id', $kelasIds)
                ->orderBy('nama')
                ->get(['id', 'nama']);

            if ($mode === 'siswa' && $request->filled('siswa_id')) {
                $filterSiswa = $siswaList->firstWhere('id', (int) $request->siswa_id);
                $query->where('siswa_id', $request->siswa_id);
                if ($semesterAktif) {
                    $query->where('semester_id', $semesterAktif->id);
                }
            } elseif ($mode === 'tanggal' && $request->filled('tanggal')) {
                $query->whereDate('tanggal', $request->tanggal);
            } elseif ($mode === 'bulan' && $request->filled('bulan')
                && preg_match('/^\d{4}-\d{2}$/', $request->bulan)) {
                [$tahun, $bulan] = explode('-', $request->bulan);
                $query->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan);
            }
        }

        $laporan = $query->orderByRaw("status = 'pengajuan_konfirmasi' DESC")
            ->orderBy('tanggal', 'desc')
            ->get();

        // Mode per siswa: data konfirmasi dipisah per bulan dalam semester berjalan
        $laporanPerBulan = null;
        if ($status === 'dikonfirmasi' && $mode === 'siswa' && $filterSiswa) {
            $laporanPerBulan = $laporan->groupBy(fn ($l) => $l->tanggal?->format('Y-m'));
        }

        // Mode per bulan: kelompokkan per siswa agar seluruh siswa kelas tetap tampil
        $laporanPerSiswa = null;
        if ($status === 'dikonfirmasi' && $mode === 'bulan'
            && $request->filled('bulan') && preg_match('/^\d{4}-\d{2}$/', $request->bulan)) {
            $laporanPerSiswa = $laporan->groupBy('siswa_id');
        }

        return view('guru.pendampingan-ortu.index', compact(
            'laporan', 'status', 'guru', 'mode', 'siswaList', 'filterSiswa', 'semesterAktif', 'laporanPerBulan', 'laporanPerSiswa'
        ));
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

        try {
            $laporan->siswa?->notify($this->notifikasiKonfirmasi());
        } catch (\Throwable $e) {
            // Gagal kirim push tidak boleh menggagalkan konfirmasi
            report($e);
        }

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

        $laporans = LaporanPendampinganOrtu::with('siswa')
            ->where('guru_id', $guru->id)
            ->where('status', 'pengajuan_konfirmasi')
            ->whereIn('id', $validated['laporan_ids'])
            ->get();

        foreach ($laporans as $laporan) {
            $laporan->update([
                'status' => 'telah_dikonfirmasi',
                'dikonfirmasi_oleh' => $guru->id,
                'tanggal_konfirmasi' => now(),
            ]);

            try {
                $laporan->siswa?->notify($this->notifikasiKonfirmasi());
            } catch (\Throwable $e) {
                // Gagal kirim push tidak boleh membatalkan konfirmasi siswa lain
                report($e);
            }
        }

        return back()->with('success', "{$laporans->count()} laporan pendampingan berhasil dikonfirmasi.");
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

        try {
            $laporan->siswa?->notify($this->notifikasiKonfirmasi());
        } catch (\Throwable $e) {
            // Gagal kirim push tidak boleh menggagalkan konfirmasi
            report($e);
        }

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

        $laporans = LaporanPendampinganOrtu::with('siswa')
            ->where('status', 'pengajuan_konfirmasi')
            ->whereIn('id', $validated['laporan_ids'])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->get();

        foreach ($laporans as $laporan) {
            $laporan->update([
                'status' => 'telah_dikonfirmasi',
                'dikonfirmasi_oleh' => $guruId,
                'tanggal_konfirmasi' => now(),
            ]);

            try {
                $laporan->siswa?->notify($this->notifikasiKonfirmasi());
            } catch (\Throwable $e) {
                // Gagal kirim push tidak boleh membatalkan konfirmasi siswa lain
                report($e);
            }
        }

        return back()->with('success', "{$laporans->count()} laporan pendampingan berhasil dikonfirmasi.");
    }

    // Notifikasi konfirmasi laporan pendampingan untuk siswa pemilik laporan
    private function notifikasiKonfirmasi(): SiswaNotifikasi
    {
        return new SiswaNotifikasi(
            'pendampingan',
            'Pendampingan Dikonfirmasi',
            'Laporan pendampingan Anda telah dikonfirmasi guru',
            SiswaNotifikasi::urlDefault('pendampingan')
        );
    }
}
