@extends('layouts.admin')
@section('title', 'Riwayat Siswa - By Kelas Tartil')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Riwayat Siswa</h1>
            <p class="page-subtitle">By Kelas Tartil — Riwayat kelas, naik kelas, dan performa siswa</p>
        </div>
    </div>

    {{-- Step 1: Pilih Kelas --}}
    @if(!$kelasAktif)
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--text-primary);">Langkah 1: Pilih Kelas</h3>

        @if($kelasList->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
            @foreach($kelasList as $k)
            <a href="?kelas_id={{ $k->id }}" class="card-tartil" style="text-decoration: none; padding: 16px; transition: all 0.2s;">
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">{{ $k->nama }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $k->mata_pelajaran ?? '-' }} | {{ $k->jenis ?? '-' }}</div>
                <div style="font-size: 12px; color: var(--accent); margin-top: 8px;">
                    {{ $k->siswas()->where('status', 'aktif')->count() }} siswa aktif
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            @if($role == 'guru')
                Anda belum memiliki kelas yang diajar.
            @else
                Tidak ada kelas aktif.
            @endif
        </div>
        @endif
    </div>

    {{-- Step 2: List Siswa --}}
    @else
    <div class="card-tartil" style="padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-primary);">{{ $kelasAktif->nama }}</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0;">{{ $kelasAktif->mata_pelajaran ?? '-' }} | {{ $kelasAktif->jenis ?? '-' }}</p>
            </div>
            <a href="?" class="btn-tartil-outline" style="font-size: 12px;">Ganti Kelas</a>
        </div>
    </div>

    @if($siswaList->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        @foreach($siswaList as $s)
        <a href="{{ $role == 'admin' ? route('admin.track-record.detail', $s->id) : route('guru.track-record.detail', $s->id) }}"
           class="card-tartil" style="text-decoration: none; padding: 16px; transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0;">
                    {{ strtoupper(substr($s->nama, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $s->nama }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">NIS: {{ $s->nis ?? '-' }} | Kelas Reg: {{ $s->kelasReguler->nama ?? '-' }}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); flex-shrink: 0;"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <div style="color: var(--text-muted);">Tidak ada siswa aktif di kelas ini.</div>
    </div>
    @endif

    @endif
</div>
@endsection
