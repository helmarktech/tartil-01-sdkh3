<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AutoSetupService;
use App\Services\PrecalculateReminderService;
use App\Jobs\HitungR2Job;

class SystemSetupController extends Controller
{
    /**
     * Halaman setup sistem utama.
     */
    public function index()
    {
        $status = AutoSetupService::getSystemStatus();
        $isFullySetup = $status['fully_setup'] ?? false;

        return view('admin.system.setup', compact('status', 'isFullySetup'));
    }

    /**
     * Jalankan full setup otomatis.
     */
    public function runSetup()
    {
        try {
            $results = AutoSetupService::runAllSetup();
            $allSuccess = collect($results)->every(fn($r) => $r['status'] === 'success');

            $message = $allSuccess
                ? 'Setup sistem berhasil diselesaikan!'
                : 'Setup selesai dengan beberapa catatan.';

            return redirect()->route('admin.system.setup')
                ->with($allSuccess ? 'success' : 'warning', $message)
                ->with('setup_results', $results);
        } catch (\Throwable $e) {
            return redirect()->route('admin.system.setup')
                ->with('error', 'Setup gagal: ' . $e->getMessage());
        }
    }

    /**
     * Run R2 precalculate (sync atau async).
     * Catat waktu SEBELUM proses agar popup tidak terus muncul kalau ada error.
     */
    public function runR2Precalculate(Request $request)
    {
        $semesterId = $request->input('semester_id');
        $kelasId = $request->input('kelas_id');

        // Catat waktu terlebih dahulu (optimistic) — popup tidak akan ganggu lagi
        PrecalculateReminderService::recordPrecalculate();
        PrecalculateReminderService::resetDismissal();

        $useQueue = $request->boolean('async', true);

        if ($useQueue) {
            try {
                HitungR2Job::dispatch($semesterId, $kelasId);

                $redirectUrl = $request->headers->get('referer', route('admin.system.setup'));
                return redirect($redirectUrl)
                    ->with('success', 'Perhitungan R2 sedang diproses. Data akan tersedia dalam 1-2 menit.');
            } catch (\Illuminate\Database\QueryException $e) {
                // Tabel jobs belum ada → fallback ke sync
                if (str_contains($e->getMessage(), "jobs' doesn't exist")) {
                    return $this->runPrecalculateSync($semesterId, $kelasId, $request);
                }
                return $this->handlePrecalculateError($e, $request);
            } catch (\Throwable $e) {
                return $this->handlePrecalculateError($e, $request);
            }
        }

        return $this->runPrecalculateSync($semesterId, $kelasId, $request);
    }

    /**
     * Jalankan precalculate secara synchronous (langsung).
     */
    private function runPrecalculateSync(?int $semesterId, ?int $kelasId, Request $request)
    {
        $options = [];
        if ($semesterId) $options['--semester_id'] = $semesterId;
        if ($kelasId) $options['--kelas_id'] = $kelasId;

        try {
            $result = AutoSetupService::runArtisan('r2:precalculate', $options);

            $redirectUrl = $request->headers->get('referer', route('admin.system.setup'));
            return redirect($redirectUrl)
                ->with($result['status'] === 'success' ? 'success' : 'error', $result['output']);
        } catch (\Throwable $e) {
            return $this->handlePrecalculateError($e, $request);
        }
    }

    /**
     * Handle error precalculate — waktu sudah tercatat jadi popup tidak ganggu.
     */
    private function handlePrecalculateError(\Throwable $e, Request $request)
    {
        $redirectUrl = $request->headers->get('referer', route('admin.system.setup'));
        return redirect($redirectUrl)
            ->with('warning', 'Precalculate dicatat, tapi terjadi error saat proses: ' . $e->getMessage() . '. Popup tidak akan ganggu lagi. Cek halaman Setup Sistem untuk detail.');
    }

    /**
     * Reset R2 cache (truncate dan hitung ulang).
     */
    public function resetR2Cache()
    {
        $result = AutoSetupService::resetR2Cache();
        
        return redirect()->route('admin.system.setup')
            ->with(str_contains($result, 'Error') ? 'error' : 'success', $result);
    }

