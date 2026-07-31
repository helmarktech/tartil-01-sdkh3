<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\IndikatorPenilaian;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Guru;
use Illuminate\Support\Facades\DB;
use App\Models\KopSuratRapor;
use App\Models\JurnalHarian;
use App\Models\RekapR2Akhir;
use Barryvdh\DomPDF\Facade\Pdf;

class PenilaianRaporInternalController extends Controller
{
    // ═══════════════════════════════════════════════
    // ADMIN: INDEX
    // ═══════════════════════════════════════════════
    public function adminIndex()
    {
        $penilaians = PenilaianRaporInternal::with('semester')
            ->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.penilaian-rapor-internal.index', compact('penilaians'));
    }

    // ═══════════════════════════════════════════════
    // ADMIN: STORE
    // ═══════════════════════════════════════════════
    public function adminStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'semester_id' => 'required|exists:semesters,id|unique:penilaian_rapor_internals,semester_id',
        ], [
            'semester_id.unique' => 'Semester ini sudah memiliki penilaian rapor. Hanya 1 penilaian per semester.',
        ]);

        try {
            PenilaianRaporInternal::create([
                'nama' => $request->nama,
                'semester_id' => $request->semester_id,
                'status' => 'aktif',
            ]);
            return back()->with('success', 'Penilaian rapor internal berhasil dibuat.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'unique')) {
                return back()->with('error', 'Semester ini sudah memiliki penilaian rapor. Hanya 1 penilaian per semester.');
            }
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════
    // ADMIN: HAPUS
    // ═══════════════════════════════════════════════
    public function adminDestroy(PenilaianRaporInternal $penilaian)
    {
        try {
            $penilaian->delete();
            return back()->with('success', 'Penilaian dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'foreign key')) {
                return back()->with('error', 'Penilaian tidak dapat dihapus karena masih terkait dengan data lain.');
            }
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════
    // GURU: INDEX
    // ═══════════════════════════════════════════════
    public function guruIndex()
    {
        $penilaians = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')->get();
        return view('guru.penilaian-rapor-internal.index', compact('penilaians'));
    }

    // ═══════════════════════════════════════════════
    // STEP 1: GURU PILIH KELAS
    // ═══════════════════════════════════════════════
    public function guruPilihKelas(PenilaianRaporInternal $penilaian)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        if ($kelasList->isEmpty()) {
            return redirect()->route('guru.penilaian-rapor-internal.index')
                ->with('error', 'Anda tidak memiliki kelas aktif.');
        }

        // Hitung progress per kelas
        $siswaIds = Siswa::whereIn('kelas_tartil_id', $kelasList->pluck('id'))
            ->where('status', 'aktif')
            ->pluck('id');

        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->whereIn('siswa_id', $siswaIds)
            ->whereNotNull('nilai')
            ->select('siswa_id')
            ->distinct()
            ->pluck('siswa_id');

        foreach ($kelasList as $kelas) {
            $siswaKelasIds = Siswa::where('kelas_tartil_id', $kelas->id)
                ->where('status', 'aktif')
                ->pluck('id');
            $total = $siswaKelasIds->count();

            $indikatorCount = IndikatorPenilaian::byJenis($kelas->jenis)->count();
            $siswaLengkap = 0;
            foreach ($siswaKelasIds as $sid) {
                $diisi = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                    ->where('siswa_id', $sid)
                    ->whereNotNull('nilai')
                    ->count();
                if ($diisi >= $indikatorCount) $siswaLengkap++;
            }

            $kelas->total_siswa = $total;
            $kelas->sudah_dinilai = $siswaLengkap;
            $kelas->jumlah_indikator = $indikatorCount;
            $kelas->progress_persen = $total > 0 ? round(($siswaLengkap / $total) * 100) : 0;
        }

        return view('guru.penilaian-rapor-internal.pilih-kelas', compact('penilaian', 'kelasList'));
    }

    // ═══════════════════════════════════════════════
    // STEP 2: GURU ISI NILAI PER INDIKATOR
    // Tampil matrix: siswa × indikator (sesuai jenis kelas)
    // ═══════════════════════════════════════════════
    public function guruIsiNilai(PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $kelas = Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->first();
        if (!$kelas) return back()->with('error', 'Kelas tidak ditemukan atau bukan kelas Anda.');

        // Ambil indikator sesuai jenis kelas (byJenis sudah return get())
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);

        if ($indikators->isEmpty()) {
            return back()->with('error', 'Belum ada indikator untuk jenis kelas "' . $kelas->jenis . '". Hubungi admin.');
        }

        // Ambil siswa kelas ini
        $siswas = Siswa::where('kelas_tartil_id', $kelasId)
            ->where('status', 'aktif')
            ->with('kelasReguler')
            ->orderBy('nama')
            ->get();

        if ($siswas->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
        }

        // Ambil nilai yang sudah diisi: [siswa_id][indikator_id] = nilai
        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get();

        $nilaiMap = [];
        foreach ($nilaiRows as $nr) {
            $nilaiMap[$nr->siswa_id][$nr->indikator_penilaian_id] = $nr->nilai;
        }

        // Hitung progress
        $totalSiswa = $siswas->count();
        $siswaLengkap = 0;
        foreach ($siswas as $s) {
            $diisi = 0;
            foreach ($indikators as $ind) {
                if (isset($nilaiMap[$s->id][$ind->id]) && $nilaiMap[$s->id][$ind->id] !== null) {
                    $diisi++;
                }
            }
            if ($diisi >= $indikators->count()) $siswaLengkap++;
        }
        $progress = $totalSiswa > 0 ? round(($siswaLengkap / $totalSiswa) * 100) : 0;

        return view('guru.penilaian-rapor-internal.isi-nilai', compact(
            'penilaian', 'kelas', 'siswas', 'indikators', 'nilaiMap', 'totalSiswa', 'siswaLengkap', 'progress'
        ));
    }

    // ═══════════════════════════════════════════════
    // GURU: SIMPAN NILAI PER INDIKATOR
    // ═══════════════════════════════════════════════
    public function guruSimpanNilai(Request $request, PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.*' => 'nullable|integer|min:1|max:100',
        ]);

        DB::transaction(function () use ($request, $penilaian, $guru) {
            $now = now();
            foreach ($request->nilai as $siswaId => $indikatorNilais) {
                foreach ($indikatorNilais as $indikatorId => $nilai) {
                    if ($nilai === null || $nilai === '') continue;

                    PenilaianRaporNilai::updateOrCreate(
                        [
                            'penilaian_id' => $penilaian->id,
                            'siswa_id' => $siswaId,
                            'indikator_penilaian_id' => $indikatorId,
                        ],
                        [
                            'nilai' => $nilai,
                            'diisi_oleh' => $guru->id,
                            'tanggal_diisi' => $now,
                        ]
                    );
                }
            }
        });

        return redirect()->route('guru.penilaian-rapor.isi-nilai', [$penilaian->id, $kelasId])
            ->with('success', 'Nilai berhasil disimpan.');
    }

    // ═══════════════════════════════════════════════
    // ADMIN: REKAP PROGRESS
    // STEP 1: Pilih Penilaian → STEP 2: Tampil Progress
    // ═══════════════════════════════════════════════
    public function adminRekapProgress(Request $request)
    {
        $penilaianId = $request->get('penilaian_id');

        $penilaians = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->get();

        $penilaianTerpilih = null;
        $gurus = collect(); // default empty kalau belum pilih penilaian

        if ($penilaianId) {
            $penilaianTerpilih = PenilaianRaporInternal::with('semester')->find($penilaianId);

            // Hanya hitung progress kalau ada penilaian yang dipilih
            $gurus = Guru::whereHas('kelas', function ($q) {
                    $q->where('status', 'aktif');
                })
                ->with(['kelas' => function ($q) {
                    $q->where('status', 'aktif')
                      ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
                      ->orderBy('nama');
                }])
                ->orderBy('nama')
                ->paginate(50); // paging 50 per halaman

            $indikatorCountMap = [];
            foreach ($gurus as $guru) {
                foreach ($guru->kelas as $kelas) {
                    $siswaIds = Siswa::where('kelas_tartil_id', $kelas->id)
                        ->where('status', 'aktif')
                        ->pluck('id');
                    $total = $siswaIds->count();

                    // Cache hitungan indikator per jenis
                    $jenis = $kelas->jenis;
                    if (!isset($indikatorCountMap[$jenis])) {
                        $indikatorCountMap[$jenis] = IndikatorPenilaian::byJenis($jenis)->count();
                    }
                    $indikatorCount = $indikatorCountMap[$jenis];

                    $siswaLengkap = 0;
                    foreach ($siswaIds as $sid) {
                        $diisi = PenilaianRaporNilai::where('penilaian_id', $penilaianId)
                            ->where('siswa_id', $sid)
                            ->whereNotNull('nilai')
                            ->count();
                        if ($diisi >= $indikatorCount) $siswaLengkap++;
                    }

                    $kelas->total_siswa = $total;
                    $kelas->sudah_dinilai = $siswaLengkap;
                    $kelas->jumlah_indikator = $indikatorCount;
                    $kelas->progress_persen = $total > 0 ? round(($siswaLengkap / $total) * 100) : 0;
                }
            }
        }

        return view('admin.penilaian-rapor-internal.rekap-progress', compact(
            'gurus', 'penilaians', 'penilaianTerpilih', 'penilaianId'
        ));
    }

    // ═══════════════════════════════════════════════
    // GURU: REKAP NILAI
    // Format: per indikator + rata-rata + R2 Harian + R2 Penilaian + R2 Akhir
    // R2 Akhir = (R2 Harian + R2 Penilaian) / 2
    // Hanya 1 penilaian per semester
    // ═══════════════════════════════════════════════
    public function guruRekapNilai(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        // Ambil 1 penilaian aktif (hanya 1 per semester)
        $penilaian = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->first();

        // Ambil kelas guru
        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        $kelasId = $request->get('kelas_id');
        $kelasTerpilih = null;
        $siswaList = collect();
        $rekapData = [];
        $indikators = collect();

        if ($kelasId && $penilaian) {
            $kelasTerpilih = $kelasList->firstWhere('id', $kelasId);
            if (!$kelasTerpilih) return back()->with('error', 'Kelas tidak ditemukan.');

            $siswaList = Siswa::where('kelas_tartil_id', $kelasId)
                ->where('status', 'aktif')
                ->with('kelasReguler')
                ->orderBy('nama')
                ->get();

            $indikators = IndikatorPenilaian::byJenis($kelasTerpilih->jenis);

            // Ambil semua nilai: [siswa_id][indikator_id] = nilai
            $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                ->whereIn('siswa_id', $siswaList->pluck('id'))
                ->whereIn('indikator_penilaian_id', $indikators->pluck('id'))
                ->whereNotNull('nilai')
                ->get()
                ->groupBy(['siswa_id', 'indikator_penilaian_id']);

            // Jurnal stats
            $jurnalStats = $this->getJurnalR2Harian($siswaList->pluck('id'));

            foreach ($siswaList as $siswa) {
                // 1. Nilai per indikator
                $nilaiPerIndikator = [];
                foreach ($indikators as $ind) {
                    $n = $nilaiRows[$siswa->id][$ind->id] ?? collect();
                    $nilaiPerIndikator[$ind->id] = $n->isNotEmpty() ? $n->first()->nilai : null;
                }

                // 2. R2 Penilaian = rata-rata nilai indikator saja
                $nilaiFilled = array_filter($nilaiPerIndikator, fn($v) => $v !== null);
                $r2Penilaian = count($nilaiFilled) > 0 ? round(array_sum($nilaiFilled) / count($nilaiFilled)) : 0;

                // 3. R2 Harian = sistem poin B=2, C=1, K=0
                $r2Harian = $jurnalStats[$siswa->id] ?? 0;

                // 4. R2 Akhir = (R2 Harian + R2 Penilaian) / 2
                $r2Akhir = round(($r2Harian + $r2Penilaian) / 2);

                $rekapData[$siswa->id] = [
                    'nilai_per_indikator' => $nilaiPerIndikator,
                    'r2_penilaian' => $r2Penilaian,
                    'r2_harian' => $r2Harian,
                    'r2_akhir' => $r2Akhir,
                ];
            }
        }

        return view('guru.penilaian-rapor-internal.rekap-nilai', compact(
            'kelasList', 'kelasTerpilih', 'siswaList', 'penilaian', 'indikators', 'rekapData'
        ));
    }

    // ═══════════════════════════════════════════════
    // HELPER: Persentase B dari jurnal harian
    // ═══════════════════════════════════════════════
    /**
     * Hitung R2 Harian dengan sistem poin: B=2, C=1, K=0
     * R2 Harian = ((B×2) + (C×1) + (K×0)) / (Total Jurnal × 2) × 100
     */
    private function getJurnalR2Harian($siswaIds)
    {
        $result = [];
        foreach ($siswaIds as $sid) {
            $total = \App\Models\JurnalHarian::where('siswa_id', $sid)->count();
            if ($total == 0) {
                $result[$sid] = 0;
                continue;
            }
            $bCount = \App\Models\JurnalHarian::where('siswa_id', $sid)->where('penilaian', 'B')->count();
            $cCount = \App\Models\JurnalHarian::where('siswa_id', $sid)->where('penilaian', 'C')->count();
            // K tidak perlu dihitung karena poinnya 0
            $totalPoin = ($bCount * 2) + ($cCount * 1);
            $maxPoin = $total * 2;
            $result[$sid] = $maxPoin > 0 ? round(($totalPoin / $maxPoin) * 100) : 0;
        }
        return $result;
    }

    // ═══════════════════════════════════════════════
    // CETAK RAPOR PDF (Admin)
    // ═══════════════════════════════════════════════

    /**
     * Halaman pilih kelas untuk cetak rapor.
     * Step 1: Pilih Mode (Kelas Tartil / Kelas Reguler)
     * Step 2: Pilih Kelas
     * Step 3: Preview & Cetak
     */
    public function adminCetakRaporPilih(Request $request)
    {
        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();

        if (! $penilaian) {
            return back()->with('error', 'Tidak ada penilaian rapor aktif. Silakan buat penilaian rapor terlebih dahulu.');
        }

        // Mode: 'tartil' | 'reguler'
        $mode = $request->get('mode', 'tartil');

        // Ambil daftar kelas sesuai mode
        if ($mode === 'reguler') {
            $kelasList = KelasReguler::where('is_aktif', true)
                ->orderBy('jenjang')
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->get();

            $kelasId = $request->get('kelas_id');
            $kelasTerpilih = $kelasId
                ? KelasReguler::with(['siswas' => fn($q) => $q->where('status', 'aktif')->with(['kelasTartil'])->orderBy('nama')])->find($kelasId)
                : null;

            // Ambil kelas tartil untuk setiap siswa (diperlukan untuk R2)
            $rekapSiswa = [];
            if ($kelasTerpilih && $penilaian) {
                foreach ($kelasTerpilih->siswas as $siswa) {
                    $kelasTartil = Kelas::find($siswa->kelas_tartil_id);
                    if ($kelasTartil) {
                        $rekapSiswa[$siswa->id] = $this->hitungR2Siswa($siswa, $penilaian, $kelasTartil);
                    }
                }
            }
        } else {
            $kelasList = Kelas::where('status', 'aktif')
                ->with('guru')
                ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
                ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
                ->orderBy('nama')
                ->get();

            $kelasId = $request->get('kelas_id');
            $kelasTerpilih = $kelasId
                ? Kelas::with(['siswas' => fn($q) => $q->where('status', 'aktif')->with('kelasReguler')->orderBy('nama')])->find($kelasId)
                : null;

            $rekapSiswa = [];
            if ($kelasTerpilih && $penilaian) {
                foreach ($kelasTerpilih->siswas as $siswa) {
                    $rekapSiswa[$siswa->id] = $this->hitungR2Siswa($siswa, $penilaian, $kelasTerpilih);
                }
            }
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        return view('admin.penilaian-rapor-internal.cetak-rapor', compact(
            'penilaian', 'kelasList', 'kelasTerpilih', 'rekapSiswa', 'kop', 'mode'
        ));
    }

    /**
     * Cetak PDF rapor per siswa.
     */
    public function adminCetakRaporPdf(Request $request, Siswa $siswa)
    {
        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (!$penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelas = Kelas::find($siswa->kelas_tartil_id);
        if (!$kelas) {
            return back()->with('error', 'Siswa tidak memiliki kelas tartil.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);
        $rekap = $this->hitungR2Siswa($siswa, $penilaian, $kelas);

        $pdf = Pdf::loadView('pdf.rapor-tartil', compact('siswa', 'penilaian', 'kelas', 'kop', 'rekap'))
            ->setPaper('A4', 'portrait');

        $filename = 'rapor_' . preg_replace('/[^a-zA-Z0-9]/', '_', $siswa->nama) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Cetak PDF rapor batch per kelas (semua siswa dalam 1 PDF).
     */
    public function adminCetakRaporKelasPdf(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        if (!$kelasId) {
            return back()->with('error', 'Pilih kelas terlebih dahulu.');
        }

        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (!$penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelas = Kelas::with(['siswas' => fn($q) => $q->where('status', 'aktif')->orderBy('nama')])->find($kelasId);
        if (!$kelas || $kelas->siswas->count() === 0) {
            return back()->with('error', 'Kelas tidak ditemukan atau tidak ada siswa aktif.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        // Hitung rekap semua siswa
        $rekapSemuaSiswa = [];
        foreach ($kelas->siswas as $siswa) {
            $rekapSemuaSiswa[$siswa->id] = [
                'siswa' => $siswa,
                'rekap' => $this->hitungR2Siswa($siswa, $penilaian, $kelas),
            ];
        }

        $pdf = Pdf::loadView('pdf.rapor-tartil-kelas', compact('kelas', 'penilaian', 'kop', 'rekapSemuaSiswa'))
            ->setPaper('A4', 'portrait')
            ->setOption('chroot', [base_path(), storage_path('app/public'), public_path()])
            ->setOption('enable_remote', true)
            ->setOption('enable_php', false);

        $filename = 'rapor_' . preg_replace('/[^a-zA-Z0-9]/', '_', $kelas->nama) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Cetak PDF rapor batch per kelas reguler (semua siswa dalam 1 PDF).
     */
    public function adminCetakRaporKelasRegulerPdf(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        if (!$kelasId) {
            return back()->with('error', 'Pilih kelas reguler terlebih dahulu.');
        }

        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (!$penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelasReguler = KelasReguler::with(['siswas' => fn($q) => $q->where('status', 'aktif')->orderBy('nama')])->find($kelasId);
        if (!$kelasReguler || $kelasReguler->siswas->count() === 0) {
            return back()->with('error', 'Kelas tidak ditemukan atau tidak ada siswa aktif.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        // Hitung rekap semua siswa (perlu cari kelas tartil masing-masing)
        $rekapSemuaSiswa = [];
        foreach ($kelasReguler->siswas as $siswa) {
            $kelasTartil = Kelas::find($siswa->kelas_tartil_id);
            if ($kelasTartil) {
                $rekapSemuaSiswa[$siswa->id] = [
                    'siswa' => $siswa,
                    'rekap' => $this->hitungR2Siswa($siswa, $penilaian, $kelasTartil),
                    'kelasTartil' => $kelasTartil,
                ];
            }
        }

        $pdf = Pdf::loadView('pdf.rapor-tartil-kelas-reguler', compact('kelasReguler', 'penilaian', 'kop', 'rekapSemuaSiswa'))
            ->setPaper('A4', 'portrait')
            ->setOption('chroot', [base_path(), storage_path('app/public'), public_path()])
            ->setOption('enable_remote', true)
            ->setOption('enable_php', false);

        $filename = 'rapor_' . preg_replace('/[^a-zA-Z0-9]/', '_', $kelasReguler->nama) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Hitung R2 untuk 1 siswa: R2 Harian + R2 Penilaian + R2 Akhir.
     * UNTUK SKALA BESAR: pakai cache RekapR2Akhir, hanya hitung ulang kalau cache miss atau expired.
     */
    private function hitungR2Siswa(Siswa $siswa, PenilaianRaporInternal $penilaian, Kelas $kelas): array
    {
        // Coba ambil dari cache (RekapR2Akhir)
        $semester = $penilaian->semester;
        if ($semester) {
            $cached = RekapR2Akhir::where('semester_id', $semester->id)
                ->where('kelas_id', $kelas->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            // Kalau cache ada dan masih fresh (< 6 jam), pakai cache
            if ($cached && $cached->last_calculated->diffInHours(now()) < 6) {
                $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
                $nilaiRows = [];
                if ($cached->jumlah_terisi > 0) {
                    $rows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                        ->where('siswa_id', $siswa->id)
                        ->whereNotNull('nilai')
                        ->pluck('nilai', 'indikator_penilaian_id');
                    foreach ($indikators as $ind) {
                        $nilaiRows[$ind->id] = [
                            'nama' => $ind->nama_indikator,
                            'nilai' => $rows[$ind->id] ?? null,
                        ];
                    }
                } else {
                    foreach ($indikators as $ind) {
                        $nilaiRows[$ind->id] = ['nama' => $ind->nama_indikator, 'nilai' => null];
                    }
                }

                return [
                    'nilai_per_indikator' => $nilaiRows,
                    'r2_penilaian' => $cached->r2_penilaian,
                    'r2_harian' => $cached->r2_harian,
                    'r2_akhir' => $cached->r2_akhir,
                    'jumlah_indikator' => $cached->jumlah_indikator,
                    'jumlah_terisi' => $cached->jumlah_terisi,
                    'is_mutasi' => $cached->is_mutasi,
                    'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil,
                ];
            }
        }

        // Cache miss — hitung ulang
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);

        // Ambil nilai siswa
        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->where('siswa_id', $siswa->id)
            ->whereIn('indikator_penilaian_id', $indikators->pluck('id'))
            ->whereNotNull('nilai')
            ->get()
            ->keyBy('indikator_penilaian_id');

        $nilaiPerIndikator = [];
        foreach ($indikators as $ind) {
            $nilaiPerIndikator[$ind->id] = [
                'nama' => $ind->nama_indikator,
                'nilai' => $nilaiRows->has($ind->id) ? $nilaiRows[$ind->id]->nilai : null,
            ];
        }

        $nilaiFilled = collect($nilaiPerIndikator)->whereNotNull('nilai')->pluck('nilai');
        $r2Penilaian = $nilaiFilled->count() > 0 ? round($nilaiFilled->avg()) : 0;

        // R2 Harian — sistem poin: B=2, C=1, K=0
        $totalJurnal = JurnalHarian::where('siswa_id', $siswa->id)->count();
        $r2Harian = 0;
        if ($totalJurnal > 0) {
            $bCount = JurnalHarian::where('siswa_id', $siswa->id)->where('penilaian', 'B')->count();
            $cCount = JurnalHarian::where('siswa_id', $siswa->id)->where('penilaian', 'C')->count();
            $totalPoin = ($bCount * 2) + ($cCount * 1);
            $maxPoin = $totalJurnal * 2;
            $r2Harian = round(($totalPoin / $maxPoin) * 100);
        }

        $r2Akhir = round(($r2Harian + $r2Penilaian) / 2);

        // Simpan ke cache
        if ($semester) {
            RekapR2Akhir::updateOrCreate(
                ['semester_id' => $semester->id, 'kelas_id' => $kelas->id, 'siswa_id' => $siswa->id],
                [
                    'r2_harian' => $r2Harian,
                    'r2_penilaian' => $r2Penilaian,
                    'r2_akhir' => $r2Akhir,
                    'jumlah_indikator' => $indikators->count(),
                    'jumlah_terisi' => $nilaiFilled->count(),
                    'is_mutasi' => $siswa->isMutasi,
                    'last_calculated' => now(),
                ]
            );
        }

        return [
            'nilai_per_indikator' => $nilaiPerIndikator,
            'r2_penilaian' => $r2Penilaian,
            'r2_harian' => $r2Harian,
            'r2_akhir' => $r2Akhir,
            'jumlah_indikator' => $indikators->count(),
            'jumlah_terisi' => $nilaiFilled->count(),
            'is_mutasi' => $siswa->isMutasi,
            'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil,
        ];
    }
}
