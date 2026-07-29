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
        // Gunakan view pagination custom Tartil (sesuai CSS .pagination-tartil)
        Paginator::defaultView('pagination::tartil');
        Paginator::defaultSimpleView('pagination::tartil');

        // ════════════════════════════════════════════
        // GATE: Laravel Pulse — hanya email tertentu
        // ════════════════════════════════════════════
        // Didefinisikan di app->booted() agar menimpa definisi default
        // Laravel Pulse yang membatasi akses hanya di environment local.
        // ════════════════════════════════════════════
        $this->app->booted(function () {
            Gate::define('viewPulse', function ($user = null) {
                // Hanya bisa diakses saat deploy (production)
                if (app()->environment('local')) {
                    return true;
                }

                $allowedEmail = config('pulse.authorized_email', 'admin@tartil.id');

                // Token-based access (bisa digunakan meski user login dengan email lain)
                $token = request('pulse_token');
                if ($token) {
                    $expected = hash('sha256', $allowedEmail.config('app.key'));

                    return hash_equals($expected, $token);
                }

                // User-based access
                if ($user && method_exists($user, 'email')) {
                    return $user->email === $allowedEmail;
                }

                return false;
            });
        });
    }
}