    /**
     * Generic artisan command runner dari web.
     */
    public function runArtisan(Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:100',
        ]);

        $command = $request->input('command');
        $allowedCommands = [
            'cache:clear',
            'config:clear',
            'config:cache',
            'route:clear',
            'route:cache',
            'view:clear',
            'view:cache',
            'optimize',
            'optimize:clear',
            'migrate',
            'migrate:status',
            'db:seed',
            'queue:work',
            'queue:restart',
            'schedule:run',
            'r2:precalculate',
        ];

        // Security: hanya allow command tertentu
        $baseCommand = explode(' ', $command)[0];
        if (!in_array($baseCommand, $allowedCommands)) {
            return redirect()->route('admin.system.setup')
                ->with('error', "Command '{$baseCommand}' tidak diizinkan.");
        }

        $options = ['--force' => true];
        if ($baseCommand === 'migrate' || $baseCommand === 'db:seed') {
            // Sudah ada --force
        }

        $result = AutoSetupService::runArtisan($command, $options);

        return redirect()->route('admin.system.setup')
            ->with($result['status'] === 'success' ? 'success' : 'error', $result['output']);
    }

    /**
     * Cek status sistem (AJAX).
     */
    public function checkStatus()
    {
        $status = AutoSetupService::getSystemStatus();
        return response()->json($status);
    }

    /**
     * Clear semua cache.
     */
    public function clearAllCache()
    {
        $results = [];
        
        $commands = [
            'cache:clear' => 'Application cache',
            'config:clear' => 'Config cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
        ];

        foreach ($commands as $cmd => $label) {
            $result = AutoSetupService::runArtisan($cmd);
            $results[] = ['label' => $label, 'status' => $result['status']];
        }

        $allSuccess = collect($results)->every(fn($r) => $r['status'] === 'success');

        return redirect()->route('admin.system.setup')
            ->with($allSuccess ? 'success' : 'warning', 'Semua cache berhasil di-clear.');
    }

    /**
     * Optimasi produksi (cache config, route, view).
     */
    public function optimize()
    {
        $commands = ['config:cache', 'route:cache', 'view:cache'];
        $results = [];

        foreach ($commands as $cmd) {
            $result = AutoSetupService::runArtisan($cmd);
            $results[] = $result;
        }

        return redirect()->route('admin.system.setup')
            ->with('success', 'Optimasi produksi selesai. Config, route, dan view sudah di-cache.');
    }

    /**
     * Dismiss precalculate reminder untuk session ini.
     */
    public function dismissPrecalculate(Request $request)
    {
        PrecalculateReminderService::dismissForSession();

        $redirectUrl = $request->headers->get('referer', route('admin.dashboard'));
        return redirect($redirectUrl);
    }

    /**
     * Diagnostik push notification siswa (read-only).
     * Menampilkan status VAPID, subscription terdaftar, dan notifikasi terakhir.
     */
    public function diagnostikPush()
    {
        $vapid = [
            'public_key_terisi' => ! empty(config('webpush.vapid.public_key') ?? env('VAPID_PUBLIC_KEY')),
            'private_key_terisi' => ! empty(config('webpush.vapid.private_key') ?? env('VAPID_PRIVATE_KEY')),
            'subject' => config('webpush.vapid.subject') ?? env('VAPID_SUBJECT'),
        ];

        $subscriptions = \Illuminate\Support\Facades\DB::table('push_subscriptions')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'subscribable_type', 'subscribable_id', 'endpoint', 'created_at', 'updated_at'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'siswa_id' => $s->subscribable_id,
                'tipe' => class_basename($s->subscribable_type),
                'endpoint' => \Illuminate\Support\Str::limit($s->endpoint, 70),
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ]);

        $notifikasiTerakhir = \Illuminate\Support\Facades\DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'notifiable_id', 'data', 'read_at', 'created_at'])
            ->map(fn ($n) => [
                'siswa_id' => $n->notifiable_id,
                'judul' => json_decode($n->data, true)['judul'] ?? '-',
                'tipe' => json_decode($n->data, true)['tipe'] ?? '-',
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]);

        return view('admin.system.push-diagnostik', compact('vapid', 'subscriptions', 'notifikasiTerakhir'));
    }
}
