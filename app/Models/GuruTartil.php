<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuruTartil extends Model
{
    use SoftDeletes;

    protected $table = 'guru_tartils';
    protected $fillable = ['nama', 'nip', 'email', 'no_hp', 'jenis_kelamin', 'alamat', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean', 'deleted_at' => 'datetime'];

    public function user()
    {
        return $this->hasOne(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'guru_id');
    }

    public function jurnals()
    {
        return $this->hasMany(Jurnal::class, 'guru_id');
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', strtoupper($this->nama));
        return implode('', array_slice(array_map(fn($w) => $w[0] ?? '', $words), 0, 2));
    }
}
