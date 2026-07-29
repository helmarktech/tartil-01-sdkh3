<?php

namespace App\Jobs;

use App\Services\PenempatanKelasTartilService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class PenempatanKelasTartilJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $path,
        public ?int $adminId = null,
        public bool $overwrite = false
    ) {}

    public function handle(): void
    {
        $output = new BufferedOutput;

        try {
            $result = PenempatanKelasTartilService::process($this->path, $output, $this->overwrite);

            Log::info('Penempatan kelas tartil via queue selesai', [
                'sukses' => $result['sukses'],
                'tidak_ditemukan' => $result['tidak_ditemukan'],
                'skip' => $result['skip'],
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Penempatan kelas tartil via queue gagal', [
                'error' => $e->getMessage(),
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
            throw $e;
        }
    }
}
