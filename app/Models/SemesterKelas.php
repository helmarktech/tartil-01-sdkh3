<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterKelas extends Model
{
    protected $table = 'semester_kelas';
    protected $fillable = [
        'semester_id', 'kelas_id', 'jumlah_siswa', 'keterangan'
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
