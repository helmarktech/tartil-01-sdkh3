<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\PenilaianRaporToggle;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianRaporToggleController extends Controller
{
    // ═══════════════════════════════════════════════════
    // STEP 1: GURU Pilih Semester
    // ═══════════════════════════════════════════════════
    public function index()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        // Ambil semester yang punya data penilaian toggle atau semester aktif
        $semesterList = Semester::orderBy('tanggal_mulai', 'desc')->get();

        return view('guru.penilaian-rapor-toggle.index', compact('semesterList', 'guru'));
    }

    // ═══════════════════════════════════════════════════
    // STEP 2: GURU Pilih Kelas (setelah pilih semester)
    // ═══════════════════════════════════════════════════
    public function pilihKelas(Request $request, $semesterId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $semester = Semester::findOrFail($semesterId);

        // Ambil kelas yang diajar guru
        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        if ($kelasList->isEmpty()) {
            return redirect()->route('guru.penilaian-rapor-toggle.index')
                ->with('error', 'Anda tidak memiliki kelas aktif.');
        }

        // Hitung progress per kelas
        foreach ($kelasList as $kelas) {
            $siswaIds = Siswa::where('kelas_tartil_id', $kelas->id)
                ->where('status', 'aktif')
                ->pluck('id');

            $total = $siswaIds->count();
            $diisi = PenilaianRaporToggle::where('semester_id', $semesterId)
                ->where('kelas_id', $kelas->id)
                ->whereIn('status', [PenilaianRaporToggle::STATUS_LULUS, PenilaianRaporToggle::STATUS_TIDAK_LULUS])
                ->count();

            $kelas->total_siswa = $total;
            $kelas->sudah_dinilai = $diisi;
            $kelas->progress_persen = $total > 0 ? round(($diisi / $total) * 100) : 0;
        }

        return view('guru.penilaian-rapor-toggle.pilih-kelas', compact('semester', 'kelasList'));
    }

    // ═══════════════════════════════════════════════════
    // STEP 3: GURU Isi Nilai Toggle (per kelas)
    // Auto-register semua siswa kelas dengan status T
    // ═══════════════════════════════════════════════════
    public function isiNilai(Request $request, $semesterId, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $semester = Semester::findOrFail($semesterId);
        $kelas = Kelas::findOrFail($kelasId);

        // Security: cek kepemilikan kelas
        if ($kelas->guru_id !== $guru->id) {
            Log::warning('Guru mencoba akses kelas lain', ['guru_id' => $guru->id, 'kelas_id' => $kelasId]);
            return redirect()->back()->with('error', 'Akses tidak diizinkan.');
        }

        // Ambil semua siswa aktif di kelas ini
        $siswas = Siswa::where('kelas_tartil_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        if ($siswas->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
        }

        // Auto-register: pastikan setiap siswa punya record penilaian_rapor_toggles
        DB::transaction(function () use ($siswas, $semesterId, $kelasId) {
            foreach ($siswas as $siswa) {
                PenilaianRaporToggle::firstOrCreate(
                    [
                        'semester_id' => $semesterId,
                        'kelas_id' => $kelasId,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'status' => PenilaianRaporToggle::STATUS_TERDAFTAR, // T
                    ]
                );
            }
        });

        // Ambil data penilaian toggle untuk siswa di kelas ini
        $penilaianToggles = PenilaianRaporToggle::where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        // Hitung progress
        $totalSiswa = $siswas->count();
        $sudahDinilai = $penilaianToggles->whereIn('status', [
            PenilaianRaporToggle::STATUS_LULUS,
            PenilaianRaporToggle::STATUS_TIDAK_LULUS
        ])->count();
        $progressPersen = $totalSiswa > 0 ? round(($sudahDinilai / $totalSiswa) * 100) : 0;

        return view('guru.penilaian-rapor-toggle.isi-nilai', compact(
            'semester', 'kelas', 'siswas', 'penilaianToggles',
            'totalSiswa', 'sudahDinilai', 'progressPersen'
        ));
    }

    // ═══════════════════════════════════════════════════
    // GURU: Simpan Nilai Toggle (batch)
    // ═══════════════════════════════════════════════════
    public function simpanNilai(Request $request, $semesterId, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan.');
        }

        $kelas = Kelas::findOrFail($kelasId);
        if ($kelas->guru_id !== $guru->id) {
            return redirect()->back()->with('error', 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*.toggle_id' => 'nullable|exists:penilaian_rapor_toggles,id',
            'nilai.*.status' => 'required|in:T,L,TL',
            'nilai.*.nilai' => 'nullable|integer|min:0|max:100',
            'nilai.*.catatan' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($validated, $semesterId, $kelasId, $guru) {
                foreach ($validated['nilai'] as $siswaId => $data) {
                    PenilaianRaporToggle::updateOrCreate(
                        [
                            'semester_id' => $semesterId,
                            'kelas_id' => $kelasId,
                            'siswa_id' => $siswaId,
                        ],
                        [
                            'status' => $data['status'],
                            'nilai' => $data['nilai'] ?? null,
                            'catatan' => $data['catatan'] ?? null,
                            'diisi_oleh' => $guru->id,
                            'tanggal_diisi' => now(),
                        ]
                    );
                }
            });

            return redirect()->route('guru.penilaian-rapor-toggle.isi-nilai', [$semesterId, $kelasId])
                ->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan nilai rapor toggle', [
                'error' => $e->getMessage(),
                'semester_id' => $semesterId,
                'kelas_id' => $kelasId,
                'guru_id' => $guru->id,
            ]);
            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // BATCH: LULUS SEMUA / TIDAK LULUS SEMUA
    // ═══════════════════════════════════════════════════
    public function lulusSemua($semesterId, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $kelas = Kelas::findOrFail($kelasId);
        if ($kelas->guru_id !== $guru->id) return back()->with('error', 'Akses tidak diizinkan.');

        $count = PenilaianRaporToggle::where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->where('status', PenilaianRaporToggle::STATUS_TERDAFTAR) // T
            ->update([
                'status' => PenilaianRaporToggle::STATUS_LULUS, // L
                'diisi_oleh' => $guru->id,
                'tanggal_diisi' => now(),
            ]);

        return back()->with('success', "{$count} siswa dinyatakan lulus.");
    }

    public function tidakLulusSemua($semesterId, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $kelas = Kelas::findOrFail($kelasId);
        if ($kelas->guru_id !== $guru->id) return back()->with('error', 'Akses tidak diizinkan.');

        $count = PenilaianRaporToggle::where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->where('status', PenilaianRaporToggle::STATUS_TERDAFTAR) // T
            ->update([
                'status' => PenilaianRaporToggle::STATUS_TIDAK_LULUS, // TL
                'diisi_oleh' => $guru->id,
                'tanggal_diisi' => now(),
            ]);

        return back()->with('success', "{$count} siswa dinyatakan tidak lulus.");
    }
}
