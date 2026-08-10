@extends('layouts.admin')

@section('title', 'Detail Hafalan - ' . $siswa->nama)

@section('content')
<style>
.juz-ring {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}
.juz-item {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s;
}
.juz-hafal { background: #0c8a5f; color: #fff; box-shadow: 0 2px 6px rgba(12,138,95,0.3); }
.juz-belum { background: #f0f0f0; color: #aaa; }
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
.surat-hafal-card {
    background: #f8faf8;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.surat-hafal-nomor {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #0c8a5f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}
.surat-hafal-info { flex: 1; min-width: 0; }
.surat-hafal-nama { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.surat-hafal-meta { font-size: 11px; color: #666; margin-top: 2px; }
@media (max-width: 640px) {
    .juz-item { width: 40px; height: 40px; font-size: 12px; }
    .hafalan-table th, .hafalan-table td { padding: 8px 6px; font-size: 12px; white-space: nowrap; }
    .status-badge { font-size: 10px; padding: 2px 6px; }
}
</style>

{{-- Breadcrumb --}}
<div style="font-size: 13px; color: #666; margin-bottom: 20px;">
    <a href="{{ route('tahfidz.index') }}" style="color: #0c8a5f; text-decoration: none; font-weight: 500;">Tahfidz & Hafalan</a>
    <span style="margin: 0 8px;">&rsaquo;</span>
    <strong>{{ $siswa->nama }}</strong>
</div>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0;">
            {{ $siswa->nama }}
        </h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">
            NIS: {{ $siswa->nis }} &middot;
            Kelas: {{ $siswa->kelasTartil?->nama ?? '-' }} &middot;
            Total Juz Hafal: <strong style="color: #0c8a5f;">{{ $totalJuzHafal }}</strong>
        </p>
    </div>
</div>

@php
    $progressJuz = \App\Models\HafalanTahfidz::progressJuz($siswa->id, $semester?->id);
    $setoranTerakhir = $hafalanList->first();
@endphp

{{-- Ring Juz 1-30 --}}
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="font-size: 13px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">
            Progress Juz ({{ $totalJuzHafal }}/30)
        </div>
        @if($setoranTerakhir)
        <div style="font-size: 12px; color: #0c8a5f; background: #e8f5e9; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
            &#128308; Setoran Terakhir: Juz {{ $setoranTerakhir->juz }}
            @if($setoranTerakhir->surat) &middot; {{ $setoranTerakhir->surat->nama_latin }}@endif
            <span style="color: #888; font-weight: 400;">({{ \App\Models\HafalanTahfidz::labelStatus($setoranTerakhir->status) }})</span>
        </div>
        @endif
    </div>
    <div class="juz-ring">
        @foreach($progressJuz as $pj)
            @php
                $itemBg = match($pj['status']) {
                    'hafal' => 'background: #0c8a5f; color: #fff;',
                    'murajaah' => 'background: #6a1b9a; color: #fff;',
                    'setengah_hafal' => 'background: #e65100; color: #fff;',
                    'baru' => 'background: #1565c0; color: #fff;',
                    default => 'background: #f0f0f0; color: #aaa;',
                };
                $tip = $pj['status']
                    ? \App\Models\HafalanTahfidz::labelStatus($pj['status'])
                        . ($pj['surat'] ? " - {$pj['surat']}" : '')
                        . ($pj['tanggal'] ? " ({$pj['tanggal']})" : '')
                    : 'Belum';
            @endphp
            <div class="juz-item" style="{{ $itemBg }}" title="Juz {{ $pj['juz'] }}: {{ $tip }}">
                {{ $pj['juz'] }}
            </div>
        @endforeach
    </div>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 12px; font-size: 11px; color: #666;">
        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#0c8a5f;margin-right:4px;"></span>Hafal</span>
        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#6a1b9a;margin-right:4px;"></span>Murojaah</span>
        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#e65100;margin-right:4px;"></span>Setengah</span>
        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#1565c0;margin-right:4px;"></span>Baru</span>
        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#f0f0f0;margin-right:4px;"></span>Belum</span>
    </div>
</div>

{{-- Surat yang Telah Dihafal --}}
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
    <div style="font-size: 13px; font-weight: 700; color: #555; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
        Surat yang Telah Dihafal ({{ $suratHafalList->count() }} surat)
    </div>

    @if($suratHafalList->isEmpty())
        <div style="text-align: center; padding: 32px; color: #888;">
            Belum ada surat yang ditandai hafal oleh guru.
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
            @foreach($suratHafalList as $sh)
            <div class="surat-hafal-card">
                <div class="surat-hafal-nomor">{{ $sh->surat->urutan ?? '-' }}</div>
                <div class="surat-hafal-info">
                    <div class="surat-hafal-nama">{{ $sh->surat?->nama_latin ?? '-' }}</div>
                    <div class="surat-hafal-meta">
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
    @endif
</div>

{{-- Daftar Hafalan --}}
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px;">
    <div style="font-size: 13px; font-weight: 700; color: #555; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
        Riwayat Hafalan ({{ $hafalanList->count() }} entry)
    </div>

    @if($hafalanList->isEmpty())
        <div style="text-align: center; padding: 40px; color: #888;">
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
                        <th>Tanggal Setoran</th>
                        <th>Konfirmasi Ortu</th>
                        <th>Guru</th>
                        <th>Catatan</th>
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
                                <span style="color: #0c8a5f; font-weight: 600;">{{ $h->dikonfirmasi_orang_tua_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span style="color: #c62828; background: #ffebee; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;">Belum dikonfirmasi</span>
                            @endif
                        </td>
                        <td style="color: #888; font-size: 12px;">{{ $h->guru?->nama ?? '-' }}</td>
                        <td style="color: #888; font-size: 12px;">{{ $h->catatan ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
