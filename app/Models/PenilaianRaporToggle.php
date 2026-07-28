<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianRaporToggle extends Model
{
    protected $table = 'penilaian_rapor_toggles';

    protected $fillable = [
        'semester_id',
        'kelas_id',
        'siswa_id',
        'status',
        'nilai',
        'catatan',
        'diisi_oleh',
        'tanggal_diisi',
    ];

    protected $casts = [
        'tanggal_diisi' => 'datetime',
    ];

    // Status: T = Terdaftar, L = Lulus, TL = Tidak Lulus
    const STATUS_TERDAFTAR = 'T';
    const STATUS_LULUS = 'L';
    const STATUS_TIDAK_LULUS = 'TL';

    // ── Relations ──

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

    public function pengisi()
    {
        return $this->belongsTo(Guru::class, 'diisi_oleh');
    }

    // ── Scopes ──

    public function scopeTerdaftar($query)
    {
        return $query->where('status', self::STATUS_TERDAFTAR);
    }

    public function scopeLulus($query)
    {
        return $query->where('status', self::STATUS_LULUS);
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('status', self::STATUS_TIDAK_LULUS);
    }

    // ── Accessors ──

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TERDAFTAR => 'Terdaftar',
            self::STATUS_LULUS => 'Lulus',
            self::STATUS_TIDAK_LULUS => 'Tidak Lulus',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TERDAFTAR => 'badge-warning',
            self::STATUS_LULUS => 'badge-success',
            self::STATUS_TIDAK_LULUS => 'badge-error',
            default => 'badge-muted',
        };
    }
}
