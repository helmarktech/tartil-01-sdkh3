<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SiswaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('siswa')->check()) {
            return redirect()->route('siswa.login')->with('error', 'Silakan login sebagai siswa.');
        }
        return $next($request);
    }
}
