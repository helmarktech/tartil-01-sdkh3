<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapJurnalSemester extends Model
{
    protected $table = 'rekap_jurnal_semesters';
    protected $fillable = [
        'semester_id', 'kelas_id', 'siswa_id', 'guru_id',
        'total_hari', 'count_b', 'count_c', 'count_k',
        'r2_harian', 'persentase_b',
        'detail_surat', 'detail_bulanan',
        'locked_at',
    ];

    protected $casts = [
        'detail_surat' => 'array',
        'detail_bulanan' => 'array',
        'locked_at' => 'datetime',
    ];

    public function semester() { return $this->belongsTo(Semester::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function guru() { return $this->belongsTo(GuruTartil::class, 'guru_id'); }

    /**
     * Snapshot jurnal harian untuk 1 siswa di 1 semester.
     * Dipanggil otomatis saat semester ditutup.
     */
    public static function snapshot(Siswa $siswa, Semester $semester, Kelas $kelas): self
    {
        // Hitung data jurnal
        $jurnals = JurnalHarian::where('siswa_id', $siswa->id)
            ->where('semester_id', $semester->id)
            ->where('kelas_id', $kelas->id)
            ->with('surat')
            ->orderBy('tanggal')
            ->get();

        $total = $jurnals->count();
        $bCount = $jurnals->where('penilaian', 'B')->count();
        $cCount = $jurnals->where('penilaian', 'C')->count();
        $kCount = $jurnals->where('penilaian', 'K')->count();

        // R2 Harian (sistem poin: B=2, C=1, K=0)
        $r2Harian = 0;
        if ($total > 0) {
            $totalPoin = ($bCount * 2) + ($cCount * 1);
            $maxPoin = $total * 2;
            $r2Harian = round(($totalPoin / $maxPoin) * 100);
        }

        $persentaseB = $total > 0 ? round(($bCount / $total) * 100) : 0;

        // Detail surat yang dibaca
        $detailSurat = $jurnals->whereNotNull('surat_id')->map(fn($j) => [
            'tanggal' => $j->tanggal->format('Y-m-d'),
            'surat' => $j->surat?->nama_latin ?? '-',
            'ayat_mulai' => $j->ayat_mulai,
            'ayat_selesai' => $j->ayat_selesai,
            'penilaian' => $j->penilaian,
        ])->values()->toArray();

        // Detail bulanan
        $bulanan = [];
        if ($semester->tanggal_mulai && $semester->tanggal_selesai) {
            $current = $semester->tanggal_mulai->copy()->startOfMonth();
            $end = min($semester->tanggal_selesai, now());
            while ($current <= $end) {
                $th = $current->year;
                $bl = $current->month;
                $bulanJurnals = $jurnals->filter(fn($j) => $j->tanggal->year == $th && $j->tanggal->month == $bl);
                $b = $bulanJurnals->where('penilaian', 'B')->count();
                $c = $bulanJurnals->where('penilaian', 'C')->count();
                $k = $bulanJurnals->where('penilaian', 'K')->count();
                $t = $bulanJurnals->count();
                $bulanan[] = [
                    'bulan' => $current->format('F Y'),
                    'total' => $t,
                    'b' => $b,
                    'c' => $c,
                    'k' => $k,
                    'persentase_b' => $t > 0 ? round(($b / $t) * 100) : 0,
                ];
                $current->addMonth();
            }
        }

        return static::updateOrCreate(
            ['semester_id' => $semester->id, 'kelas_id' => $kelas->id, 'siswa_id' => $siswa->id],
            [
                'guru_id' => $kelas->guru_id,
                'total_hari' => $total,
                'count_b' => $bCount,
                'count_c' => $cCount,
                'count_k' => $kCount,
                'r2_harian' => $r2Harian,
                'persentase_b' => $persentaseB,
                'detail_surat' => $detailSurat,
                'detail_bulanan' => $bulanan,
                'locked_at' => now(),
            ]
        );
    }
}
