<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RekapR2Akhir extends Model
{
    protected $table = 'rekap_r2_akhirs';
    protected $fillable = [
        'semester_id', 'kelas_id', 'siswa_id',
        'r2_harian', 'r2_penilaian', 'r2_akhir',
        'jumlah_indikator', 'jumlah_terisi', 'is_mutasi', 'last_calculated',
    ];
    protected $casts = [
        'is_mutasi' => 'boolean',
        'last_calculated' => 'datetime',
    ];

    public function siswa()  { return $this->belongsTo(Siswa::class); }
    public function kelas()  { return $this->belongsTo(Kelas::class); }
    public function semester() { return $this->belongsTo(Semester::class); }

    /**
     * Ambil atau buat rekap R2 untuk siswa.
     * Kalau sudah ada dan belum expired (24 jam), return cache.
     * Kalau tidak ada atau expired, hitung ulang.
     */
    public static function getOrCalculate(Siswa $siswa, Semester $semester, Kelas $kelas): self
    {
        $rekap = static::where('semester_id', $semester->id)
            ->where('kelas_id', $kelas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        // Kalau ada dan masih fresh (< 24 jam), return
        if ($rekap && $rekap->last_calculated->diffInHours(now()) < 24) {
            return $rekap;
        }

        // Hitung ulang
        return static::calculateAndSave($siswa, $semester, $kelas);
    }

    /**
     * Hitung R2 dan simpan ke cache.
     */
    public static function calculateAndSave(Siswa $siswa, Semester $semester, Kelas $kelas): self
    {
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
        $penilaian = PenilaianRaporInternal::where('semester_id', $semester->id)
            ->whereIn('status', ['aktif', 'selesai'])
            ->first();

        // R2 Penilaian
        $nilaiFilled = collect();
        if ($penilaian) {
            $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                ->where('siswa_id', $siswa->id)
                ->whereIn('indikator_penilaian_id', $indikators->pluck('id'))
                ->whereNotNull('nilai')
                ->get();
            $nilaiFilled = $nilaiRows->pluck('nilai');
        }
        $r2Penilaian = $nilaiFilled->count() > 0 ? round($nilaiFilled->avg()) : 0;

        // R2 Harian — sistem poin: B=2, C=1, K=0
        // Hanya hitung jurnal di semester dan kelas yang bersangkutan
        // serta hanya pada hari aktif (Senin-Kamis) dan bukan hari libur kelas.
        $endDate = min($semester->tanggal_selesai, now());
        $hariLiburList = KelasLibur::where('kelas_id', $kelas->id)
            ->whereBetween('tanggal', [$semester->tanggal_mulai, $endDate])
            ->pluck('tanggal')
            ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();

        $jurnals = JurnalHarian::where('siswa_id', $siswa->id)
            ->where('semester_id', $semester->id)
            ->where('kelas_id', $kelas->id)
            ->whereNotNull('penilaian')
            ->get()
            ->filter(function ($j) use ($semester, $endDate, $hariLiburList) {
                $tgl = Carbon::parse($j->tanggal);

                return $tgl->between($semester->tanggal_mulai, $endDate)
                    && $tgl->dayOfWeek >= 1 && $tgl->dayOfWeek <= 4
                    && ! in_array($tgl->format('Y-m-d'), $hariLiburList);
            });

        $totalJurnal = $jurnals->count();
        $r2Harian = 0;
        if ($totalJurnal > 0) {
            $bCount = $jurnals->where('penilaian', 'B')->count();
            $cCount = $jurnals->where('penilaian', 'C')->count();
            $totalPoin = ($bCount * 2) + ($cCount * 1);
            $maxPoin = $totalJurnal * 2;
            $r2Harian = round(($totalPoin / $maxPoin) * 100);
        }

        $r2Akhir = round(($r2Harian + $r2Penilaian) / 2);

        return static::updateOrCreate(
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

    /**
     * Invalidate cache R2 untuk siswa ini.
     * Dipanggil saat jurnal baru diinput atau nilai rapor diubah.
     */
    public static function invalidate(Siswa $siswa): void
    {
        static::where('siswa_id', $siswa->id)->delete();
    }
}
