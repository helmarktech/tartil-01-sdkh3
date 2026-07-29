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
        public string $path,
        public ?int $adminId = null
    ) {}

    public function handle(): void
    {
        $output = new BufferedOutput;

        try {
            $result = ImportSiswaService::process($this->path, $output);

            Log::info('Import siswa via queue selesai', [
                'sukses' => $result['sukses'],
                'gagal' => $result['gagal'],
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Import siswa via queue gagal', [
                'error' => $e->getMessage(),
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
            throw $e;
        }
    }
}
