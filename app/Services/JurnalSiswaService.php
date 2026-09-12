<?php

namespace App\Services;

use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KelasLibur;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Satu sumber kebenaran (SSOT) hitungan hari jurnal siswa.
 *
 * Aturan:
 * - Satu tanggal = satu hari jurnal. Baris duplikat pada tanggal yang sama
 *   diambil satu (baris terbaru / id terbesar).
 * - Hanya hari aktif pembelajaran: Senin–Kamis.
 * - Bukan hari libur kelas (kelas_liburs) pada kelas tempat jurnal tercatat.
 * - Tidak menghitung jurnal sebelum tanggal mulai resmi kelasnya
 *   (Kelas::getAwalHitungHari) — selaras dengan monitoring admin/guru.
 * - Berada dalam rentang tanggal semester.
 *
 * Digunakan oleh dashboard siswa dan track record agar angka konsisten
 * dengan monitoring guru (yang menghitung tanggal unik tingkat kelas).
 */
class JurnalSiswaService
{
    /**
     * Jurnal efektif siswa dalam satu semester: satu baris kanonik per tanggal,
     * terurut berdasarkan tanggal.
     *
     * @param  int|null  $kelasId  Batasi ke satu kelas (dipakai snapshot per kelas);
     *                             null = semua kelas (siswa bisa pindah kelas dalam semester).
     */
    public static function jurnalEfektif(int $siswaId, Semester $semester, ?int $kelasId = null): Collection
    {
        // Semester berjalan dibatasi sampai hari ini agar selaras dengan monitoring
        // guru; semester yang sudah ditutup otomatis memakai tanggal_selesai.
        $akhir = Carbon::parse($semester->tanggal_selesai)->endOfDay();
        if ($akhir->isFuture()) {
            $akhir = now()->endOfDay();
        }

        $rows = JurnalHarian::where('siswa_id', $siswaId)
            ->where('semester_id', $semester->id)
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->with('surat')
            ->whereBetween('tanggal', [
                Carbon::parse($semester->tanggal_mulai)->toDateString(),
                $akhir->toDateTimeString(),
            ])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        // Satu baris kanonik per tanggal: ambil baris terbaru (id terbesar)
        $perTanggal = $rows
            ->groupBy(fn ($j) => $j->tanggal->format('Y-m-d'))
            ->map(fn ($grup) => $grup->sortByDesc('id')->first());

        // Daftar libur + tanggal mulai resmi per kelas yang muncul di jurnal siswa
        // (siswa bisa punya jurnal di lebih dari satu kelas saat perpindahan)
        $kelasIds = $perTanggal->pluck('kelas_id')->unique()->values()->all() ?: [0];

        $liburPerKelas = KelasLibur::whereIn('kelas_id', $kelasIds)
            ->whereBetween('tanggal', [
                Carbon::parse($semester->tanggal_mulai)->toDateString(),
                Carbon::parse($semester->tanggal_selesai)->toDateString().' 23:59:59',
            ])
            ->get()
            ->groupBy('kelas_id')
            ->map(fn ($grup) => $grup->pluck('tanggal')
                ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
                ->all());

        $semesterMulai = Carbon::parse($semester->tanggal_mulai);
        $kelasMap = Kelas::whereIn('id', $kelasIds)->get()->keyBy('id');

        return $perTanggal
            ->filter(function ($j) use ($liburPerKelas, $kelasMap, $semesterMulai) {
                if (! static::isHariAktif($j->tanggal)) {
                    return false;
                }

                // Jurnal sebelum tanggal mulai resmi kelas tidak dihitung
                $kelas = $kelasMap->get($j->kelas_id);
                if ($kelas) {
                    $awalKelas = $kelas->getAwalHitungHari($semesterMulai)->max($semesterMulai);
                    if ($j->tanggal->lt($awalKelas)) {
                        return false;
                    }
                }

                return ! in_array($j->tanggal->format('Y-m-d'), $liburPerKelas->get($j->kelas_id, []));
            })
            ->sortBy('tanggal')
            ->values();
    }

    /**
     * Hari aktif pembelajaran: Senin (1) – Kamis (4).
     */
    public static function isHariAktif(Carbon $tanggal): bool
    {
        return $tanggal->dayOfWeek >= 1 && $tanggal->dayOfWeek <= 4;
    }

    /**
     * R2 Harian (poin B=2, C=1, K=0) — pembagi hanya hari yang dinilai,
     * mengikuti RekapR2Akhir::calculateAndSave.
     */
    public static function r2Harian(int $siswaId, Semester $semester, ?int $kelasId = null): int
    {
        $dinilai = static::jurnalEfektif($siswaId, $semester, $kelasId)->whereNotNull('penilaian');
        $total = $dinilai->count();
        if ($total === 0) {
            return 0;
        }

        $poin = ($dinilai->where('penilaian', 'B')->count() * 2)
            + $dinilai->where('penilaian', 'C')->count();

        return (int) round(($poin / ($total * 2)) * 100);
    }

    /**
     * Ringkasan hitungan jurnal siswa dalam satu semester.
     *
     * total = hari jurnal (distinct tanggal, termasuk yang belum dinilai).
     * dinilai = hari dengan penilaian B/C/K.
     */
    public static function ringkasan(int $siswaId, Semester $semester, ?int $kelasId = null): array
    {
        $jurnal = static::jurnalEfektif($siswaId, $semester, $kelasId);
        $b = $jurnal->where('penilaian', 'B')->count();
        $c = $jurnal->where('penilaian', 'C')->count();
        $k = $jurnal->where('penilaian', 'K')->count();

        return [
            'jurnal' => $jurnal,
            'total' => $jurnal->count(),
            'dinilai' => $b + $c + $k,
            'b' => $b,
            'c' => $c,
            'k' => $k,
        ];
    }
}
