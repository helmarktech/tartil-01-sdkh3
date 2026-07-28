<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['nama', 'email', 'password', 'role', 'guru_id', 'is_aktif'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_aktif' => 'boolean'];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }
}
