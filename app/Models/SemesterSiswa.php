<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterSiswa extends Model
{
    protected $table = 'semester_siswa';
    protected $fillable = [
        'semester_id', 'siswa_id', 'kelas_id', 'kelas_reguler_id',
        'status_siswa', 'keterangan'
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasTartil()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function kelasReguler()
    {
        return $this->belongsTo(KelasReguler::class, 'kelas_reguler_id');
    }
}
