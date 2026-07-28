<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KopSuratRapor extends Model
{
    protected $table = 'kop_surat_rapors';
    protected $fillable = [
        'semester_id', 'is_default',
        'logo_path', 'stempel_path', 'ttd_path',
        'judul', 'sub_judul', 'nama_sekolah',
        'alamat', 'telepon', 'email', 'website',
        'tahun_ajaran', 'tanggal_cetak', 'catatan_kaki',
        'kepala_sekolah', 'nip_kepala_sekolah',
    ];

    protected $casts = [
        'tanggal_cetak' => 'date',
        'is_default' => 'boolean',
    ];

    // ════════════════════════════════════════════
    // RELASI
    // ════════════════════════════════════════════

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // ════════════════════════════════════════════
    // SCOPE
    // ════════════════════════════════════════════

    /**
     * Ambil kop surat default/global.
     * Kalau ada duplikat, ambil yang terakhir di-update.
     */
    public static function default(): ?self
    {
        try {
            // Cek apakah kolom semester_id sudah ada (migration sudah dijalankan)
            if (\Schema::hasColumn('kop_surat_rapors', 'semester_id')) {
                return static::whereNull('semester_id')->orderBy('updated_at', 'desc')->first();
            }
            // Fallback: kolom semester_id belum ada
            return static::orderBy('updated_at', 'desc')->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil kop surat untuk semester tertentu.
     * Logika fallback:
     *   1. Cari kop surat dengan semester_id = X
     *   2. Kalau tidak ada → cari kop surat semester sebelumnya (terdekat)
     *   3. Kalau masih tidak ada → fallback ke kop surat default/global
     *   4. Kalau masih tidak ada → return null-safe object
     */
    public static function untukSemester(int $semesterId): self
    {
        try {
            // Cek apakah kolom semester_id sudah ada
            if (!\Schema::hasColumn('kop_surat_rapors', 'semester_id')) {
                return static::getOrCreate();
            }

            // 1. Cari kop surat untuk semester ini
            $kop = static::where('semester_id', $semesterId)->first();
            if ($kop) return $kop;

            // 2. Cari semester referensi untuk dapat tanggal
            $semester = Semester::find($semesterId);
            if ($semester) {
                // Cari kop surat semester terdekat sebelumnya
                $kopLama = static::whereHas('semester', function ($q) use ($semester) {
                    $q->where('tanggal_mulai', '<=', $semester->tanggal_mulai);
                })
                ->where('semester_id', '!=', $semesterId)
                ->orderByDesc(
                    Semester::select('tanggal_mulai')
                        ->whereColumn('semesters.id', 'kop_surat_rapors.semester_id')
                )
                ->first();

                if ($kopLama) return $kopLama;
            }

            // 3. Fallback ke default (terbaru)
            return static::getOrCreate();

        } catch (\Throwable $e) {
            return static::getOrCreate();
        }
    }

    /**
     * Ambil atau buat kop surat default.
     * Cleanup duplikat kalau ada — hanya pertahankan 1 record default.
     */
    public static function getOrCreate(): self
    {
        try {
            $hasSemesterId = \Schema::hasColumn('kop_surat_rapors', 'semester_id');

            if ($hasSemesterId) {
                // Cari SEMUA record default (semester_id = NULL)
                $defaults = static::whereNull('semester_id')->orderBy('updated_at', 'desc')->get();
            } else {
                // Kolom semester_id belum ada — ambil semua record
                $defaults = static::orderBy('updated_at', 'desc')->get();
            }

            if ($defaults->count() === 0) {
                // Belum ada → buat baru
                $data = [
                    'judul' => 'LAPORAN HASIL BELAJAR',
                    'sub_judul' => 'Program Pembelajaran Al-Quran',
                    'nama_sekolah' => 'Nama Sekolah',
                ];
                // Hanya set is_default kalau kolomnya sudah ada
                if ($hasSemesterId) {
                    $data['is_default'] = true;
                }
                return static::create($data);
            }

            // Ambil yang terbaru
            $kop = $defaults->first();

            // Hapus duplikat (sisanya) — hanya pertahankan yang terbaru
            if ($defaults->count() > 1) {
                $idsToDelete = $defaults->slice(1)->pluck('id')->toArray();
                static::whereIn('id', $idsToDelete)->delete();
            }

            return $kop;
        } catch (\Throwable $e) {
            return static::nullSafe();
        }
    }

    /**
     * Snapshot kop surat default untuk semester tertentu.
     * Dipanggil otomatis saat semester ditutup.
     * File logo, stempel, dan ttd juga diduplikat ke path unik
     * agar tidak ikut berubah kalau admin update file di semester berikutnya.
     */
    public static function snapshotSemester(int $semesterId): self
    {
        // Cek apakah kolom semester_id sudah ada
        if (!\Schema::hasColumn('kop_surat_rapors', 'semester_id')) {
            return static::getOrCreate();
        }

        // Cek apakah sudah ada snapshot untuk semester ini
        $existing = static::where('semester_id', $semesterId)->first();
        if ($existing) return $existing;

        // Ambil kop surat default (terbaru)
        $default = static::getOrCreate();

        // Duplikat data teks
        $snapshot = $default->replicate([
            'id', 'semester_id', 'is_default', 'created_at', 'updated_at'
        ]);
        $snapshot->semester_id = $semesterId;
        $snapshot->is_default = false;

        // Duplikat file logo, stempel, ttd ke path unik per semester
        $timestamp = now()->format('Ymd_His');
        $disk = Storage::disk('public');

        // Copy logo
        if ($default->logo_path && $disk->exists($default->logo_path)) {
            $ext = pathinfo($default->logo_path, PATHINFO_EXTENSION);
            $newLogoPath = 'kop-surat/semester_' . $semesterId . '_logo_' . $timestamp . '.' . $ext;
            $disk->copy($default->logo_path, $newLogoPath);
            $snapshot->logo_path = $newLogoPath;
        }

        // Copy stempel
        if ($default->stempel_path && $disk->exists($default->stempel_path)) {
            $ext = pathinfo($default->stempel_path, PATHINFO_EXTENSION);
            $newStempelPath = 'kop-surat/semester_' . $semesterId . '_stempel_' . $timestamp . '.' . $ext;
            $disk->copy($default->stempel_path, $newStempelPath);
            $snapshot->stempel_path = $newStempelPath;
        }

        // Copy ttd
        if ($default->ttd_path && $disk->exists($default->ttd_path)) {
            $ext = pathinfo($default->ttd_path, PATHINFO_EXTENSION);
            $newTtdPath = 'kop-surat/semester_' . $semesterId . '_ttd_' . $timestamp . '.' . $ext;
            $disk->copy($default->ttd_path, $newTtdPath);
            $snapshot->ttd_path = $newTtdPath;
        }

        $snapshot->save();

        return $snapshot;
    }

    /**
     * Object null-safe tanpa database.
     */
    public static function nullSafe(): self
    {
        $instance = new static;
        $instance->judul = 'LAPORAN HASIL BELAJAR';
        $instance->sub_judul = 'Program Pembelajaran Al-Quran';
        $instance->nama_sekolah = 'Nama Sekolah';
        $instance->alamat = null;
        $instance->telepon = null;
        $instance->email = null;
        $instance->website = null;
        $instance->tahun_ajaran = null;
        $instance->tanggal_cetak = null;
        $instance->catatan_kaki = null;
        $instance->kepala_sekolah = null;
        $instance->nip_kepala_sekolah = null;
        $instance->logo_path = null;
        $instance->stempel_path = null;
        $instance->ttd_path = null;
        $instance->semester_id = null;
        $instance->is_default = true;
        return $instance;
    }

    // ════════════════════════════════════════════
    // URL / FILE PATH
    // ════════════════════════════════════════════

    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : '';
    }

    public function getStempelUrlAttribute(): string
    {
        return $this->stempel_path ? asset('storage/' . $this->stempel_path) : '';
    }

    public function getTtdUrlAttribute(): string
    {
        return $this->ttd_path ? asset('storage/' . $this->ttd_path) : '';
    }

    public function getLogoFullPathAttribute(): string
    {
        if (!$this->logo_path) return '';
        // Priority: storage_path (langsung ke file asli, tidak perlu symlink)
        $storagePath = storage_path('app/public/' . $this->logo_path);
        if (file_exists($storagePath)) return $storagePath;
        // Fallback: public_path (perlu symlink storage)
        $publicPath = public_path('storage/' . $this->logo_path);
        if (file_exists($publicPath)) return $publicPath;
        return '';
    }

    public function getStempelFullPathAttribute(): string
    {
        if (!$this->stempel_path) return '';
        $storagePath = storage_path('app/public/' . $this->stempel_path);
        if (file_exists($storagePath)) return $storagePath;
        $publicPath = public_path('storage/' . $this->stempel_path);
        if (file_exists($publicPath)) return $publicPath;
        return '';
    }

    public function getTtdFullPathAttribute(): string
    {
        if (!$this->ttd_path) return '';
        $storagePath = storage_path('app/public/' . $this->ttd_path);
        if (file_exists($storagePath)) return $storagePath;
        $publicPath = public_path('storage/' . $this->ttd_path);
        if (file_exists($publicPath)) return $publicPath;
        return '';
    }

    // ════════════════════════════════════════════
    // BASE64 (DOMPDF inline)
    // ════════════════════════════════════════════

    public function getLogoBase64Attribute(): string
    {
        $path = $this->logo_full_path;
        if (!$path || !file_exists($path)) return '';
        try {
            $ext = pathinfo($this->logo_path, PATHINFO_EXTENSION);
            $mime = in_array(strtolower($ext), ['png']) ? 'png' : 'jpeg';
            return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        } catch (\Throwable $e) { return ''; }
    }

    public function getStempelBase64Attribute(): string
    {
        $path = $this->stempel_full_path;
        if (!$path || !file_exists($path)) return '';
        try {
            $ext = pathinfo($this->stempel_path, PATHINFO_EXTENSION);
            $mime = in_array(strtolower($ext), ['png']) ? 'png' : 'jpeg';
            return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        } catch (\Throwable $e) { return ''; }
    }

    public function getTtdBase64Attribute(): string
    {
        $path = $this->ttd_full_path;
        if (!$path || !file_exists($path)) return '';
        try {
            $ext = pathinfo($this->ttd_path, PATHINFO_EXTENSION);
            $mime = in_array(strtolower($ext), ['png']) ? 'png' : 'jpeg';
            return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        } catch (\Throwable $e) { return ''; }
    }

    // ════════════════════════════════════════════
    // FILE PATH UNTUK DOMPDF (lebih reliable di multi-page)
    // ════════════════════════════════════════════

    public function getLogoDompdfAttribute(): string
    {
        if (!$this->logo_path) return '';
        $path = storage_path('app/public/' . $this->logo_path);
        return file_exists($path) ? $path : '';
    }

    public function getStempelDompdfAttribute(): string
    {
        if (!$this->stempel_path) return '';
        $path = storage_path('app/public/' . $this->stempel_path);
        return file_exists($path) ? $path : '';
    }

    public function getTtdDompdfAttribute(): string
    {
        if (!$this->ttd_path) return '';
        $path = storage_path('app/public/' . $this->ttd_path);
        return file_exists($path) ? $path : '';
    }
}
