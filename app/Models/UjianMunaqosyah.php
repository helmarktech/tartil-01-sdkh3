<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjianMunaqosyah extends Model
{
    protected $table = 'ujian_munaqosyahs';

    // Status approval workflow
    protected $fillable = [
        'nama', 'tingkat', 'tanggal_ujian', 'semester_id',
        'status', 'status_pendaftaran',
        'diajukan_oleh', 'approved_by', 'approved_at', 'catatan'
    ];
    protected $casts = [
        'tanggal_ujian' => 'date',
        'approved_at' => 'datetime',
    ];

    // ─── STATUS PENDAFTARAN ───
    public const PENDAFTARAN_BUKA = 'buka';
    public const PENDAFTARAN_TUTUP = 'tutup';

    public function isPendaftaranBuka(): bool
    {
        return $this->status_pendaftaran === self::PENDAFTARAN_BUKA;
    }

    public function isPendaftaranTutup(): bool
    {
        return $this->status_pendaftaran === self::PENDAFTARAN_TUTUP;
    }

    public function bukaPendaftaran(): void
    {
        $this->update(['status_pendaftaran' => self::PENDAFTARAN_BUKA]);
    }

    public function tutupPendaftaran(): void
    {
        $this->update(['status_pendaftaran' => self::PENDAFTARAN_TUTUP]);
    }

    public function scopePendaftaranBuka($query)
    {
        return $query->where('status_pendaftaran', self::PENDAFTARAN_BUKA);
    }

    public function scopePendaftaranTutup($query)
    {
        return $query->where('status_pendaftaran', self::PENDAFTARAN_TUTUP);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(Guru::class, 'diajukan_oleh');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pendaftarans()
    {
        return $this->hasMany(MunaqosyahPendaftaran::class, 'munaqosyah_id');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pengajuan');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['disetujui', 'sedang_berlangsung']);
    }

    public function jumlahLulus(): int
    {
        return $this->pendaftarans()->where('status', MunaqosyahPendaftaran::STATUS_LULUS)->count();
    }

    public function jumlahTidakLulus(): int
    {
        return $this->pendaftarans()->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS)->count();
    }

    public function jumlahTerdaftar(): int
    {
        return $this->pendaftarans()->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)->count();
    }

    public function persentaseKelulusan(): float
    {
        $total = $this->pendaftarans()->count();
        if ($total == 0) return 0;
        return round(($this->jumlahLulus() / $total) * 100, 2);
    }
}
