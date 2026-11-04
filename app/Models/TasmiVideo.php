<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TasmiVideo extends Model
{
    use Auditable;

    protected $fillable = [
        'juz', 'judul', 'youtube_url', 'youtube_id',
        'deskripsi', 'tanggal_tasmi', 'diupload_oleh',
    ];

    protected $casts = [
        'tanggal_tasmi' => 'date',
    ];

    /**
     * Siswa-siswa yang tampil dalam satu video tasmi' (bisa lebih dari satu).
     */
    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'tasmi_video_siswa')->withTimestamps();
    }

    /**
     * Nama seluruh siswa dalam video, digabung koma (urut nama).
     */
    public function getNamaSiswaListAttribute(): string
    {
        return $this->siswas->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->pluck('nama')->implode(', ');
    }

    /**
     * Ringkas nama siswa untuk notifikasi/share: maks $maks nama + "& n lainnya".
     */
    public function ringkasNamaSiswa(int $maks = 3): string
    {
        $daftar = $this->siswas->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->pluck('nama');
        if ($daftar->count() <= $maks) {
            return $daftar->implode(', ');
        }

        return $daftar->take($maks)->implode(', ').' & '.($daftar->count() - $maks).' lainnya';
    }

    public function likes()
    {
        return $this->hasMany(TasmiLike::class);
    }

    public function comments()
    {
        return $this->hasMany(TasmiComment::class)->latest();
    }

    /**
     * Ekstrak YouTube video ID dari berbagai format URL:
     * watch?v=, youtu.be/, /shorts/, /embed/, atau ID mentah 11 karakter.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        $patterns = [
            '/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|embed\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}";
    }

    public function getThumbnailAttribute(): string
    {
        return "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    /**
     * Apakah aktor (User/Siswa) sudah me-like video ini.
     */
    public function isLikedOleh(?Model $actor): bool
    {
        if (! $actor) {
            return false;
        }

        return $this->likes()
            ->where('penyuka_type', $actor->getMorphClass())
            ->where('penyuka_id', $actor->getKey())
            ->exists();
    }
}
