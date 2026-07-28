<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PrecalculateReminderService
{
    const SESSION_KEY_TIME = 'last_precalculate_time';
    const SESSION_KEY_DISMISS = 'precalculate_dismissed';

    /**
     * Daftar route/menu yang dianggap KRUSIAL dan perlu precalculate.
     */
    public static array $criticalMenus = [
        'admin.cetak-rapor.pilih' => [
            'label' => 'Cetak Rapor',
            'deskripsi' => 'Cetak rapor PDF memerlukan perhitungan R2 akhir untuk semua siswa. Tanpa precalculate, proses cetak bisa lambat atau timeout.',
            'icon' => 'file-earmark-pdf',
            'warna' => 'danger',
        ],
        'admin.penilaian-rapor-internal.rekap' => [
            'label' => 'Progress Rapor',
            'deskripsi' => 'Rekap menampilkan R2 Harian + R2 Penilaian + R2 Akhir untuk semua siswa. Precalculate memastikan data tampil instan.',
            'icon' => 'clipboard-data',
            'warna' => 'warning',
        ],
        'admin.progress.jurnal' => [
            'label' => 'Progress Jurnal',
            'deskripsi' => 'Halaman progress menampilkan persentase dan R2 per siswa. Precalculate mempercepat loading monitoring.',
            'icon' => 'graph-up',
            'warna' => 'info',
        ],
        'admin.monitoring.guru' => [
            'label' => 'Monitoring Guru',
            'deskripsi' => 'Monitoring menampilkan performa guru berdasarkan data jurnal dan R2 siswa.',
            'icon' => 'person-check',
            'warna' => 'info',
        ],
    ];

    // ==================== CORE: WAKTU PRECAlCULATE ====================

    /**
     * Simpan waktu terakhir precalculate berhasil — pakai Session (primary) + Cache (backup).
     */
    public static function recordPrecalculate(): void
    {
        $now = now()->toDateTimeString();
        Session::put(self::SESSION_KEY_TIME, $now);
        try {
            Cache::put(self::SESSION_KEY_TIME, $now, now()->addDays(30));
        } catch (\Throwable $e) {
            // Cache gagal tidak masalah, session sudah tersimpan
        }
        static::resetDismissal();
    }

    /**
     * Ambil waktu terakhir precalculate. Priority: Session → Cache → null.
     */
    public static function getLastPrecalculateTime(): ?Carbon
    {
        // 1. Cek session dulu (paling reliable)
        $sessionVal = Session::get(self::SESSION_KEY_TIME);
        if ($sessionVal) {
            return Carbon::parse($sessionVal);
        }

        // 2. Fallback ke cache
        try {
            $cached = Cache::get(self::SESSION_KEY_TIME);
            if ($cached) {
                return Carbon::parse($cached);
            }
        } catch (\Throwable $e) {
            // Cache error, abaikan
        }

        return null;
    }

    // ==================== POPUP & BADGE LOGIC ====================

    /**
     * Cek apakah perlu tampilkan popup reminder (> 6 jam atau belum pernah).
     */
    public static function needsPopupReminder(): bool
    {
        $last = static::getLastPrecalculateTime();

        if (!$last) {
            return true;
        }

        return $last->diffInHours(now()) >= 6;
    }

    /**
     * Cek apakah perlu badge merah di sidebar (> 72 jam / 3 hari atau belum pernah).
     */
    public static function needsPrecalculate(): bool
    {
        $last = static::getLastPrecalculateTime();

        if (!$last) {
            return true;
        }

        return $last->diffInHours(now()) >= 72;
    }

    // ==================== SESSION DISMISS ====================

    public static function isDismissedForSession(): bool
    {
        return Session::get(self::SESSION_KEY_DISMISS, false) === true;
    }

    public static function dismissForSession(): void
    {
        Session::put(self::SESSION_KEY_DISMISS, true);
    }

    public static function resetDismissal(): void
    {
        Session::forget(self::SESSION_KEY_DISMISS);
    }

    // ==================== POPUP DATA ====================

    public static function getPopupData(): array
    {
        try {
            $currentRoute = request()->route()?->getName();
            if (!$currentRoute || !isset(static::$criticalMenus[$currentRoute])) {
                return ['show' => false];
            }

            // Sudah dismiss di session ini?
            if (static::isDismissedForSession()) {
                return ['show' => false];
            }

            // Cache masih fresh (< 6 jam)?
            if (!static::needsPopupReminder()) {
                return ['show' => false];
            }

            $menu = static::$criticalMenus[$currentRoute];
            $menu['route'] = $currentRoute;

            return [
                'show' => true,
                'menu' => $menu,
                'last_precalculate_html' => static::formatLastPrecalculate(),
                'cached_count' => static::getCachedCount(),
                'total_siswa' => static::getTotalSiswaAktif(),
                'needs_precalculate' => static::needsPrecalculate(),
                'needs_popup' => true,
            ];
        } catch (\Throwable $e) {
            return ['show' => false];
        }
    }

    // ==================== SIDEBAR BADGE ====================

    public static function getMenuBadge(string $routeName): ?array
    {
        try {
            if (!isset(static::$criticalMenus[$routeName])) {
                return null;
            }

            if (!static::needsPrecalculate()) {
                return null;
            }

            return [
                'text' => '!',
                'class' => 'bg-danger',
                'title' => 'Belum pernah atau sudah > 3 hari tidak precalculate',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ==================== HELPERS ====================

    public static function getCachedCount(): int
    {
        try {
            return DB::table('rekap_r2_akhirs')->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function getTotalSiswaAktif(): int
    {
        try {
            return DB::table('siswas')->where('status', 'aktif')->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function formatLastPrecalculate(): string
    {
        $last = static::getLastPrecalculateTime();

        if (!$last) {
            return '<span class="badge bg-danger">Belum pernah</span>';
        }

        $diffHours = $last->diffInHours(now());
        $diffDays = $last->diffInDays(now());
        $diffMinutes = $last->diffInMinutes(now()) % 60;

        if ($diffHours < 1) {
            return '<span class="badge bg-success">' . $diffMinutes . ' menit yang lalu</span>';
        } elseif ($diffHours < 24) {
            return '<span class="badge bg-success">' . $diffHours . ' jam ' . $diffMinutes . ' menit yang lalu</span>';
        } elseif ($diffHours < 72) {
            return '<span class="badge bg-warning text-dark">' . $diffDays . ' hari yang lalu</span>';
        } else {
            return '<span class="badge bg-danger">' . $diffDays . ' hari yang lalu</span>';
        }
    }
}
