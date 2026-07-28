<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'kelas';
    protected $fillable = [
        'nama', 'jenis', 'mata_pelajaran', 'deskripsi',
        'guru_id', 'status', 'tanggal_dibuat'
    ];
    protected $casts = [
        'tanggal_dibuat' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_tartil_id');
    }

    public function jurnals()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function liburs()
    {
        return $this->hasMany(KelasLibur::class)->orderBy('tanggal');
    }

    /**
     * Hitung jumlah hari libur untuk kelas ini dalam range tanggal.
     */
    public function jumlahHariLibur($mulai, $selesai, $semesterId = null): int
    {
        $query = $this->liburs()
            ->whereBetween('tanggal', [$mulai, $selesai]);

        // Kalau semester_id disediakan, filter juga (future-proofing)
        // Untuk sekarang, hanya filter tanggal saja

        return $query->count();
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function jurnalHarians()
    {
        return $this->hasMany(JurnalHarian::class, 'kelas_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Cek apakah kelas ini dibuat di pertengahan semester (ada tanggal_dibuat).
     */
    public function getIsKelasBaruAttribute(): bool
    {
        return $this->tanggal_dibuat !== null;
    }

    /**
     * Ambil tanggal awal untuk perhitungan target hari.
     * Kalau kelas baru (punya tanggal_dibuat), pakai tanggal_dibuat.
     * Kalau kelas lama, pakai awal semester.
     */
    public function getAwalHitungHari(\Carbon\Carbon $semesterMulai): \Carbon\Carbon
    {
        return $this->tanggal_dibuat ?? $semesterMulai;
    }
}
