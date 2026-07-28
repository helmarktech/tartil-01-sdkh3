<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = ['nama', 'nama_latin', 'jumlah_ayat', 'jenis', 'urutan'];
    public $timestamps = false;

    public function jurnals()
    {
        return $this->hasMany(JurnalHarian::class);
    }
}
