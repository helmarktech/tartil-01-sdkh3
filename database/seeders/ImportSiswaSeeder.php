<?php

namespace Database\Seeders;

use App\Services\ImportSiswaService;
use Illuminate\Database\Seeder;

/**
 * Seeder import data siswa dari file template-import-siswa (3).xlsx.
 *
 * Format Excel yang diharapkan:
 *   NIS | NAMA | JENIS_KELAMIN | NO_HP | KELAS_NAMA | KELAS_JENJANG | KELAS_TINGKAT | TANGGAL_MASUK
 *
 * Kelas reguler yang belum ada akan dibuat otomatis.
 * Jika semester aktif ada, siswa akan langsung terdaftar di semester aktif.
 */
class ImportSiswaSeeder extends Seeder
{
    /**
     * Path default relatif ke file Excel siswa.
     * Bisa di-override dengan meletakkan file di storage/app/import-data/template-import-siswa.xlsx
     * atau menjalankan command: php artisan import:siswa /path/to/file.xlsx
     */
    private string $defaultFilePath = 'storage/app/import-data/template-import-siswa.xlsx';

    public function run(): void
    {
        $path = base_path($this->defaultFilePath);

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            $this->command->info('Gunakan command khusus agar bisa mengarahkan path file:');
            $this->command->info('  php artisan import:siswa /path/to/file.xlsx');

            return;
        }

        try {
            $result = ImportSiswaService::process($path, $this->command->getOutput());

            $this->command->info('Import siswa selesai.');
            $this->command->info("  Sukses: {$result['sukses']}");
            $this->command->info("  Gagal: {$result['gagal']}");

            if (! empty($result['errors'])) {
                $this->command->warn('Detail error (20 pertama):');
                foreach (array_slice($result['errors'], 0, 20) as $err) {
                    $this->command->warn('  - '.$err);
                }
            }
        } catch (\Throwable $e) {
            $this->command->error('Gagal: '.$e->getMessage());
            throw $e;
        }
    }
}
