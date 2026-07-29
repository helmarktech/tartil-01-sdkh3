<?php

namespace App\Jobs;

use App\Services\ImportGuruService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class ImportGuruJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $path,
        public string $jenis,
        public ?int $adminId = null
    ) {
        if (! in_array($jenis, ['reguler', 'tartil'])) {
            throw new \InvalidArgumentException('Jenis guru harus reguler atau tartil.');
        }
    }

    public function handle(): void
    {
        $output = new BufferedOutput;

        try {
            $result = ImportGuruService::process($this->path, $this->jenis, $output);

            Log::info('Import guru via queue selesai', [
                'jenis' => $this->jenis,
                'sukses' => $result['sukses'],
                'gagal' => $result['gagal'],
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Import guru via queue gagal', [
                'error' => $e->getMessage(),
                'jenis' => $this->jenis,
                'admin_id' => $this->adminId,
                'file' => $this->path,
            ]);
            throw $e;
        }
    }
}
