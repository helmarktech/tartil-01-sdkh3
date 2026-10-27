@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
@php
    $ikonTipe = [
        'tasmi' => ['simbol' => '&#127909;', 'warna' => 'linear-gradient(135deg, #0c8a5f, #065f43)'],
    ];
@endphp

<div style="max-width: 720px;">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Notifikasi</h1>
            <p class="page-subtitle">Informasi terbaru untuk Anda</p>
        </div>
        <button type="button" id="btn-read-all" class="btn-tartil-outline">Tandai semua dibaca</button>
    </div>

    @if($notifications->count() > 0)
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @foreach($notifications as $n)
        @php
            $ikon = $ikonTipe[$n->data['tipe'] ?? ''] ?? ['simbol' => '&#128276;', 'warna' => 'linear-gradient(135deg, #57534e, #292524)'];
            $url = $n->data['url'] ?? '#';
        @endphp
        <a href="{{ $url }}" class="gn-item {{ $n->read_at ? '' : 'gn-unread' }}"
           data-notif-id="{{ $n->id }}" data-url="{{ $url }}">
            <div class="gn-icon" style="background: {{ $ikon['warna'] }};">{!! $ikon['simbol'] !!}</div>
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 14px; font-weight: 700; color: var(--text-primary);">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                    @unless($n->read_at)
                    <span style="padding: 2px 8px; border-radius: 999px; background: #d1fae5; color: #065f43; font-size: 10px; font-weight: 700;">Baru</span>
                    @endunless
                </div>
                <div style="font-size: 13px; color: var(--text-secondary, #44403c); margin-top: 2px;">{{ $n->data['pesan'] ?? '' }}</div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </a>
        @endforeach
    </div>

    <div style="margin-top: 16px;">{{ $notifications->links() }}</div>
    @else
    <div class="card-tartil" style="padding: 48px 24px; text-align: center; color: var(--text-muted); border-style: dashed;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Belum Ada Notifikasi</h3>
        <p style="font-size: 13px;">Notifikasi akan muncul di sini.</p>
    </div>
    @endif
</div>

<style>
.gn-item {
    display: flex; align-items: flex-start; gap: 14px;
    background: var(--bg-card, #fff); border: 1px solid var(--border, #e7e5e4); border-radius: 14px;
    padding: 16px; text-decoration: none; color: inherit; transition: all 0.15s;
}
.gn-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
.gn-unread { border-left: 4px solid #0c8a5f; background: #f0fdf6; }
.gn-icon {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var token = '{{ csrf_token() }}';

    document.querySelectorAll('.gn-item').forEach(function (item) {
        item.addEventListener('click', function (e) {
            var id = item.getAttribute('data-notif-id');
            var url = item.getAttribute('data-url');
            e.preventDefault();
            fetch('{{ url('guru/notifikasi') }}/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            }).finally(function () {
                if (url && url !== '#') window.location.href = url;
                else window.location.reload();
            });
        });
    });

    var btnReadAll = document.getElementById('btn-read-all');
    if (btnReadAll) {
        btnReadAll.addEventListener('click', function () {
            fetch('{{ route('guru.notifikasi.read-all') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            }).finally(function () { window.location.reload(); });
        });
    }
});
</script>
@endsection
