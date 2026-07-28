<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterPenilaianRapor extends Model
{
    use HasFactory;

    protected $table = 'semester_penilaian_rapors';

    protected $fillable = [
        'semester_id',
        'status',
        'is_cetak',
        'tanggal_cetak',
        'tanggal_aktif',
        'tanggal_selesai',
        'dibuat_oleh',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_aktif' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    // ── Relations ──

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penilaianRapors()
    {
        return $this->hasMany(PenilaianRapor::class, 'semester_penilaian_rapor_id');
    }

    // ── Scopes ──

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // ── Helpers ──

    /**
     * Cek apakah nilai rapor sudah dikunci (tidak bisa diubah).
     * Nilai dikunci jika: semester ditutup, status selesai, atau sudah dicetak.
     */
    public function isLocked(): bool
    {
        // Sudah dicetak
        if ($this->is_cetak) return true;

        // Status selesai
        if ($this->status === 'selesai') return true;

        // Semester sudah ditutup (tidak aktif)
        $semesterAktif = $this->semester;
        if ($semesterAktif && !$semesterAktif->is_aktif) return true;

        return false;
    }

    /**
     * Label alasan kenapa dikunci.
     */
    public function lockReason(): string
    {
        if ($this->is_cetak) return 'Rapor sudah dicetak pada ' . $this->tanggal_cetak?->format('d/m/Y H:i');
        if ($this->status === 'selesai') return 'Status penilaian sudah selesai';
        if ($this->semester && !$this->semester->is_aktif) return 'Semester sudah ditutup';
        return '';
    }

    /**
     * Hitung berapa persen penilaian yang sudah diisi (semua siswa, semua kelas).
     */
    public function progressPersen(): int
    {
        $total = $this->penilaianRapors()->count();
        if ($total === 0) return 0;

        $diisi = $this->penilaianRapors()->whereNotNull('nilai_angka')->count();
        return (int) round(($diisi / $total) * 100);
    }

    /**
     * Hitung jumlah siswa yang terlibat.
     */
    public function jumlahSiswa(): int
    {
        return $this->penilaianRapors()->distinct('siswa_id')->count('siswa_id');
    }

    /**
     * Hitung jumlah kelas yang terlibat.
     */
    public function jumlahKelas(): int
    {
        return $this->penilaianRapors()
            ->join('siswas', 'penilaian_rapors.siswa_id', '=', 'siswas.id')
            ->distinct('siswas.kelas_tartil_id')
            ->count('siswas.kelas_tartil_id');
    }

    /**
     * Status label dengan badge style.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            default => 'Draft',
        };
    }

    /**
     * Status badge class untuk styling.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'aktif' => 'badge-success',
            'selesai' => 'badge-info',
            default => 'badge-warning',
        };
    }
}
