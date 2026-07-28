@extends('layouts.admin')

@section('title', 'Tahfidz')

@section('content')
<style>
.tahfidz-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.2s;
}
.tahfidz-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.tahfidz-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e8f5e9;
}
.tahfidz-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
}
.tahfidz-meta {
    font-size: 12px;
    color: #888;
}
.siswa-row {
    display: grid;
    grid-template-columns: 40px 1fr 80px 100px 100px 80px;
    gap: 8px;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-size: 13px;
}
.siswa-row:nth-child(odd) {
    background: #f8faf8;
}
.siswa-row:hover {
    background: #e8f5e9;
}
.juz-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #0c8a5f;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}
.kualitas-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.kualitas-mumtaz { background: #d4edda; color: #155724; }
.kualitas-jayyid_jiddan { background: #e3f2fd; color: #1565c0; }
.kualitas-jayyid { background: #fff3cd; color: #856404; }
.kualitas-naqis { background: #fce4ec; color: #880e4f; }
.empty-tahfidz {
    text-align: center;
    padding: 48px;
    color: #888;
}
.summary-tahfidz {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.summary-box-tf {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.summary-box-tf .val {
    font-size: 24px;
    font-weight: 700;
    color: #0c8a5f;
}
.summary-box-tf .lbl {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">&#128218; Tahfidz</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Tracking hafalan Al-Quran siswa kelas Tahfidz</p>
    </div>
</div>

{{-- Ringkasan --}}
<div class="summary-tahfidz">
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTahfidz->count() }}</div>
        <div class="lbl">Kelas Tahfidz</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTahfidz->sum('siswas_count') }}</div>
        <div class="lbl">Total Siswa</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTahfidz->sum(fn($k) => $k->rekap['totalHafal'] ?? 0) }}</div>
        <div class="lbl">Total Hafalan</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTahfidz->avg('avgJuz') ? round($kelasTahfidz->avg('avgJuz'), 1) : 0 }}</div>
        <div class="lbl">Rata-rata Juz</div>
    </div>
</div>

@if($kelasTahfidz->isEmpty())
    <div class="empty-tahfidz">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
        <h3>Belum ada kelas Tahfidz</h3>
        <p>Buat kelas dengan jenis "Tahfidz" di menu Kelas Tartil.</p>
    </div>
@else
    @foreach($kelasTahfidz as $kelas)
    <div class="tahfidz-card">
        <div class="tahfidz-header">
            <div>
                <div class="tahfidz-title">{{ $kelas->nama }}</div>
                <div class="tahfidz-meta">
                    Guru: {{ $kelas->guru?->nama ?? '-' }} &middot; 
                    {{ $kelas->siswas_count }} siswa &middot; 
                    Rata-rata {{ $kelas->avgJuz }} juz &middot;
                    Semester: {{ $semester?->nama ?? '-' }}
                </div>
            </div>
        </div>

        @if(empty($kelas->rekap['perSiswa']))
            <div style="text-align: center; padding: 24px; color: #888; font-size: 13px;">
                Belum ada data hafalan untuk kelas ini.
            </div>
        @else
            <div style="font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding: 0 12px;">
                <div class="siswa-row" style="background: none; font-weight: 700; color: #555;">
                    <div>#</div>
                    <div>Nama Siswa</div>
                    <div style="text-align: center;">Juz Hafal</div>
                    <div>Sedang Proses</div>
                    <div>Hafalan Terakhir</div>
                    <div style="text-align: center;">Kualitas</div>
                </div>
            </div>
            @foreach($kelas->rekap['perSiswa'] as $i => $s)
                @php
                    $progress = \App\Models\HafalanTahfidz::progressJuz($s['siswa']->id, $semester?->id);
                    $juzAktif = collect($progress)->first(fn($p) => $p['status'] && $p['status'] !== 'hafal');
                @endphp
                <a href="{{ route('admin.tahfidz.detail-siswa', $s['siswa']->id) }}" class="siswa-row" style="text-decoration: none; color: inherit;">
                    <div style="font-weight: 600; color: #888;">{{ $i + 1 }}</div>
                    <div style="font-weight: 600;">{{ $s['siswa']->nama }}</div>
                    <div style="text-align: center;">
                        <span class="juz-badge" style="{{ $s['juzHafal'] > 0 ? '' : 'background: #ccc;' }}">{{ $s['juzHafal'] }}</span>
                    </div>
                    <div style="font-size: 12px;">
                        @if($juzAktif && $juzAktif['status'])
                            <span style="color: #e65100; font-weight: 600;">Juz {{ $juzAktif['juz'] }}</span>
                            <span style="color: #888;">({{ \App\Models\HafalanTahfidz::labelStatus($juzAktif['status']) }})</span>
                        @elseif($s['juzHafal'] > 0)
                            <span style="color: #0c8a5f; font-size: 11px;">Lanjut Juz {{ $s['juzHafal'] + 1 }}</span>
                        @else
                            <span style="color: #aaa;">-</span>
                        @endif
                    </div>
                    <div style="font-size: 12px;">
                        @if($s['lastJuz'] !== '-')
                            Juz {{ $s['lastJuz'] }} &middot; {{ $s['lastSurat'] }}
                            <span style="color: #888;">&middot; {{ $s['lastTanggal'] }}</span>
                        @else
                            <span style="color: #aaa;">-</span>
                        @endif
                    </div>
                    <div style="text-align: center;">
                        @if($s['kualitas'] !== '-')
                            <span class="kualitas-badge kualitas-{{ $s['kualitas'] }}">{{ \App\Models\HafalanTahfidz::labelKualitas($s['kualitas']) }}</span>
                        @else
                            <span style="color: #aaa;">-</span>
                        @endif
                    </div>
                </a>
            @endforeach
        @endif
    </div>
    @endforeach
@endif
@endsection
