@extends('layouts.admin')
@section('title', 'Penilaian Rapor')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title-display">Penilaian Rapor</h1>
            <p class="page-subtitle">Daftar penilaian rapor aktif untuk kelas yang Anda ajar</p>
        </div>
    </div>

    @if($penilaians->isEmpty())
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted); margin-bottom: 8px;">Belum ada penilaian rapor yang aktif untuk semester ini.</div>
        <div style="font-size: 12px; color: var(--text-muted);">Hubungi admin untuk mengaktifkan penilaian rapor semester ini.</div>
    </div>
    @else
    <div style="display: grid; gap: 16px;">
        @foreach($penilaians as $p)
        <div class="card-tartil" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <strong style="font-size: 15px; color: var(--text-primary);">{{ $p->semester->nama ?? '-' }}</strong>
                        <span class="{{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
                    </div>
                    @if($p->keterangan)
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">{{ $p->keterangan }}</div>
                    @endif
                    <div style="font-size: 11px; color: var(--text-muted);">
                        {{ $p->jumlahSiswa() }} siswa | {{ $p->jumlahKelas() }} kelas
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 24px; font-weight: 700; color: {{ $p->progressPersen() >= 80 ? '#5A7D5A' : ($p->progressPersen() >= 50 ? '#B8860B' : '#C62828') }};">
                        {{ $p->progressPersen() }}%
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">terisi</div>
                </div>
            </div>
            @if($p->status === 'aktif')
            <div style="margin-top: 16px; display: flex; gap: 8px;">
                <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $p->id) }}" class="btn-tartil" style="text-decoration: none; font-size: 12px; padding: 8px 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Isi Nilai
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
