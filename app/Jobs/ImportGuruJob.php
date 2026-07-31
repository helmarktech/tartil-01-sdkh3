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

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public string $fileContent,
        public string $jenis,
        public ?int $adminId = null,
        public string $originalName = ''
    ) {
        if (! in_array($jenis, ['reguler', 'tartil'])) {
            throw new \InvalidArgumentException('Jenis guru harus reguler atau tartil.');
        }
    }

    public function handle(): void
    {
        $output = new BufferedOutput;
        $tempPath = tempnam(sys_get_temp_dir(), 'import_guru_').'.xlsx';

        try {
            $binaryContent = base64_decode($this->fileContent, true);

            if ($binaryContent === false) {
                throw new \InvalidArgumentException('Konten file tidak valid (base64 decode gagal).');
            }

            file_put_contents($tempPath, $binaryContent);

            $result = ImportGuruService::process($tempPath, $this->jenis, $output);

            Log::info('Import guru via queue selesai', [
                'jenis' => $this->jenis,
                'sukses' => $result['sukses'],
                'gagal' => $result['gagal'],
                'admin_id' => $this->adminId,
                'file' => $this->originalName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Import guru via queue gagal', [
                'error' => $e->getMessage(),
                'jenis' => $this->jenis,
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
