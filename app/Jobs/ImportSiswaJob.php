<?php

namespace App\Jobs;

use App\Services\ImportSiswaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class ImportSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $fileContent,
        public ?int $adminId = null,
        public string $originalName = ''
    ) {}

    public function handle(): void
    {
        $output = new BufferedOutput;
        $tempPath = tempnam(sys_get_temp_dir(), 'import_siswa_').'.xlsx';

        try {
            $binaryContent = base64_decode($this->fileContent, true);

            if ($binaryContent === false) {
                throw new \InvalidArgumentException('Konten file tidak valid (base64 decode gagal).');
            }

            file_put_contents($tempPath, $binaryContent);

            $result = ImportSiswaService::process($tempPath, $output);

            Log::info('Import siswa via queue selesai', [
                'sukses' => $result['sukses'],
                'gagal' => $result['gagal'],
                'admin_id' => $this->adminId,
                'file' => $this->originalName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Import siswa via queue gagal', [
                'error' => $e->getMessage(),
                'admin_id' => $this->adminId,
                'file' => $this->originalName,
            ]);
            throw $e;
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
