@extends('layouts.siswa')
@section('title', 'Riwayat Munaqosyah')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Riwayat Munaqosyah</h1>
            <p class="page-subtitle">Daftar ujian munaqosyah yang pernah diikuti</p>
        </div>
    </div>

    {{-- Statistik Cards --}}
    @if(isset($statistik) && $statistik['total_ikut'] > 0)
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label">Total Mengikuti</div>
            <div class="stat-value" style="color: var(--accent);">{{ $statistik['total_ikut'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Lulus</div>
            <div class="stat-value" style="color: var(--success);">{{ $statistik['total_lulus'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Tidak Lulus</div>
            <div class="stat-value" style="color: var(--danger);">{{ $statistik['total_tidak_lulus'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">% Kelulusan</div>
            <div class="stat-value" style="color: var(--info);">{{ $statistik['persentase_kelulusan'] }}%</div>
        </div>
    </div>
    @endif

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ujian</th>
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
                    <td style="font-weight: 500;">{{ $r->munaqosyah->nama ?? '-' }}</td>
                    <td><span class="badge-subject">{{ ucfirst($r->munaqosyah->tingkat ?? '-') }}</span></td>
                    <td>{{ $r->munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($r->munaqosyah->tanggal_ujian)) : '-' }}</td>
                    <td>
                        {{ $r->munaqosyah->semester->nama ?? '-' }}
                        @if($r->munaqosyah->semester && $r->munaqosyah->semester->status == 'ditutup')
                        <span class="badge-error" style="font-size: 9px; margin-left: 4px;">Ditutup</span>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $r->status_badge_class }}">{{ $r->status_label }}</span>
                    </td>
                    <td>{{ $r->nilai ?? '-' }}</td>
                    <td>{{ $r->catatan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada riwayat ujian munaqosyah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $riwayat->links() }}
</div>
@endsection
