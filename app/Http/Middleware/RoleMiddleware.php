<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($role === 'admin' && !$user->isAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator.');
        }

        if ($role === 'guru' && !($user->isAdmin() || $user->isGuru())) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Guru.');
        }

        return $next($request);
    }
}
