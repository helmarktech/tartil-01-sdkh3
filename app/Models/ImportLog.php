<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id', 'jenis', 'file_name', 'status', 'sukses', 'gagal', 'errors', 'processed_at',
    ];

    protected $casts = [
        'sukses' => 'integer',
        'gagal' => 'integer',
        'errors' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markSuccess(int $sukses, int $gagal, array $errors = []): void
    {
        $this->update([
            'status' => 'success',
            'sukses' => $sukses,
            'gagal' => $gagal,
            'errors' => $errors,
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'errors' => [$errorMessage],
            'processed_at' => now(),
        ]);
    }
}
