<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterAuditLog extends Model
{
    protected $table = 'semester_audit_logs';
    protected $fillable = [
        'semester_id', 'tipe', 'aksi',
        'jumlah_record', 'detail', 'user_id', 'ip_address', 'locked_at',
    ];

    protected $casts = [
        'detail' => 'array',
        'locked_at' => 'datetime',
    ];

    public function semester() { return $this->belongsTo(Semester::class); }
    public function user() { return $this->belongsTo(User::class); }

    /**
     * Log satu entri audit.
     */
    public static function log(Semester $semester, string $tipe, string $aksi, int $jumlahRecord = 0, array $detail = [], ?int $userId = null): self
    {
        return static::create([
            'semester_id' => $semester->id,
            'tipe' => $tipe,
            'aksi' => $aksi,
            'jumlah_record' => $jumlahRecord,
            'detail' => $detail,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'locked_at' => now(),
        ]);
    }
}
