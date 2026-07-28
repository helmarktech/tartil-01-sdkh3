<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'status'];
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran', 'nama');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function getIsDitutupAttribute(): bool
    {
        return $this->status === 'ditutup';
    }

    /**
     * Cek apakah semua semester dalam TA ini sudah ditutup.
     * Jika ya, TA dianggap selesai dan bisa dibuat TA baru.
     */
    public function isSemuaSemesterDitutup(): bool
    {
        $totalSemester = $this->semesters()->count();
        if ($totalSemester === 0) return false;

        $semesterDitutup = $this->semesters()
            ->where(function ($q) {
                $q->where('status', 'ditutup')
                  ->orWhere('is_aktif', false);
            })
            ->count();

        return $semesterDitutup >= $totalSemester;
    }

    /**
     * Cek apakah TA ini benar-benar aktif (ada semester yang aktif).
     */
    public function isBenarAktif(): bool
    {
        return $this->status === 'aktif' && !$this->isSemuaSemesterDitutup();
    }
}
