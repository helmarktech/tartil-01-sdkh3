<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['tahun_ajaran', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'is_aktif', 'status'];
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($semester) {
            if (empty($semester->status)) {
                $semester->status = 'nonaktif';
            }
        });
    }

    public function getNamaAttribute(): string
    {
        $jenisLabel = $this->jenis === 'ganjil' ? 'Ganjil' : 'Genap';
        return "TA {$this->tahun_ajaran} - {$jenisLabel}";
    }

    public function getIsDitutupAttribute(): bool
    {
        return $this->status === 'ditutup';
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran', 'nama');
    }

    public function jurnals()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function perpindahanKelas()
    {
        return $this->hasMany(PerpindahanKelas::class);
    }

    public function kelasTartils()
    {
        return $this->belongsToMany(Kelas::class, 'semester_kelas')
            ->withPivot('jumlah_siswa', 'keterangan')
            ->withTimestamps();
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'semester_siswa')
            ->withPivot('kelas_id', 'kelas_reguler_id', 'status_siswa', 'keterangan')
            ->withTimestamps();
    }

    public function semesterSiswaRecords()
    {
        return $this->hasMany(\App\Models\SemesterSiswa::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeBuka($query)
    {
        return $query->where('status', '!=', 'ditutup');
    }

    public function scopeTahunAjaran($query, $ta)
    {
        return $query->where('tahun_ajaran', $ta);
    }
}
