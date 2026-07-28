<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class HitungR2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $semesterId;
    public ?int $kelasId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $semesterId = null, ?int $kelasId = null)
    {
        $this->semesterId = $semesterId;
        $this->kelasId = $kelasId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $options = [];
        if ($this->semesterId) {
            $options['--semester_id'] = $this->semesterId;
        }
        if ($this->kelasId) {
            $options['--kelas_id'] = $this->kelasId;
        }

        Artisan::call('r2:precalculate', $options);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('HitungR2Job failed: ' . $exception->getMessage());
    }
}
