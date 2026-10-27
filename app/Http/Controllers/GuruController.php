<?php

namespace App\Http\Controllers;

use App\Models\JurnalKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    // ============ DASHBOARD ============
    public function dashboard()
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return view('guru.dashboard', [
                'stats' => ['kelas_mengajar' => 0, 'total_siswa' => 0, 'jurnal_bulan_ini' => 0],
                'kelasList' => collect(),
                'recentJurnals' => collect(),
                'noGuru' => true,
            ]);
        }
        $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id');

        $stats = [
            'kelas_mengajar' => Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->count(),
            'total_siswa' => Siswa::whereIn('kelas_tartil_id', $kelasIds)->where('status', 'aktif')->count(),
            // jurnal_kelas: 1 baris per kelas per tanggal (padanan tabel legacy jurnals)
            'jurnal_bulan_ini' => JurnalKelas::where('guru_id', $guru->id)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->count(),
        ];

        $kelasList = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->get();
        $recentJurnals = JurnalKelas::where('guru_id', $guru->id)->with(['kelas', 'surat'])
            ->orderBy('tanggal', 'desc')->limit(5)->get();

        return view('guru.dashboard', compact('stats', 'kelasList', 'recentJurnals'));
    }

    // ============ API: SISWA PER KELAS ============
    public function getSiswaByKelas(Request $request)
    {
        $siswas = Siswa::where('kelas_tartil_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);

        return response()->json($siswas);
    }

    // ============ DATA SISWA KELAS TARTIL (edit no hp oleh guru) ============
    public function siswaIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        }

        $kelasIds = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->pluck('id');

        if ($kelasIds->isEmpty()) {
            return view('guru.siswa.index', [
                'siswaList' => collect(),
                'kelasList' => collect(),
                'kelasFilter' => null,
            ])->with('warning', 'Anda belum ditugaskan ke kelas tartil aktif.');
        }

        $kelasFilter = $request->filled('kelas_id') ? (int) $request->get('kelas_id') : null;

        $query = Siswa::whereIn('kelas_tartil_id', $kelasIds)
            ->where('status', 'aktif')
            ->with('kelasTartil')
            ->orderBy('kelas_tartil_id')
            ->orderBy('nama');

        if ($kelasFilter && $kelasIds->contains($kelasFilter)) {
            $query->where('kelas_tartil_id', $kelasFilter);
        }

        $siswaList = $query->get();
        $kelasList = Kelas::whereIn('id', $kelasIds)->orderBy('nama')->get();

        return view('guru.siswa.index', compact('siswaList', 'kelasList', 'kelasFilter'));
    }

    public function siswaUpdateNoHp(Request $request, Siswa $siswa)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $kelasIds = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->pluck('id');
        if (! $kelasIds->contains($siswa->kelas_tartil_id)) {
            return back()->with('error', 'Siswa ini bukan di kelas Anda.');
        }

        $validated = $request->validate([
            'no_hp' => 'required|string|max:15',
        ]);

        $siswa->update($validated);

        return back()->with('success', "Nomor HP {$siswa->nama} berhasil diperbarui.");
    }

    // ============ GANTI PASSWORD ============
    public function editPassword()
    {
        return view('guru.password.edit');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return redirect()->route('guru.password.edit')->with('success', 'Password berhasil diperbarui.');
    }
}
