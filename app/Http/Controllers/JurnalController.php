<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\JurnalHarian;
use App\Models\JurnalKelas;
use App\Models\Kelas;
use App\Models\KelasLibur;
use App\Models\KelasReguler;
use App\Models\RekapJurnalBulanan;
use App\Models\Semester;
use App\Models\SemesterKelas;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use App\Models\Surat;

class JurnalController extends Controller
{
    // ==================== GURU: HALAMAN JURNAL + ABSENSI ====================
    public function index(Request $request)
    {
        $guru = auth()->guard('web')->user()?->guru ?? null;
        $semesterAktif = Semester::aktif()->first();
        if (!$semesterAktif) {
            return view('guru.jurnal.index', ['noSemester' => true]);
        }

        // Guru hanya lihat kelas yang dia ajar
        $kelasQuery = Kelas::where('status', 'aktif');
        if ($guru) {
            $kelasQuery->where('guru_id', $guru->id);
        }
        $kelasList = $kelasQuery->orderBy('nama')->get();

        $kelasId = $request->get('kelas_id', $kelasList->first()?->id);
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $kelasAktif = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;
        $siswaList = collect();
        $jurnalKelas = null;
        $penilaianMap = collect();

        if ($kelasAktif) {
            // Siswa aktif di kelas ini
            $siswaList = Siswa::where('status', 'aktif')
                ->where('kelas_tartil_id', $kelasId)
                ->with('kelasReguler')
                ->orderBy('nama')
                ->get();

            // Jurnal kelas (info umum) yang sudah diisi untuk tanggal ini
            $jurnalKelas = JurnalKelas::where('kelas_id', $kelasId)
                ->where('tanggal', $tanggal)
                ->first();

            // Penilaian per siswa yang sudah diisi untuk tanggal ini
            $penilaianExisting = JurnalHarian::where('kelas_id', $kelasId)
                ->where('tanggal', $tanggal)
                ->get()
                ->keyBy('siswa_id');
            $penilaianMap = $penilaianExisting;
        }

        $surats = Surat::orderBy('urutan')->get(['id', 'nama', 'jumlah_ayat']);

        return view('guru.jurnal.index', compact(
            'kelasList', 'kelasAktif', 'kelasId', 'tanggal',
            'siswaList', 'jurnalKelas', 'penilaianMap', 'surats', 'semesterAktif'
        ));
    }

