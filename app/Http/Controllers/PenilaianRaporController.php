<?php

namespace App\Http\Controllers;

use App\Models\IndikatorPenilaian;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\PenilaianRapor;
use App\Models\Semester;
use App\Models\SemesterPenilaianRapor;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianRaporController extends Controller
{
    // ═══════════════════════════════════════════════════
    // GURU: Daftar Penilaian Rapor Aktif
    // ═══════════════════════════════════════════════════
    public function index()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            Log::warning('Akses penilaian rapor tanpa data guru', ['user_id' => auth()->id()]);
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $kelasIds = Kelas::where('guru_id', $guru->id)->where('status', 'aktif')->pluck('id');

        $penilaians = SemesterPenilaianRapor::with('semester')
            ->whereIn('status', ['aktif', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($p) use ($kelasIds) {
                return PenilaianRapor::where('semester_penilaian_rapor_id', $p->id)
                    ->whereHas('siswa', fn($q) => $q->whereIn('kelas_tartil_id', $kelasIds))
                    ->exists();
            });

        return view('guru.penilaian-rapor.index', compact('penilaians', 'guru'));
    }

    // ═══════════════════════════════════════════════════
    // STEP 1: GURU Pilih Kelas
    // ═══════════════════════════════════════════════════
    public function pilihKelas($id)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $semesterPenilaian = SemesterPenilaianRapor::with('semester')->findOrFail($id);

        if ($semesterPenilaian->status !== 'aktif') {
            return redirect()->route('guru.penilaian-rapor.index')
                ->with('error', 'Penilaian rapor sudah tidak aktif.');
        }

        // Ambil kelas yang diajar guru & punya siswa di penilaian ini
        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->whereExists(function ($query) use ($id) {
                $query->select(DB::raw(1))
                    ->from('siswas')
                    ->whereColumn('siswas.kelas_tartil_id', 'kelas.id')
                    ->where('siswas.status', 'aktif')
                    ->whereExists(function ($q2) use ($id) {
                        $q2->select(DB::raw(1))
                            ->from('penilaian_rapors')
                            ->whereColumn('penilaian_rapors.siswa_id', 'siswas.id')
                            ->where('semester_penilaian_rapor_id', $id);
                    });
            })
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->get();

        if ($kelasList->isEmpty()) {
            return redirect()->route('guru.penilaian-rapor.index')
                ->with('error', 'Tidak ada kelas Anda yang terdaftar dalam penilaian ini.');
        }

        // Hitung progress per kelas
        foreach ($kelasList as $kelas) {
            $siswaIds = Siswa::where('kelas_tartil_id', $kelas->id)->where('status', 'aktif')->pluck('id');
            $total = PenilaianRapor::where('semester_penilaian_rapor_id', $id)
                ->whereIn('siswa_id', $siswaIds)
                ->count();
            $diisi = PenilaianRapor::where('semester_penilaian_rapor_id', $id)
                ->whereIn('siswa_id', $siswaIds)
                ->whereNotNull('nilai_angka')
                ->count();
            $kelas->progress_total = $total;
            $kelas->progress_diisi = $diisi;
            $kelas->progress_persen = $total > 0 ? round(($diisi / $total) * 100) : 0;
        }

        return view('guru.penilaian-rapor.pilih-kelas', compact('semesterPenilaian', 'kelasList'));
    }

    // ═══════════════════════════════════════════════════
    // STEP 2: GURU Isi Nilai (per kelas, mobile-first)
    // ═══════════════════════════════════════════════════
    public function isiNilai($id, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $semesterPenilaian = SemesterPenilaianRapor::with('semester')->findOrFail($id);
        $kelas = Kelas::with('guru')->findOrFail($kelasId);

        // Security: cek kepemilikan kelas
        if ($kelas->guru_id !== $guru->id) {
            Log::warning('Guru mencoba akses kelas lain', ['guru_id' => $guru->id, 'kelas_id' => $kelasId]);
            return redirect()->back()->with('error', 'Akses tidak diizinkan.');
        }

        if ($semesterPenilaian->isLocked()) {
            // Bisa lihat tapi tidak bisa edit
            $isLocked = true;
        } else {
            $isLocked = false;
            if ($semesterPenilaian->status !== 'aktif') {
                return redirect()->back()->with('error', 'Penilaian rapor tidak aktif.');
            }
        }

        // Ambil indikator sesuai jenis kelas SAJA
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
        if ($indikators->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada indikator untuk jenis kelas ' . $kelas->jenis . '.');
        }

        // Ambil siswa di kelas ini yang terdaftar di penilaian
        $siswas = Siswa::where('kelas_tartil_id', $kelasId)
            ->where('status', 'aktif')
            ->whereExists(function ($q) use ($id) {
                $q->select(DB::raw(1))
                    ->from('penilaian_rapors')
                    ->whereColumn('penilaian_rapors.siswa_id', 'siswas.id')
                    ->where('semester_penilaian_rapor_id', $id);
            })
            ->orderBy('nama')
            ->get();

        if ($siswas->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini yang terdaftar dalam penilaian.');
        }

        // Ambil penilaian untuk siswa di kelas ini
        $penilaianRapors = PenilaianRapor::where('semester_penilaian_rapor_id', $semesterPenilaian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->with('indikator')
            ->get()
            ->groupBy('siswa_id');

        // Build matrix: siswa x indikator
        $nilaiMap = [];
        foreach ($siswas as $siswa) {
            $siswaNilai = $penilaianRapors->get($siswa->id, collect());
            foreach ($indikators as $ind) {
                $n = $siswaNilai->firstWhere('indikator_penilaian_id', $ind->id);
                $nilaiMap[$siswa->id][$ind->id] = [
                    'nilai_angka' => $n?->nilai_angka,
                    'catatan' => $n?->catatan,
                    'is_diisi' => $n?->nilai_angka !== null,
                ];
            }
        }

        // Progress
        $totalEntry = $siswas->count() * $indikators->count();
        $filledEntry = 0;
        foreach ($nilaiMap as $sId => $indMap) {
            foreach ($indMap as $iId => $d) {
                if ($d['is_diisi']) $filledEntry++;
            }
        }
        $progressPersen = $totalEntry > 0 ? round(($filledEntry / $totalEntry) * 100) : 0;

        return view('guru.penilaian-rapor.isi-nilai', compact(
            'semesterPenilaian', 'kelas', 'indikators', 'siswas',
            'nilaiMap', 'progressPersen', 'filledEntry', 'totalEntry', 'isLocked'
        ));
    }

    // ═══════════════════════════════════════════════════
    // GURU: Simpan Nilai (bulk save per kelas)
    // ═══════════════════════════════════════════════════
    public function simpanNilai(Request $request, $id, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan.');
        }

        $semesterPenilaian = SemesterPenilaianRapor::findOrFail($id);
        $kelas = Kelas::findOrFail($kelasId);

        if ($kelas->guru_id !== $guru->id) {
            return redirect()->back()->with('error', 'Akses tidak diizinkan.');
        }

        if ($semesterPenilaian->isLocked()) {
            return redirect()->back()->with('error', 'Nilai rapor tidak dapat diubah. ' . $semesterPenilaian->lockReason());
        }

        if ($semesterPenilaian->status !== 'aktif') {
            return redirect()->back()->with('error', 'Penilaian rapor tidak aktif.');
        }

        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*.*' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated, $semesterPenilaian, $guru) {
                $now = now();
                foreach ($validated['nilai'] as $siswaId => $indikatorNilai) {
                    foreach ($indikatorNilai as $indikatorId => $nilaiAngka) {
                        // Jika kosong/null/0 → otomatis K (Kurang, nilai 0)
                        if ($nilaiAngka === null || $nilaiAngka === '' || $nilaiAngka === '0' || (int) $nilaiAngka === 0) {
                            $nilaiAngka = 0;
                            $huruf = 'K';
                        } else {
                            $nilaiAngka = (int) $nilaiAngka;
                            $huruf = PenilaianRapor::angkaKeHuruf($nilaiAngka);
                        }

                        PenilaianRapor::updateOrCreate(
                            [
                                'semester_penilaian_rapor_id' => $semesterPenilaian->id,
                                'siswa_id' => $siswaId,
                                'indikator_penilaian_id' => $indikatorId,
                            ],
                            [
                                'nilai_angka' => $nilaiAngka,
                                'nilai_huruf' => $huruf,
                                'diisi_oleh' => $guru->id,
                                'tanggal_diisi' => $now,
                            ]
                        );
                    }
                }
            });

            return redirect()->route('guru.penilaian-rapor.isi-nilai', [$id, $kelasId])
                ->with('success', 'Nilai berhasil disimpan. Nilai kosong otomatis diisi K.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan nilai rapor', [
                'error' => $e->getMessage(),
                'penilaian_id' => $id,
                'kelas_id' => $kelasId,
                'guru_id' => $guru->id,
            ]);
            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // GURU: Rekap Nilai Rapor per Kelas — LONG TERM
    // ═══════════════════════════════════════════════════
    public function rekapNilai(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        // ── 1. Daftar semester untuk filter historis ──
        $semesterList = Semester::orderBy('tanggal_mulai', 'desc')->get();

        // Ambil kelas yang diajar guru (kelas aktif saat ini)
        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->get();

        $kelasId = $request->get('kelas_id');
        $semesterId = $request->get('semester_id');
        $semesterFilter = null;
        $kelasAktif = null;
        $siswas = collect();
        $indikators = collect();
        $nilaiMap = [];
        $semesterPenilaian = null;
        $rataHarianMap = [];

        if ($kelasId) {
            $kelasAktif = Kelas::where('id', $kelasId)
                ->where('guru_id', $guru->id)
                ->first();

            if (!$kelasAktif) {
                return redirect()->back()->with('error', 'Akses tidak diizinkan.');
            }

            // ── 2. Tentukan semester penilaian ──
            if ($semesterId) {
                $semesterPenilaian = SemesterPenilaianRapor::where('semester_id', $semesterId)
                    ->whereIn('status', ['aktif', 'selesai'])
                    ->with('semester')
                    ->first();
                $semesterFilter = Semester::find($semesterId);
            }

            if (!$semesterPenilaian) {
                // Fallback: ambil semester penilaian terbaru
                $semesterPenilaian = SemesterPenilaianRapor::whereIn('status', ['aktif', 'selesai'])
                    ->latest()
                    ->first();
            }

            if ($semesterPenilaian) {
                $indikators = IndikatorPenilaian::byJenis($kelasAktif->jenis);

                // ── 3. LONG TERM: Ambil siswa dari data penilaian, bukan dari status aktif ──
                $siswaIdsDariPenilaian = PenilaianRapor::where('semester_penilaian_rapor_id', $semesterPenilaian->id)
                    ->distinct('siswa_id')
                    ->pluck('siswa_id')
                    ->toArray();

                // Filter siswa yang pernah di kelas ini (dari semester_siswa snapshot)
                $siswaIdsKelasIni = SemesterSiswa::where('semester_id', $semesterPenilaian->semester_id)
                    ->where('kelas_id', $kelasId)
                    ->pluck('siswa_id')
                    ->toArray();

                $siswaIds = array_unique(array_merge($siswaIdsDariPenilaian, $siswaIdsKelasIni));

                $siswas = Siswa::whereIn('id', $siswaIds)
                    ->orderBy('nama')
                    ->get();

                // Build matrix
                if ($siswas->isNotEmpty() && $indikators->isNotEmpty()) {
                    $penilaianRapors = PenilaianRapor::where('semester_penilaian_rapor_id', $semesterPenilaian->id)
                        ->whereIn('siswa_id', $siswas->pluck('id'))
                        ->with('indikator')
                        ->get()
                        ->groupBy('siswa_id');

                    foreach ($siswas as $siswa) {
                        $siswaNilai = $penilaianRapors->get($siswa->id, collect());
                        foreach ($indikators as $ind) {
                            $n = $siswaNilai->firstWhere('indikator_penilaian_id', $ind->id);
                            $nilaiMap[$siswa->id][$ind->id] = [
                                'nilai_angka' => $n?->nilai_angka,
                                'nilai_huruf' => $n?->nilai_huruf ?? '-',
                            ];
                        }
                    }
                }
            }

            // ── 4. Hitung rata-rata harian per siswa ──
            $rataHarianMap = [];
            if ($siswas->isNotEmpty()) {
                $rataHarianRows = JurnalHarian::whereIn('siswa_id', $siswas->pluck('id'))
                    ->whereNotNull('penilaian')
                    ->when($semesterPenilaian?->semester_id, fn($q, $sid) => $q->where('semester_id', $sid))
                    ->select('siswa_id',
                        DB::raw('COUNT(*) as total_hari'),
                        DB::raw('AVG(CASE 
                            WHEN penilaian = "B" THEN 100 
                            WHEN penilaian = "C" THEN 67 
                            WHEN penilaian = "K" THEN 33 
                            ELSE 0 END) as rata_harian')
                    )
                    ->groupBy('siswa_id')
                    ->get()
                    ->keyBy('siswa_id');

                foreach ($siswas as $siswa) {
                    $row = $rataHarianRows->get($siswa->id);
                    $rataHarianMap[$siswa->id] = $row ? (float) $row->rata_harian : 0;
                }
            }
        }

        return view('guru.penilaian-rapor.rekap-nilai', compact(
            'kelasList', 'kelasId', 'kelasAktif', 'siswas',
            'indikators', 'nilaiMap', 'semesterPenilaian', 'rataHarianMap',
            'semesterList', 'semesterId', 'semesterFilter'
        ));
    }

    // ═══════════════════════════════════════════════════
    // TRACK RECORD: 2-step (kelas → siswa) untuk guru
    // Admin: search nama/NIS
    // ═══════════════════════════════════════════════════
    public function trackRecordNilaiRapor(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user?->role === 'admin';

        if (!$isAdmin && !$user?->guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Data tidak ditemukan.');
        }

        // ── 1. Daftar semester untuk filter ──
        $semesterList = Semester::orderBy('tanggal_mulai', 'desc')->get();
        $semesterId = $request->get('semester_id');

        // ── 2. Daftar kelas ──
        if ($isAdmin) {
            $kelasList = Kelas::orderBy('nama')->get();
        } else {
            $kelasList = Kelas::where('guru_id', $user->guru->id)
                ->orderBy('nama')
                ->get();
        }

        // ── 3. Daftar siswa ──
        $kelasId = $request->get('kelas_id');
        $siswaId = $request->get('siswa_id');
        $search = $request->get('search');
        $siswaPilih = null;
        $riwayatPerSemester = [];

        // Guru: pilih kelas dulu → baru daftar siswa
        // Admin: bisa search nama/NIS langsung
        if ($isAdmin && $search) {
            // LONG TERM: Cari semua siswa yang punya penilaian (tanpa filter status)
            $siswaList = Siswa::where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('penilaian_rapors')
                    ->whereColumn('penilaian_rapors.siswa_id', 'siswas.id');
            })
            ->orderBy('nama')
            ->limit(50)
            ->get();
        } elseif ($kelasId) {
            // Filter kelas (security untuk guru)
            if (!$isAdmin) {
                $kelasAktif = Kelas::where('id', $kelasId)
                    ->where('guru_id', $user->guru->id)
                    ->first();
                if (!$kelasAktif) {
                    return redirect()->back()->with('error', 'Akses tidak diizinkan.');
                }
            }

            // LONG TERM: Ambil siswa yang pernah di kelas ini (dari semester_siswa snapshot)
            $siswaIdsSnapshot = SemesterSiswa::where('kelas_id', $kelasId)
                ->pluck('siswa_id')
                ->unique()
                ->toArray();

            // Juga ambil siswa yang punya penilaian (untuk kelas ini dari penilaian_rapors)
            $siswaIdsPenilaian = PenilaianRapor::whereIn('siswa_id', $siswaIdsSnapshot)
                ->distinct('siswa_id')
                ->pluck('siswa_id')
                ->toArray();

            $siswaIds = array_unique(array_merge($siswaIdsSnapshot, $siswaIdsPenilaian));

            $siswaList = Siswa::whereIn('id', $siswaIds)
                ->orderBy('nama')
                ->get();
        } else {
            $siswaList = collect();
        }

        // ── Track record siswa terpilih ──
        if ($siswaId) {
            $siswaPilih = Siswa::where('id', $siswaId)
                ->with('kelasTartil')
                ->first();

            if ($siswaPilih) {
                // Semua semester penilaian yang melibatkan siswa ini
                $semesterPenilaians = SemesterPenilaianRapor::with('semester')
                    ->whereIn('status', ['aktif', 'selesai'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->filter(function ($sp) use ($siswaId) {
                        return PenilaianRapor::where('semester_penilaian_rapor_id', $sp->id)
                            ->where('siswa_id', $siswaId)
                            ->exists();
                    });

                foreach ($semesterPenilaians as $sp) {
                    $nilaiSiswa = PenilaianRapor::where('semester_penilaian_rapor_id', $sp->id)
                        ->where('siswa_id', $siswaId)
                        ->with('indikator')
                        ->get();

                    if ($nilaiSiswa->isNotEmpty()) {
                        // Ambil jumlah indikator dari jenis kelas (bukan jumlah entry)
                        $jenisKelas = $siswaPilih->kelasTartil?->jenis ?? '';
                        $indikators = IndikatorPenilaian::byJenis($jenisKelas);
                        $jumlahIndikator = $indikators->count();
                        if ($jumlahIndikator === 0) {
                            $jumlahIndikator = $nilaiSiswa->pluck('indikator_penilaian_id')->unique()->count();
                        }

                        // Build nilai map — indikator tanpa nilai = 0
                        $nilaiByIndikator = $nilaiSiswa->keyBy('indikator_penilaian_id');
                        $nilaiLengkap = [];
                        $totalNilai = 0;
                        foreach ($indikators as $ind) {
                            $n = $nilaiByIndikator->get($ind->id);
                            $angka = $n ? ($n->nilai_angka ?? 0) : 0;
                            $nilaiLengkap[] = [
                                'indikator' => $ind,
                                'nilai_angka' => $angka,
                                'nilai_huruf' => $n ? ($n->nilai_huruf ?? 'K') : 'K',
                            ];
                            $totalNilai += $angka;
                        }

                        $rataNilai = $jumlahIndikator > 0 ? round($totalNilai / $jumlahIndikator) : 0;

                        // Rata-rata harian
                        $rataHarian = \App\Models\JurnalHarian::where('siswa_id', $siswaId)
                            ->whereNotNull('penilaian')
                            ->when($sp->semester_id, fn($q, $sid) => $q->where('semester_id', $sid))
                            ->select(DB::raw('AVG(CASE 
                                WHEN penilaian = "B" THEN 100 
                                WHEN penilaian = "C" THEN 67 
                                WHEN penilaian = "K" THEN 33 
                                ELSE 0 END) as rata'))
                            ->value('rata');

                        $riwayatPerSemester[] = [
                            'semester' => $sp->semester,
                            'keterangan' => $sp->keterangan,
                            'status' => $sp->status,
                            'nilai_lengkap' => $nilaiLengkap,
                            'jumlah_indikator' => $jumlahIndikator,
                            'rata_nilai_rapor' => $rataNilai,
                            'rata_nilai_harian' => $rataHarian ? round((float) $rataHarian) : 0,
                        ];
                    }
                }
            }
        }

        return view('guru.penilaian-rapor.track-record', compact(
            'kelasList', 'kelasId', 'siswaList', 'siswaId', 'siswaPilih',
            'riwayatPerSemester', 'search', 'isAdmin', 'semesterList', 'semesterId'
        ));
    }
}
