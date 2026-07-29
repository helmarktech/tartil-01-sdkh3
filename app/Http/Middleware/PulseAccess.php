<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PulseAccess
{
    /**
     * Handle an incoming request for Laravel Pulse.
     *
     * Akses diizinkan jika:
     * - Environment local.
     * - Membawa token Pulse yang valid.
     * - User login dengan email yang diizinkan (default admin@tartil.id).
     * - User login dengan role admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        $allowedEmail = config('pulse.authorized_email', 'admin@tartil.id');

        // Token-based access: SHA256(email + APP_KEY)
        $token = $request->get('pulse_token');
        if ($token) {
            $expected = hash('sha256', $allowedEmail.config('app.key'));

            if (hash_equals($expected, $token)) {
                return $next($request);
            }
        }

        // User-based access
        $user = auth()->user();
        $userEmail = $user ? ($user->email ?? 'null') : 'not-authenticated';
        $userRole = $user ? ($user->role ?? 'null') : 'not-authenticated';

        if ($user && $user->email === $allowedEmail) {
            return $next($request);
        }

        // Role-based admin access
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Akses Laravel Pulse ditolak. User: '.$userEmail.' | Role: '.$userRole.' | Token provided: '.($token ? 'yes' : 'no'));
    }
}
