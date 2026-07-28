@extends('layouts.admin')
@section('title', 'History Munaqosyah - ' . $siswa->nama)

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">History Munaqosyah</h1>
            <p class="page-subtitle">Riwayat ujian munaqosyah per siswa</p>
        </div>
        <a href="{{ route('admin.munaqosyah.rekap') }}" class="btn-tartil btn-tartil-secondary">Kembali ke Rekap</a>
    </div>

    {{-- Info Siswa --}}
    <div class="card-tartil" style="margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: auto 1fr; gap: 16px; align-items: center;">
            <div style="width: 60px; height: 60px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
            </div>
            <div>
                <h3 style="font-size: 20px; font-weight: 600; color: var(--primary); margin: 0;">{{ $siswa->nama }}</h3>
                <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 14px;">
                    NIS: {{ $siswa->nis ?? '-' }} | Kelas Reguler: {{ $siswa->kelasReguler->nama ?? '-' }} | Kelas Tartil: {{ $siswa->kelasTartil->nama ?? '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Statistik Cards --}}
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

    {{-- Riwayat Ujian --}}
    <div class="card-tartil">
        <h2 style="font-size: 18px; font-weight: 600; color: var(--primary); margin-bottom: 16px;">Riwayat Ujian</h2>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Ujian</th>
                        <th>Tingkat</th>
                        <th>Tanggal Ujian</th>
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
                        <td>{{ $r->munaqosyah->semester->nama ?? '-' }}</td>
                        <td>
                            <span class="{{ $r->status_badge_class }}">{{ $r->status_label }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $r->nilai ?? '-' }}</td>
                        <td>{{ $r->catatan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">Belum ada riwayat ujian munaqosyah untuk siswa ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $riwayat->links() }}
    </div>
</div>
@endsection
