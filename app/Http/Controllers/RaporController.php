<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\KopSuratRapor;
use App\Models\RekapR2Akhir;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporController extends Controller
{
    /**
     * Ambil data rapor siswa dari data yang DI-LOCK (rekap_r2_akhirs).
     * TIDAK ADA perhitungan ulang. TIDAK ADA fallback.
     * Jika guru tidak mengisi → nilai 0.
     */
    private function ambilDataRapor($siswa, $kelasId, $semesterId)
    {
        $rekap = RekapR2Akhir::where('siswa_id', $siswa->id)
            ->where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->first();

        if ($rekap) {
            return [
                'siswa' => $siswa,
                'r2_harian' => $rekap->r2_harian ?? 0,
                'r2_penilaian' => $rekap->r2_penilaian ?? 0,
                'r2_akhir' => $rekap->r2_akhir ?? 0,
                'jumlah_indikator' => $rekap->jumlah_indikator ?? 0,
                'jumlah_terisi' => $rekap->jumlah_terisi ?? 0,
                'predikat' => $this->predikatR2($rekap->r2_akhir ?? 0),
                'semester_id' => $semesterId,
            ];
        }

        // Jika tidak ada rekap → semua nilai 0 (guru tidak mengisi / tidak dihitung)
        return [
            'siswa' => $siswa,
            'r2_harian' => 0,
            'r2_penilaian' => 0,
            'r2_akhir' => 0,
            'jumlah_indikator' => 0,
            'jumlah_terisi' => 0,
            'predikat' => $this->predikatR2(0),
            'semester_id' => $semesterId,
        ];
    }

    private function predikatR2($nilai)
    {
        return match(true) {
            $nilai >= 85 => 'A — Sangat Baik',
            $nilai >= 70 => 'B — Baik',
            $nilai >= 60 => 'C — Cukup',
            default => 'D — Perlu Bimbingan',
        };
    }

    /**
     * Cetak rapor untuk siswa yang sedang login (guard: siswa).
     * Hanya semester yang sudah DITUTUP.
     */
    public function pdfRaporSiswaSendiri(Request $request)
    {
        $siswa = auth('siswa')->user();

        if (!$siswa || !$siswa->kelas_tartil_id) {
            return redirect()->route('siswa.dashboard')->with('error', 'Anda belum terdaftar di kelas tartil.');
        }

        $semesterId = $request->get('semester_id');
        if (!$semesterId) {
            return redirect()->route('siswa.nilai')->with('error', 'Pilih semester terlebih dahulu.');
        }

        $semester = Semester::find($semesterId);
        if (!$semester) {
            return redirect()->route('siswa.nilai')->with('error', 'Semester tidak ditemukan.');
        }

        // Rapor hanya untuk semester yang sudah DITUTUP admin
        if ($semester->status !== 'ditutup') {
            return redirect()->route('siswa.nilai')
                ->with('error', 'Rapor hanya tersedia setelah semester ditutup oleh admin.');
        }

        $kelas = Kelas::with('guru')->find($siswa->kelas_tartil_id);
        if (!$kelas) {
            return redirect()->route('siswa.nilai')->with('error', 'Data kelas tartil tidak ditemukan.');
        }

        $rapor = $this->ambilDataRapor($siswa, $siswa->kelas_tartil_id, $semester->id);
        $kop = KopSuratRapor::untukSemester($semester->id);

        $pdf = Pdf::loadView('pdf.rapor-siswa', compact('siswa', 'kelas', 'semester', 'rapor', 'kop'))
            ->setPaper('A4', 'portrait');
        $filename = "Rapor_{$siswa->nis}_{$semester->tahun_ajaran}_{$semester->jenis}.pdf";
        return $pdf->stream($filename);
    }

    public function pilihKelas()
    {
        $kelas = Kelas::where('status', 'aktif')->orderBy('nama')->get();
        $semesters = Semester::orderBy('tahun_ajaran', 'desc')->get();
        return view('guru.rapor.pilih', compact('kelas', 'semesters'));
    }

    public function previewRaporKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'semester_id' => 'required|exists:semesters,id',
            'jenis' => 'required|in:tengah,akhir',
        ]);

        $kelas = Kelas::with('guru')->findOrFail($request->kelas_id);
        $semester = Semester::findOrFail($request->semester_id);

        $siswas = Siswa::where('kelas_tartil_id', $request->kelas_id)
            ->where('status', 'aktif')->orderBy('nama')->get();

        $dataRapor = [];
        foreach ($siswas as $siswa) {
            $dataRapor[] = $this->ambilDataRapor($siswa, $request->kelas_id, $request->semester_id);
        }

        return view('guru.rapor.preview', compact('kelas', 'semester', 'dataRapor', 'siswas'));
    }

    public function pdfRaporSiswa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
            'semester_id' => 'required|exists:semesters,id',
            'jenis' => 'required|in:tengah,akhir',
        ]);

        $siswa = Siswa::with(['kelasReguler', 'kelasTartil'])->findOrFail($request->siswa_id);
        $kelas = Kelas::with('guru')->findOrFail($request->kelas_id);
        $semester = Semester::findOrFail($request->semester_id);

        $rapor = $this->ambilDataRapor($siswa, $request->kelas_id, $request->semester_id);
        $kop = KopSuratRapor::untukSemester($semester->id);

        $pdf = Pdf::loadView('pdf.rapor-siswa', compact('siswa', 'kelas', 'semester', 'rapor', 'kop'))
            ->setPaper('A4', 'portrait');
        $filename = "Rapor_{$siswa->nis}_{$semester->tahun_ajaran}_{$semester->jenis}.pdf";
        return $pdf->download($filename);
    }

    public function pdfRaporKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'semester_id' => 'required|exists:semesters,id',
            'jenis' => 'required|in:tengah,akhir',
        ]);

        $kelas = Kelas::with('guru')->findOrFail($request->kelas_id);
        $semester = Semester::findOrFail($request->semester_id);

        $siswas = Siswa::where('kelas_tartil_id', $request->kelas_id)
            ->where('status', 'aktif')->orderBy('nama')->get();

        $dataRapor = [];
        foreach ($siswas as $siswa) {
            $dataRapor[] = $this->ambilDataRapor($siswa, $request->kelas_id, $request->semester_id);
        }

        $kop = KopSuratRapor::untukSemester($semester->id);

        $pdf = Pdf::loadView('pdf.rapor-kelas', compact('kelas', 'semester', 'dataRapor', 'siswas', 'kop'))
            ->setPaper('A4', 'portrait');
        $filename = "Rapor_Kelas_{$kelas->nama}_{$semester->tahun_ajaran}_{$semester->jenis}.pdf";
        return $pdf->download($filename);
    }
}
