<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HafalanTahfidz extends Model
{
    protected $table = 'hafalan_tahfidzs';

    protected $fillable = [
        'siswa_id', 'semester_id', 'kelas_id', 'surat_id',
        'juz', 'ayat_mulai', 'ayat_selesai',
        'status', 'kualitas', 'catatan',
        'tanggal_hafalan', 'created_by',
    ];

    protected $casts = [
        'tanggal_hafalan' => 'date',
        'juz' => 'integer',
        'ayat_mulai' => 'integer',
        'ayat_selesai' => 'integer',
    ];

    // ─── RELASI ───
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function guru()
    {
        return $this->belongsTo(GuruTartil::class, 'created_by');
    }

    // ─── SCOPE ───
    public function scopeHafal($query)
    {
        return $query->where('status', 'hafal');
    }

    public function scopeMurajaah($query)
    {
        return $query->where('status', 'murajaah');
    }

    public function scopePerSiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopePerSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopePerKelas($query, int $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // ─── HELPERS ───

    /**
     * Total juz yang sudah dihafal (status = hafal) per siswa.
     */
    public static function totalJuzHafal(int $siswaId): int
    {
        return self::where('siswa_id', $siswaId)
            ->where('status', 'hafal')
            ->distinct('juz')
            ->count('juz');
    }

    /**
     * Progress semua 30 juz untuk 1 siswa.
     * Return: [{juz: 1, status: 'hafal'|'proses'|'baru'|null, kualitas, surat, tanggal}, ...]
     */
    public static function progressJuz(int $siswaId, ?int $semesterId = null): array
    {
        $hafalan = self::where('siswa_id', $siswaId);
        if ($semesterId) {
            $hafalan->where('semester_id', $semesterId);
        }
        $hafalan = $hafalan->with('surat')
            ->orderBy('tanggal_hafalan', 'desc')
            ->get()
            ->groupBy('juz');

        $progress = [];
        for ($j = 1; $j <= 30; $j++) {
            $entries = $hafalan->get($j);
            if (!$entries || $entries->isEmpty()) {
                $progress[] = ['juz' => $j, 'status' => null, 'kualitas' => null, 'surat' => null, 'tanggal' => null];
                continue;
            }
            // Ambil entry terbaru untuk juz ini
            $latest = $entries->first();
            $progress[] = [
                'juz' => $j,
                'status' => $latest->status,
                'kualitas' => $latest->kualitas,
                'surat' => $latest->surat?->nama_latin ?? '-',
                'tanggal' => $latest->tanggal_hafalan?->format('d/m/Y'),
            ];
        }
        return $progress;
    }

    /**
     * Daftar juz yang sudah dihafal dengan detail surat.
     */
    public static function daftarJuzHafal(int $siswaId, ?int $semesterId = null): array
    {
        $query = self::where('siswa_id', $siswaId)
            ->with('surat')
            ->orderBy('juz')
            ->orderBy('ayat_mulai');

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        return $query->get()->map(fn($h) => [
            'juz' => $h->juz,
            'surat' => $h->surat?->nama_latin ?? '-',
            'ayat' => $h->ayat_mulai . ($h->ayat_selesai ? '-' . $h->ayat_selesai : ''),
            'status' => $h->status,
            'kualitas' => $h->kualitas,
            'tanggal' => $h->tanggal_hafalan?->format('d/m/Y'),
        ])->toArray();
    }

    /**
     * Rekap per kelas Tahfidz untuk statistik.
     */
    public static function rekapPerKelas(int $kelasId, ?int $semesterId = null): array
    {
        $query = self::where('kelas_id', $kelasId);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $totalEntry = $query->count();
        $totalHafal = (clone $query)->where('status', 'hafal')->count();
        $totalMurajaah = (clone $query)->where('status', 'murajaah')->count();

        // Distribusi juz
        $distribusiJuz = (clone $query)
            ->selectRaw('juz, COUNT(*) as total')
            ->groupBy('juz')
            ->orderBy('juz')
            ->pluck('total', 'juz')
            ->toArray();

        // Per siswa
        $perSiswa = Siswa::where('kelas_tartil_id', $kelasId)
            ->where('status', 'aktif')
            ->get()
            ->map(function ($s) use ($semesterId) {
                $juzHafal = self::where('siswa_id', $s->id)
                    ->where('status', 'hafal');
                if ($semesterId) {
                    $juzHafal->where('semester_id', $semesterId);
                }
                $juzCount = $juzHafal->distinct('juz')->count('juz');

                $lastHafalan = self::where('siswa_id', $s->id)
                    ->orderBy('tanggal_hafalan', 'desc')
                    ->first();

                return [
                    'siswa' => $s,
                    'juzHafal' => $juzCount,
                    'lastJuz' => $lastHafalan?->juz ?? '-',
                    'lastSurat' => $lastHafalan?->surat?->nama_latin ?? '-',
                    'lastTanggal' => $lastHafalan?->tanggal_hafalan?->format('d/m/Y') ?? '-',
                    'kualitas' => $lastHafalan?->kualitas ?? '-',
                ];
            })
            ->sortByDesc('juzHafal')
            ->values()
            ->toArray();

        return [
            'totalEntry' => $totalEntry,
            'totalHafal' => $totalHafal,
            'totalMurajaah' => $totalMurajaah,
            'distribusiJuz' => $distribusiJuz,
            'perSiswa' => $perSiswa,
        ];
    }

    public static function labelStatus(string $status): string
    {
        return match ($status) {
            'baru' => 'Baru',
            'setengah_hafal' => 'Setengah Hafal',
            'hafal' => 'Hafal',
            'murajaah' => 'Murojaah',
            default => $status,
        };
    }

    public static function labelKualitas(string $kualitas): string
    {
        return match ($kualitas) {
            'mumtaz' => 'Mumtaz',
            'jayyid_jiddan' => 'Jayyid Jiddan',
            'jayyid' => 'Jayyid',
            'naqis' => 'Naqis',
            default => $kualitas,
        };
    }
}
