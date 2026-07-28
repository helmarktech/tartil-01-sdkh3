<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gunakan Tailwind untuk komponen pagination Laravel
        Paginator::useTailwind();

        // ════════════════════════════════════════════
        // GATE: Laravel Pulse — hanya email tertentu
        // ════════════════════════════════════════════
        Gate::define('viewPulse', function ($user = null) {
            // Hanya bisa diakses saat deploy (production)
            if (app()->environment('local')) {
                return true;
            }

            // Di production: hanya admin@tartil.id
            $allowedEmail = config('pulse.authorized_email', 'admin@tartil.id');

            if ($user && method_exists($user, 'email')) {
                return $user->email === $allowedEmail;
            }

            // Fallback: cek dari request query token (untuk akses tanpa login)
            $token = request('pulse_token');
            if ($token) {
                return hash_equals(
                    hash('sha256', $allowedEmail.config('app.key')),
                    $token
                );
            }

            return false;
        });
    }
}
