<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelasReguler extends Model
{
    use SoftDeletes;

    protected $table = 'kelas_regulers';
    protected $fillable = ['nama', 'jenjang', 'tingkat', 'guru_pengampu_id', 'keterangan', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean'];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_reguler_id');
    }

    public function guruPengampu()
    {
        return $this->belongsTo(GuruReguler::class, 'guru_pengampu_id');
    }

    // Proteksi: cek apakah kelas masih punya siswa aktif sebelum soft delete
    public function getCanDeleteAttribute(): bool
    {
        return Siswa::where('kelas_reguler_id', $this->id)->where('status', 'aktif')->count() === 0;
    }
}
