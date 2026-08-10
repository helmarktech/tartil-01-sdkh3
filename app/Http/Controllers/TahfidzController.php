<?php

namespace App\Http\Controllers;

use App\Models\HafalanTahfidz;
use App\Models\JuzSurat;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahfidzController extends Controller
{
    // ==================== ADMIN: INDEX (REKAP KELAS TARTIL) ====================
    public function adminIndex(Request $request)
    {
        $semesterAktif = Semester::aktif()->first();
        $selectedSemester = $request->filled('semester_id')
            ? Semester::find($request->semester_id)
            : null;
        $semester = $selectedSemester ?? $semesterAktif;

        $juzSelected = (int) $request->get('juz', 0);
        if ($juzSelected < 1 || $juzSelected > 30) {
            $juzSelected = 0;
        }

        $kelasList = Kelas::where('status', 'aktif')
            ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
            ->get();

        $kelasList = $kelasList->map(function ($k) use ($semester, $semesterAktif) {
            $siswaIds = null;
            if ($semester && $semester->id !== $semesterAktif?->id) {
                $siswaIds = SemesterSiswa::where('semester_id', $semester->id)
                    ->where('kelas_id', $k->id)
                    ->pluck('siswa_id')
                    ->toArray();
            }

            $rekap = HafalanTahfidz::cacheStore()->remember(
                HafalanTahfidz::cacheKeyRekapKelas($k->id, $semester->id),
                now()->addHours(6),
                fn () => HafalanTahfidz::rekapPerKelasSampaiSemester($k->id, $semester, $siswaIds)
            );
            $k->rekap = $rekap;
            $k->avgJuz = count($rekap['perSiswa']) > 0
                ? round(collect($rekap['perSiswa'])->avg('juzHafal'), 1)
                : 0;

            return $k;
        });

        $juzSurat = $juzSelected
            ? HafalanTahfidz::suratDalamJuz($juzSelected)
            : collect();

        $persentaseJuz = $juzSelected
            ? $this->buildPersentaseJuz($kelasList, $juzSelected, $semester)
            : [];

        $tahunAjaranList = TahunAjaran::orderBy('nama', 'desc')->get();
        $semesterMap = Semester::orderBy('tanggal_mulai')
            ->get()
            ->groupBy('tahun_ajaran')
            ->map(fn ($items) => $items->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()->toArray())
            ->toArray();

        return view('admin.tahfidz.index', compact(
            'kelasList', 'semester', 'semesterAktif', 'juzSelected', 'juzSurat', 'persentaseJuz',
            'tahunAjaranList', 'semesterMap'
        ));
    }

    /**
     * Build persentase hafalan per juz untuk semua siswa aktif di kelas tartil.
     */
    private function buildPersentaseJuz($kelasList, int $juz, ?Semester $semester): array
    {
        if (! $semester) {
            return [];
        }

        $result = [];
        foreach ($kelasList as $kelas) {
            foreach ($kelas->rekap['perSiswa'] ?? [] as $s) {
                $persentase = HafalanTahfidz::cacheStore()->remember(
                    HafalanTahfidz::cacheKeyPersentaseJuz($s['siswa']['id'], $juz, $semester->id),
                    now()->addHours(6),
                    fn () => HafalanTahfidz::hitungPersentaseJuzSampaiSemester($s['siswa']['id'], $juz, $semester)
                );

                $result[] = [
                    'siswa' => $s['siswa'],
                    'kelas' => $kelas->nama,
                    'persentase' => $persentase,
                ];
            }
        }

        return collect($result)->sortByDesc('persentase.persentase')->values()->toArray();
    }

    // ==================== ADMIN: REKAP SEMESTER PER JUZ ====================
    public function adminRekapSemester(Request $request)
    {
        $semesterAktif = Semester::aktif()->first();
        $selectedSemester = $request->filled('semester_id')
            ? Semester::find($request->semester_id)
            : null;
        $semester = $selectedSemester ?? $semesterAktif;

        $juzSelected = (int) $request->get('juz', 1);
        if ($juzSelected < 1 || $juzSelected > 30) {
            $juzSelected = 1;
        }

        $kelasList = Kelas::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        $rekapPerKelas = [];
        foreach ($kelasList as $kelas) {
            $siswaIds = null;
            if ($semester && $semester->id !== $semesterAktif?->id) {
                $siswaIds = SemesterSiswa::where('semester_id', $semester->id)
                    ->where('kelas_id', $kelas->id)
                    ->pluck('siswa_id')
                    ->toArray();
            }

            $juzData = HafalanTahfidz::rekapJuzPerKelas($kelas->id, $semester, $siswaIds);
            $kelasJuz = collect($juzData)->firstWhere('juz', $juzSelected);
            $totalSiswa = collect($juzData)->first()['totalSiswa'] ?? 0;

            $rekapPerKelas[] = [
                'kelas' => $kelas,
                'totalSiswa' => $totalSiswa,
                'juzData' => $juzData,
                'juzSelected' => $kelasJuz,
            ];
        }

        $tahunAjaranList = TahunAjaran::orderBy('nama', 'desc')->get();
        $semesterMap = Semester::orderBy('tanggal_mulai')
            ->get()
            ->groupBy('tahun_ajaran')
            ->map(fn ($items) => $items->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()->toArray())
            ->toArray();

        $totalSummary = [
            'totalSiswa' => collect($rekapPerKelas)->sum('totalSiswa'),
            'sudahHafal' => collect($rekapPerKelas)->sum(fn ($k) => $k['juzSelected']['sudahHafal'] ?? 0),
            'tuntas' => collect($rekapPerKelas)->sum(fn ($k) => $k['juzSelected']['tuntas'] ?? 0),
        ];

        return view('admin.tahfidz.rekap-semester', compact(
            'semester', 'semesterAktif', 'juzSelected', 'rekapPerKelas',
            'tahunAjaranList', 'semesterMap', 'totalSummary'
        ));
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

        $suratHafalList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->where('status', 'hafal')
            ->whereNotNull('surat_id')
            ->with('surat')
            ->orderBy('tanggal_hafalan', 'desc')
            ->get()
            ->groupBy('surat_id')
            ->map(fn ($items) => $items->first())
            ->values();

        return view('admin.tahfidz.detail-siswa', compact('siswa', 'hafalanList', 'totalJuzHafal', 'juzDistinct', 'semester', 'suratHafalList'));
    }

    // ==================== ADMIN: FORM TAMBAH HAFALAN ====================
    public function adminCreate(Request $request)
    {
        $siswa = Siswa::findOrFail($request->siswa_id);
        $suratList = Surat::orderBy('urutan')->get();
        $juzSuratMap = JuzSurat::with('surat')
            ->orderBy('juz')
            ->orderBy('ayat_mulai')
            ->get()
            ->groupBy('juz')
            ->map(fn ($items) => $items->pluck('surat_id')->unique()->values()->toArray())
            ->toArray();
        $semester = Semester::aktif()->first();

        return view('admin.tahfidz.create', compact('siswa', 'suratList', 'juzSuratMap', 'semester'));
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

        HafalanTahfidz::forgetRekapKelasCache($hafalan->kelas_id, $hafalan->semester_id);

        return redirect()->route('admin.tahfidz.detail-siswa', $request->siswa_id)
            ->with('success', "Hafalan Juz {$hafalan->juz} berhasil dicatat.");
    }

    // ==================== ADMIN: HAPUS HAFALAN ====================
    public function adminDestroy(HafalanTahfidz $hafalan)
    {
        $siswaId = $hafalan->siswa_id;
        $juz = $hafalan->juz;
        $kelasId = $hafalan->kelas_id;
        $semesterId = $hafalan->semester_id;
        $hafalan->delete();

        HafalanTahfidz::forgetRekapKelasCache($kelasId, $semesterId);

        return redirect()->route('admin.tahfidz.detail-siswa', $siswaId)
            ->with('success', "Hafalan Juz {$juz} dihapus.");
    }

    // ==================== GURU: INDEX (KELAS SENDIRI) ====================
    public function guruIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $juzSelected = (int) $request->get('juz', 0);
        if ($juzSelected < 1 || $juzSelected > 30) {
            $juzSelected = 0;
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->first();

        if (! $kelas) {
            return view('guru.tahfidz.index', [
                'kelas' => null,
                'rekap' => null,
                'semester' => null,
                'juzSelected' => 0,
                'juzSurat' => collect(),
                'persentaseJuz' => [],
            ]);
        }

        $semester = Semester::aktif()->first();
        $rekap = HafalanTahfidz::rekapPerKelas($kelas->id, $semester?->id);

        $juzSurat = $juzSelected
            ? HafalanTahfidz::suratDalamJuz($juzSelected)
            : collect();

        $persentaseJuz = $juzSelected
            ? collect($rekap['perSiswa'] ?? [])
                ->map(fn ($s) => [
                    'siswa' => $s['siswa'],
                    'persentase' => HafalanTahfidz::hitungPersentaseJuz($s['siswa']['id'], $juzSelected, $semester?->id),
                ])
                ->sortByDesc('persentase.persentase')
                ->values()
                ->toArray()
            : [];

        $juzSuratMap = JuzSurat::orderBy('juz')
            ->orderBy('ayat_mulai')
            ->get()
            ->groupBy('juz')
            ->map(fn ($items) => $items->pluck('surat_id')->unique()->values()->toArray())
            ->toArray();

        return view('guru.tahfidz.index', compact(
            'kelas', 'rekap', 'semester', 'juzSelected', 'juzSurat', 'persentaseJuz', 'juzSuratMap'
        ));
    }

    // ==================== GURU: DETAIL SISWA ====================
    public function guruDetailSiswa(Siswa $siswa)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        // Validasi: siswa harus di kelas guru ini
        if ($siswa->kelasTartil?->guru_id !== $guru->id) {
            return back()->with('error', 'Siswa tidak ada di kelas Anda.');
        }

        $semester = Semester::aktif()->first();
        $hafalanList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->with(['surat', 'semester', 'guru'])
            ->orderBy('tanggal_hafalan', 'desc')
            ->get();

        $totalJuzHafal = HafalanTahfidz::totalJuzHafal($siswa->id);
        $juzDistinct = $hafalanList->where('status', 'hafal')->pluck('juz')->unique()->sort()->values();

        $suratHafalList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->where('status', 'hafal')
            ->whereNotNull('surat_id')
            ->with('surat')
            ->orderBy('tanggal_hafalan', 'desc')
            ->get()
            ->groupBy('surat_id')
            ->map(fn ($items) => $items->first())
            ->values();

        return view('guru.tahfidz.detail-siswa', compact('siswa', 'hafalanList', 'totalJuzHafal', 'juzDistinct', 'semester', 'suratHafalList'));
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
        if (! $guru || $siswa->kelasTartil?->guru_id !== $guru->id) {
            return back()->with('error', 'Siswa tidak ada di kelas Anda.');
        }

        $hafalan = HafalanTahfidz::create([
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

        HafalanTahfidz::forgetRekapKelasCache($hafalan->kelas_id, $hafalan->semester_id);

        return back()->with('success', 'Hafalan berhasil dicatat.');
    }

    // ==================== STATISTIK: DATA TAHFIDZ & HAFALAN ====================
    public static function buildTahfidzData(): array
    {
        $kelasList = Kelas::where('status', 'aktif')
            ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
            ->get();

        $totalSiswaTahfidz = $kelasList->sum('siswas_count');
        $totalKelasTahfidz = $kelasList->count();

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
        $perKelas = $kelasList->map(function ($k) {
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
