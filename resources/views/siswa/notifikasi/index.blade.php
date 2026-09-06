@extends('layouts.siswa')

@section('title', 'Notifikasi')

@section('content')
@php
    // Ikon & warna per tipe notifikasi
    $ikonTipe = [
        'jurnal' => ['simbol' => '&#128211;', 'warna' => 'linear-gradient(135deg, #1565c0, #1e3a8a)'],
        'hafalan' => ['simbol' => '&#128218;', 'warna' => 'linear-gradient(135deg, #0c8a5f, #065f43)'],
        'pendampingan' => ['simbol' => '&#128106;', 'warna' => 'linear-gradient(135deg, #b45309, #78350f)'],
    ];
@endphp

<div class="sn-wrap">

    {{-- Header --}}
    <div class="siswa-page-header">
        <div class="siswa-page-icon" style="background: linear-gradient(135deg, #0c8a5f, #065f43);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div style="flex:1;">
            <h1 class="siswa-page-title">Notifikasi</h1>
            <p class="siswa-page-subtitle">Informasi nilai jurnal, hafalan, dan pendampingan dari guru</p>
        </div>
        <button type="button" id="btn-read-all" class="btn-tartil-outline">Tandai semua dibaca</button>
    </div>

    {{-- Daftar Notifikasi --}}
    @if($notifications->count() > 0)
    <div class="sn-list">
        @foreach($notifications as $n)
        @php
            $ikon = $ikonTipe[$n->data['tipe'] ?? ''] ?? ['simbol' => '&#128276;', 'warna' => 'linear-gradient(135deg, #57534e, #292524)'];
            $url = $n->data['url'] ?? '#';
        @endphp
        <a href="{{ $url }}" class="sn-item {{ $n->read_at ? '' : 'sn-unread' }}"
           data-notif-id="{{ $n->id }}" data-url="{{ $url }}">
            <div class="sn-icon" style="background: {{ $ikon['warna'] }};">{!! $ikon['simbol'] !!}</div>
            <div class="sn-body">
                <div class="sn-judul-row">
                    <span class="sn-judul">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                    @unless($n->read_at)
                    <span class="sn-badge-baru">Baru</span>
                    @endunless
                </div>
                <div class="sn-pesan">{{ $n->data['pesan'] ?? '' }}</div>
                <div class="sn-waktu">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="sn-pagination">
        {{ $notifications->links() }}
    </div>
    @else
    <div class="sn-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <h3>Belum Ada Notifikasi</h3>
        <p>Notifikasi dari guru akan muncul di sini.</p>
    </div>
    @endif

</div>

<style>
.sn-wrap { width: 100%; }
.sn-list { display: flex; flex-direction: column; gap: 10px; }
.sn-item {
    display: flex; align-items: flex-start; gap: 14px;
    background: #fff; border: 1px solid #e7e5e4; border-radius: 14px;
    padding: 16px; text-decoration: none; color: inherit;
    transition: all 0.15s;
}
.sn-item:hover { border-color: #d6d3d1; box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
.sn-unread { border-left: 4px solid #0c8a5f; background: #f0fdf6; }
.sn-icon {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff;
}
.sn-body { flex: 1; min-width: 0; }
.sn-judul-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.sn-judul { font-size: 14px; font-weight: 700; color: #1c1917; }
.sn-unread .sn-judul { font-weight: 800; }
.sn-badge-baru {
    padding: 2px 8px; border-radius: 999px;
    background: #d1fae5; color: #065f43;
    font-size: 10px; font-weight: 700; letter-spacing: 0.3px;
}
.sn-pesan { font-size: 13px; color: #44403c; margin-top: 2px; }
.sn-waktu { font-size: 11px; color: #a8a29e; margin-top: 4px; }
.sn-empty {
    background: #fff; border: 1px dashed #d6d3d1; border-radius: 14px;
    padding: 48px 24px; text-align: center; color: #78716c;
}
.sn-empty svg { margin-bottom: 12px; color: #d6d3d1; }
.sn-empty h3 { font-size: 16px; font-weight: 700; color: #1c1917; margin-bottom: 4px; }
.sn-empty p { font-size: 13px; }
.sn-pagination { margin-top: 16px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var token = '{{ csrf_token() }}';

    // Klik item: tandai dibaca lalu buka deep link
    document.querySelectorAll('.sn-item').forEach(function (item) {
        item.addEventListener('click', function (e) {
            var id = item.getAttribute('data-notif-id');
            var url = item.getAttribute('data-url');
            e.preventDefault();
            fetch('{{ url('siswa/notifikasi') }}/' + id + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).finally(function () {
                if (url && url !== '#') window.location.href = url;
                else window.location.reload();
            });
        });
    });

    // Tandai semua dibaca
    var btnReadAll = document.getElementById('btn-read-all');
    if (btnReadAll) {
        btnReadAll.addEventListener('click', function () {
            fetch('{{ route('siswa.notifikasi.read-all') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).finally(function () {
                window.location.reload();
            });
        });
    }
});
</script>
@endsection
