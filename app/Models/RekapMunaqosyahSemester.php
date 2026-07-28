<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapMunaqosyahSemester extends Model
{
    protected $table = 'rekap_munaqosyah_semesters';
    protected $fillable = [
        'semester_id', 'siswa_id',
        'total_ujian', 'total_lulus', 'total_tidak_lulus', 'total_terdaftar',
        'rata_rata_nilai', 'detail_ujian',
        'locked_at',
    ];

    protected $casts = [
        'detail_ujian' => 'array',
        'rata_rata_nilai' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public function semester() { return $this->belongsTo(Semester::class); }
    public function siswa() { return $this->belongsTo(Siswa::class); }

    /**
     * Snapshot data munaqosyah untuk 1 siswa di 1 semester.
     */
    public static function snapshot(Siswa $siswa, Semester $semester): self
    {
        $pendaftaranList = MunaqosyahPendaftaran::where('siswa_id', $siswa->id)
            ->whereHas('munaqosyah', fn($q) => $q->where('semester_id', $semester->id))
            ->with('munaqosyah')
            ->get();

        $total = $pendaftaranList->count();
        $lulus = $pendaftaranList->where('status', MunaqosyahPendaftaran::STATUS_LULUS)->count();
        $tidakLulus = $pendaftaranList->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS)->count();
        $terdaftar = $pendaftaranList->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)->count();

        $nilaiList = $pendaftaranList->pluck('nilai')->filter(fn($n) => $n !== null);
        $rataRata = $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : null;

        $detailUjian = $pendaftaranList->map(fn($p) => [
            'nama_ujian' => $p->munaqosyah->nama ?? '-',
            'tingkat' => $p->munaqosyah->tingkat ?? '-',
            'status' => $p->status,
            'nilai' => $p->nilai,
            'catatan' => $p->catatan,
        ])->values()->toArray();

        return static::updateOrCreate(
            ['semester_id' => $semester->id, 'siswa_id' => $siswa->id],
            [
                'total_ujian' => $total,
                'total_lulus' => $lulus,
                'total_tidak_lulus' => $tidakLulus,
                'total_terdaftar' => $terdaftar,
                'rata_rata_nilai' => $rataRata,
                'detail_ujian' => $detailUjian,
                'locked_at' => now(),
            ]
        );
    }
}
