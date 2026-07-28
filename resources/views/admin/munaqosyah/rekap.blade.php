@extends('layouts.admin')
@section('title', 'Rekap History Munaqosyah')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Rekap History Munaqosyah</h1>
            <p class="page-subtitle">Ringkasan seluruh ujian munaqosyah sekolah</p>
        </div>
    </div>

    {{-- Statistik Cards --}}
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label">Total Ujian</div>
            <div class="stat-value" style="color: var(--primary);">{{ $totalUjian }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Peserta</div>
            <div class="stat-value" style="color: var(--accent);">{{ $totalPeserta }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Lulus</div>
            <div class="stat-value" style="color: var(--success);">{{ $totalLulus }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Tidak Lulus</div>
            <div class="stat-value" style="color: var(--danger);">{{ $totalTidakLulus }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">% Kelulusan</div>
            <div class="stat-value" style="color: var(--info);">{{ $persentaseKelulusan }}%</div>
        </div>
    </div>

    {{-- Daftar Ujian --}}
    <div class="card-tartil">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 18px; font-weight: 600; color: var(--primary);">Daftar Ujian Munaqosyah</h2>
        </div>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Ujian</th>
                        <th>Tanggal</th>
                        <th>Tingkat</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Peserta</th>
                        <th>Lulus</th>
                        <th>Tidak Lulus</th>
                        <th>% Kelulusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujians as $i => $u)
                    @php
                        $totalP = $u->total_peserta ?? 0;
                        $totalL = $u->total_lulus ?? 0;
                        $persen = $totalP > 0 ? round(($totalL / $totalP) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $ujians->firstItem() + $i }}</td>
                        <td style="font-weight: 500;">{{ $u->nama }}</td>
                        <td>{{ $u->tanggal_ujian ? date('d/m/Y', strtotime($u->tanggal_ujian)) : '-' }}</td>
                        <td><span class="badge-subject">{{ ucfirst($u->tingkat) }}</span></td>
                        <td>{{ $u->semester->nama ?? '-' }}</td>
                        <td>
                            @if($u->status == 'disetujui')
                                <span class="badge-success">Disetujui</span>
                            @elseif($u->status == 'sedang_berlangsung')
                                <span class="badge-info">Berlangsung</span>
                            @elseif($u->status == 'ditolak')
                                <span class="badge-error">Ditolak</span>
                            @elseif($u->status == 'selesai')
                                <span class="badge-secondary">Selesai</span>
                            @else
                                <span class="badge-warning">{{ ucfirst($u->status) }}</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $totalP }}</td>
                        <td style="text-align: center; color: var(--success);">{{ $totalL }}</td>
                        <td style="text-align: center; color: var(--danger);">{{ $u->total_tidak_lulus ?? 0 }}</td>
                        <td style="text-align: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="width: {{ $persen }}%; background: {{ $persen >= 70 ? 'var(--success)' : ($persen >= 50 ? 'var(--warning)' : 'var(--danger)') }}; height: 100%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 12px; font-weight: 600; min-width: 36px;">{{ $persen }}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.munaqosyah.detail', $u->id) }}" class="btn-tartil btn-tartil-sm" style="font-size: 12px; padding: 4px 10px;">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" style="text-align: center; color: var(--text-muted); padding: 32px;">Belum ada data ujian munaqosyah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $ujians->links() }}
    </div>

    {{-- Top Siswa Lulus --}}
    @if($topLulus->count() > 0)
    <div class="card-tartil" style="margin-top: 24px;">
        <h2 style="font-size: 18px; font-weight: 600; color: var(--primary); margin-bottom: 16px;">Top Siswa - Paling Sering Lulus</h2>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas Reguler</th>
                        <th>Jumlah Lulus</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topLulus as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $t->siswa->nama ?? '-' }}</td>
                        <td>{{ $t->siswa->kelasReguler->nama ?? '-' }}</td>
                        <td style="text-align: center; color: var(--success); font-weight: 600;">{{ $t->jumlah_lulus }}</td>
                        <td>
                            <a href="{{ route('admin.munaqosyah.rekap.siswa', $t->siswa_id) }}" class="btn-tartil btn-tartil-sm" style="font-size: 12px; padding: 4px 10px;">Lihat History</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
