<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk user admin/guru (tabel users).
 * Hanya channel database — user belum punya push subscription
 * (push Web saat ini khusus siswa).
 */
class GuruNotifikasi extends Notification
{
    public function __construct(
        public string $tipe,
        public string $judul,
        public string $pesan,
        public string $url,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'tipe' => $this->tipe,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'url' => $this->url,
        ];
    }
}
