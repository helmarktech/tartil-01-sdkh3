@extends('layouts.siswa')
@section('title', 'Riwayat Munaqosyah')

@section('content')
<div class="sm-wrap">

    {{-- Header --}}
    <div class="siswa-page-header">
        <div class="siswa-page-icon" style="background: linear-gradient(135deg, #b45309, #92400e);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <h1 class="siswa-page-title">Riwayat Munaqosyah</h1>
            <p class="siswa-page-subtitle">Daftar ujian munaqosyah yang pernah diikuti</p>
        </div>
    </div>

    {{-- Statistik Cards --}}
    @if(isset($statistik) && $statistik['total_ikut'] > 0)
    <div class="sm-stats">
        <div class="stat-card sm-stat">
            <div class="stat-label">Mengikuti</div>
            <div class="stat-value" style="color: var(--ink);">{{ $statistik['total_ikut'] }}</div>
        </div>
        <div class="stat-card sm-stat">
            <div class="stat-label">Lulus</div>
            <div class="stat-value" style="color: #0c8a5f;">{{ $statistik['total_lulus'] }}</div>
        </div>
        <div class="stat-card sm-stat">
            <div class="stat-label">Tidak Lulus</div>
            <div class="stat-value" style="color: #dc2626;">{{ $statistik['total_tidak_lulus'] }}</div>
        </div>
        <div class="stat-card sm-stat">
            <div class="stat-label">Kelulusan</div>
            <div class="stat-value" style="color: #1565c0;">{{ $statistik['persentase_kelulusan'] }}%</div>
        </div>
    </div>
    @endif

    {{-- Tabel Riwayat --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table-tartil sm-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ujian</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Nilai</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $i => $r)
                    <tr>
                        <td>{{ $riwayat->firstItem() + $i }}</td>
                        <td style="font-weight: 700; color: var(--ink);">{{ $r->munaqosyah->nama ?? '-' }}</td>
                        <td><span class="badge-subject">{{ ucfirst($r->munaqosyah->tingkat ?? '-') }}</span></td>
                        <td>{{ $r->munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($r->munaqosyah->tanggal_ujian)) : '-' }}</td>
                        <td>
                            {{ $r->munaqosyah->semester->nama ?? '-' }}
                            @if($r->munaqosyah->semester && $r->munaqosyah->semester->status == 'ditutup')
                            <span class="badge-error" style="margin-left: 4px;">Ditutup</span>
                            @endif
                        </td>
                        <td>
                            <span class="sm-status {{ $r->status == 'L' ? 'lulus' : ($r->status == 'TL' ? 'tidak-lulus' : 'pending') }}">
                                {{ $r->status == 'L' ? 'Lulus' : ($r->status == 'TL' ? 'Tidak Lulus' : 'Pending') }}
                            </span>
                        </td>
                        <td style="font-weight: 700;">{{ $r->nilai ?? '-' }}</td>
                        <td style="color: var(--ink-muted); font-size: 12px;">{{ $r->catatan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="sm-empty-cell">
                            <div class="sm-empty">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                <p>Belum ada riwayat ujian munaqosyah.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $riwayat->links() }}
</div>

<style>
.sm-wrap { width: 100%; }
.sm-header { margin-bottom: 20px; }
.sm-stats {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;
    margin-bottom: 20px;
}
.sm-stat { padding: 18px 12px; }
.sm-table th { white-space: nowrap; }
.sm-status {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 3px 10px; border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
}
.sm-status.lulus { background: #d1fae5; color: #065f43; }
.sm-status.tidak-lulus { background: #fef2f2; color: #991b1b; }
.sm-status.pending { background: #fef9c3; color: #854d0e; }
.sm-empty-cell { padding: 0 !important; border-bottom: none !important; }
.sm-empty { text-align: center; padding: 48px 20px; color: var(--ink-muted); }
.sm-empty svg { margin-bottom: 16px; opacity: 0.35; }
.sm-empty p { font-size: 14px; margin: 0; }

@media (max-width: 640px) {
    .sm-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endsection
