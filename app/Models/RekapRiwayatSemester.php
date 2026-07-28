<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapRiwayatSemester extends Model
{
    protected $table = 'rekap_riwayat_semesters';
    protected $fillable = [
        'semester_id', 'siswa_id',
        'kelas_tartil_id', 'kelas_reguler_id',
        'jumlah_pindah_tartil', 'jumlah_pindah_reguler', 'kenaikan_reguler',
        'detail_perpindahan', 'detail_kenaikan',
        'locked_at',
    ];

    protected $casts = [
        'detail_perpindahan' => 'array',
        'detail_kenaikan' => 'array',
        'locked_at' => 'datetime',
    ];

    public function semester() { return $this->belongsTo(Semester::class); }
    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function kelasTartil() { return $this->belongsTo(Kelas::class, 'kelas_tartil_id'); }
    public function kelasReguler() { return $this->belongsTo(KelasReguler::class, 'kelas_reguler_id'); }

    /**
     * Snapshot riwayat perubahan kelas untuk 1 siswa di 1 semester.
     */
    public static function snapshot(Siswa $siswa, Semester $semester): self
    {
        // Kelas akhir semester dari semester_siswa (historical)
        $semSiswa = SemesterSiswa::where('semester_id', $semester->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $kelasTartilId = $semSiswa?->kelas_id ?? $siswa->kelas_tartil_id;
        $kelasRegulerId = $semSiswa?->kelas_reguler_id ?? $siswa->kelas_reguler_id;

        // Perpindahan dalam semester ini
        $perpindahanList = PerpindahanKelas::where('siswa_id', $siswa->id)
            ->where(function($q) use ($semester) {
                $q->whereBetween('created_at', [$semester->tanggal_mulai, $semester->tanggal_selesai])
                  ->orWhereBetween('tanggal_pindah', [$semester->tanggal_mulai, $semester->tanggal_selesai]);
            })
            ->with(['kelasLama', 'kelasBaru'])
            ->get();

        $pindahTartil = $perpindahanList->whereNotNull('kelas_tujuan_id')->count();
        $pindahReguler = $perpindahanList->whereNotNull('kelas_reguler_tujuan_id')->count();

        $detailPindah = $perpindahanList->map(fn($p) => [
            'jenis' => $p->kelas_tujuan_id ? 'tartil' : ($p->kelas_reguler_tujuan_id ? 'reguler' : 'lainnya'),
            'dari_kelas' => $p->kelasLama?->nama ?? '-',
            'ke_kelas' => $p->kelasBaru?->nama ?? '-',
            'tanggal' => $p->tanggal_pindah?->format('Y-m-d') ?? $p->created_at->format('Y-m-d'),
            'status' => $p->status,
        ])->values()->toArray();

        // Kenaikan kelas reguler
        $kenaikan = KenaikanKelasReguler::where('siswa_id', $siswa->id)
            ->whereBetween('created_at', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->with(['kelasLama', 'kelasBaru'])
            ->first();

        $detailKenaikan = null;
        if ($kenaikan) {
            $detailKenaikan = [
                'dari_reguler' => $kenaikan->kelasLama?->nama ?? '-',
                'ke_reguler' => $kenaikan->kelasBaru?->nama ?? '-',
                'tanggal' => $kenaikan->created_at->format('Y-m-d'),
                'status' => $kenaikan->status,
            ];
        }

        return static::updateOrCreate(
            ['semester_id' => $semester->id, 'siswa_id' => $siswa->id],
            [
                'kelas_tartil_id' => $kelasTartilId,
                'kelas_reguler_id' => $kelasRegulerId,
                'jumlah_pindah_tartil' => $pindahTartil,
                'jumlah_pindah_reguler' => $pindahReguler,
                'kenaikan_reguler' => $kenaikan ? 1 : 0,
                'detail_perpindahan' => $detailPindah,
                'detail_kenaikan' => $detailKenaikan,
                'locked_at' => now(),
            ]
        );
    }
}
