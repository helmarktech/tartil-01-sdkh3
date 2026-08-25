<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasLibur;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgressJurnalController extends Controller
{
    // ═══════════════════════════════════════════════════
    // PROGRESS JURNAL — 4 step: TA → Semester → Guru → Kelas
    // ═══════════════════════════════════════════════════
    public function progressJurnal(Request $request)
    {
        $step = $request->get('step', 'ta');
        $ta = $request->get('ta');
        $semesterId = $request->get('semester_id');
        $guruId = $request->get('guru_id');
        $kelasId = $request->get('kelas_id');

        // Safety: jika step 'guru'/'kelas' tapi semester_id tidak ada, redirect ke pilih semester
        if (($step === 'guru' || $step === 'kelas') && ! $semesterId && $ta) {
            return redirect()->route('admin.progress.jurnal', ['step' => 'semester', 'ta' => $ta]);
        }

        // Step 1: Pilih Tahun Ajaran
        if ($step === 'ta' || ! $ta) {
            $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();

            return view('admin.progress.jurnal', compact('step', 'tahunAjarans'));
        }

        // Step 2: Pilih Semester
        if ($step === 'semester' || ($ta && ! $semesterId)) {
            $semesters = Semester::where('tahun_ajaran', $ta)
                ->orderBy('tanggal_mulai')
                ->get();

            return view('admin.progress.jurnal', compact('step', 'ta', 'semesters'));
        }

        // Step 3: Pilih Guru
        if ($step === 'guru' || ($semesterId && ! $guruId && $step !== 'kelas')) {
            // Tampilkan semua kelas aktif beserta guru pengampunya untuk semester ini,
            // bukan hanya kelas yang sudah punya jurnal/siswa. Ini memastikan kelas baru
            // atau kelas yang belum mengisi jurnal tetap muncul di monitoring.
            $gurus = Guru::whereHas('kelas', fn ($q) => $q->where('status', 'aktif'))
                ->with(['kelas' => fn ($q) => $q->where('status', 'aktif')->orderBy('nama')])
                ->get();

            // Hitung progress jurnal: distinct tanggal / target hari efektif
            foreach ($gurus as $guru) {
                foreach ($guru->kelas as $kelas) {
                    $kelas->progressJurnal = $this->hitungProgressJurnal($kelas->id, [$semesterId]);
                }
            }

            $semester = Semester::find($semesterId);

            return view('admin.progress.jurnal', compact('step', 'ta', 'semesterId', 'semester', 'gurus'));
        }

        // Step 4: Detail per Kelas
        if ($step === 'kelas' && $kelasId) {
            $semester = Semester::find($semesterId);
            $kelas = Kelas::with('guru')->findOrFail($kelasId);

            // Ambil siswa dari snapshot + jurnal
            $siswaIdsSemester = SemesterSiswa::where('semester_id', $semesterId)
                ->where('kelas_id', $kelasId)
                ->pluck('siswa_id')
                ->unique()
                ->toArray();

            $siswaIdsJurnal = JurnalHarian::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId)
                ->distinct('siswa_id')
                ->pluck('siswa_id')
                ->toArray();

            $siswaIds = array_unique(array_merge($siswaIdsSemester, $siswaIdsJurnal));
            $siswas = Siswa::whereIn('id', $siswaIds)
                ->orderBy('nama')
                ->get();

            // Hitung progress kelas DULU (diperlukan untuk perhitungan persentase siswa reguler)
            $progressKelas = $this->hitungProgressJurnal($kelasId, [$semesterId]);

            // Detail jurnal per siswa (dengan penyesuaian siswa mutasi)
            $semester = Semester::find($semesterId);
            $detailSiswa = [];

            $semesterMulai = $semester->tanggal_mulai ?? $semester->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas->getAwalHitungHari($semesterMulai);
            $endDate = min(Carbon::parse($semester->tanggal_selesai ?? now()), now());
            $hariLiburList = $kelas->liburs()
                ->whereBetween('tanggal', [$awalHitung, $endDate])
                ->pluck('tanggal')
                ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
                ->toArray();

            // Total pertemuan kelas = unik tanggal aktif/non-libur yang sudah ada jurnal (sama untuk semua siswa)
            $totalHariKelas = JurnalHarian::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId)
                ->distinct('tanggal')
                ->pluck('tanggal')
                ->filter(function ($t) use ($awalHitung, $endDate, $hariLiburList) {
                    $tgl = Carbon::parse($t);

                    return $tgl->between($awalHitung, $endDate)
                        && $this->isHariAktif($tgl)
                        && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
                })
                ->count();

            foreach ($siswas as $siswa) {
                $jurnalRows = JurnalHarian::where('siswa_id', $siswa->id)
                    ->where('semester_id', $semesterId)
                    ->where('kelas_id', $kelasId)
                    ->get()
                    ->filter(function ($j) use ($awalHitung, $endDate, $hariLiburList) {
                        $tgl = Carbon::parse($j->tanggal);

                        return $tgl->between($awalHitung, $endDate)
                            && $this->isHariAktif($tgl)
                            && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
                    });

                $countB = $jurnalRows->where('penilaian', 'B')->count();
                $countC = $jurnalRows->where('penilaian', 'C')->count();
                $countK = $jurnalRows->where('penilaian', 'K')->count();
                $hadirDinilai = $countB + $countC + $countK;

                // Total pertemuan yang ditampilkan sama untuk semua siswa (total pertemuan kelas)
                $totalHari = $totalHariKelas;

                // Penyesuaian target untuk siswa mutasi
                $targetDinamis = null;
                $persenSiswa = 0;
                if ($siswa->isMutasi && $semester) {
                    $targetDinamis = $siswa->getTargetPertemuanDinamis($semester);
                    if ($targetDinamis && $targetDinamis > 0) {
                        $persenSiswa = min(100, round(($hadirDinilai / $targetDinamis) * 100));
                    }
                } else {
                    // Siswa reguler: persentase dari total pertemuan kelas
                    $persenSiswa = $totalHariKelas > 0
                        ? min(100, round(($hadirDinilai / $totalHariKelas) * 100))
                        : 0;
                }

                $detailSiswa[] = [
                    'siswa' => $siswa,
                    'total' => $totalHari,
                    'B' => $countB,
                    'C' => $countC,
                    'K' => $countK,
                    'is_mutasi' => $siswa->isMutasi,
                    'tanggal_masuk' => $siswa->tanggal_masuk_kelas_tartil,
                    'target_dinamis' => $targetDinamis,
                    'persen_siswa' => $persenSiswa,
                ];
            }

            return view('admin.progress.jurnal', compact(
                'step', 'ta', 'semesterId', 'semester', 'guruId', 'kelas', 'detailSiswa', 'progressKelas'
            ));
        }

        return redirect()->route('admin.progress.jurnal');
    }

    // ═══════════════════════════════════════════════════
    // PROGRESS ABSENSI — 4 step: TA → Semester → Guru → Kelas
    // ═══════════════════════════════════════════════════
    public function progressAbsensi(Request $request)
    {
        $step = $request->get('step', 'ta');
        $ta = $request->get('ta');
        $semesterId = $request->get('semester_id');
        $guruId = $request->get('guru_id');
        $kelasId = $request->get('kelas_id');

        // Safety: jika step 'guru'/'kelas' tapi semester_id tidak ada, redirect ke pilih semester
        if (($step === 'guru' || $step === 'kelas') && ! $semesterId && $ta) {
            return redirect()->route('admin.progress.absensi', ['step' => 'semester', 'ta' => $ta]);
        }

        // Step 1: Pilih Tahun Ajaran
        if ($step === 'ta' || ! $ta) {
            $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();

            return view('admin.progress.absensi', compact('step', 'tahunAjarans'));
        }

        // Step 2: Pilih Semester
        if ($step === 'semester' || ($ta && ! $semesterId)) {
            $semesters = Semester::where('tahun_ajaran', $ta)
                ->orderBy('tanggal_mulai')
                ->get();

            return view('admin.progress.absensi', compact('step', 'ta', 'semesters'));
        }

        // Step 3: Pilih Guru
        if ($step === 'guru' || ($semesterId && ! $guruId && $step !== 'kelas')) {
            // Tampilkan semua kelas aktif beserta guru pengampunya untuk semester ini,
            // bukan hanya kelas yang sudah punya jurnal/siswa.
            $gurus = Guru::whereHas('kelas', fn ($q) => $q->where('status', 'aktif'))
                ->with(['kelas' => fn ($q) => $q->where('status', 'aktif')->orderBy('nama')])
                ->get();

            // Hitung progress absensi: entry dengan penilaian / total entry
            foreach ($gurus as $guru) {
                foreach ($guru->kelas as $kelas) {
                    $kelas->progressAbsensi = $this->hitungProgressAbsensi($kelas->id, [$semesterId]);
                }
            }

            $semester = Semester::find($semesterId);

            return view('admin.progress.absensi', compact('step', 'ta', 'semesterId', 'semester', 'gurus'));
        }

        // Step 4: Detail per Kelas
        if ($step === 'kelas' && $kelasId) {
            $semester = Semester::find($semesterId);
            $kelas = Kelas::with('guru')->findOrFail($kelasId);

            $siswaIdsSemester = SemesterSiswa::where('semester_id', $semesterId)
                ->where('kelas_id', $kelasId)
                ->pluck('siswa_id')
                ->unique()
                ->toArray();

            $siswaIdsJurnal = JurnalHarian::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId)
                ->distinct('siswa_id')
                ->pluck('siswa_id')
                ->toArray();

            $siswaIds = array_unique(array_merge($siswaIdsSemester, $siswaIdsJurnal));
            $siswas = Siswa::whereIn('id', $siswaIds)
                ->orderBy('nama')
                ->get();

            $detailSiswa = [];

            $semesterMulai = $semester->tanggal_mulai ?? $semester->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas->getAwalHitungHari($semesterMulai);
            $endDate = min(Carbon::parse($semester->tanggal_selesai ?? now()), now());
            $hariLiburList = $kelas->liburs()
                ->whereBetween('tanggal', [$awalHitung, $endDate])
                ->pluck('tanggal')
                ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
                ->toArray();

            foreach ($siswas as $siswa) {
                $jurnalRows = JurnalHarian::where('siswa_id', $siswa->id)
                    ->where('semester_id', $semesterId)
                    ->where('kelas_id', $kelasId)
                    ->get()
                    ->filter(function ($j) use ($awalHitung, $endDate, $hariLiburList) {
                        $tgl = Carbon::parse($j->tanggal);

                        return $tgl->between($awalHitung, $endDate)
                            && $this->isHariAktif($tgl)
                            && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
                    });

                $totalEntry = $jurnalRows->count();
                $dinilai = $jurnalRows->whereNotNull('penilaian')->count();

                $belum = $totalEntry - $dinilai;

                // Penyesuaian untuk siswa mutasi: totalEntry sudah otomatis
                // lebih kecil karena jurnal sebelum tanggal masuk tidak ada
                $detailSiswa[] = [
                    'siswa' => $siswa,
                    'total' => $totalEntry,
                    'dinilai' => $dinilai,
                    'belum' => $belum,
                    'persen' => $totalEntry > 0 ? round(($dinilai / $totalEntry) * 100) : 0,
                    'is_mutasi' => $siswa->isMutasi,
                    'tanggal_masuk' => $siswa->tanggal_masuk_kelas_tartil,
                ];
            }

            $progressKelas = $this->hitungProgressAbsensi($kelasId, [$semesterId]);

            return view('admin.progress.absensi', compact(
                'step', 'ta', 'semesterId', 'semester', 'guruId', 'kelas', 'detailSiswa', 'progressKelas'
            ));
        }

        return redirect()->route('admin.progress.absensi');
    }

    // ═══════════════════════════════════════════════════
    // MANAJEMEN HARI LIBUR PER KELAS
    // ═══════════════════════════════════════════════════

    /**
     * Tampilkan daftar hari libur dan form tandai libur massal.
     */
    public function liburIndex(Request $request)
    {
        $semesterAktif = Semester::aktif()->first();

        $liburList = collect();
        $kelasAktif = collect();
        if ($semesterAktif) {
            $mulai = $semesterAktif->tanggal_mulai;
            $selesai = min($semesterAktif->tanggal_selesai, now());

            $liburList = KelasLibur::with('kelas')
                ->whereBetween('tanggal', [$mulai, $selesai])
                ->orderBy('tanggal', 'desc')
                ->get()
                ->groupBy(fn ($l) => $l->tanggal->format('Y-m-d'))
                ->map(function ($items, $tgl) {
                    return [
                        'tanggal' => \Carbon\Carbon::parse($tgl),
                        'keterangan' => $items->first()->keterangan,
                        'jumlah_kelas' => $items->count(),
                        'contoh_kelas' => $items->first()->kelas?->nama ?? '-',
                        'items' => $items,
                    ];
                })
                ->values();

            $kelasAktif = Kelas::where('status', 'aktif')
                ->with('guru')
                ->orderBy('nama')
                ->get()
                ->map(function ($k) use ($semesterAktif) {
                    $awalHitung = $k->getAwalHitungHari($semesterAktif->tanggal_mulai);

                    return [
                        'kelas' => $k,
                        'tanggal_mulai_efektif' => $awalHitung,
                    ];
                });
        }

        return view('admin.libur.index', compact('semesterAktif', 'liburList', 'kelasAktif'));
    }

    /**
     * Tandai hari libur untuk kelas tertentu atau semua kelas aktif.
     */
    public function liburStore(Request $request)
    {
        $isMassal = $request->boolean('semua_kelas');

        $validated = $request->validate([
            'kelas_id' => $isMassal ? 'nullable' : 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'keterangan' => 'required|max:255',
        ]);

        $createdBy = auth()->id();
        $tanggal = $validated['tanggal'];
        $keterangan = $validated['keterangan'];

        if ($isMassal) {
            $kelasList = Kelas::where('status', 'aktif')->get();

            if ($kelasList->isEmpty()) {
                return back()->with('error', 'Tidak ada kelas aktif untuk ditandai libur.');
            }

            $count = 0;
            foreach ($kelasList as $kelas) {
                KelasLibur::firstOrCreate(
                    ['kelas_id' => $kelas->id, 'tanggal' => $tanggal],
                    [
                        'kelas_id' => $kelas->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'created_by' => $createdBy,
                    ]
                );
                $count++;
            }

            return back()->with('success', "Hari libur {$tanggal} ditandai untuk {$count} kelas aktif.");
        }

        $validated['created_by'] = $createdBy;

        KelasLibur::firstOrCreate(
            ['kelas_id' => $validated['kelas_id'], 'tanggal' => $validated['tanggal']],
            $validated
        );

        return back()->with('success', 'Hari libur berhasil ditandai.');
    }

    /**
     * Hapus tanda hari libur.
     */
    public function liburDestroy(KelasLibur $libur)
    {
        $libur->delete();

        return back()->with('success', 'Tanda libur berhasil dihapus.');
    }

    /**
     * Hapus semua tanda hari libur untuk tanggal tertentu (massal).
     */
    public function liburDestroyByTanggal(Request $request, string $tanggal)
    {
        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            return back()->with('error', 'Tidak ada semester aktif.');
        }

        $count = KelasLibur::whereDate('tanggal', $tanggal)->delete();

        return back()->with('success', "{$count} tanda libur pada tanggal " . \Carbon\Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil dihapus.');
    }

    // ═══════════════════════════════════════════════════
    // MONITORING: Guru yang belum mengisi jurnal
    // ═══════════════════════════════════════════════════

    /**
     * Monitoring guru yang belum mengisi jurnal.
     *
     * Logika (dengan penyesuaian hari libur per kelas):
     * 1. Hitung hari kerja (Senin-Kamis) dari awal semester sampai hari ini
     * 2. Kurangi hari libur yang sudah ditandai per kelas
     * 3. Target per kelas = hari kerja − hari libur kelas tersebut
     * 4. Terisi = distinct tanggal jurnal yang sudah ada
     * 5. Kurang = target − terisi
     */
    public function monitoringGuru(Request $request)
    {
        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            return view('admin.monitoring.guru', [
                'semesterAktif' => null,
                'dataGuru' => collect(),
                'ringkasan' => [],
            ]);
        }

        $endDate = min($semesterAktif->tanggal_selesai, now());

        // Hitung hari kerja dasar (Senin-Kamis)
        $hariKerjaDasar = $this->hitungHariKerja($semesterAktif->tanggal_mulai, $endDate);

        // Ambil semua kelas aktif dengan guru
        $kelasList = Kelas::where('status', 'aktif')
            ->with(['guru', 'siswas' => fn ($q) => $q->where('status', 'aktif')])
            ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
            ->get();

        $dataGuru = [];
        $totalKelasKurang = 0;
        $totalHariKurang = 0;
        $totalHariLibur = 0;

        foreach ($kelasList as $kelas) {
            $guru = $kelas->guru;
            if (! $guru) {
                continue;
            }

            // Hitung hari libur khusus kelas ini (dari tanggal_dibuat atau awal semester)
            // Referensi tanggal mulai dari semester yang terhubung ke tahun ajaran
            $semesterMulai = $semesterAktif->tanggal_mulai ?? $semesterAktif->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas->getAwalHitungHari($semesterMulai);
            $hariKerjaKelas = $this->hitungHariKerja($awalHitung, $endDate);
            $hariLibur = $kelas->jumlahHariLibur($awalHitung, $endDate);
            $targetHari = max(0, $hariKerjaKelas - $hariLibur);

            // Daftar tanggal libur untuk kelas ini dalam rentang perhitungan
            $hariLiburList = $kelas->liburs()
                ->whereBetween('tanggal', [$awalHitung, $endDate])
                ->pluck('tanggal')
                ->map(fn ($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
                ->toArray();

            // Distinct tanggal jurnal yang sudah ada untuk kelas ini,
            // difilter hanya yang masuk dalam target (rentang awal kelas, hari kerja, bukan libur).
            $tanggalJurnal = JurnalHarian::where('kelas_id', $kelas->id)
                ->where('semester_id', $semesterAktif->id)
                ->distinct('tanggal')
                ->pluck('tanggal');

            $tanggalTerisi = $tanggalJurnal->map(function ($t) {
                return \Carbon\Carbon::parse($t)->format('Y-m-d');
            });

            $terisi = $tanggalJurnal->filter(function ($t) use ($awalHitung, $endDate, $hariLiburList) {
                $tgl = \Carbon\Carbon::parse($t);

                return $tgl->between($awalHitung, $endDate)
                    && $this->isHariAktif($tgl)
                    && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
            })->count();

            // Daftar tanggal yang seharusnya diisi tetapi belum ada jurnal
            $tanggalKurang = collect();
            $current = $awalHitung->copy();
            while ($current->lte($endDate)) {
                if ($this->isHariAktif($current)
                    && ! in_array($current->format('Y-m-d'), $hariLiburList)
                    && ! $tanggalTerisi->contains($current->format('Y-m-d'))
                ) {
                    $tanggalKurang->push($current->copy());
                }
                $current->addDay();
            }

            $kurang = $tanggalKurang->count();

            // Ambil tanggal terakhir mengisi jurnal
            $terakhir = JurnalHarian::where('kelas_id', $kelas->id)
                ->where('semester_id', $semesterAktif->id)
                ->max('tanggal');

            $dataGuru[$guru->id]['nama'] = $guru->nama;
            $dataGuru[$guru->id]['inisial'] = $guru->initials ?? substr($guru->nama, 0, 1);
            $dataGuru[$guru->id]['kelas'][] = [
                'kelas' => $kelas,
                'jumlah_siswa' => $kelas->siswas_count,
                'hari_kerja' => $hariKerjaDasar,
                'hari_libur' => $hariLibur,
                'target_hari' => $targetHari,
                'terisi' => $terisi,
                'kurang' => max(0, $kurang),
                'tanggal_kurang' => $tanggalKurang,
                'persen' => $targetHari > 0 ? min(100, round(($terisi / $targetHari) * 100)) : 0,
                'terakhir' => $terakhir,
            ];

            $totalHariLibur += $hariLibur;

            if ($kurang > 0) {
                $totalKelasKurang++;
                $totalHariKurang += $kurang;
            }
        }

        // Urutkan: guru dengan kelas paling tertinggal di atas
        uasort($dataGuru, function ($a, $b) {
            $minA = min(array_column($a['kelas'], 'persen'));
            $minB = min(array_column($b['kelas'], 'persen'));

            return $minA <=> $minB;
        });

        $ringkasan = [
            'hari_kerja' => $hariKerjaDasar,
            'total_hari_libur' => $totalHariLibur,
            'total_kelas' => $kelasList->count(),
            'total_kelas_kurang' => $totalKelasKurang,
            'total_hari_kurang' => $totalHariKurang,
        ];

        return view('admin.monitoring.guru', compact('semesterAktif', 'dataGuru', 'ringkasan'));
    }

    /**
     * Cek apakah tanggal merupakan hari aktif pembelajaran (Senin-Kamis).
     */
    private function isHariAktif($tanggal): bool
    {
        // dayOfWeek: 0=Minggu, 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu
        return $tanggal->dayOfWeek >= 1 && $tanggal->dayOfWeek <= 4;
    }

    /**
     * Hitung hari kerja (Senin-Kamis) antara dua tanggal.
     */
    private function hitungHariKerja($mulai, $selesai): int
    {
        $count = 0;
        $current = $mulai->copy();
        $end = $selesai->copy();

        while ($current->lte($end)) {
            if ($this->isHariAktif($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    // ═══════════════════════════════════════════════════
    // Helpers
    // ═══════════════════════════════════════════════════

    /**
     * Progress Jurnal:
     * Persentase = distinct tanggal kelas ini / target hari di semester
     * Target = hari kerja (Senin-Kamis) − hari libur yang ditandai untuk kelas ini
     * Terisi hanya dihitung pada hari aktif (Senin-Kamis) dan bukan hari libur.
     */
    private function hitungProgressJurnal($kelasId, $semesterIds): array
    {
        $semester = Semester::find($semesterIds[0] ?? null);
        $kelas = Kelas::find($kelasId);

        // Target: hari kerja dari tanggal_dibuat (atau awal semester) − hari libur
        // Referensi tanggal mulai dari semester yang terhubung ke tahun ajaran
        if ($semester && $kelas) {
            $endDate = min($semester->tanggal_selesai, now());
            // Fallback: kalau semester tidak punya tanggal_mulai, gunakan tahun ajaran
            $semesterMulai = $semester->tanggal_mulai ?? $semester->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas->getAwalHitungHari($semesterMulai);
            $hariKerja = $this->hitungHariKerja($awalHitung, $endDate);
            $hariLibur = $kelas->jumlahHariLibur($awalHitung, $endDate);
            $targetPertemuan = max(1, $hariKerja - $hariLibur);

            $hariLiburList = $kelas->liburs()
                ->whereBetween('tanggal', [$awalHitung, $endDate])
                ->pluck('tanggal')
                ->map(fn ($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
                ->toArray();

            // Distinct tanggal jurnal yang hanya jatuh pada hari aktif dan bukan libur
            $tanggalTerisi = JurnalHarian::where('kelas_id', $kelasId)
                ->whereIn('semester_id', $semesterIds)
                ->distinct('tanggal')
                ->pluck('tanggal')
                ->filter(function ($t) use ($awalHitung, $endDate, $hariLiburList) {
                    $tgl = \Carbon\Carbon::parse($t);

                    return $tgl->between($awalHitung, $endDate)
                        && $this->isHariAktif($tgl)
                        && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
                })
                ->count();
        } else {
            $targetPertemuan = 1;
            $hariLibur = 0;
            $tanggalTerisi = JurnalHarian::where('kelas_id', $kelasId)
                ->whereIn('semester_id', $semesterIds)
                ->distinct('tanggal')
                ->count('tanggal');
        }

        return [
            'tanggal_terisi' => $tanggalTerisi,
            'target' => $targetPertemuan,
            'hari_libur' => $hariLibur,
            'is_kelas_baru' => $kelas?->is_kelas_baru ?? false,
            'tanggal_dibuat' => $kelas?->tanggal_dibuat,
            'persen' => $targetPertemuan > 0 ? min(100, round(($tanggalTerisi / $targetPertemuan) * 100)) : 0,
        ];
    }

    /**
     * Progress Absensi:
     * Persentase = total jurnal terisi / (jumlah_siswa × jumlah_hari_efektif) × 100
     *
     * jumlah_hari_efektif = hari kerja (Senin-Kamis) − hari libur yang ditandai
     * total_yang_seharusnya = setiap siswa seharusnya punya jurnal di setiap hari efektif
     * terisi = baris jurnal yang sudah ada (dengan nilai B/C/K) pada hari aktif dan bukan libur
     */
    private function hitungProgressAbsensi($kelasId, $semesterIds): array
    {
        $semester = Semester::find($semesterIds[0] ?? null);
        $kelas = Kelas::find($kelasId);

        // Ambil semua siswa yang terdaftar di kelas ini di semester ini
        $siswaIds = SemesterSiswa::whereIn('semester_id', $semesterIds)
            ->where('kelas_id', $kelasId)
            ->distinct('siswa_id')
            ->pluck('siswa_id')
            ->toArray();

        // Juga ambil siswa yang punya jurnal (backup jika snapshot kosong)
        $siswaIdsJurnal = JurnalHarian::where('kelas_id', $kelasId)
            ->whereIn('semester_id', $semesterIds)
            ->distinct('siswa_id')
            ->pluck('siswa_id')
            ->toArray();

        $siswaIds = array_unique(array_merge($siswaIds, $siswaIdsJurnal));
        $jumlahSiswa = count($siswaIds);

        // Hitung hari efektif = hari kerja dari tanggal_dibuat − hari libur
        // Referensi tanggal mulai dari semester yang terhubung ke tahun ajaran
        if ($semester && $kelas) {
            $endDate = min($semester->tanggal_selesai, now());
            $semesterMulai = $semester->tanggal_mulai ?? $semester->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas->getAwalHitungHari($semesterMulai);
            $hariKerja = $this->hitungHariKerja($awalHitung, $endDate);
            $hariLibur = $kelas->jumlahHariLibur($awalHitung, $endDate);
            $jumlahHari = max(0, $hariKerja - $hariLibur);

            $hariLiburList = $kelas->liburs()
                ->whereBetween('tanggal', [$awalHitung, $endDate])
                ->pluck('tanggal')
                ->map(fn ($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
                ->toArray();

            // Total jurnal yang sudah terisi hanya pada hari aktif dan bukan libur
            $terisi = JurnalHarian::where('kelas_id', $kelasId)
                ->whereIn('semester_id', $semesterIds)
                ->whereNotNull('penilaian')
                ->get()
                ->filter(function ($j) use ($awalHitung, $endDate, $hariLiburList) {
                    $tgl = \Carbon\Carbon::parse($j->tanggal);

                    return $tgl->between($awalHitung, $endDate)
                        && $this->isHariAktif($tgl)
                        && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
                })
                ->count();
        } else {
            $jumlahHari = JurnalHarian::where('kelas_id', $kelasId)
                ->whereIn('semester_id', $semesterIds)
                ->distinct('tanggal')
                ->count('tanggal');
            $hariLibur = 0;
            $terisi = JurnalHarian::where('kelas_id', $kelasId)
                ->whereIn('semester_id', $semesterIds)
                ->whereNotNull('penilaian')
                ->count();
        }

        // Total slot yang seharusnya terisi = siswa × hari efektif
        $totalSlot = $jumlahSiswa * $jumlahHari;

        return [
            'jumlah_siswa' => $jumlahSiswa,
            'jumlah_hari' => $jumlahHari,
            'total_slot' => $totalSlot,
            'terisi' => $terisi,
            'hari_libur' => $hariLibur ?? 0,
            'persen' => $totalSlot > 0 ? min(100, round(($terisi / $totalSlot) * 100)) : 0,
        ];
    }
}