    // ==================== BATCH STORE: JURNAL KELAS + ABSENSI SISWA ====================
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'pertemuan_ke' => 'nullable|integer|min:1',
            'halaman_juz' => 'nullable|string|max:50',
            'surat_id' => 'nullable|exists:surats,id',
            'ayat' => 'nullable|string|max:50',
            'materi_pembelajaran' => 'nullable|string|max:255',
            'topik' => 'nullable|string|max:255',
            'rencana' => 'nullable|string|max:255',
            'catatan_kelas' => 'nullable|string',
            'entries' => 'required|array|min:1',
            'entries.*.siswa_id' => 'required|exists:siswas,id',
            'entries.*.penilaian' => 'nullable|in:B,C,K',
            'entries.*.catatan' => 'nullable|string|max:255',
        ], [
            'entries.required' => 'Tidak ada data penilaian siswa.',
        ]);

        $semesterAktif = Semester::aktif()->first();
        if (!$semesterAktif) {
            return response()->json(['error' => 'Tidak ada semester aktif.'], 422);
        }

        $guru = auth()->guard('web')->user()?->guru ?? null;
        $guruId = $guru?->id ?? auth()->guard('web')->user()?->id;
        $kelasId = $validated['kelas_id'];
        $tanggal = $validated['tanggal'];
        $tanggalCarbon = Carbon::parse($tanggal);
        $bulan = (int) date('Ym', strtotime($tanggal));

        // Validasi wali kelas
        if ($guru) {
            $isWaliKelas = Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->exists();
            if (!$isWaliKelas) {
                return response()->json(['error' => 'Anda bukan wali kelas untuk kelas ini.'], 403);
            }
        }

        // Validasi hari aktif dan hari libur
        if (! $this->isHariAktif($tanggalCarbon)) {
            return response()->json(['error' => 'Tidak dapat mengisi jurnal di hari non-aktif (Jumat-Minggu).'], 422);
        }

        $kelas = Kelas::find($kelasId);
        $semesterMulai = $semesterAktif->tanggal_mulai ?? $semesterAktif->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
        $awalHitung = $kelas?->getAwalHitungHari($semesterMulai) ?? $semesterMulai;
        $hariLiburList = $this->getHariLiburList($kelasId, $awalHitung, min($semesterAktif->tanggal_selesai, now()));

        if (in_array($tanggalCarbon->format('Y-m-d'), $hariLiburList)) {
            return response()->json(['error' => 'Tanggal ini ditandai sebagai hari libur untuk kelas ini.'], 422);
        }

        // Auto-increment pertemuan_ke: urutkan berdasarkan tanggal, bukan urutan simpan.
        // Hitung jumlah jurnal di bulan ini yang tanggalnya lebih awal dari tanggal ini + 1.
        $pertemuanKe = $validated['pertemuan_ke'] ?? null;
        if (empty($pertemuanKe)) {
            $countSebelumTanggal = JurnalKelas::where('kelas_id', $kelasId)
                ->whereYear('tanggal', date('Y', strtotime($tanggal)))
                ->whereMonth('tanggal', date('m', strtotime($tanggal)))
                ->where('tanggal', '<', $tanggal)
                ->count();
            $pertemuanKe = $countSebelumTanggal + 1;
        }

        DB::beginTransaction();
        try {
            // 1. Simpan/Update Jurnal Kelas (info umum)
            JurnalKelas::updateOrCreate(
                ['kelas_id' => $kelasId, 'tanggal' => $tanggal],
                [
                    'semester_id' => $semesterAktif->id,
                    'guru_id' => $guruId,
                    'pertemuan_ke' => $pertemuanKe,
                    'halaman_juz' => $validated['halaman_juz'] ?? null,
                    'surat_id' => $validated['surat_id'] ?? null,
                    'ayat' => $validated['ayat'] ?? null,
                    'materi_pembelajaran' => $validated['materi_pembelajaran'] ?? null,
                    'topik' => $validated['topik'] ?? null,
                    'rencana' => $validated['rencana'] ?? null,
                    'catatan_kelas' => $validated['catatan_kelas'] ?? null,
                ]
            );

            // 2. Simpan/Update Penilaian per Siswa
            $inserted = 0;
            $updated = 0;

            // Deduplicate entries per siswa_id
            $entriesBySiswa = [];
            foreach ($validated['entries'] as $e) {
                $entriesBySiswa[$e['siswa_id']] = $e;
            }

            // Parse ayat: "1-5" → ayat_mulai=1, ayat_selesai=5; "3" → ayat_mulai=3, ayat_selesai=null
            $ayatInput = $validated['ayat'] ?? null;
            $ayatMulai = null;
            $ayatSelesai = null;
            if ($ayatInput) {
                if (str_contains($ayatInput, '-')) {
                    [$ayatMulai, $ayatSelesai] = array_map('trim', explode('-', $ayatInput, 2));
                    $ayatMulai = is_numeric($ayatMulai) ? (int) $ayatMulai : null;
                    $ayatSelesai = is_numeric($ayatSelesai) ? (int) $ayatSelesai : null;
                } else {
                    $ayatMulai = is_numeric(trim($ayatInput)) ? (int) trim($ayatInput) : null;
                }
            }
            $suratId = $validated['surat_id'] ?? null;
            $halaman = $validated['halaman_juz'] ?? null;
            $materi = $validated['materi_pembelajaran'] ?? null;
            $topik = $validated['topik'] ?? null;
            $rencana = $validated['rencana'] ?? null;

            foreach ($entriesBySiswa as $siswaId => $e) {
                $existing = JurnalHarian::where('kelas_id', $kelasId)
                    ->where('tanggal', $tanggal)
                    ->where('siswa_id', $siswaId)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'guru_id' => $guruId,
                        'semester_id' => $semesterAktif->id,
                        'penilaian' => $e['penilaian'] ?? null,
                        'catatan' => $e['catatan'] ?? null,
                        'surat_id' => $suratId,
                        'ayat_mulai' => $ayatMulai,
                        'ayat_selesai' => $ayatSelesai,
                        'halaman' => $halaman,
                        'materi' => $materi,
                        'topik' => $topik,
                        'rencana' => $rencana,
                    ]);
                    $updated++;
                } else {
                    JurnalHarian::create([
                        'semester_id' => $semesterAktif->id,
                        'kelas_id' => $kelasId,
                        'guru_id' => $guruId,
                        'siswa_id' => $siswaId,
                        'tanggal' => $tanggal,
                        'penilaian' => $e['penilaian'] ?? null,
                        'catatan' => $e['catatan'] ?? null,
                        'surat_id' => $suratId,
                        'ayat_mulai' => $ayatMulai,
                        'ayat_selesai' => $ayatSelesai,
                        'halaman' => $halaman,
                        'materi' => $materi,
                        'topik' => $topik,
                        'rencana' => $rencana,
                    ]);
                    $inserted++;
                }
            }

            // 3. Update rekap bulanan
            $this->updateRekapBulanan($semesterAktif->id, $kelasId, $tanggal);

            // 4. Invalidate cache R2 untuk semua siswa di kelas ini
            $siswaIds = array_column($validated['entries'], 'siswa_id');
            \App\Models\RekapR2Akhir::whereIn('siswa_id', array_unique($siswaIds))->delete();

            DB::commit();
            Cache::forget("rekap_kelas:{$kelasId}:{$bulan}");

            return response()->json([
                'success' => true,
                'message' => "Jurnal tersimpan. {$inserted} baru, {$updated} diperbarui.",
                'inserted' => $inserted,
                'updated' => $updated,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    // ==================== REKAP BULANAN (GURU) — LONG TERM ====================
    /**
     * Rekap nilai bulanan guru. Support long-term: data tetap terlihat
     * walaupun siswa sudah lulus, TA sudah ditutup, 5+ tahun berlalu.
     * Menggunakan semester_id dari jurnal_harians + snapshot semester_siswa
     * untuk menampilkan kelas_reguler historis yang benar.
     */
    public function rekapBulanan(Request $request)
    {
        $guru = auth()->guard('web')->user()?->guru ?? null;

        // ── 1. Daftar semester (untuk filter historis) ──
        $semesterList = Semester::orderBy('tanggal_mulai', 'desc')->get();

        // Daftar kelas yang pernah diajar guru ini (dari snapshot semester_kelas + kelas aktif)
        $kelasQuery = Kelas::where('status', 'aktif');
        if ($guru) {
            $kelasQuery->where('guru_id', $guru->id);
        }
        $kelasList = $kelasQuery->orderBy('nama')->get();

        // ── 2. Parameter request ──
        $kelasId = $request->get('kelas_id', $kelasList->first()?->id);
        // Standard format YYYY-MM (dari input type="month")
        // Fallback: support format Ym lama (202605 → 2026-05)
        $bulan = $request->get('bulan', now()->format('Y-m'));
        if (strlen($bulan) === 6 && ctype_digit($bulan)) {
            // Format lama: Ym → YYYY-MM
            $bulan = substr($bulan, 0, 4) . '-' . substr($bulan, 4, 2);
        }
        $semesterId = $request->get('semester_id');

        $kelasAktif = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;
        $siswaList = collect();
        $tanggalList = [];
        $rekapData = [];
        $summaryPerTanggal = [];
        $rataRataKelas = 0;
        $semesterFilter = null;

        if ($kelasAktif) {
            // Parse YYYY-MM ke year/month
            try {
                $date = \Carbon\Carbon::parse($bulan . '-01');
                $year = $date->year;
                $month = $date->month;
            } catch (\Exception $e) {
                $year = now()->year;
                $month = now()->month;
            }

            // ── 3. Query jurnal ──
            // Jika ada filter semester_id, gunakan itu. Jika tidak, ambil semua jurnal di bulan/kelas
            $jurnalQuery = JurnalHarian::where('kelas_id', $kelasId)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereNotNull('penilaian')
                ->select('tanggal', 'siswa_id', 'penilaian', 'semester_id')
                ->orderBy('tanggal');

            if ($semesterId) {
                $jurnalQuery->where('semester_id', $semesterId);
                $semesterFilter = Semester::find($semesterId);
            }
            $jurnals = $jurnalQuery->get();

            // ── 4. Ambil siswa — LONG TERM STRATEGY ──
            // Strategi: ambil siswa berdasarkan ID dari jurnal (bukan status aktif)
            // Kemudian gunakan snapshot semester_siswa untuk kelas_reguler historis.
            $siswaIdsDariJurnal = $jurnals->pluck('siswa_id')->unique()->filter()->values()->toArray();

            if (!empty($siswaIdsDariJurnal)) {
                // Ambil data siswa (nama, NIS, status) — tanpa filter status
                $siswaRaw = Siswa::whereIn('id', $siswaIdsDariJurnal)
                    ->select('id', 'nis', 'nama', 'status', 'kelas_reguler_id')
                    ->orderBy('nama')
                    ->get();

                // ── 5. Gunakan snapshot semester_siswa untuk kelas_reguler historis ──
                // Coba dapatkan snapshot dari semester yang relevan
                $semesterIdsDariJurnal = $jurnals->pluck('semester_id')->unique()->filter()->values()->toArray();
                $snapshotSemesterId = $semesterId ?? ($semesterIdsDariJurnal[0] ?? null);

                if ($snapshotSemesterId) {
                    $snapshots = SemesterSiswa::where('semester_id', $snapshotSemesterId)
                        ->whereIn('siswa_id', $siswaIdsDariJurnal)
                        ->select('siswa_id', 'kelas_reguler_id', 'status_siswa')
                        ->get()
                        ->keyBy('siswa_id');
                } else {
                    $snapshots = collect();
                }

                // Gabungkan: data siswa + snapshot kelas reguler
                $kelasRegulerList = KelasReguler::whereIn('id', $snapshots->pluck('kelas_reguler_id')->filter()->unique())
                    ->get()
                    ->keyBy('id');

                $siswaList = $siswaRaw->map(function ($s) use ($snapshots, $kelasRegulerList) {
                    $snap = $snapshots->get($s->id);
                    if ($snap && $snap->kelas_reguler_id) {
                        $s->kelas_reguler_snapshot = $kelasRegulerList->get($snap->kelas_reguler_id)?->nama ?? $s->kelas_reguler_id;
                        $s->status_snapshot = $snap->status_siswa ?? $s->status;
                    } else {
                        $s->kelas_reguler_snapshot = $s->kelasReguler?->nama ?? '-';
                        $s->status_snapshot = $s->status;
                    }
                    return $s;
                });
            }

            // ── 6. Daftar tanggal unik ──
            $uniqueTanggal = $jurnals->pluck('tanggal')->unique()->sort()->values();
            foreach ($uniqueTanggal as $t) {
                $tanggalList[] = [
                    'tanggal' => $t,
                    'tanggal_str' => $t->format('Y-m-d'),
                ];
            }

            // ── 7. Map data nilai per siswa ──
            $rekapData = [];
            $siswaIdMap = [];
            foreach ($siswaList as $s) {
                $sid = (int) $s->id;
                $rekapData[$sid] = ['nilai' => [], 'summary' => ['b_c' => 0, 'k' => 0]];
                $siswaIdMap[$sid] = true;
            }

            foreach ($jurnals as $j) {
                $tStr = $j->tanggal->format('Y-m-d');
                $jid = (int) $j->siswa_id;
                if (isset($siswaIdMap[$jid])) {
                    $rekapData[$jid]['nilai'][$tStr] = $j->penilaian;
                    if (in_array($j->penilaian, ['B', 'C'])) {
                        $rekapData[$jid]['summary']['b_c']++;
                    } elseif ($j->penilaian == 'K') {
                        $rekapData[$jid]['summary']['k']++;
                    }
                }
            }

            // ── 8. Summary per tanggal ──
            foreach ($tanggalList as $t) {
                $tStr = $t['tanggal_str'];
                $summaryPerTanggal[$tStr] = ['b_c' => 0, 'k' => 0];
                foreach ($jurnals as $j) {
                    if ($j->tanggal->format('Y-m-d') == $tStr) {
                        if (in_array($j->penilaian, ['B', 'C'])) {
                            $summaryPerTanggal[$tStr]['b_c']++;
                        } elseif ($j->penilaian == 'K') {
                            $summaryPerTanggal[$tStr]['k']++;
                        }
                    }
                }
            }

            // ── 9. Rata-rata kelas ──
            $totalPersen = 0;
            $siswaCount = $siswaList->count();
            $tglCount = count($tanggalList);
            if ($siswaCount > 0 && $tglCount > 0) {
                foreach ($rekapData as $data) {
                    $totalPersen += round(($data['summary']['b_c'] / $tglCount) * 100);
                }
                $rataRataKelas = round($totalPersen / $siswaCount);
            }
        }

        // Handle Export Excel
        if ($request->has('export') && $request->export === 'excel') {
            $bulanLabel = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');

            $export = new \App\Exports\RekapNilaiExport(
                $siswaList, $tanggalList, $rekapData, $summaryPerTanggal,
                $kelasAktif->nama ?? 'Kelas', $bulanLabel
            );
            $spreadsheet = $export->export();

            $filename = 'rekap_nilai_' . str_replace(' ', '_', strtolower($kelasAktif->nama ?? 'kelas')) . '_' . $bulan . '.xlsx';

            // Clear any output buffers to prevent corrupt XLSX
            while (ob_get_level()) { ob_end_clean(); }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        return view('guru.jurnal.rekap', compact(
            'kelasList', 'kelasId', 'bulan', 'kelasAktif',
            'siswaList', 'tanggalList', 'rekapData',
            'summaryPerTanggal', 'rataRataKelas',
            'semesterList', 'semesterId', 'semesterFilter'
        ));
    }

    // ==================== REKAP SEMESTER (ADMIN) ====================
    public function adminRekap(Request $request)
    {
        $semesterId = $request->get('semester_id');
        $kelasId = $request->get('kelas_id');
        // Bulan default NULL (belum terpilih) → tampilkan SEMUA bulan di semester
        $bulan = $request->get('bulan');

        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        // LONG TERM: Ambil semua kelas yang punya jurnal di semester manapun
        $kelasIdsJurnal = JurnalHarian::distinct('kelas_id')->pluck('kelas_id')->toArray();
        $kelasList = Kelas::whereIn('id', $kelasIdsJurnal)->orderBy('nama')->get();

        $rekap = collect();
        $siswaDariRekap = collect();
        $warningBulan = null;

        if ($semesterId && $kelasId) {
            // Ambil tahun dari semester untuk filter bulan
            $semester = Semester::find($semesterId);
            $tahunSemester = $semester ? $semester->tanggal_mulai->year : now()->year;

            // ── Validasi bulan yang dipilih ──
            if ($bulan && $semester) {
                // Cek apakah bulan masuk dalam range semester
                $bulanInt = (int) $bulan;
                $blnMulai = $semester->tanggal_mulai->month;
                $blnSelesai = $semester->tanggal_selesai->month;

                // Cek apakah bulan dalam range semester
                $bulanValid = false;
                if ($blnMulai <= $blnSelesai) {
                    // Genap: Jan-Jun (bulan naik)
                    $bulanValid = ($bulanInt >= $blnMulai && $bulanInt <= $blnSelesai);
                } else {
                    // Ganjil: Jul-Des (melintas tahun)
                    $bulanValid = ($bulanInt >= $blnMulai || $bulanInt <= $blnSelesai);
                }

                if (!$bulanValid) {
                    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                                  'Juli','Agustus','September','Oktober','November','Desember'];
                    $warningBulan = 'Bulan ' . $namaBulan[$bulanInt] . ' tidak termasuk dalam ' . $semester->nama 
                        . ' (' . $semester->tanggal_mulai->format('M Y') . ' - ' . $semester->tanggal_selesai->format('M Y') . ').';
                    $bulan = null; // Reset, tampilkan semua bulan
                }
            }

            // Build query: semester + kelas (bulan opsional)
            $query = JurnalHarian::where('semester_id', $semesterId)
                ->where('kelas_id', $kelasId)
                ->whereNotNull('penilaian');

            // Jika bulan terpilih (format "01"-"12"), filter per bulan.
            // Jika tidak, tampilkan SEMUA bulan di semester.
            if ($bulan) {
                $query->whereYear('tanggal', $tahunSemester)
                      ->whereMonth('tanggal', (int) $bulan);
            }

            // Filter hanya hari aktif (Senin-Kamis) dan bukan hari libur kelas
            $kelas = Kelas::find($kelasId);
            $semesterMulai = $semester->tanggal_mulai ?? $semester->tahunAjaran?->tanggal_mulai ?? now()->startOfYear();
            $awalHitung = $kelas?->getAwalHitungHari($semesterMulai) ?? $semesterMulai;
            $batasAkhir = min(Carbon::parse($semester->tanggal_selesai ?? now()), now());
            $hariLiburList = $this->getHariLiburList($kelasId, Carbon::parse($awalHitung), $batasAkhir);

            $rows = $query->select('siswa_id', 'tanggal', 'penilaian')->get();
            $filteredRows = $rows->filter(function ($row) use ($hariLiburList) {
                $t = Carbon::parse($row->tanggal);
                return $this->isHariAktif($t) && !in_array($t->format('Y-m-d'), $hariLiburList);
            });

            // Ambil data siswa (termasuk yang sudah lulus/nonaktif)
            $siswaIds = $filteredRows->pluck('siswa_id')->unique()->toArray();
            $siswaDariRekap = Siswa::whereIn('id', $siswaIds)
                ->select('id', 'nis', 'nama', 'status')
                ->get()
                ->keyBy('id');

            // Aggregate manual per siswa
            $grouped = $filteredRows->groupBy('siswa_id');
            $rekap = $grouped->mapWithKeys(function ($items, $siswaId) use ($siswaDariRekap) {
                $siswa = $siswaDariRekap->get($siswaId);
                $b = $items->where('penilaian', 'B')->count();
                $c = $items->where('penilaian', 'C')->count();
                $k = $items->where('penilaian', 'K')->count();
                $total = $items->count();
                $rataRata = $total > 0 ? round((($b * 100) + ($c * 67) + ($k * 33)) / $total, 2) : 0;

                return [$siswaId => collect([(object)[
                    'siswa_id' => $siswaId,
                    'siswa' => $siswa,
                    'total_hadir' => $total,
                    'count_b' => $b,
                    'count_c' => $c,
                    'count_k' => $k,
                    'rata_rata' => $rataRata,
                ]])];
            });
        }

        return view('admin.jurnal.rekap', compact('semesters', 'kelasList', 'semesterId', 'kelasId', 'rekap', 'siswaDariRekap', 'bulan', 'warningBulan'));
    }

    // ==================== DAFTAR JURNAL PER BULAN (ADMIN) ====================
    public function adminDaftarJurnal(Request $request)
    {
        $semesterId = $request->get('semester_id');
        $kelasId = $request->get('kelas_id');
        $bulan = $request->get('bulan', now()->format('Y-m'));

        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        $kelasList = Kelas::where('status', 'aktif')->orderBy('nama')->get();

        $jurnals = collect();
        if ($semesterId && $kelasId) {
            $year = (int) substr(str_replace('-', '', $bulan), 0, 4);
            $month = (int) substr(str_replace('-', '', $bulan), 4, 2);

            $semester = Semester::find($semesterId);
            $semesterMulai = $semester?->tanggal_mulai ?? now()->startOfYear();
            $batasAkhir = min(Carbon::parse($semester?->tanggal_selesai ?? now()), now());
            $hariLiburList = $this->getHariLiburList($kelasId, Carbon::parse($semesterMulai), $batasAkhir);

            $rawJurnals = JurnalKelas::where('semester_id', $semesterId)
                ->where('kelas_id', $kelasId)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->with('kelas', 'guru', 'surat')
                ->orderBy('tanggal')
                ->get();

            $jurnals = $rawJurnals->filter(function ($jk) use ($hariLiburList) {
                $t = Carbon::parse($jk->tanggal);
                return $this->isHariAktif($t) && !in_array($t->format('Y-m-d'), $hariLiburList);
            })->values();
        }

        return view('admin.jurnal.daftar', compact(
            'semesters', 'kelasList', 'semesterId', 'kelasId', 'bulan', 'jurnals'
        ));
    }

    // ==================== JURNAL BULANAN (ADMIN) ====================
    public function adminJurnalBulanan(Request $request)
    {
        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        $kelasList = Kelas::where('status', 'aktif')->with('guru')->orderBy('nama')->get();

        return $this->buildJurnalBulanan($request, $semesters, $kelasList, null);
    }

    // ==================== GURU: JURNAL BULANAN ====================
    public function guruJurnalBulanan(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) {
            \Log::warning('Akses jurnal bulanan tanpa data guru', ['user_id' => auth()->id()]);
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan. Hubungi admin.');
        }

        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        $kelasList = Kelas::where('status', 'aktif')->where('guru_id', $guru->id)->with('guru')->orderBy('nama')->get();

        return $this->buildJurnalBulanan($request, $semesters, $kelasList, $guru->id);
    }

    // ==================== SHARED: BUILD JURNAL BULANAN DATA ====================
    private function buildJurnalBulanan(Request $request, $semesters, $kelasList, ?int $guruId)
    {
        $semesterId = $request->get('semester_id');
        $kelasId = $request->get('kelas_id');
        // Bulan default NULL → tampilkan SEMUA bulan di semester
        $bulan = $request->get('bulan');

        $kelasAktif = null;
        $jurnalRows = [];
        $totalSiswa = 0;

        $warningBulan = null;

        if ($semesterId && $kelasId) {
            $kelasQuery = Kelas::with('guru')->where('id', $kelasId);
            if ($guruId) {
                $kelasQuery->where('guru_id', $guruId);
            }
            $kelasAktif = $kelasQuery->first();

            // Security: log & redirect jika guru akses kelas bukan miliknya
            if (!$kelasAktif) {
                if ($guruId) {
                    \Log::warning('Guru mencoba akses kelas bukan miliknya', [
                        'guru_id' => $guruId, 'kelas_id' => $kelasId
                    ]);
                }
                return redirect()->back()->with('error', 'Akses tidak diizinkan.');
            }

            // Ambil tahun dari semester
            $semester = Semester::find($semesterId);
            $year = $semester ? $semester->tanggal_mulai->year : now()->year;

            // ── Validasi bulan yang dipilih ──
            if ($bulan && $semester) {
                $bulanInt = (int) $bulan;
                $blnMulai = $semester->tanggal_mulai->month;
                $blnSelesai = $semester->tanggal_selesai->month;

                $bulanValid = false;
                if ($blnMulai <= $blnSelesai) {
                    $bulanValid = ($bulanInt >= $blnMulai && $bulanInt <= $blnSelesai);
                } else {
                    $bulanValid = ($bulanInt >= $blnMulai || $bulanInt <= $blnSelesai);
                }

                if (!$bulanValid) {
                    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                                  'Juli','Agustus','September','Oktober','November','Desember'];
                    $warningBulan = 'Bulan ' . $namaBulan[$bulanInt] . ' tidak termasuk dalam ' . $semester->nama
                        . ' (' . $semester->tanggal_mulai->format('M Y') . ' - ' . $semester->tanggal_selesai->format('M Y') . '). Menampilkan semua bulan.';
                    $bulan = null; // Reset, tampilkan semua bulan
                }
            }

            // Jika bulan terpilih (format "01"-"12"), filter per bulan
            // Jika tidak, tampilkan SEMUA bulan di semester
            $jurnalQuery = JurnalHarian::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId)
                ->whereNotNull('penilaian');

            if ($bulan) {
                $jurnalQuery->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', (int) $bulan);
            }

            // LONG TERM: Hitung total siswa dari jurnal (historis)
            $totalSiswa = (clone $jurnalQuery)->distinct('siswa_id')->count('siswa_id');

            // Ambil tanggal unik dari JurnalHarian
            $tanggalUnik = (clone $jurnalQuery)->distinct('tanggal')
                ->orderBy('tanggal')
                ->pluck('tanggal');

            // Filter hanya hari aktif (Senin-Kamis) dan bukan hari libur kelas
            $semesterMulai = $semester->tanggal_mulai ?? now()->startOfYear();
            $batasAkhir = min(Carbon::parse($semester->tanggal_selesai ?? now()), now());
            $hariLiburList = $this->getHariLiburList($kelasId, Carbon::parse($semesterMulai), $batasAkhir);

            $tanggalUnik = $tanggalUnik->filter(function ($tgl) use ($hariLiburList) {
                return $this->isHariAktif($tgl) && !in_array($tgl->format('Y-m-d'), $hariLiburList);
            })->values();

            // Ambil detail jurnal kelas jika ada (materi, rencana, catatan)
            $jurnalKelasQuery = JurnalKelas::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId);
            if ($bulan) {
                $jurnalKelasQuery->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', (int) $bulan);
            }
            $jurnalKelasMap = $jurnalKelasQuery->with('surat')
                ->get()
                ->keyBy(fn($jk) => $jk->tanggal->format('Y-m-d'));

            // Fix N+1: Single query GROUP BY untuk semua penilaian
            $allPenilaianQuery = JurnalHarian::where('kelas_id', $kelasId)
                ->where('semester_id', $semesterId)
                ->whereNotNull('penilaian');

            if ($bulan) {
                $allPenilaianQuery->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', (int) $bulan);
            }

            $allPenilaian = $allPenilaianQuery->select('tanggal',
                    DB::raw("SUM(CASE WHEN penilaian='B' THEN 1 ELSE 0 END) as count_b"),
                    DB::raw("SUM(CASE WHEN penilaian='C' THEN 1 ELSE 0 END) as count_c"),
                    DB::raw("SUM(CASE WHEN penilaian='K' THEN 1 ELSE 0 END) as count_k"))
                ->groupBy('tanggal')
                ->get()
                ->keyBy(fn($p) => $p->tanggal->format('Y-m-d'));

            // Build rows dari tanggal unik (dari jurnal_harians)
            $pertemuanKe = 1;
            foreach ($tanggalUnik as $tgl) {
                $tStr = $tgl->format('Y-m-d');
                $p = $allPenilaian->get($tStr);
                $jk = $jurnalKelasMap->get($tStr); // detail jurnal kelas (bisa null)

                $b = (int) ($p?->count_b ?? 0);
                $c = (int) ($p?->count_c ?? 0);
                $k = (int) ($p?->count_k ?? 0);
                $totalNilai = $b + $c + $k;
                $persen = $totalNilai > 0 ? round((($b + $c) / $totalNilai) * 100) : 0;

                $jurnalRows[] = [
                    'tanggal' => $tgl,
                    'hari' => strtoupper($tgl->locale('id')->isoFormat('dddd')),
                    'tgl_short' => $tgl->format('d/m'),
                    'pertemuan_ke' => $jk?->pertemuan_ke ?? $pertemuanKe++,
                    'hal' => $jk?->surat
                        ? ($jk->surat->nama . ($jk->ayat ? ' (' . $jk->ayat . ')' : ''))
                        : ($jk->halaman_juz ?? '-'),
                    'materi' => $jk?->materi_pembelajaran ?? ($jk?->topik ?? '-'),
                    'b' => $b, 'c' => $c, 'k' => $k,
                    'persen' => $persen,
                    'rencana' => $jk?->rencana ?? '-',
                    'catatan' => $jk?->catatan_kelas ?? '-',
                ];
            }
        }

        return view('admin.jurnal.bulanan', compact(
            'semesters', 'kelasList', 'semesterId', 'kelasId', 'bulan',
            'kelasAktif', 'jurnalRows', 'totalSiswa', 'warningBulan'
        ));
    }

    // ==================== PRIVATE: UPDATE REKAP BULANAN ====================
    private function updateRekapBulanan(int $semesterId, int $kelasId, string $tanggal): void
    {
        $bulan = (int) date('Ym', strtotime($tanggal));

        $aggregasi = JurnalHarian::where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->whereRaw("YEAR(tanggal) * 100 + MONTH(tanggal) = ?", [$bulan])
            ->select(
                'siswa_id',
                DB::raw('SUM(CASE WHEN penilaian IS NOT NULL THEN 1 ELSE 0 END) as total_hadir'),
                DB::raw('SUM(CASE WHEN penilaian = "B" THEN 1 ELSE 0 END) as count_b'),
                DB::raw('SUM(CASE WHEN penilaian = "C" THEN 1 ELSE 0 END) as count_c'),
                DB::raw('SUM(CASE WHEN penilaian = "K" THEN 1 ELSE 0 END) as count_k'),
            )
            ->groupBy('siswa_id')
            ->get();

        foreach ($aggregasi as $agg) {
            $totalDinilai = $agg->count_b + $agg->count_c + $agg->count_k;
            $rataRata = null;
            if ($totalDinilai > 0) {
                $skor = ($agg->count_b * 1.0 + $agg->count_c * 0.67 + $agg->count_k * 0.33) / $totalDinilai;
                $rataRata = round($skor, 2);
            }

            RekapJurnalBulanan::updateOrCreate(
                ['semester_id' => $semesterId, 'kelas_id' => $kelasId, 'siswa_id' => $agg->siswa_id, 'bulan' => $bulan],
                [
                    'total_hadir' => $agg->total_hadir,
                    'count_b' => $agg->count_b,
                    'count_c' => $agg->count_c,
                    'count_k' => $agg->count_k,
                    'rata_rata' => $rataRata,
                ]
            );
        }
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
     * Ambil daftar tanggal libur untuk kelas dalam rentang semester.
     */
    private function getHariLiburList(int $kelasId, Carbon $mulai, Carbon $selesai): array
    {
        return KelasLibur::where('kelas_id', $kelasId)
            ->whereBetween('tanggal', [$mulai, $selesai])
            ->pluck('tanggal')
            ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();
    }
}