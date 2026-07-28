<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuruReguler extends Model
{
    use SoftDeletes;

    protected $table = 'guru_regulers';
    protected $fillable = ['nama', 'nip', 'email', 'no_hp', 'jenis_kelamin', 'alamat', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean', 'deleted_at' => 'datetime'];

    public function kelasReguler()
    {
        return $this->hasMany(KelasReguler::class, 'guru_pengampu_id');
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', strtoupper($this->nama));
        return implode('', array_slice(array_map(fn($w) => $w[0] ?? '', $words), 0, 2));
    }
}
