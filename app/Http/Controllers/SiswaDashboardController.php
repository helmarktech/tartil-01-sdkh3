<?php

namespace App\Http\Controllers;

use App\Models\HafalanTahfidz;
use App\Models\JurnalHarian;
use App\Models\MunaqosyahPendaftaran;
use App\Models\PerpindahanKelas;
use App\Models\RekapJurnalSemester;
use App\Models\RekapMunaqosyahSemester;
use App\Models\RekapR2Akhir;
use App\Models\Semester;
use Illuminate\Http\Request;

class SiswaDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $siswa = auth('siswa')->user();

        $semesterList = Semester::orderBy('tanggal_mulai', 'desc')->get();

        $semesterId = $request->get('semester_id');
        if ($semesterId) {
            $semester = Semester::find($semesterId);
        } else {
            // Prioritas: semester aktif → semester terbaru (berapapun statusnya)
            $semester = Semester::aktif()->first()
                ?? Semester::orderBy('tanggal_mulai', 'desc')->first();
        }

        $semesterId = $semester?->id;

        // Jurnal data
        // Note: 1 jurnal = 1 hari mengaji (1 entri per siswa per hari)
        $jurnals = collect();
        $totalJurnal = 0;
        $bCount = 0;
        $cCount = 0;
        $kCount = 0;

        if ($semesterId && $siswa->kelas_tartil_id) {
            $jurnals = JurnalHarian::where('siswa_id', $siswa->id)
                ->where('semester_id', $semesterId)
                ->with('surat')
                ->orderBy('tanggal', 'desc')
                ->limit(30)
                ->get();

            $totalJurnal = JurnalHarian::where('siswa_id', $siswa->id)
                ->where('semester_id', $semesterId)
                ->count();

            $bCount = JurnalHarian::where('siswa_id', $siswa->id)->where('semester_id', $semesterId)->where('penilaian', 'B')->count();
            $cCount = JurnalHarian::where('siswa_id', $siswa->id)->where('semester_id', $semesterId)->where('penilaian', 'C')->count();
            $kCount = JurnalHarian::where('siswa_id', $siswa->id)->where('semester_id', $semesterId)->where('penilaian', 'K')->count();
        }

        // 1 jurnal = 1 hari mengaji (1 entri = 1 kali pertemuan/hari)
        $hariMengaji = $totalJurnal;

        // R2 data
        $r2Data = null;
        if ($semesterId && $siswa->kelas_tartil_id) {
            $r2Data = RekapR2Akhir::where('semester_id', $semesterId)
                ->where('siswa_id', $siswa->id)
                ->first();
        }

        // R2 Harian — sistem poin: B=2, C=1, K=0
        // Untuk semester ditutup: pakai snapshot. Untuk semester aktif: hitung real-time.
        $r2Harian = 0;
        $snapJurnal = null;
        if ($semester?->status === 'ditutup') {
            $snapJurnal = RekapJurnalSemester::where('semester_id', $semesterId)
                ->where('siswa_id', $siswa->id)
                ->first();
            $r2Harian = $snapJurnal?->r2_harian ?? $r2Data?->r2_harian ?? 0;
        } elseif ($totalJurnal > 0) {
            $totalPoin = ($bCount * 2) + ($cCount * 1);
            $maxPoin = $totalJurnal * 2;
            $r2Harian = round(($totalPoin / $maxPoin) * 100);
        }
        $r2Penilaian = $r2Data?->r2_penilaian ?? 0;
        $r2Akhir = $r2Data?->r2_akhir ?? round(($r2Harian + $r2Penilaian) / 2);

        // Munaqosyah — semester ditutup: snapshot. aktif: real-time.
        $munaqosyah = collect();
        $snapMunaqosyah = null;
        if ($semesterId && $siswa->kelas_tartil_id) {
            if ($semester?->status === 'ditutup') {
                $snapMunaqosyah = RekapMunaqosyahSemester::where('semester_id', $semesterId)
                    ->where('siswa_id', $siswa->id)
                    ->first();
            }
            if (! $snapMunaqosyah) {
                $munaqosyah = MunaqosyahPendaftaran::where('siswa_id', $siswa->id)
                    ->whereHas('munaqosyah', fn ($q) => $q->where('semester_id', $semesterId))
                    ->with('munaqosyah')
                    ->get();
            }
        }

        // Tahfidz — progress hafalan juz 1-30
        $tahfidzProgress = [];
        $tahfidzTotalJuz = 0;
        $tahfidzJuzAktif = null; // juz yang sedang dipelajari (status != hafal, terbaru)
        if ($semesterId && $siswa->kelas_tartil_id) {
            $tahfidzProgress = HafalanTahfidz::progressJuz($siswa->id, $semesterId);
            $tahfidzTotalJuz = collect($tahfidzProgress)->where('status', 'hafal')->count();
            // Cari juz aktif: yang terbaru dengan status != hafal
            $aktifEntry = HafalanTahfidz::where('siswa_id', $siswa->id)
                ->where('semester_id', $semesterId)
                ->where('status', '!=', 'hafal')
                ->orderBy('tanggal_hafalan', 'desc')
                ->first();
            $tahfidzJuzAktif = $aktifEntry ? ['juz' => $aktifEntry->juz, 'status' => $aktifEntry->status, 'surat' => $aktifEntry->surat?->nama_latin ?? '-'] : null;
        }

        // Bulan data + perubahan persentase
        $bulanData = [];
        $prevPct = null;
        if ($semesterId && $siswa->kelas_tartil_id) {
            $start = $semester->tanggal_mulai ?? now()->startOfYear();
            $end = min($semester->tanggal_selesai ?? now(), now());
            $current = $start->copy();
            while ($current <= $end) {
                $tahun = $current->year;
                $bln = $current->month;
                $jB = JurnalHarian::where('siswa_id', $siswa->id)->where('semester_id', $semesterId)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln)->where('penilaian', 'B')->count();
                $jTotal = JurnalHarian::where('siswa_id', $siswa->id)->where('semester_id', $semesterId)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln)->count();
                $pct = $jTotal > 0 ? round(($jB / $jTotal) * 100) : 0;

                // Hitung keterangan perubahan vs bulan sebelumnya
                $perubahan = null;
                if ($prevPct !== null) {
                    $selisih = $pct - $prevPct;
                    $abs = abs($selisih);
                    if ($abs <= 3) {
                        $perubahan = $selisih > 0 ? 'Stabil Meningkat' : ($selisih < 0 ? 'Stabil Menurun' : 'Stabil');
                    } elseif ($selisih > 3) {
                        $perubahan = 'Meningkat';
                    } elseif ($selisih < -3) {
                        $perubahan = 'Menurun';
                    }
                }

                $bulanData[] = [
                    'label' => $current->format('M Y'),
                    'pct' => $pct,
                    'b' => $jB,
                    'total' => $jTotal,
                    'perubahan' => $perubahan,
                    'selisih' => $prevPct !== null ? $pct - $prevPct : null,
                ];
                $prevPct = $pct;
                $current->addMonth();
            }
        }

        return view('siswa.dashboard', compact(
            'siswa', 'semester', 'semesterList',
            'jurnals', 'totalJurnal', 'hariMengaji', 'bCount', 'cCount', 'kCount',
            'r2Harian', 'r2Penilaian', 'r2Akhir',
            'munaqosyah', 'bulanData',
            'tahfidzProgress', 'tahfidzTotalJuz', 'tahfidzJuzAktif'
        ));
    }

    public function nilai(Request $request)
    {
        $siswa = auth('siswa')->user();

        // Ambil SEMUA semester yang sudah ditutup
        $semesters = Semester::where('status', 'ditutup')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $raporList = [];
        foreach ($semesters as $sem) {
            // Data rapor HANYA dari rekap_r2_akhirs (data terkunci saat tutup semester)
            // TIDAK ADA perhitungan ulang — murni data yang di-lock
            $rekap = RekapR2Akhir::where('semester_id', $sem->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            $r2H = $rekap?->r2_harian ?? 0;
            $r2P = $rekap?->r2_penilaian ?? 0;
            $r2A = $rekap?->r2_akhir ?? 0;

            // Predikat
            $predikat = match (true) {
                $r2A >= 85 => 'A — Sangat Baik',
                $r2A >= 70 => 'B — Baik',
                $r2A >= 60 => 'C — Cukup',
                default => 'D — Perlu Bimbingan',
            };

            $raporList[] = [
                'semester' => $sem,
                'r2_harian' => $r2H,
                'r2_penilaian' => $r2P,
                'r2_akhir' => $r2A,
                'predikat' => $predikat,
            ];
        }

        return view('siswa.nilai', compact('raporList'));
    }

    public function perpindahan()
    {
        $siswa = auth('siswa')->user();
        $perpindahans = PerpindahanKelas::where('siswa_id', $siswa->id)
            ->with(['kelasLama', 'kelasBaru', 'semester'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('siswa.perpindahan', compact('perpindahans'));
    }

    /**
     * Halaman detail hafalan untuk siswa.
     */
    public function hafalan()
    {
        $siswa = auth('siswa')->user();
        $semester = Semester::aktif()->first();

        $progressJuz = HafalanTahfidz::progressJuz($siswa->id, $semester?->id);
        $totalJuzHafal = collect($progressJuz)->where('status', 'hafal')->count();
        $juzDistinct = collect($progressJuz)->where('status', 'hafal')->pluck('juz');

        $hafalanList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->with(['surat', 'guru'])
            ->orderBy('tanggal_hafalan', 'desc')
            ->get();

        $juzAktif = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->where('semester_id', $semester?->id)
            ->where('status', '!=', 'hafal')
            ->orderBy('tanggal_hafalan', 'desc')
            ->first();

        return view('siswa.hafalan', compact(
            'siswa', 'semester', 'progressJuz', 'totalJuzHafal',
            'juzDistinct', 'hafalanList', 'juzAktif'
        ));
    }

    /**
     * Tampilkan form edit nomor HP untuk siswa.
     */
    public function editNoHp()
    {
        $siswa = auth('siswa')->user();

        return view('siswa.edit-no-hp', compact('siswa'));
    }

    /**
     * Simpan perubahan nomor HP siswa.
     */
    public function updateNoHp(Request $request)
    {
        $siswa = auth('siswa')->user();

        $validated = $request->validate([
            'no_hp' => 'required|string|max:15',
        ]);

        $siswa->update($validated);

        return redirect()->route('siswa.no-hp.edit')
            ->with('success', 'Nomor HP berhasil diperbarui.');
    }
}
