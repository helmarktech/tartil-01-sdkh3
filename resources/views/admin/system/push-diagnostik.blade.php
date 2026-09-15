@extends('layouts.admin')

@section('title', 'Diagnostik Push Notification')

@section('content')
<div class="su-wrap">

    <div class="su-head">
        <h1 class="su-title">Diagnostik Push Notification</h1>
        <p class="su-sub">Status konfigurasi VAPID, subscription perangkat siswa, dan notifikasi terakhir. Halaman ini read-only.</p>
    </div>

    {{-- Status VAPID --}}
    <div class="pd-card">
        <h2 class="pd-card-title">Konfigurasi VAPID (server)</h2>
        <table class="pd-table">
            <tr>
                <td>VAPID Public Key</td>
                <td>{!! $vapid['public_key_terisi'] ? '<span class="pd-ok">Terisi</span>' : '<span class="pd-err">KOSONG — push tidak akan berfungsi</span>' !!}</td>
            </tr>
            <tr>
                <td>VAPID Private Key</td>
                <td>{!! $vapid['private_key_terisi'] ? '<span class="pd-ok">Terisi</span>' : '<span class="pd-err">KOSONG — push tidak akan berfungsi</span>' !!}</td>
            </tr>
            <tr>
                <td>VAPID Subject</td>
                <td>{{ $vapid['subject'] ?: '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Subscription --}}
    <div class="pd-card">
        <h2 class="pd-card-title">Push Subscription Terdaftar ({{ $subscriptions->count() }})</h2>
        @if($subscriptions->isEmpty())
            <p class="pd-empty">Belum ada perangkat yang terdaftar. Minta siswa membuka aplikasi/situs, login, dan muat satu halaman — subscription akan terkirim otomatis.</p>
        @else
            <table class="pd-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Siswa</th>
                        <th>Endpoint</th>
                        <th>Terdaftar</th>
                        <th>Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $s)
                    <tr>
                        <td>{{ $s['id'] }}</td>
                        <td>{{ $s['tipe'] }} #{{ $s['siswa_id'] }}</td>
                        <td class="pd-mono">{{ $s['endpoint'] }}</td>
                        <td>{{ $s['created_at'] }}</td>
                        <td>{{ $s['updated_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Notifikasi terakhir --}}
    <div class="pd-card">
        <h2 class="pd-card-title">10 Notifikasi Terakhir (channel database)</h2>
        @if($notifikasiTerakhir->isEmpty())
            <p class="pd-empty">Belum ada notifikasi yang pernah dikirim.</p>
        @else
            <table class="pd-table">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Tipe</th>
                        <th>Judul</th>
                        <th>Dikirim</th>
                        <th>Dibaca</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifikasiTerakhir as $n)
                    <tr>
                        <td>#{{ $n['siswa_id'] }}</td>
                        <td>{{ $n['tipe'] }}</td>
                        <td>{{ $n['judul'] }}</td>
                        <td>{{ $n['created_at'] }}</td>
                        <td>{{ $n['read_at'] ?: 'belum' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>

<style>
    .su-wrap { max-width: 960px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .su-head { margin-bottom: 28px; }
    .su-title { font-size: 24px; font-weight: 700; color: #171717; margin: 0; letter-spacing: -0.5px; }
    .su-sub { font-size: 13px; color: #737373; margin: 4px 0 0; }
    .pd-card { background: #fff; border: 1px solid #e5e1d8; border-radius: 12px; padding: 18px 20px; margin-bottom: 16px; overflow-x: auto; }
    .pd-card-title { font-size: 15px; font-weight: 700; margin: 0 0 12px; color: #1c2b23; }
    .pd-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .pd-table td, .pd-table th { text-align: left; padding: 7px 10px; border-bottom: 1px solid #f0ede5; vertical-align: top; }
    .pd-table th { color: #6b7280; font-weight: 600; font-size: 12px; }
    .pd-table tr:last-child td { border-bottom: none; }
    .pd-ok { color: #0c8a5f; font-weight: 700; }
    .pd-err { color: #c0392b; font-weight: 700; }
    .pd-mono { font-family: monospace; font-size: 11px; word-break: break-all; }
    .pd-empty { color: #6b7280; font-size: 13px; margin: 0; }
</style>
@endsection
