<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MunaqosyahPendaftaran extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'munaqosyah_pendaftarans';
    protected $fillable = ['munaqosyah_id', 'siswa_id', 'diajukan_oleh', 'pengaju_type', 'status', 'nilai', 'catatan'];

    // Status: T = Terdaftar, L = Lulus, TL = Tidak Lulus
    const STATUS_TERDAFTAR = 'T';
    const STATUS_LULUS = 'L';
    const STATUS_TIDAK_LULUS = 'TL';

    public function munaqosyah()
    {
        return $this->belongsTo(UjianMunaqosyah::class, 'munaqosyah_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function approval()
    {
        return $this->hasOne(MunaqosyahApproval::class, 'pendaftaran_id');
    }

    public function scopeTerdaftar($query)
    {
        return $query->where('status', self::STATUS_TERDAFTAR);
    }

    public function scopeSudahDinilai($query)
    {
        return $query->whereIn('status', [self::STATUS_LULUS, self::STATUS_TIDAK_LULUS]);
    }

    public function scopeDisetujui($query)
    {
        return $query->whereHas('approval', fn($q) => $q->where('status', 'disetujui'));
    }

    public function scopePendingApproval($query)
    {
        return $query->whereHas('approval', fn($q) => $q->where('status', 'pending'));
    }

    public function getStatusApprovalAttribute(): string
    {
        return $this->approval?->status ?? 'pending';
    }

    /**
     * Cek apakah siswa sudah di-approve admin dan bisa dinilai.
     */
    public function getCanBeGradedAttribute(): bool
    {
        // Kalau tidak punya approval record → bisa dinilai (admin direct)
        if (!$this->approval) return true;
        // Kalau approval sudah disetujui → bisa dinilai
        if ($this->approval->status === 'disetujui') return true;
        // Masih pending atau ditolak → tidak bisa dinilai
        return false;
    }

    /**
     * Label status untuk ditampilkan di UI.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TERDAFTAR => 'Terdaftar',
            self::STATUS_LULUS => 'Lulus',
            self::STATUS_TIDAK_LULUS => 'Tidak Lulus',
            default => $this->status,
        };
    }

    /**
     * Badge class untuk status.
     */
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
