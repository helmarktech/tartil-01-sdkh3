<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KenaikanKelasReguler extends Model
{
    protected $table = 'kenaikan_kelas_regulers';
    protected $fillable = [
        'siswa_id', 'kelas_reguler_lama_id', 'kelas_reguler_baru_id',
        'semester_id', 'tahun_ajaran', 'kategori', 'approved_by', 'approved_at', 'keterangan'
    ];
    protected $casts = ['approved_at' => 'datetime'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasLama()
    {
        return $this->belongsTo(KelasReguler::class, 'kelas_reguler_lama_id');
    }

    public function kelasBaru()
    {
        return $this->belongsTo(KelasReguler::class, 'kelas_reguler_baru_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
