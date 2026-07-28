<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HafalanTahfidz;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Surat;
use Illuminate\Support\Facades\DB;

class TahfidzController extends Controller
{
    // ==================== ADMIN: INDEX (REKAP KELAS TAHFIDZ) ====================
    public function adminIndex()
    {
        $semester = Semester::aktif()->first();
        $kelasTahfidz = Kelas::where('jenis', 'Tahfidz')
            ->where('status', 'aktif')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->get();

        // Tambah rekap hafalan per kelas
        $kelasTahfidz = $kelasTahfidz->map(function ($k) use ($semester) {
            $rekap = HafalanTahfidz::rekapPerKelas($k->id, $semester?->id);
            $k->rekap = $rekap;
            $k->avgJuz = $k->siswas_count > 0
                ? round(collect($rekap['perSiswa'])->avg('juzHafal'), 1)
                : 0;
            return $k;
        });

        return view('admin.tahfidz.index', compact('kelasTahfidz', 'semester'));
    }

    // ==================== ADMIN: DETAIL SISWA ====================
    public function adminDetailSiswa(Siswa $siswa)
    {
        $semester = Semester::aktif()->first();
        $hafalanList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->with(['surat', 'semester', 'guru'])
            ->orderBy('tanggal_hafalan', 'desc')
            ->get();

        $totalJuzHafal = HafalanTahfidz::totalJuzHafal($siswa->id);
        $juzDistinct = $hafalanList->where('status', 'hafal')->pluck('juz')->unique()->sort()->values();

        return view('admin.tahfidz.detail-siswa', compact('siswa', 'hafalanList', 'totalJuzHafal', 'juzDistinct', 'semester'));
    }

    // ==================== ADMIN: FORM TAMBAH HAFALAN ====================
    public function adminCreate(Request $request)
    {
        $siswa = Siswa::findOrFail($request->siswa_id);
        $suratList = Surat::orderBy('urutan')->get();
        $semester = Semester::aktif()->first();

        return view('admin.tahfidz.create', compact('siswa', 'suratList', 'semester'));
    }

