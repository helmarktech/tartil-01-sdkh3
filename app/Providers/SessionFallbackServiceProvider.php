<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SessionFallbackServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * Auto-detect apakah tabel sessions ada.
     * Kalau tidak ada dan SESSION_DRIVER=database,
     * otomatis fallback ke file driver agar tidak error.
     */
    public function register(): void
    {
        $driver = Config::get('session.driver', env('SESSION_DRIVER', 'file'));

        // Hanya cek kalau driver = database
        if ($driver !== 'database') {
            return;
        }

        // Cek apakah koneksi DB tersedia
        try {
            if (!DB::connection()->getPdo()) {
                $this->fallbackToFile('Koneksi database tidak tersedia');
                return;
            }
        } catch (\Throwable $e) {
            $this->fallbackToFile('Koneksi database gagal: ' . $e->getMessage());
            return;
        }

        // Cek apakah tabel sessions ada
        try {
            if (!Schema::hasTable('sessions')) {
                $this->fallbackToFile('Tabel sessions belum ada di database');
                return;
            }
        } catch (\Throwable $e) {
            $this->fallbackToFile('Gagal cek tabel sessions: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Fallback session ke file driver.
     */
    private function fallbackToFile(string $reason): void
    {
        Config::set('session.driver', 'file');
        Config::set('session.connection', null);
        Config::set('session.table', null);

        Log::info("[SessionFallback] SESSION_DRIVER diubah dari 'database' ke 'file'. Alasan: {$reason}");
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Kosong — logic di register agar jalan sebelum session start
    }
}
