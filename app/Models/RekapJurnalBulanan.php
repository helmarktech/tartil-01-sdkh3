<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapJurnalBulanan extends Model
{
    protected $table = 'rekap_jurnal_bulanans';
    protected $fillable = [
        'semester_id', 'kelas_id', 'siswa_id', 'bulan',
        'total_hadir',
        'count_b', 'count_c', 'count_k', 'rata_rata',
    ];

    // ================= RELATIONS =================
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    // ================= HELPERS =================
    public function persenPenilaian(string $nilai): float
    {
        $totalDinilai = $this->count_b + $this->count_c + $this->count_k;
        if ($totalDinilai === 0) return 0;
        return match($nilai) {
            'B' => round($this->count_b / $totalDinilai * 100, 1),
            'C' => round($this->count_c / $totalDinilai * 100, 1),
            'K' => round($this->count_k / $totalDinilai * 100, 1),
        };
    }

    // ================= SCOPE =================
    public function scopeByKelasBulan($q, int $kelasId, int $bulan)
    {
        return $q->where('kelas_id', $kelasId)->where('bulan', $bulan);
    }

    public function scopeBySemester($q, int $semesterId)
    {
        return $q->where('semester_id', $semesterId);
    }
}
