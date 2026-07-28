<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MunaqosyahApproval extends Model
{
    protected $table = 'munaqosyah_approvals';
    protected $fillable = [
        'pendaftaran_id', 'status', 'approved_by', 'approved_at', 'catatan'
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(MunaqosyahPendaftaran::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
