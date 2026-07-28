<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
            if (! $entries || $entries->isEmpty()) {
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

        return $query->get()->map(fn ($h) => [
            'juz' => $h->juz,
            'surat' => $h->surat?->nama_latin ?? '-',
            'ayat' => $h->ayat_mulai.($h->ayat_selesai ? '-'.$h->ayat_selesai : ''),
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
                    'lastStatus' => $lastHafalan?->status ?? null,
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

    /**
     * Daftar surat beserta jumlah ayat dalam satu juz.
     */
    public static function suratDalamJuz(int $juz): Collection
    {
        return JuzSurat::where('juz', $juz)
            ->with('surat')
            ->orderBy('ayat_mulai')
            ->get();
    }

    /**
     * Total ayat dalam satu juz berdasarkan mapping JuzSurat.
     */
    public static function totalAyatJuz(int $juz): int
    {
        return JuzSurat::where('juz', $juz)->sum('total_ayat');
    }

    /**
     * Hitung persentase hafalan siswa untuk satu juz.
     * Hanya menghitung ayat dengan status 'hafal' atau 'murajaah' dan punya surat_id.
     * Return: [juz, totalAyat, ayatHafal, persentase, status].
     */
    public static function hitungPersentaseJuz(int $siswaId, int $juz, ?int $semesterId = null): array
    {
        $mapping = JuzSurat::where('juz', $juz)->get();
        $totalAyat = $mapping->sum('total_ayat');

        if ($totalAyat === 0) {
            return [
                'juz' => $juz,
                'totalAyat' => 0,
                'ayatHafal' => 0,
                'persentase' => 0.0,
                'status' => 'belum',
            ];
        }

        $hafalan = self::where('siswa_id', $siswaId)
            ->where('juz', $juz)
            ->whereIn('status', ['hafal', 'murajaah'])
            ->whereNotNull('surat_id')
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->get();

        $hafalSet = [];
        foreach ($hafalan as $h) {
            foreach ($mapping as $m) {
                if ($m->surat_id !== $h->surat_id) {
                    continue;
                }
                $start = max($h->ayat_mulai, $m->ayat_mulai);
                $end = $h->ayat_selesai
                    ? min($h->ayat_selesai, $m->ayat_selesai)
                    : min($h->ayat_mulai, $m->ayat_selesai);
                if ($start > $end) {
                    continue;
                }
                for ($a = $start; $a <= $end; $a++) {
                    $hafalSet["{$m->surat_id}:{$a}"] = true;
                }
            }
        }

        $ayatHafal = count($hafalSet);
        $persentase = round(($ayatHafal / $totalAyat) * 100, 1);

        $status = match (true) {
            $persentase >= 100 => 'selesai',
            $persentase > 0 => 'proses',
            default => 'belum',
        };

        return [
            'juz' => $juz,
            'totalAyat' => $totalAyat,
            'ayatHafal' => $ayatHafal,
            'persentase' => $persentase,
            'status' => $status,
        ];
    }

    /**
     * Ringkasan persentase hafalan untuk semua 30 juz.
     */
    public static function ringkasanPersentaseJuz(int $siswaId, ?int $semesterId = null): array
    {
        $ringkasan = [];
        for ($j = 1; $j <= 30; $j++) {
            $ringkasan[] = self::hitungPersentaseJuz($siswaId, $j, $semesterId);
        }

        return $ringkasan;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // KUMULATIF HAFALAN PER SEMESTER (hafalan tidak reset antar semester/TA)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Scope hafalan dengan tanggal <= tanggal selesai semester batas.
     */
    public function scopeSampaiSemester($query, Semester $semester)
    {
        return $query->where('tanggal_hafalan', '<=', $semester->tanggal_selesai);
    }

    /**
     * Total juz yang sudah dihafal (status hafal) secara kumulatif sampai semester tertentu.
     */
    public static function totalJuzHafalSampaiSemester(int $siswaId, Semester $semester): int
    {
        return self::where('siswa_id', $siswaId)
            ->where('status', 'hafal')
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->distinct('juz')
            ->count('juz');
    }

    /**
     * Hafalan terakhir (status hafal/murajaah) secara kumulatif sampai semester tertentu.
     */
    public static function hafalanTerakhirSampaiSemester(int $siswaId, Semester $semester): ?self
    {
        return self::where('siswa_id', $siswaId)
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->orderBy('tanggal_hafalan', 'desc')
            ->first();
    }

    /**
     * Setoran hafalan terakhir yang tercatat di semester tersebut (bukan kumulatif).
     */
    public static function setoranTerakhirDiSemester(int $siswaId, Semester $semester): ?self
    {
        return self::where('siswa_id', $siswaId)
            ->where('semester_id', $semester->id)
            ->orderBy('tanggal_hafalan', 'desc')
            ->first();
    }

    /**
     * Cek apakah siswa memiliki hafalan di semester-tahun ajaran sebelum semester batas.
     */
    public static function punyaHafalanSebelumSemester(int $siswaId, Semester $semester): bool
    {
        return self::where('siswa_id', $siswaId)
            ->where('tanggal_hafalan', '<', $semester->tanggal_mulai)
            ->exists();
    }

    /**
     * Hitung persentase hafalan untuk satu juz secara kumulatif sampai semester batas.
     */
    public static function hitungPersentaseJuzSampaiSemester(int $siswaId, int $juz, Semester $semester): array
    {
        $mapping = JuzSurat::where('juz', $juz)->get();
        $totalAyat = $mapping->sum('total_ayat');

        if ($totalAyat === 0) {
            return [
                'juz' => $juz,
                'totalAyat' => 0,
                'ayatHafal' => 0,
                'persentase' => 0.0,
                'status' => 'belum',
            ];
        }

        $hafalan = self::where('siswa_id', $siswaId)
            ->where('juz', $juz)
            ->whereIn('status', ['hafal', 'murajaah'])
            ->whereNotNull('surat_id')
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->get();

        $hafalSet = [];
        foreach ($hafalan as $h) {
            foreach ($mapping as $m) {
                if ($m->surat_id !== $h->surat_id) {
                    continue;
                }
                $start = max($h->ayat_mulai, $m->ayat_mulai);
                $end = $h->ayat_selesai
                    ? min($h->ayat_selesai, $m->ayat_selesai)
                    : min($h->ayat_mulai, $m->ayat_selesai);
                if ($start > $end) {
                    continue;
                }
                for ($a = $start; $a <= $end; $a++) {
                    $hafalSet["{$m->surat_id}:{$a}"] = true;
                }
            }
        }

        $ayatHafal = count($hafalSet);
        $persentase = round(($ayatHafal / $totalAyat) * 100, 1);

        $status = match (true) {
            $persentase >= 100 => 'selesai',
            $persentase > 0 => 'proses',
            default => 'belum',
        };

        return [
            'juz' => $juz,
            'totalAyat' => $totalAyat,
            'ayatHafal' => $ayatHafal,
            'persentase' => $persentase,
            'status' => $status,
        ];
    }

    /**
     * Ringkasan persentase hafalan 30 juz secara kumulatif sampai semester batas.
     */
    public static function ringkasanPersentaseJuzSampaiSemester(int $siswaId, Semester $semester): array
    {
        $ringkasan = [];
        for ($j = 1; $j <= 30; $j++) {
            $ringkasan[] = self::hitungPersentaseJuzSampaiSemester($siswaId, $j, $semester);
        }

        return $ringkasan;
    }

    /**
     * Rekap per kelas Tahfidz sampai semester batas.
     * Parameter $siswaIds opsional untuk membatasi siswa (misal dari snapshot semester).
     */
    public static function rekapPerKelasSampaiSemester(int $kelasId, Semester $semester, ?array $siswaIds = null): array
    {
        $query = Siswa::query();
        if ($siswaIds !== null) {
            // Semester lama: gunakan snapshot semester_siswa, jangan filter status/kelas aktif sekarang.
            $query->whereIn('id', $siswaIds);
        } else {
            // Semester aktif: gunakan komposisi kelas saat ini.
            $query->where('kelas_tartil_id', $kelasId)->where('status', 'aktif');
        }
        $siswaList = $query->orderBy('nama')->get();

        $perSiswa = $siswaList->map(function ($s) use ($semester) {
            $juzCount = self::totalJuzHafalSampaiSemester($s->id, $semester);
            $lastHafalan = self::hafalanTerakhirSampaiSemester($s->id, $semester);
            $setoranSemester = self::setoranTerakhirDiSemester($s->id, $semester);
            $punyaHafalanLama = self::punyaHafalanSebelumSemester($s->id, $semester);

            return [
                'siswa' => $s,
                'juzHafal' => $juzCount,
                'lastJuz' => $lastHafalan?->juz ?? '-',
                'lastSurat' => $lastHafalan?->surat?->nama_latin ?? '-',
                'lastTanggal' => $lastHafalan?->tanggal_hafalan?->format('d/m/Y') ?? '-',
                'lastStatus' => $lastHafalan?->status ?? null,
                'setoranSemester' => $setoranSemester?->tanggal_hafalan?->format('d/m/Y') ?? '-',
                'kualitas' => $lastHafalan?->kualitas ?? '-',
                'punyaHafalanLama' => $punyaHafalanLama,
            ];
        })
            ->sortByDesc('juzHafal')
            ->values()
            ->toArray();

        $totalEntry = self::where('kelas_id', $kelasId)
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->count();
        $totalHafal = self::where('kelas_id', $kelasId)
            ->where('status', 'hafal')
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->count();
        $totalMurajaah = self::where('kelas_id', $kelasId)
            ->where('status', 'murajaah')
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->count();

        return [
            'totalEntry' => $totalEntry,
            'totalHafal' => $totalHafal,
            'totalMurajaah' => $totalMurajaah,
            'perSiswa' => $perSiswa,
        ];
    }

    /**
     * Rekap per juz untuk satu kelas Tahfidz pada semester batas.
     * Return per juz: [totalSiswa, sudahHafal, tuntas, daftarSiswaTuntas].
     */
    public static function rekapJuzPerKelas(int $kelasId, Semester $semester, ?array $siswaIds = null): array
    {
        $query = Siswa::query();
        if ($siswaIds !== null) {
            $query->whereIn('id', $siswaIds);
        } else {
            $query->where('kelas_tartil_id', $kelasId)->where('status', 'aktif');
        }
        $siswaList = $query->orderBy('nama')->get()->keyBy('id');
        $totalSiswa = $siswaList->count();

        if ($totalSiswa === 0) {
            return collect(range(1, 30))->map(fn ($j) => [
                'juz' => $j,
                'totalSiswa' => 0,
                'sudahHafal' => 0,
                'tuntas' => 0,
                'siswaTuntas' => [],
            ])->values()->toArray();
        }

        // Ambil semua hafalan relevan sekaligus untuk menghindari N+1.
        $hafalan = self::where('kelas_id', $kelasId)
            ->where('tanggal_hafalan', '<=', $semester->tanggal_selesai)
            ->whereIn('siswa_id', $siswaList->keys())
            ->whereIn('status', ['hafal', 'murajaah'])
            ->whereNotNull('surat_id')
            ->with('surat')
            ->orderBy('tanggal_hafalan', 'desc')
            ->get();

        $result = [];
        for ($juz = 1; $juz <= 30; $juz++) {
            $sudahHafalIds = $hafalan
                ->where('juz', $juz)
                ->where('status', 'hafal')
                ->pluck('siswa_id')
                ->unique()
                ->all();

            $tuntasIds = [];
            foreach ($siswaList as $siswaId => $siswa) {
                if (in_array($siswaId, $sudahHafalIds)) {
                    $persentase = self::hitungPersentaseJuzSampaiSemester($siswaId, $juz, $semester);
                    if ($persentase['persentase'] >= 100) {
                        $tuntasIds[] = $siswaId;
                    }
                }
            }

            $result[] = [
                'juz' => $juz,
                'totalSiswa' => $totalSiswa,
                'sudahHafal' => count($sudahHafalIds),
                'tuntas' => count($tuntasIds),
                'siswaTuntas' => $siswaList->only($tuntasIds)->values()->toArray(),
            ];
        }

        return $result;
    }
}
