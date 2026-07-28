<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatMutasi extends Model
{
    protected $table = 'riwayat_mutasis';
    protected $fillable = ['mutasi_type', 'mutasi_id', 'jenis', 'keterangan', 'dilakukan_oleh', 'tanggal_mutasi'];
    protected $casts = ['tanggal_mutasi' => 'datetime'];

    public function mutasi()
    {
        return $this->morphTo();
    }

    public function pelaku()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
