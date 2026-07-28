<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JuzSurat extends Model
{
    protected $table = 'juz_surats';

    protected $fillable = [
        'juz', 'surat_id', 'ayat_mulai', 'ayat_selesai', 'total_ayat',
    ];

    protected $casts = [
        'juz' => 'integer',
        'ayat_mulai' => 'integer',
        'ayat_selesai' => 'integer',
        'total_ayat' => 'integer',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
