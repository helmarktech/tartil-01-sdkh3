<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalKelas extends Model
{
    protected $table = 'jurnal_kelas';
    protected $fillable = [
        'semester_id', 'kelas_id', 'guru_id', 'tanggal',
        'pertemuan_ke', 'halaman_juz', 'surat_id', 'ayat',
        'materi_pembelajaran', 'topik', 'rencana', 'catatan_kelas',
    ];
    protected $casts = [
        'tanggal' => 'date',
    ];

    public function semester() { return $this->belongsTo(Semester::class); }
    public function kelas()   { return $this->belongsTo(Kelas::class); }
    public function guru()    { return $this->belongsTo(GuruTartil::class, 'guru_id'); }
    public function surat()   { return $this->belongsTo(Surat::class); }

    public function penilaianSiswa()
    {
        return $this->hasMany(JurnalHarian::class, 'kelas_id', 'kelas_id')
            ->whereColumn('tanggal', 'tanggal');
    }
}
