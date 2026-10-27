<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasmiComment extends Model
{
    protected $fillable = ['tasmi_video_id', 'pengomentar_type', 'pengomentar_id', 'isi'];

    public function tasmiVideo()
    {
        return $this->belongsTo(TasmiVideo::class);
    }

    public function pengomentar()
    {
        return $this->morphTo();
    }

    public function getNamaPengomentarAttribute(): string
    {
        return $this->pengomentar?->nama ?? 'Pengguna';
    }

    public function getPeranPengomentarAttribute(): string
    {
        return match ($this->pengomentar_type) {
            (new Siswa)->getMorphClass() => 'Siswa',
            default => $this->pengomentar instanceof User && $this->pengomentar->isAdmin() ? 'Admin' : 'Guru',
        };
    }

    /**
     * Apakah komentar ini milik aktor yang sedang login.
     */
    public function milikAktor(?Model $actor): bool
    {
        if (! $actor) {
            return false;
        }

        return $this->pengomentar_type === $actor->getMorphClass()
            && (int) $this->pengomentar_id === (int) $actor->getKey();
    }
}
