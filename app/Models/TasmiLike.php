<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasmiLike extends Model
{
    protected $fillable = ['tasmi_video_id', 'penyuka_type', 'penyuka_id'];

    public function tasmiVideo()
    {
        return $this->belongsTo(TasmiVideo::class);
    }

    public function penyuka()
    {
        return $this->morphTo();
    }

    /**
     * Resolve aktor yang sedang login dari guard siswa atau web.
     * Mengembalikan model Siswa/User, atau null jika tidak ada.
     */
    public static function aktor(): ?Model
    {
        return auth('siswa')->user() ?? auth()->user();
    }
}
