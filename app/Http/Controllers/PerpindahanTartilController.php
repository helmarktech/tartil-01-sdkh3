<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\PerpindahanKelas;
use App\Models\Guru;
use Illuminate\Support\Facades\DB;

class PerpindahanTartilController extends Controller
{
    // ==================== ADMIN: AJUKAN PERPINDAHAN ====================
    public function adminAjukan(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_lama_id' => 'nullable|exists:kelas,id',
            'kelas_baru_id' => 'required|exists:kelas,id',
            'semester_id' => 'required|exists:semesters,id',
            'alasan' => 'required|string',
        ]);

        $siswa = Siswa::find($request->siswa_id);
        $kelasBaru = Kelas::find($request->kelas_baru_id);

        // Validasi: kelas tujuan tidak boleh sama dengan kelas lama
        if ($siswa->kelas_tartil_id == $request->kelas_baru_id) {
            return back()->with('error', 'Siswa sudah berada di kelas tujuan.')->withInput();
        }

        PerpindahanKelas::create([
            'siswa_id' => $request->siswa_id,
            'kelas_lama_id' => $siswa->kelas_tartil_id,
            'kelas_baru_id' => $request->kelas_baru_id,
            'semester_id' => $request->semester_id,
            'alasan' => $request->alasan,
            'jenis' => 'tartil',
            'diajukan_oleh' => auth()->id(),
            'guru_tujuan_id' => $kelasBaru->guru_id,
            'status' => 'pending',
        ]);

        return redirect()->route('admin.perpindahan-tartil.admin')->with('success', 'Pengajuan perpindahan berhasil dikirim.');
    }

    // ==================== ADMIN: AJUKAN PERPINDAHAN MASSAL (3-STEP) ====================
    public function adminAjukanMassal(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'kelas_asal_id' => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id|different:kelas_asal_id',
            'semester_id' => 'required|exists:semesters,id',
            'alasan' => 'required|string',
        ]);

        $kelasBaru = Kelas::find($request->kelas_tujuan_id);
        $count = 0;

        foreach ($request->siswa_ids as $siswaId) {
            $siswa = Siswa::find($siswaId);

            // Skip kalau siswa sudah di kelas tujuan atau tidak aktif
            if ($siswa->kelas_tartil_id == $request->kelas_tujuan_id) continue;
            if ($siswa->status !== 'aktif') continue;

            PerpindahanKelas::create([
                'siswa_id' => $siswaId,
                'kelas_lama_id' => $siswa->kelas_tartil_id,
                'kelas_baru_id' => $request->kelas_tujuan_id,
                'semester_id' => $request->semester_id,
                'alasan' => $request->alasan,
                'jenis' => 'tartil',
                'diajukan_oleh' => auth()->id(),
                'guru_tujuan_id' => $kelasBaru->guru_id,
                'status' => 'pending',
            ]);
            $count++;
        }

        return redirect()->route('admin.perpindahan-tartil.admin')->with('success', "{$count} pengajuan perpindahan berhasil dikirim. Menunggu persetujuan.");
    }

    // ==================== ADMIN: PERPINDAHAN TARTIL (3-STEP) ====================
    // Alur: Pilih Kelas Asal → Pilih Kelas Tujuan → Checkbox Siswa → Submit
    public function adminIndex(Request $request)
    {
        $step = 1;
        $kelasAsal = null;
        $kelasTujuan = null;
        $siswaList = collect();

        // Semua kelas tartil aktif
        $kelasList = Kelas::where('status', 'aktif')
            ->with('guru')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        $semester = Semester::aktif()->first();

        // Step 3: Kelas asal dan tujuan sudah dipilih, tampilkan siswa
        if ($request->filled('kelas_asal_id') && $request->filled('kelas_tujuan_id')) {
            $kelasAsal = Kelas::find($request->kelas_asal_id);
            $kelasTujuan = Kelas::find($request->kelas_tujuan_id);
            if ($kelasAsal && $kelasTujuan) {
                $siswaList = Siswa::where('kelas_tartil_id', $kelasAsal->id)
                    ->where('status', 'aktif')
                    ->orderBy('nama')
                    ->get();
                $step = 3;
            }
        }
        // Step 2: Pilih kelas tujuan
        elseif ($request->filled('kelas_asal_id')) {
            $kelasAsal = Kelas::find($request->kelas_asal_id);
            $step = 2;
        }

        // Riwayat perpindahan
        $perpindahans = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru', 'semester', 'approver', 'pengaju', 'guruTujuan'])
            ->where('jenis', 'tartil')
            ->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.perpindahan.index', compact(
            'kelasList', 'kelasAsal', 'kelasTujuan', 'siswaList', 'step', 'semester', 'perpindahans'
        ));
    }

    public function adminApprove(PerpindahanKelas $perpindahan)
    {
        if ($perpindahan->status !== 'pending') {
            return back()->with('error', 'Perpindahan sudah diproses sebelumnya.');
        }

        return \DB::transaction(function () use ($perpindahan) {
            $perpindahan->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update kelas tartil siswa
            Siswa::where('id', $perpindahan->siswa_id)->update([
                'kelas_tartil_id' => $perpindahan->kelas_baru_id,
            ]);

            // Update snapshot semester_kelas
            $semesterAktif = Semester::aktif()->first();
            if ($semesterAktif) {
                // Decrement kelas lama
                if ($perpindahan->kelas_lama_id) {
                    \DB::table('semester_kelas')
                        ->where('semester_id', $semesterAktif->id)
                        ->where('kelas_id', $perpindahan->kelas_lama_id)
                        ->update(['jumlah_siswa' => \DB::raw('GREATEST(jumlah_siswa - 1, 0)'), 'updated_at' => now()]);
                }
                // Increment kelas baru
                \DB::table('semester_kelas')
                    ->where('semester_id', $semesterAktif->id)
                    ->where('kelas_id', $perpindahan->kelas_baru_id)
                    ->update(['jumlah_siswa' => \DB::raw('jumlah_siswa + 1'), 'updated_at' => now()]);
            }

            return back()->with('success', 'Perpindahan disetujui. Siswa ' . $perpindahan->siswa->nama . ' pindah ke ' . $perpindahan->kelasBaru->nama . '.');
        });
    }

    public function adminTolak(Request $request, PerpindahanKelas $perpindahan)
    {
        if ($perpindahan->status !== 'pending') {
            return back()->with('error', 'Perpindahan sudah diproses sebelumnya.');
        }

        $perpindahan->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $request->catatan,
        ]);
        return back()->with('success', 'Perpindahan ditolak.');
    }

    // ==================== ADMIN: SETUJU SEMUA ====================
    public function adminApproveAll(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:perpindahan_kelas,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $p = PerpindahanKelas::find($id);
            if (!$p || $p->status !== 'pending') continue;

            \DB::transaction(function () use ($p) {
                $p->update([
                    'status' => 'disetujui',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                Siswa::where('id', $p->siswa_id)->update([
                    'kelas_tartil_id' => $p->kelas_baru_id,
                ]);
            });
            $count++;
        }

        return back()->with('success', "{$count} perpindahan disetujui. Siswa sudah pindah kelas.");
    }

    // ==================== ADMIN: TOLAK SEMUA ====================
    public function adminTolakAll(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:perpindahan_kelas,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $p = PerpindahanKelas::find($id);
            if (!$p || $p->status !== 'pending') continue;

            $p->update([
                'status' => 'ditolak',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $count++;
        }

        return back()->with('success', "{$count} perpindahan ditolak.");
    }

    // ==================== GURU: AJUKAN PERPINDAHAN ====================
    public function guruCreate()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasList = Kelas::where('status', 'aktif')->with('guru')->orderBy('nama')->get();
        $semester = Semester::aktif()->first();
        return view('guru.perpindahan.create', compact('kelasList', 'semester'));
    }

    public function guruStore(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_lama_id' => 'required|exists:kelas,id',
            'kelas_baru_id' => 'required|exists:kelas,id|different:kelas_lama_id',
            'semester_id' => 'required|exists:semesters,id',
            'alasan' => 'required',
        ]);

        $kelasBaru = Kelas::find($request->kelas_baru_id);

        PerpindahanKelas::create([
            'siswa_id' => $request->siswa_id,
            'kelas_lama_id' => $request->kelas_lama_id,
            'kelas_baru_id' => $request->kelas_baru_id,
            'semester_id' => $request->semester_id,
            'alasan' => $request->alasan,
            'jenis' => 'tartil',
            'diajukan_oleh' => auth()->id(),
            'guru_tujuan_id' => $kelasBaru->guru_id,
        ]);
        return redirect()->route('guru.dashboard')->with('success', 'Pengajuan perpindahan kelas dikirim. Menunggu persetujuan.');
    }

    // ==================== GURU: PERPINDAHAN MASSAL 3-STEP ====================
    public function guruMassalIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');

        $step = 1;
        $kelasAsal = null;
        $kelasTujuan = null;
        $siswaList = collect();

        $semester = Semester::aktif()->first();
        if (!$semester) return back()->with('error', 'Tidak ada semester aktif.');

        // Kelas asal: HANYA kelas yang diajar oleh guru ini
        $kelasAsalList = Kelas::where('status', 'aktif')
            ->where('guru_id', $guru->id)
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderBy('nama')
            ->get();

        // Kelas tujuan: semua kelas aktif
        $kelasTujuanList = Kelas::where('status', 'aktif')
            ->with('guru')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        // Step 3: Kelas asal dan tujuan sudah dipilih
        if ($request->filled('kelas_asal_id') && $request->filled('kelas_tujuan_id')) {
            // Validasi: kelas asal harus milik guru ini
            $kelasAsal = Kelas::where('id', $request->kelas_asal_id)
                ->where('guru_id', $guru->id)
                ->first();
            $kelasTujuan = Kelas::find($request->kelas_tujuan_id);

            if ($kelasAsal && $kelasTujuan) {
                $siswaList = Siswa::where('kelas_tartil_id', $kelasAsal->id)
                    ->where('status', 'aktif')
                    ->orderBy('nama')
                    ->get();
                $step = 3;
            } else {
                return back()->with('error', 'Kelas asal tidak valid atau bukan kelas yang Anda ajar.');
            }
        }
        // Step 2: Pilih kelas tujuan
        elseif ($request->filled('kelas_asal_id')) {
            $kelasAsal = Kelas::where('id', $request->kelas_asal_id)
                ->where('guru_id', $guru->id)
                ->first();
            if (!$kelasAsal) {
                return back()->with('error', 'Kelas asal tidak valid atau bukan kelas yang Anda ajar.');
            }
            $step = 2;
        }

        // Riwayat perpindahan yang diajukan oleh guru ini
        $perpindahans = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru', 'semester', 'guruTujuan'])
            ->where('jenis', 'tartil')
            ->where('diajukan_oleh', auth()->id())
            ->orderBy('created_at', 'desc')->paginate(20);

        return view('guru.perpindahan.massal', compact(
            'kelasAsalList', 'kelasTujuanList', 'kelasAsal', 'kelasTujuan',
            'siswaList', 'step', 'semester', 'perpindahans', 'guru'
        ));
    }

    public function guruMassalStore(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan. Hubungi admin.');

        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'kelas_asal_id' => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id|different:kelas_asal_id',
            'semester_id' => 'required|exists:semesters,id',
            'alasan' => 'required|string',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa.',
            'kelas_asal_id.exists' => 'Kelas asal tidak valid.',
            'kelas_tujuan_id.different' => 'Kelas tujuan harus berbeda dengan kelas asal.',
        ]);

        // Validasi: kelas asal harus milik guru ini
        $kelasAsal = Kelas::where('id', $request->kelas_asal_id)
            ->where('guru_id', $guru->id)
            ->first();
        if (!$kelasAsal) {
            return back()->with('error', 'Kelas asal bukan kelas yang Anda ajar.');
        }

        $kelasBaru = Kelas::find($request->kelas_tujuan_id);
        $count = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($request->siswa_ids as $siswaId) {
                $siswa = Siswa::find($siswaId);

                // Skip jika sudah di kelas tujuan atau tidak aktif
                if ($siswa->kelas_tartil_id == $request->kelas_tujuan_id) { $skipped++; continue; }
                if ($siswa->status !== 'aktif') { $skipped++; continue; }

                PerpindahanKelas::create([
                    'siswa_id' => $siswaId,
                    'kelas_lama_id' => $siswa->kelas_tartil_id,
                    'kelas_baru_id' => $request->kelas_tujuan_id,
                    'semester_id' => $request->semester_id,
                    'alasan' => $request->alasan,
                    'jenis' => 'tartil',
                    'diajukan_oleh' => auth()->id(),
                    'guru_tujuan_id' => $kelasBaru->guru_id,
                    'status' => 'pending',
                ]);
                $count++;
            }
            DB::commit();

            $message = "{$count} pengajuan perpindahan berhasil dikirim.";
            if ($skipped > 0) $message .= " {$skipped} siswa dilewati (sudah di kelas tujuan/tidak aktif).";
            $message .= " Menunggu persetujuan admin dan guru kelas tujuan.";

            return redirect()->route('guru.perpindahan.massal')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    // ==================== GURU: APPROVAL (hanya kelas yang dia ajar) ====================
    public function guruApprovalIndex()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $perpindahans = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru', 'semester'])
            ->where('guru_tujuan_id', $guru->id)
            ->where('status', 'pending')
            ->where('jenis', 'tartil')
            ->orderBy('created_at', 'desc')->get();
        return view('guru.perpindahan.approval', compact('perpindahans'));
    }

    public function guruApprove(PerpindahanKelas $perpindahan)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        if ($perpindahan->guru_tujuan_id !== $guru->id) {
            abort(403, 'Anda bukan guru kelas tujuan.');
        }
        $perpindahan->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        $perpindahan->siswa->update(['kelas_tartil_id' => $perpindahan->kelas_baru_id]);
        return back()->with('success', 'Perpindahan disetujui.');
    }

    public function guruTolak(Request $request, PerpindahanKelas $perpindahan)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        if ($perpindahan->guru_tujuan_id !== $guru->id) {
            abort(403, 'Anda bukan guru kelas tujuan.');
        }
        $perpindahan->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $request->catatan,
        ]);
        return back()->with('success', 'Perpindahan ditolak.');
    }
}
