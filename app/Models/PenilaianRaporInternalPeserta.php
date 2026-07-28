<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianRaporInternalPeserta extends Model
{
    protected $table = 'penilaian_rapor_internal_pesertas';

    protected $fillable = [
        'ujian_id', 'siswa_id', 'catatan'
    ];

    // Siswa terdaftar = ada row di tabel ini
    // Siswa tidak terdaftar = tidak ada row

    // ── Relations ──

    public function ujian()
    {
        return $this->belongsTo(PenilaianRaporInternal::class, 'ujian_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
