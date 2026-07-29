<?php

namespace App\Console\Commands;

use App\Services\ImportSiswaService;
use Illuminate\Console\Command;

class ImportSiswaCommand extends Command
{
    protected $signature = 'import:siswa
                            {file? : Path ke file Excel siswa (default: storage/app/import-data/template-import-siswa.xlsx)}';

    protected $description = 'Import data siswa dari file Excel';

    public function handle(): int
    {
        $defaultPath = storage_path('app/import-data/template-import-siswa.xlsx');
        $path = $this->argument('file') ?: $defaultPath;

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            $this->info('Upload file Excel ke path di atas, atau berikan path saat menjalankan command:');
            $this->info('  php artisan import:siswa /path/to/file.xlsx');

            return self::FAILURE;
        }

        try {
            $result = ImportSiswaService::process($path, $this->getOutput());

            $this->info('Import siswa selesai.');
            $this->info("  Sukses: {$result['sukses']}");
            $this->info("  Gagal: {$result['gagal']}");

            if (! empty($result['errors'])) {
                $this->warn('Detail error (20 pertama):');
                foreach (array_slice($result['errors'], 0, 20) as $err) {
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
