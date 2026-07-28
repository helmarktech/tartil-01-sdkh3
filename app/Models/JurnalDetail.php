<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_details';
    protected $fillable = [
        'jurnal_id', 'siswa_id',
        'nilai_b', 'nilai_c', 'nilai_k', 'predikat', 'catatan'
    ];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function hitungPredikat(): string
    {
        $na = $this->nilai_akhir;
        return match(true) {
            $na >= 85 => 'A',
            $na >= 75 => 'B',
            $na >= 65 => 'C',
            $na >= 50 => 'D',
            default => 'E',
        };
    }

    protected static function booted()
    {
        static::saving(function ($detail) {
            $detail->predikat = $detail->hitungPredikat();
        });
    }
}
