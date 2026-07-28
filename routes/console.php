<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ════════════════════════════════════════════
// COMMAND: Fix Session — Auto-detect & repair
// ════════════════════════════════════════════
Artisan::command('session:fix', function () {
    $this->info('=== SESSION FIX ===');

    $driver = env('SESSION_DRIVER', 'file');
    $this->info("Session driver saat ini: {$driver}");

    // Cek koneksi DB
    try {
        DB::connection()->getPdo();
        $this->info('Koneksi database: OK');
    } catch (\Throwable $e) {
        $this->error('Koneksi database GAGAL: ' . $e->getMessage());
        $this->warn("Solusi: Ubah SESSION_DRIVER=file di .env");
        return 1;
    }

    // Cek tabel sessions
    if (Schema::hasTable('sessions')) {
        $this->info('Tabel sessions: ADA');
        $count = DB::table('sessions')->count();
        $this->info("Record sessions: {$count}");
    } else {
        $this->warn('Tabel sessions: TIDAK ADA');

        if ($this->confirm('Buat tabel sessions sekarang?', true)) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2026_07_22_000000_create_sessions_table.php',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
                $this->info('Tabel sessions berhasil dibuat!');
            } catch (\Throwable $e) {
                $this->error('Gagal buat tabel: ' . $e->getMessage());
                $this->warn("Solusi cepat: Ubah SESSION_DRIVER=file di .env");
                return 1;
            }
        } else {
            $this->warn('Tabel tidak dibuat. Solusi:');
            $this->warn('  1. Ubah SESSION_DRIVER=file di .env (paling cepat)');
            $this->warn('  2. Jalankan: php artisan session:table && php artisan migrate');
            return 1;
        }
    }

    // Rekomendasi
    if ($driver === 'database') {
        $this->info('Session driver = database, semua sudah OK.');
    } else {
        $this->info('Session driver = file. Ini aman dan tidak perlu tabel database.');
        if ($this->confirm('Ubah session driver ke database?', false)) {
            $this->comment('Ubah SESSION_DRIVER=database di file .env, lalu restart server.');
        }
    }

    return 0;
})->purpose('Perbaiki konfigurasi session (auto-detect & repair)');
