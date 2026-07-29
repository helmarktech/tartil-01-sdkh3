<?php

namespace Database\Seeders;

use App\Services\PenempatanKelasTartilService;
use Illuminate\Database\Seeder;

/**
 * Seeder penempatan kelas tartil/tahfidz dari file import.xlsx.
 *
 * File Excel diharapkan memiliki struktur:
 *   - Baris 1-4: header/judul (diabaikan)
 *   - Kolom A: nama program (contoh: BILQOLAM 1) — muncul di baris pembuka setiap kelompok
 *   - Kolom B: nomor urut
 *   - Kolom D: No Induk (NIS)
 *   - Kolom E: Nama siswa
 *   - Kolom F: Kelas Reguler (hanya informasi, tidak dipakai untuk penempatan)
 *
 * Mapping program ke kelas tartil dapat disesuaikan di App\Services\PenempatanKelasTartilService.
 */
class PenempatanKelasTartilSeeder extends Seeder
{
    /**
     * Path default relatif ke file Excel penempatan.
     * Bisa di-override dengan menjalankan command:
     *   php artisan import:penempatan /path/to/file.xlsx
     */
    private string $defaultFilePath = 'storage/app/import-data/import.xlsx';

    public function run(): void
    {
        $path = base_path($this->defaultFilePath);

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            $this->command->info('Gunakan command khusus agar bisa mengarahkan path file:');
            $this->command->info('  php artisan import:penempatan /path/to/file.xlsx');

            return;
        }

        try {
            $result = PenempatanKelasTartilService::process($path, $this->command->getOutput());

            $this->command->info('Penempatan selesai.');
            $this->command->info("  Sukses: {$result['sukses']}");
            $this->command->info("  NIS tidak ditemukan: {$result['tidak_ditemukan']}");
            $this->command->info("  Dilewati (sudah punya kelas / program tidak dikenali): {$result['skip']}");

            if (! empty($result['detailGagal'])) {
                $this->command->warn('Detail NIS tidak ditemukan (20 pertama):');
                foreach (array_slice($result['detailGagal'], 0, 20) as $err) {
                    $this->command->warn('  - '.$err);
                }
            }
        } catch (\Throwable $e) {
            $this->command->error('Gagal: '.$e->getMessage());
            throw $e;
        }
    }
}
