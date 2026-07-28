@extends('layouts.admin')
@section('title', 'Rekap Approval Pendaftaran')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Rekap Approval</h1>
            <p class="page-subtitle">Status pendaftaran siswa dari kelas Anda ke ujian munaqosyah</p>
        </div>
        <a href="{{ route('guru.munaqosyah.index') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">Kembali ke Ujian</a>
    </div>

    {{-- Summary Cards --}}
    @php
        $total = $pendaftarans->count();
        $terdaftar = $pendaftarans->where('status', 'T')->count();
        $lulus = $pendaftarans->where('status', 'L')->count();
        $tidakLulus = $pendaftarans->where('status', 'TL')->count();
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $total }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #D4A373;">{{ $terdaftar }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Terdaftar (T)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #5A7D5A;">{{ $lulus }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Lulus (L)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #A85A52;">{{ $tidakLulus }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Tidak Lulus (TL)</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Ujian</th>
                    <th>Pengaju</th>
                    <th>Status Pendaftaran</th>
                    <th>Status Approval</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftarans as $i => $p)
                <tr>
                    <td>{{ $pendaftarans->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>
                        <div>{{ $p->munaqosyah->nama ?? '-' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ ucfirst($p->munaqosyah->tingkat ?? '-') }} - {{ $p->munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($p->munaqosyah->tanggal_ujian)) : '-' }}</div>
                    </td>
                    <td>
                        <div>{{ $p->pengaju->nama ?? '-' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            @if($p->pengaju_type == 'admin')
                                <span class="badge-subject" style="background: #E8D5B5; font-size: 10px;">Admin</span>
                            @elseif($p->pengaju_type == 'guru')
                                <span class="badge-subject" style="background: #E9F0E9; font-size: 10px;">Guru</span>
                            @else
                                <span class="badge-muted" style="font-size: 10px;">-</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="{{ $p->status_badge_class }}">{{ $p->status_label }}</span>
                    </td>
                    <td>
                        @if($p->approval)
                            @if($p->approval->status == 'pending')
                                <span class="badge-warning">Menunggu</span>
                            @elseif($p->approval->status == 'disetujui')
                                <span class="badge-success">Disetujui</span>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $p->approval->approved_at?->format('d/m/Y') ?? '' }}</div>
                            @elseif($p->approval->status == 'ditolak')
                                <span class="badge-error">Ditolak</span>
                                @if($p->approval->catatan)
                                <div style="font-size: 11px; color: #c62828;">{{ $p->approval->catatan }}</div>
                                @endif
                            @endif
                        @else
                            <span class="badge-muted">-</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada pendaftaran siswa dari kelas Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pendaftarans->links() }}
</div>
@endsection
