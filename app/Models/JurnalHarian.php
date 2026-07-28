<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class JurnalHarian extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'jurnal_harians';
    protected $fillable = [
        'semester_id', 'kelas_id', 'guru_id', 'siswa_id', 'tanggal',
        'penilaian', 'surat_id', 'ayat_mulai', 'ayat_selesai', 'halaman',
        'materi', 'topik', 'rencana', 'catatan',
    ];

    /**
     * PENILAIAN: B = Baik, C = Cukup, K = Kurang
     * Sistem hanya mengenal B, C, K. Tidak ada izin/sakit/alpa.
     */
    protected $casts = [
        'tanggal' => 'date',
        'ayat_mulai' => 'integer',
        'ayat_selesai' => 'integer',
    ];

    // ================= RELATIONS =================
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(GuruTartil::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    // ================= HELPERS =================
    public static function penilaianLabel(?string $p): string
    {
        return match($p) {
            'B' => 'Baik',
            'C' => 'Cukup',
            'K' => 'Kurang',
            default => '-',
        };
    }

    public static function penilaianBadge(?string $p): string
    {
        return match($p) {
            'B' => '<span class="sd-badge sd-badge-b">B</span>',
            'C' => '<span class="sd-badge sd-badge-c">C</span>',
            'K' => '<span class="sd-badge sd-badge-k">K</span>',
            default => '<span class="sd-badge sd-badge-muted">-</span>',
        };
    }

    // ================= SCOPE =================
    public function scopeDinilai($q)
    {
        return $q->whereNotNull('penilaian');
    }

    public function scopeByKelasTanggal($q, int $kelasId, string $tanggal)
    {
        return $q->where('kelas_id', $kelasId)->where('tanggal', $tanggal);
    }

    // ================= CACHE INVALIDATION =================
    protected static function boot()
    {
        parent::boot();
        static::saved(function ($jurnal) {
            Cache::forget("rekap_kelas:{$jurnal->kelas_id}:" . str_replace('-', '', $jurnal->tanggal->format('Y-m')));
        });
        static::deleted(function ($jurnal) {
            Cache::forget("rekap_kelas:{$jurnal->kelas_id}:" . str_replace('-', '', $jurnal->tanggal->format('Y-m')));
        });
    }
}
