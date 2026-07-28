<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatMutasi;
use App\Models\Semester;
use App\Models\JurnalHarian;
use App\Models\RekapJurnalBulanan;

class TrackRecordController extends Controller
{
    // ==================== ADMIN ====================
    public function adminIndex(Request $request)
    {
        $kelasList = Kelas::where('status', 'aktif')->orderBy('nama')->get();
        $kelasId = $request->get('kelas_id');
        $kelasAktif = $kelasId ? Kelas::find($kelasId) : null;

        $siswaList = collect();
        if ($kelasAktif) {
            $siswaList = Siswa::where('kelas_tartil_id', $kelasId)
                ->where('status', 'aktif')
                ->with('kelasReguler')
                ->orderBy('nama')
                ->get();
        }

        return view('track-record.index', [
            'kelasList' => $kelasList,
            'kelasId' => $kelasId,
            'kelasAktif' => $kelasAktif,
            'siswaList' => $siswaList,
            'role' => 'admin',
        ]);
    }

    // ==================== GURU ====================
    public function guruIndex(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan.');

        // Hanya kelas yang diajar guru ini
        $kelasList = Kelas::where('status', 'aktif')->where('guru_id', $guru->id)->orderBy('nama')->get();
        $kelasId = $request->get('kelas_id');
        $kelasAktif = $kelasId ? Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->first() : null;

        $siswaList = collect();
        if ($kelasAktif) {
            $siswaList = Siswa::where('kelas_tartil_id', $kelasId)
                ->where('status', 'aktif')
                ->with('kelasReguler')
                ->orderBy('nama')
                ->get();
        }

        return view('track-record.index', [
            'kelasList' => $kelasList,
            'kelasId' => $kelasId,
            'kelasAktif' => $kelasAktif,
            'siswaList' => $siswaList,
            'role' => 'guru',
        ]);
    }

    // ==================== SISWA ====================
    public function siswaIndex()
    {
        $siswa = auth()->guard('siswa')->user();
        if (!$siswa) return redirect()->route('siswa.login');

        return redirect()->route('siswa.track-record.detail', ['siswa' => $siswa->id]);
    }

    // ==================== DETAIL (Semua Role) ====================
    public function detail(Siswa $siswa)
    {
        // Cek akses
        $role = 'admin';
        if (auth()->guard('siswa')->check()) {
            $role = 'siswa';
            // Siswa hanya bisa lihat dirinya sendiri
            if (auth()->guard('siswa')->user()->id !== $siswa->id) {
                return redirect()->route('siswa.track-record')->with('error', 'Anda tidak dapat mengakses data siswa lain.');
            }
        } elseif (auth()->guard('web')->check() && auth()->user()->guru) {
            $role = 'guru';
            // Guru hanya bisa lihat siswa di kelasnya
            $guru = auth()->user()->guru;
            $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id')->toArray();
            if (!in_array($siswa->kelas_tartil_id, $kelasIds)) {
                return back()->with('error', 'Siswa ini tidak berada di kelas yang Anda ajar.');
            }
        }

        // Perpindahan kelas tartil yang sudah disetujui
        $perpindahans = \App\Models\PerpindahanKelas::where('siswa_id', $siswa->id)
            ->where('jenis', 'tartil')
            ->where('status', 'disetujui')
            ->with('semester', 'kelasLama', 'kelasBaru')
            ->orderBy('created_at', 'desc')
            ->get();

        // Rekap jurnal per semester
        $rekapPerSemester = [];
        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        foreach ($semesters as $semester) {
            $rekap = RekapJurnalBulanan::where('siswa_id', $siswa->id)
                ->where('semester_id', $semester->id)
                ->orderBy('bulan')
                ->get();
            if ($rekap->count() > 0) {
                $totalHadir = $rekap->sum('total_hadir');
                $totalB = $rekap->sum('count_b');
                $totalC = $rekap->sum('count_c');
                $totalK = $rekap->sum('count_k');
                $totalNilai = $totalB + $totalC + $totalK;
                $rataRata = $totalNilai > 0 ? round((($totalB * 1.0 + $totalC * 0.67 + $totalK * 0.33) / $totalNilai) * 100) : 0;

                $rekapPerSemester[] = [
                    'semester' => $semester,
                    'bulan_count' => $rekap->count(),
                    'total_hadir' => $totalHadir,
                    'count_b' => $totalB,
                    'count_c' => $totalC,
                    'count_k' => $totalK,
                    'rata_rata' => $rataRata,
                ];
            }
        }

        // Kelas tartil history dari perpindahan kelas
        $kelasHistory = [];
        foreach ($perpindahans as $p) {
            $kelasHistory[] = [
                'tanggal' => $p->created_at,
                'kelas_lama' => $p->kelasLama?->nama ?? '-',
                'kelas_baru' => $p->kelasBaru?->nama ?? '-',
                'semester' => $p->semester?->nama ?? '-',
                'alasan' => $p->alasan ?? '-',
            ];
        }

        return view('track-record.detail', [
            'siswa' => $siswa,
            'kelasHistory' => $kelasHistory,
            'rekapPerSemester' => $rekapPerSemester,
            'role' => $role,
        ]);
    }
}
