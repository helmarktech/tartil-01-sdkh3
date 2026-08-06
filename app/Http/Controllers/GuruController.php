<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Semester;
use App\Models\PerpindahanKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    // ============ DASHBOARD ============
    public function dashboard()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
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
            'jurnal_bulan_ini' => Jurnal::where('guru_id', $guru->id)
                ->whereMonth('tanggal', now()->month)->count(),
        ];

        $kelasList = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->get();
        $recentJurnals = Jurnal::where('guru_id', $guru->id)->with('kelas')
            ->orderBy('tanggal', 'desc')->limit(5)->get();

        return view('guru.dashboard', compact('stats', 'kelasList', 'recentJurnals'));
    }

    // ============ JURNAL HARIAN ============
    public function jurnalIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $query = Jurnal::where('guru_id', $guru->id)->with('kelas')->orderBy('tanggal', 'desc');

        if ($request->filled('kelas')) {
            $query->where('kelas_id', $request->kelas);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $jurnals = $query->paginate(20);
        $kelasList = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->get();

        return view('guru.jurnal.index', compact('jurnals', 'kelasList'));
    }

    public function jurnalCreate(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasList = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->get();
        $semester = Semester::aktif()->first();

        if (!$semester) {
            return redirect()->route('guru.dashboard')->with('error', 'Tidak ada semester aktif. Hubungi admin.');
        }

        $selectedKelas = null;
        $siswas = collect();

        if ($request->filled('kelas_id')) {
            $selectedKelas = Kelas::findOrFail($request->kelas_id);
            $siswas = Siswa::where('kelas_tartil_id', $request->kelas_id)
                ->where('status', 'aktif')->orderBy('nama')->get();
        }

        return view('guru.jurnal.create', compact('kelasList', 'semester', 'selectedKelas', 'siswas'));
    }

    public function jurnalStore(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'semester_id' => 'required|exists:semesters,id',
            'surat' => 'required|max:100',
            'ayat' => 'required|max:50',
            'materi' => 'nullable',
            'jenis_penilaian' => 'required|in:harian,tengah_semester,akhir_semester',
            'penilaian' => 'required|array',
            'penilaian.*.siswa_id' => 'required|exists:siswas,id',
            'penilaian.*.nilai_b' => 'required|integer|min:0|max:100',
            'penilaian.*.nilai_c' => 'required|integer|min:0|max:100',
            'penilaian.*.nilai_k' => 'required|integer|min:0|max:100',
            'penilaian.*.catatan' => 'nullable',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswas,id',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alpha',
        ]);

        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');

        DB::beginTransaction();
        try {
            // 1. Buat jurnal
            $jurnal = Jurnal::create([
                'tanggal' => $request->tanggal,
                'kelas_id' => $request->kelas_id,
                'guru_id' => $guru->id,
                'semester_id' => $request->semester_id,
                'surat' => $request->surat,
                'ayat' => $request->ayat,
                'materi' => $request->materi,
                'jenis_penilaian' => $request->jenis_penilaian,
            ]);

            // 2. Simpan penilaian B, C, K
            foreach ($request->penilaian as $p) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'siswa_id' => $p['siswa_id'],
                    'nilai_b' => $p['nilai_b'],
                    'nilai_c' => $p['nilai_c'],
                    'nilai_k' => $p['nilai_k'],
                    'catatan' => $p['catatan'] ?? null,
                ]);
            }

            // 3. Absensi otomatis - siswa yang dinilai = Hadir, siswa tidak dinilai sesuai pilihan
            foreach ($request->absensi as $a) {
                Absensi::create([
                    'tanggal' => $request->tanggal,
                    'siswa_id' => $a['siswa_id'],
                    'kelas_id' => $request->kelas_id,
                    'jurnal_id' => $jurnal->id,
                    'status' => $a['status'],
                ]);
            }

            DB::commit();
            return redirect()->route('guru.jurnal.index')->with('success', 'Jurnal berhasil disimpan. Absensi tercatat otomatis.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function jurnalShow(Jurnal $jurnal)
    {
        $jurnal->load(['details.siswa', 'absensis.siswa', 'kelas', 'semester']);
        return view('guru.jurnal.show', compact('jurnal'));
    }

    // ============ PERPINDAHAN KELAS REQUEST ============
    public function perpindahanCreate()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasList = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->get();
        $semester = Semester::aktif()->first();
        
        return view('guru.perpindahan.create', compact('kelasList', 'semester'));
    }

    public function getSiswaByKelas(Request $request)
    {
        $siswas = Siswa::where('kelas_tartil_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);
        return response()->json($siswas);
    }

    public function perpindahanStore(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_lama_id' => 'required|exists:kelas,id',
            'kelas_baru_id' => 'required|exists:kelas,id|different:kelas_lama_id',
            'semester_id' => 'required|exists:semesters,id',
            'alasan' => 'required',
        ]);

        PerpindahanKelas::create($request->all());
        return redirect()->route('guru.dashboard')->with('success', 'Pengajuan perpindahan kelas dikirim. Menunggu persetujuan admin.');
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
