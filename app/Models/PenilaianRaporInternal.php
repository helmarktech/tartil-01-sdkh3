<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianRaporInternal extends Model
{
    protected $table = 'penilaian_rapor_internals';
    protected $fillable = ['nama', 'semester_id', 'status'];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function nilais()
    {
        return $this->hasMany(PenilaianRaporNilai::class, 'penilaian_id');
    }
}
