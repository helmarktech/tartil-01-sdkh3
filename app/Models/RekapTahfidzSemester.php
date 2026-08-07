<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapTahfidzSemester extends Model
{
    protected $table = 'rekap_tahfidz_semesters';

    protected $fillable = [
        'semester_id', 'kelas_id', 'siswa_id', 'guru_id',
        'total_juz_dihafal', 'total_entry', 'juz_terakhir', 'surat_terakhir',
        'kualitas_rata', 'detail_juz', 'locked_at',
    ];

    protected $casts = [
        'detail_juz' => 'array',
        'locked_at' => 'datetime',
    ];

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

    public function guru()
    {
        return $this->belongsTo(GuruTartil::class, 'guru_id');
    }

    /**
     * Snapshot hafalan tahfidz untuk 1 siswa di 1 semester.
     * Dipanggil otomatis saat semester ditutup (STEP 8).
     */
    public static function snapshot(Siswa $siswa, Semester $semester): self
    {
        $hafalanList = HafalanTahfidz::where('siswa_id', $siswa->id)
            ->where('semester_id', $semester->id)
            ->with('surat')
            ->orderBy('tanggal_hafalan', 'asc')
            ->get();

        // Ambil kelas dari semester_siswa (historical) bukan siswa aktif
        $semSiswa = SemesterSiswa::where('semester_id', $semester->id)
            ->where('siswa_id', $siswa->id)
            ->first();
        $kelasId = $semSiswa?->kelas_id ?? $siswa->kelas_tartil_id;

        $totalEntry = $hafalanList->count();
        $juzHafal = $hafalanList->where('status', 'hafal')->pluck('juz')->unique()->count();

        // Juz terakhir (by tanggal)
        $lastHafalan = $hafalanList->sortByDesc('tanggal_hafalan')->first();
        $juzTerakhir = $lastHafalan?->juz;
        $suratTerakhir = $lastHafalan?->surat?->nama_latin;

        // Rata-rata kualitas (convert ke angka)
        $nilaiMap = ['mumtaz' => 4, 'jayyid_jiddan' => 3, 'jayyid' => 2, 'naqis' => 1];
        $avgNilai = $hafalanList->avg(fn ($h) => $nilaiMap[$h->kualitas] ?? 2);
        $kualitasRata = array_search(round($avgNilai), $nilaiMap) ?: 'jayyid';

        // Detail juz JSON
        $detailJuz = $hafalanList->map(fn ($h) => [
            'juz' => $h->juz,
            'surat' => $h->surat?->nama_latin ?? '-',
            'ayat' => $h->ayat_mulai.($h->ayat_selesai ? '-'.$h->ayat_selesai : ''),
            'status' => $h->status,
            'kualitas' => $h->kualitas,
            'tanggal' => $h->tanggal_hafalan?->format('Y-m-d'),
        ])->values()->toArray();

        return static::updateOrCreate(
            ['semester_id' => $semester->id, 'siswa_id' => $siswa->id],
            [
                'kelas_id' => $kelasId,
                'guru_id' => $lastHafalan?->created_by,
                'total_juz_dihafal' => $juzHafal,
                'total_entry' => $totalEntry,
                'juz_terakhir' => $juzTerakhir,
                'surat_terakhir' => $suratTerakhir,
                'kualitas_rata' => $kualitasRata,
                'detail_juz' => $detailJuz,
                'locked_at' => now(),
            ]
        );
    }
}
