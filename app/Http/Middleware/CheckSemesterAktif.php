<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Semester;

class CheckSemesterAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $semester = Semester::aktif()->first();

        if (!$semester) {
            return redirect()->back()->with('error', 'Tidak ada semester aktif. Silakan buat semester terlebih dahulu.');
        }

        if ($semester->status == 'ditutup') {
            return redirect()->back()->with('error', 'Semester ' . $semester->nama . ' sudah ditutup. Data bersifat permanen dan tidak dapat diubah.');
        }

        $now = now();
        if ($now < $semester->tanggal_mulai || $now > $semester->tanggal_selesai) {
            return redirect()->back()->with('error', 'Semester ' . $semester->nama . ' tidak dalam periode aktif (' . $semester->tanggal_mulai->format('d/m/Y') . ' - ' . $semester->tanggal_selesai->format('d/m/Y') . ').');
        }

        // Simpan semester ke request untuk digunakan controller
        $request->merge(['semester_aktif' => $semester]);

        return $next($request);
    }
}
