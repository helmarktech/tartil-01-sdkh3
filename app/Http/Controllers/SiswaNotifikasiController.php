<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiswaNotifikasiController extends Controller
{
    private function siswa()
    {
        return auth('siswa')->user();
    }

    // ==================== SISWA: DAFTAR NOTIFIKASI ====================
    public function index()
    {
        $notifications = $this->siswa()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('siswa.notifikasi.index', compact('notifications'));
    }

    // ==================== SISWA: JUMLAH & NOTIFIKASI TERBARU (unread) ====================
    public function unreadCount()
    {
        $siswa = $this->siswa();
        $unread = $siswa->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'count' => $siswa->unreadNotifications()->count(),
            'latest' => $unread->map(fn ($n) => [
                'id' => $n->id,
                'tipe' => $n->data['tipe'] ?? null,
                'judul' => $n->data['judul'] ?? null,
                'pesan' => $n->data['pesan'] ?? null,
                'url' => $n->data['url'] ?? null,
                'waktu' => $n->created_at->diffForHumans(),
            ])->values(),
        ]);
    }

    // ==================== SISWA: TANDAI SATU NOTIFIKASI DIBACA ====================
    public function markRead($id)
    {
        $this->siswa()->unreadNotifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ==================== SISWA: TANDAI SEMUA DIBACA ====================
    public function readAll()
    {
        $this->siswa()->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ==================== SISWA: SIMPAN PUSH SUBSCRIPTION ====================
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:1000',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $this->siswa()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return response()->json(['ok' => true]);
    }

    // ==================== SISWA: HAPUS PUSH SUBSCRIPTION ====================
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:1000',
        ]);

        $this->siswa()->deletePushSubscription($validated['endpoint']);

        return response()->json(['ok' => true]);
    }
}
