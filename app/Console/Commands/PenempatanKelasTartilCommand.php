<?php

namespace App\Console\Commands;

use App\Services\PenempatanKelasTartilService;
use Illuminate\Console\Command;

class PenempatanKelasTartilCommand extends Command
{
    protected $signature = 'import:penempatan
                            {file? : Path ke file Excel penempatan kelas (default: storage/app/import-data/import.xlsx)}
                            {--overwrite : Timpa penempatan yang sudah ada}
                            {--force : Jalankan meskipun environment production}';

    protected $description = 'Import penempatan kelas tartil/tahfidz dari file Excel';

    public function handle(): int
    {
        $defaultPath = storage_path('app/import-data/import.xlsx');
        $path = $this->argument('file') ?: $defaultPath;

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            $this->info('Upload file Excel ke path di atas, atau berikan path saat menjalankan command:');
            $this->info('  php artisan import:penempatan /path/to/file.xlsx');

            return self::FAILURE;
        }

        try {
            $result = PenempatanKelasTartilService::process($path, $this->getOutput(), $this->option('overwrite'));

            $this->info('Penempatan selesai.');
            $this->info("  Sukses: {$result['sukses']}");
            $this->info("  NIS tidak ditemukan: {$result['tidak_ditemukan']}");
            $this->info("  Dilewati (sudah punya kelas / program tidak dikenali): {$result['skip']}");

            if (! empty($result['detailGagal'])) {
                $this->warn('Detail NIS tidak ditemukan (20 pertama):');
                foreach (array_slice($result['detailGagal'], 0, 20) as $err) {
                    $this->warn('  - '.$err);
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
