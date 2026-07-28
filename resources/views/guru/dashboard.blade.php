@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Dashboard Guru</h1>
        <p class="page-subtitle">Selamat datang, {{ auth()->user()->nama }}</p>
    </div>

    @if(!empty($noGuru))
    <div class="alert-tartil alert-error" style="margin-bottom: 20px;">
        <strong>Akun guru belum terhubung ke data guru.</strong><br>
        Hubungi admin untuk memastikan akun login ini terhubung dengan data guru tartil.
    </div>
    @else

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Kelas Mengajar</span>
                <div class="stat-icon icon-kelas"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['kelas_mengajar'] }}</div>
        </div>
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Total Siswa</span>
                <div class="stat-icon icon-siswa"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['total_siswa'] }}</div>
        </div>
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Jurnal Bulan Ini</span>
                <div class="stat-icon icon-jurnal"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['jurnal_bulan_ini'] }}</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 24px;">
        <a href="{{ route('guru.jurnal.index') }}" class="btn-tartil" style="justify-content: center; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Input Jurnal
        </a>
        <a href="{{ route('guru.jurnal.rekap') }}" class="btn-tartil-outline" style="text-decoration: none;">Lihat Rekap</a>
        <a href="{{ route('guru.rapor.pilih') }}" class="btn-tartil-outline" style="text-decoration: none;">Cetak Rapor</a>
    </div>

    {{-- Kelas List --}}
    <div class="card-tartil" style="margin-bottom: 20px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Kelas yang Diampu</h2>
        </div>
        @forelse($kelasList as $k)
        <a href="{{ route('guru.jurnal.index', ['kelas_id' => $k->id]) }}" class="quick-link">
            <div class="quick-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <div class="quick-text">
                <div class="quick-title">{{ $k->nama }}</div>
                <div class="quick-desc">{{ $k->mata_pelajaran }} | {{ $k->jenis }}</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted)"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        @empty
        <div style="padding: 24px; text-align: center; color: var(--text-muted);">Belum ada kelas yang diampu.</div>
        @endforelse
    </div>

    {{-- Recent Journals --}}
    <div class="card-tartil">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Jurnal Terbaru</h2>
        </div>
        @forelse($recentJurnals as $j)
        <a href="{{ route('guru.jurnal.index') }}" class="quick-link">
            <div class="quick-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg></div>
            <div class="quick-text">
                <div class="quick-title">{{ $j->surat }} ({{ $j->ayat }})</div>
                <div class="quick-desc">{{ $j->kelas->nama }} | {{ $j->tanggal->format('d/m/Y') }}</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted)"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        @empty
        <div style="padding: 24px; text-align: center; color: var(--text-muted);">Belum ada jurnal.</div>
        @endforelse
    </div>
    @endif
</div>
@endsection
