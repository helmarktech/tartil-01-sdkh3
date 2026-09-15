<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SiswaNotifikasi extends Notification
{
    public function __construct(
        public string $tipe,
        public string $judul,
        public string $pesan,
        public string $url,
        public string $icon = '/icons/icon-192.png',
    ) {}

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * URL tujuan per tipe notifikasi (fallback).
     * URL yang tersimpan di data notifikasi lebih diutamakan; method ini
     * dipakai untuk notifikasi lama yang tidak menyimpan url.
     */
    public static function urlDefault(?string $tipe): ?string
    {
        return match ($tipe) {
            'jurnal' => '/siswa/dashboard#jurnal-terbaru',
            'hafalan' => '/siswa/hafalan',
            'pendampingan' => '/siswa/pendampingan-ortu',
            default => null,
        };
    }

    public function toArray($notifiable): array
    {
        return [
            'tipe' => $this->tipe,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }

    /**
     * Payload push ke browser siswa.
     * Package webpush v12 mewajibkan return WebPushMessage
     * (WebPushChannel memanggil toArray() & getOptions() di hasilnya).
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->judul)
            ->body($this->pesan)
            ->icon($this->icon)
            ->badge($this->icon)
            ->data(['url' => $this->url]);
    }
}
