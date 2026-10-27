<?php

namespace App\Http\Controllers;

class GuruNotifikasiController extends Controller
{
    private function user()
    {
        return auth()->user();
    }

    // ==================== GURU: DAFTAR NOTIFIKASI ====================
    public function index()
    {
        $notifications = $this->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guru.notifikasi.index', compact('notifications'));
    }

    // ==================== GURU: JUMLAH BELUM DIBACA (untuk badge nav) ====================
    public function unreadCount()
    {
        return response()->json([
            'count' => $this->user()->unreadNotifications()->count(),
        ]);
    }

    // ==================== GURU: TANDAI SATU NOTIFIKASI DIBACA ====================
    public function markRead($id)
    {
        $this->user()->unreadNotifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ==================== GURU: TANDAI SEMUA DIBACA ====================
    public function readAll()
    {
        $this->user()->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
