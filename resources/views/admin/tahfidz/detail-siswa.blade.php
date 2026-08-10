@extends('layouts.admin')

@section('title', 'Detail Hafalan - ' . $siswa->nama)

@section('content')
<style>
.tahfidz-profile {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.tahfidz-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0c8a5f, #0a6b4a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    flex-shrink: 0;
}
.tahfidz-profile-info { flex: 1; min-width: 0; }
.tahfidz-profile-name {
    font-family: 'DM Serif Display', serif;
    font-size: 24px;
    margin: 0 0 4px;
    color: #1a1a2e;
}
.tahfidz-profile-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #666;
}
.tahfidz-profile-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.tahfidz-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.tahfidz-badge-juz { background: #e8f5e9; color: #0c8a5f; }
.tahfidz-badge-class { background: #f0f0f0; color: #555; }

.tahfidz-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
}
.tahfidz-tab {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}
.tahfidz-tab:hover { color: #0c8a5f; }
.tahfidz-tab.active {
    color: #0c8a5f;
    border-bottom-color: #0c8a5f;
}

.tahfidz-tab-content { display: none; }
.tahfidz-tab-content.active { display: block; }

.tahfidz-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.tahfidz-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
}

.tahfidz-juz-grid {
    display: grid;
    grid-template-columns: repeat(15, 1fr);
    gap: 6px;
}
.tahfidz-juz-item {
    aspect-ratio: 1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    background: #f0f0f0;
    color: #aaa;
    transition: transform 0.15s;
    cursor: default;
}
.tahfidz-juz-item:hover { transform: scale(1.1); }
.tahfidz-juz-hafal { background: #0c8a5f; color: #fff; }
.tahfidz-juz-murajaah { background: #6a1b9a; color: #fff; }
.tahfidz-juz-setengah { background: #e65100; color: #fff; }
.tahfidz-juz-baru { background: #1565c0; color: #fff; }
.tahfidz-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 14px;
    font-size: 11px;
    color: #666;
}
.tahfidz-legend span { display: inline-flex; align-items: center; gap: 6px; }
.tahfidz-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 3px;
}

.tahfidz-surat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 10px;
}
.tahfidz-surat-card {
    background: #f8faf8;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.tahfidz-surat-nomor {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #0c8a5f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}
.tahfidz-surat-info { flex: 1; min-width: 0; }
.tahfidz-surat-nama { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.tahfidz-surat-meta { font-size: 11px; color: #666; margin-top: 2px; }

.tahfidz-empty {
    text-align: center;
    padding: 32px;
    color: #888;
    font-size: 13px;
}

.hafalan-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.hafalan-table th {
    text-align: left;
    padding: 10px 12px;
    background: #f8faf8;
    font-size: 11px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0;
}
.hafalan-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.hafalan-table tr:hover td { background: #f8faf8; }
.status-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.status-baru { background: #fff3cd; color: #856404; }
.status-setengah_hafal { background: #e3f2fd; color: #1565c0; }
.status-hafal { background: #d4edda; color: #155724; }
.status-murajaah { background: #f3e5f5; color: #6a1b9a; }

@media (max-width: 768px) {
    .tahfidz-juz-grid { grid-template-columns: repeat(10, 1fr); }
}
@media (max-width: 640px) {
    .tahfidz-profile-name { font-size: 20px; }
    .tahfidz-juz-grid { grid-template-columns: repeat(8, 1fr); }
    .tahfidz-juz-item { font-size: 10px; }
    .hafalan-table th, .hafalan-table td { padding: 8px 6px; font-size: 12px; white-space: nowrap; }
    .status-badge { font-size: 10px; padding: 2px 6px; }
    .tahfidz-surat-grid { grid-template-columns: 1fr; }
}
@media (max-width: 420px) {
    .tahfidz-juz-grid { grid-template-columns: repeat(6, 1fr); }
}
</style>

{{-- Breadcrumb --}}
<div style="font-size: 13px; color: #666; margin-bottom: 16px;">
    <a href="{{ route('admin.tahfidz.index') }}" style="color: #0c8a5f; text-decoration: none; font-weight: 500;">Tahfidz & Hafalan</a>
    <span style="margin: 0 8px;">&rsaquo;</span>
    <strong>{{ $siswa->nama }}</strong>
</div>

{{-- Profile Card --}}
<div class="tahfidz-card" style="margin-bottom: 20px;">
    <div class="tahfidz-profile">
        <div class="tahfidz-avatar">{{ substr($siswa->nama, 0, 1) }}</div>
        <div class="tahfidz-profile-info">
            <h1 class="tahfidz-profile-name">{{ $siswa->nama }}</h1>
            <div class="tahfidz-profile-meta">
                <span>NIS: {{ $siswa->nis }}</span>
                <span class="tahfidz-badge tahfidz-badge-class">{{ $siswa->kelasTartil?->nama ?? 'Belum ada kelas' }}</span>
                <span class="tahfidz-badge tahfidz-badge-juz">{{ $totalJuzHafal }} Juz Hafal</span>
            </div>
        </div>
        <a href="{{ route('admin.tahfidz.hafalan.create', ['siswa_id' => $siswa->id]) }}" class="btn-tartil" style="flex-shrink: 0;">
            + Tambah Hafalan
        </a>
    </div>
</div>

@php
    $progressJuz = \App\Models\HafalanTahfidz::progressJuz($siswa->id, $semester?->id);
    $setoranTerakhir = $hafalanList->first();
@endphp

{{-- Tabs --}}
<div class="tahfidz-tabs">
    <button type="button" class="tahfidz-tab active" onclick="switchTab('ringkasan')" id="tab-ringkasan">
        Ringkasan
    </button>
    <button type="button" class="tahfidz-tab" onclick="switchTab('riwayat')" id="tab-riwayat">
        Riwayat Hafalan ({{ $hafalanList->count() }})
    </button>
</div>

{{-- Tab Ringkasan --}}
<div id="content-ringkasan" class="tahfidz-tab-content active">
    {{-- Progress Juz --}}
    <div class="tahfidz-card">
        <div class="tahfidz-card-title">
            Progress Juz ({{ $totalJuzHafal }}/30)
            @if($setoranTerakhir)
            <span style="font-size: 12px; color: #0c8a5f; background: #e8f5e9; padding: 4px 12px; border-radius: 20px; font-weight: 600; float: right; text-transform: none; letter-spacing: 0;">
                Setoran Terakhir: Juz {{ $setoranTerakhir->juz }}
                @if($setoranTerakhir->surat) &middot; {{ $setoranTerakhir->surat->nama_latin }}@endif
                <span style="color: #888; font-weight: 400;">({{ \App\Models\HafalanTahfidz::labelStatus($setoranTerakhir->status) }})</span>
            </span>
            @endif
        </div>
        <div class="tahfidz-juz-grid">
            @foreach($progressJuz as $pj)
                <div class="tahfidz-juz-item tahfidz-juz-{{ $pj['status'] ?? 'belum' }}"
                     title="Juz {{ $pj['juz'] }}: {{ $pj['status'] ? \App\Models\HafalanTahfidz::labelStatus($pj['status']) : 'Belum' }}{{ $pj['surat'] ? ' - ' . $pj['surat'] : '' }}{{ $pj['tanggal'] ? ' (' . $pj['tanggal'] . ')' : '' }}">
                    {{ $pj['juz'] }}
                </div>
            @endforeach
        </div>
        <div class="tahfidz-legend">
            <span><span class="tahfidz-legend-dot" style="background:#0c8a5f;"></span>Hafal</span>
            <span><span class="tahfidz-legend-dot" style="background:#6a1b9a;"></span>Murojaah</span>
            <span><span class="tahfidz-legend-dot" style="background:#e65100;"></span>Setengah</span>
            <span><span class="tahfidz-legend-dot" style="background:#1565c0;"></span>Baru</span>
            <span><span class="tahfidz-legend-dot" style="background:#f0f0f0;"></span>Belum</span>
        </div>
    </div>

    {{-- Surat yang Telah Dihafal --}}
    @if($suratHafalList->isNotEmpty())
    <div class="tahfidz-card">
        <div class="tahfidz-card-title">Surat yang Telah Dihafal ({{ $suratHafalList->count() }} surat)</div>
        <div class="tahfidz-surat-grid">
            @foreach($suratHafalList as $sh)
            <div class="tahfidz-surat-card">
                <div class="tahfidz-surat-nomor">{{ $sh->surat->urutan ?? '-' }}</div>
                <div class="tahfidz-surat-info">
                    <div class="tahfidz-surat-nama">{{ $sh->surat?->nama_latin ?? '-' }}</div>
                    <div class="tahfidz-surat-meta">
                        Juz {{ $sh->juz }} &middot; Ayat {{ $sh->ayat_mulai }}{{ $sh->ayat_selesai ? '-'.$sh->ayat_selesai : '' }}
                        &middot; {{ $sh->tanggal_hafalan?->format('d/m/Y') }}
                        @if($sh->kualitas)
                            &middot; {{ \App\Models\HafalanTahfidz::labelKualitas($sh->kualitas) }}
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Tab Riwayat Hafalan --}}
<div id="content-riwayat" class="tahfidz-tab-content">
    <div class="tahfidz-card">
        <div class="tahfidz-card-title">Riwayat Hafalan</div>

        @if($hafalanList->isEmpty())
            <div class="tahfidz-empty">
                Belum ada data hafalan untuk siswa ini.
            </div>
        @else
            <div class="table-responsive">
                <table class="hafalan-table">
                    <thead>
                        <tr>
                            <th>Juz</th>
                            <th>Surat</th>
                            <th>Ayat</th>
                            <th>Status</th>
                            <th>Kualitas</th>
                            <th>Tanggal</th>
                            <th>Konfirmasi Ortu</th>
                            <th>Guru</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hafalanList as $h)
                        <tr>
                            <td><strong style="color: #0c8a5f;">Juz {{ $h->juz }}</strong></td>
                            <td>{{ $h->surat?->nama_latin ?? '-' }}</td>
                            <td>{{ $h->ayat_mulai }}{{ $h->ayat_selesai ? '-' . $h->ayat_selesai : '' }}</td>
                            <td><span class="status-badge status-{{ $h->status }}">{{ \App\Models\HafalanTahfidz::labelStatus($h->status) }}</span></td>
                            <td><span class="kualitas-badge kualitas-{{ $h->kualitas }}">{{ \App\Models\HafalanTahfidz::labelKualitas($h->kualitas) }}</span></td>
                            <td style="color: #888; font-size: 12px;">{{ $h->tanggal_hafalan?->format('d/m/Y') }}</td>
                            <td style="font-size: 12px;">
                                @if($h->dikonfirmasi_orang_tua_at)
                                    <span style="color: #0c8a5f; font-weight: 600;">{{ $h->dikonfirmasi_orang_tua_at->format('d/m/Y') }}</span>
                                @else
                                    <span style="color: #c62828; background: #ffebee; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;">Belum</span>
                                @endif
                            </td>
                            <td style="color: #888; font-size: 12px;">{{ $h->guru?->nama ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.tahfidz.hafalan.destroy', $h->id) }}" style="display:inline;" onsubmit="return confirm('Hapus hafalan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #c62828; cursor: pointer; font-size: 16px;">&times;</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tahfidz-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tahfidz-tab-content').forEach(c => c.classList.remove('active'));

    document.getElementById('tab-' + tabName).classList.add('active');
    document.getElementById('content-' + tabName).classList.add('active');
}
</script>
@endsection
