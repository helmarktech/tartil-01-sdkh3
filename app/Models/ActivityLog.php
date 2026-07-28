<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'loggable_type', 'loggable_id', 'action', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent',
        'user_id', 'user_type',
    ];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function loggable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }

    public static function log($loggable, string $action, string $description = null, array $old = null, array $new = null): self
    {
        return static::create([
            'loggable_type' => get_class($loggable),
            'loggable_id' => $loggable->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'user_type' => auth()->guard()->name ?? null,
        ]);
    }

    public static function logCustom(string $action, string $description, string $type = null, int $id = null): self
    {
        return static::create([
            'loggable_type' => $type ?? 'App\Models\System',
            'loggable_id' => $id ?? 0,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'user_type' => auth()->guard()->name ?? null,
        ]);
    }
}
