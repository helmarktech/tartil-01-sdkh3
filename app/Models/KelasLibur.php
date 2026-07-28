<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasLibur extends Model
{
    protected $table = 'kelas_liburs';
    protected $fillable = ['kelas_id', 'tanggal', 'keterangan', 'created_by'];
    protected $casts = ['tanggal' => 'date'];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
