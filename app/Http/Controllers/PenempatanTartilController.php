<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenempatanTartilController extends Controller
{
    // ═══════════════════════════════════════════════════
    // HALAMAN: Daftar siswa tanpa kelas tartil
    // ═══════════════════════════════════════════════════
    public function index(Request $request)
    {
        $kelasList = Kelas::where('status', 'aktif')->orderBy('nama')->get();

        // Filter siswa yang BELUM punya kelas tartil
        $siswas = Siswa::whereNull('kelas_tartil_id')
            ->where('status', 'aktif')
            ->with('kelasReguler')
            ->orderBy('nama')
            ->paginate(50);

        return view('admin.siswa.penempatan', compact('siswas', 'kelasList'));
    }

    // ═══════════════════════════════════════════════════
    // PROSES: Tempatkan siswa ke kelas tartil (massal)
    // ═══════════════════════════════════════════════════
    public function tempatkan(Request $request)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:siswas,id',
            'kelas_tartil_id' => 'required|exists:kelas,id',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa.',
            'kelas_tartil_id.required' => 'Pilih kelas tartil tujuan.',
        ]);

        $kelas = Kelas::findOrFail($validated['kelas_tartil_id']);
        $siswaIds = $validated['siswa_ids'];
        $count = 0;

        try {
            DB::transaction(function () use ($siswaIds, $kelas, &$count) {
                foreach ($siswaIds as $siswaId) {
                    $siswa = Siswa::where('id', $siswaId)
                        ->whereNull('kelas_tartil_id')
                        ->lockForUpdate()
                        ->first();

                    if ($siswa) {
                        $siswa->update([
                            'kelas_tartil_id' => $kelas->id,
                            'keterangan_status' => 'Ditempatkan ke ' . $kelas->nama . ' (' . $kelas->jenis . ')',
                        ]);
                        $count++;
                    }
                }
            });

            Log::info('Penempatan siswa ke kelas tartil', [
                'jumlah' => $count,
                'kelas_id' => $kelas->id,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.siswa.penempatan')
                ->with('success', $count . ' siswa berhasil ditempatkan ke ' . $kelas->nama . '.');
        } catch (\Exception $e) {
            Log::error('Gagal penempatan siswa', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // PROSES: Pindah kelas tartil (per siswa)
    // ═══════════════════════════════════════════════════
    public function pindah(Request $request, $id)
    {
        $validated = $request->validate([
            'kelas_tartil_id' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::findOrFail($id);
        $kelasLama = $siswa->kelasTartil?->nama ?? '-';
        $kelasBaru = Kelas::findOrFail($validated['kelas_tartil_id']);

        $siswa->update([
            'kelas_tartil_id' => $kelasBaru->id,
            'keterangan_status' => 'Pindah dari ' . $kelasLama . ' ke ' . $kelasBaru->nama,
        ]);

        // Catat riwayat
        \App\Models\RiwayatMutasi::create([
            'siswa_id' => $siswa->id,
            'kelas_lama_id' => $siswa->kelas_tartil_id,
            'kelas_baru_id' => $kelasBaru->id,
            'jenis' => 'perpindahan',
            'status' => 'diterima',
            'keterangan' => 'Penempatan manual admin',
            'diajukan_oleh' => 'admin',
            'disetujui_oleh' => auth()->id(),
            'tanggal_pindah' => now(),
        ]);

        return redirect()->back()->with('success', $siswa->nama . ' berhasil dipindah ke ' . $kelasBaru->nama . '.');
    }
}