    // ==================== ADMIN: SIMPAN HAFALAN ====================
    public function adminStore(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'surat_id' => 'nullable|exists:surats,id',
            'juz' => 'required|integer|min:1|max:30',
            'ayat_mulai' => 'required|integer|min:1',
            'ayat_selesai' => 'nullable|integer|min:1|gte:ayat_mulai',
            'status' => 'required|in:baru,setengah_hafal,hafal,murajaah',
            'kualitas' => 'required|in:mumtaz,jayyid_jiddan,jayyid,naqis',
            'tanggal_hafalan' => 'required|date',
            'catatan' => 'nullable',
        ]);

        $hafalan = HafalanTahfidz::create([
            'siswa_id' => $request->siswa_id,
            'semester_id' => $request->semester_id,
            'kelas_id' => $request->kelas_id ?? Siswa::find($request->siswa_id)?->kelas_tartil_id,
            'surat_id' => $request->surat_id,
            'juz' => $request->juz,
            'ayat_mulai' => $request->ayat_mulai,
            'ayat_selesai' => $request->ayat_selesai,
            'status' => $request->status,
            'kualitas' => $request->kualitas,
            'tanggal_hafalan' => $request->tanggal_hafalan,
            'catatan' => $request->catatan,
            'created_by' => auth()->user()?->guru_id,
        ]);

        return redirect()->route('admin.tahfidz.detail-siswa', $request->siswa_id)
            ->with('success', "Hafalan Juz {$hafalan->juz} berhasil dicatat.");
    }

    // ==================== ADMIN: HAPUS HAFALAN ====================
    public function adminDestroy(HafalanTahfidz $hafalan)
    {
        $siswaId = $hafalan->siswa_id;
        $juz = $hafalan->juz;
        $hafalan->delete();

        return redirect()->route('admin.tahfidz.detail-siswa', $siswaId)
            ->with('success', "Hafalan Juz {$juz} dihapus.");
    }

    // ==================== GURU: INDEX (KELAS SENDIRI) ====================
    public function guruIndex()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('jenis', 'Tahfidz')
            ->where('status', 'aktif')
            ->first();

        if (!$kelas) {
            return view('guru.tahfidz.index', ['kelas' => null, 'rekap' => null, 'semester' => null]);
        }

        $semester = Semester::aktif()->first();
        $rekap = HafalanTahfidz::rekapPerKelas($kelas->id, $semester?->id);

        return view('guru.tahfidz.index', compact('kelas', 'rekap', 'semester'));
    }

    // ==================== GURU: INPUT HAFALAN ====================
    public function guruStore(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'surat_id' => 'nullable|exists:surats,id',
            'juz' => 'required|integer|min:1|max:30',
            'ayat_mulai' => 'required|integer|min:1',
            'ayat_selesai' => 'nullable|integer|min:1|gte:ayat_mulai',
            'status' => 'required|in:baru,setengah_hafal,hafal,murajaah',
            'kualitas' => 'required|in:mumtaz,jayyid_jiddan,jayyid,naqis',
            'tanggal_hafalan' => 'required|date',
            'catatan' => 'nullable',
        ]);

        $guru = auth()->user()?->guru;
        $siswa = Siswa::find($request->siswa_id);

        // Validasi: siswa harus di kelas guru ini
        if (!$guru || $siswa->kelasTartil?->guru_id !== $guru->id) {
            return back()->with('error', 'Siswa tidak ada di kelas Anda.');
        }

        HafalanTahfidz::create([
            'siswa_id' => $request->siswa_id,
            'semester_id' => $request->semester_id,
            'kelas_id' => $siswa->kelas_tartil_id,
            'surat_id' => $request->surat_id,
            'juz' => $request->juz,
            'ayat_mulai' => $request->ayat_mulai,
            'ayat_selesai' => $request->ayat_selesai,
            'status' => $request->status,
            'kualitas' => $request->kualitas,
            'tanggal_hafalan' => $request->tanggal_hafalan,
            'catatan' => $request->catatan,
            'created_by' => $guru->id,
        ]);

        return back()->with('success', 'Hafalan berhasil dicatat.');
    }

    // ==================== STATISTIK: DATA TAHFIDZ ====================
    public static function buildTahfidzData(): array
    {
        $kelasTahfidz = Kelas::where('jenis', 'Tahfidz')
            ->where('status', 'aktif')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->get();

        $totalSiswaTahfidz = $kelasTahfidz->sum('siswas_count');
        $totalKelasTahfidz = $kelasTahfidz->count();

        // Total juz hafal keseluruhan
        $totalJuzEntries = HafalanTahfidz::where('status', 'hafal')->count();

        // Distribusi status
        $distribusiStatus = HafalanTahfidz::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Distribusi kualitas
        $distribusiKualitas = HafalanTahfidz::selectRaw('kualitas, COUNT(*) as total')
            ->groupBy('kualitas')
            ->pluck('total', 'kualitas')
            ->toArray();

        // Per kelas
        $perKelas = $kelasTahfidz->map(function ($k) {
            $rekap = HafalanTahfidz::rekapPerKelas($k->id);
            return [
                'nama' => $k->nama,
                'guru' => $k->guru?->nama ?? '-',
                'totalSiswa' => $k->siswas_count,
                'avgJuz' => $k->siswas_count > 0
                    ? round(collect($rekap['perSiswa'])->avg('juzHafal'), 1)
                    : 0,
                'topSiswa' => collect($rekap['perSiswa'])
                    ->sortByDesc('juzHafal')
                    ->take(3)
                    ->values()
                    ->toArray(),
            ];
        })->toArray();

        return [
            'totalSiswaTahfidz' => $totalSiswaTahfidz,
            'totalKelasTahfidz' => $totalKelasTahfidz,
            'totalJuzEntries' => $totalJuzEntries,
            'distribusiStatus' => $distribusiStatus,
            'distribusiKualitas' => $distribusiKualitas,
            'perKelas' => $perKelas,
        ];
    }
}
