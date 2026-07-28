<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerpindahanKelas extends Model
{
    protected $table = 'perpindahan_kelas';
    protected $fillable = [
        'siswa_id', 'kelas_lama_id', 'kelas_baru_id', 'semester_id',
        'diajukan_oleh', 'guru_tujuan_id', 'jenis',
        'alasan', 'status', 'approved_by', 'approved_at', 'catatan'
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasLama()
    {
        return $this->belongsTo(Kelas::class, 'kelas_lama_id');
    }

    public function kelasBaru()
    {
        return $this->belongsTo(Kelas::class, 'kelas_baru_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function guruTujuan()
    {
        return $this->belongsTo(GuruTartil::class, 'guru_tujuan_id');
    }
}
