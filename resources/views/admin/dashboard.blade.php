@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Dashboard Admin</h1>
        <p class="page-subtitle">Ringkasan sistem penilaian tartil</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Guru Aktif</span>
                <div class="stat-icon icon-jurnal"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['total_guru'] }}</div>
        </div>
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Siswa Aktif</span>
                <div class="stat-icon icon-siswa"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['total_siswa'] }}</div>
        </div>
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Kelas Aktif</span>
                <div class="stat-icon icon-kelas"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['total_kelas'] }}</div>
        </div>
        <div class="stat-card card-hover">
            <div class="stat-header">
                <span class="stat-label">Pending Pindah</span>
                <div class="stat-icon icon-kehadiran"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            </div>
            <div class="stat-value">{{ $stats['pending_pindah'] }}</div>
        </div>
    </div>

    {{-- Rekap Siswa per Jenis Kelas (BQ) --}}
    @if($rekapBQ->count() > 0)
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 16px; font-weight: 600; color: var(--primary); margin: 0 0 12px 0;">Rekap Siswa per Jenis Kelas</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
            @foreach($rekapBQ as $jenis => $data)
            <div class="stat-card card-hover" style="padding: 16px;">
                <div class="stat-label" style="font-size: 12px; margin-bottom: 4px;">{{ $jenis }}</div>
                <div style="display: flex; align-items: baseline; gap: 8px;">
                    <div class="stat-value" style="font-size: 28px;">{{ $data['siswa'] }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">siswa</div>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ $data['kelas'] }} kelas</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Penyebaran Siswa per Kelas Tartil --}}
    @if($penyebaranKelas->count() > 0)
    <div class="card-tartil" style="margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Penyebaran Siswa per Kelas Tartil</h2>
        </div>
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Jenis</th>
                        <th>Guru Pengampu</th>
                        <th>Jumlah Siswa</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penyebaranKelas as $i => $k)
                    @php
                        $maxSiswa = 30; // asumsi kapasitas maksimal per kelas
                        $persen = min(100, round(($k->siswas_count / $maxSiswa) * 100));
                        $barColor = $persen >= 90 ? 'var(--danger)' : ($persen >= 70 ? 'var(--warning)' : 'var(--success)');
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $k->nama }}</td>
                        <td><span class="badge-subject">{{ $k->jenis }}</span></td>
                        <td>{{ $k->guru->nama ?? '-' }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ $k->siswas_count }}</td>
                        <td style="min-width: 120px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="width: {{ $persen }}%; background: {{ $barColor }}; height: 100%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 11px; min-width: 32px;">{{ $k->siswas_count }}/{{ $maxSiswa }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card-tartil">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Pengajuan Perpindahan Kelas Terbaru</h2>
        </div>
        @forelse($recentPerpindahan as $p)
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-weight: 500; font-size: 14px;">{{ $p->siswa->nama }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $p->kelasLama->nama }} → {{ $p->kelasBaru->nama }}</div>
            </div>
            <span class="badge-subject" style="background: {{ $p->status === 'pending' ? '#F0ECE9' : ($p->status === 'disetujui' ? '#E9F0E9' : '#F0E9E9') }}; color: {{ $p->status === 'pending' ? '#8A7A6B' : ($p->status === 'disetujui' ? '#5A7D5A' : '#A85A52') }};">
                {{ ucfirst($p->status) }}
            </span>
        </div>
        @empty
        <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 14px;">Belum ada pengajuan perpindahan kelas.</div>
        @endforelse
    </div>
</div>
@endsection
