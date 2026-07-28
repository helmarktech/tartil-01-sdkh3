<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $fillable = [
        'tanggal', 'kelas_id', 'guru_id', 'semester_id',
        'surat', 'ayat', 'materi', 'jenis_penilaian'
    ];
    protected $casts = ['tanggal' => 'date'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}
