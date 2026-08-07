@extends('layouts.siswa')

@section('title', 'Hafalan Al-Quran')

@section('content')
<style>
.haf-juz-grid {
    display: grid;
    grid-template-columns: repeat(15, 1fr);
    gap: 4px;
    margin-bottom: 16px;
}
.haf-juz-item {
    aspect-ratio: 1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    transition: transform 0.15s;
    cursor: default;
}
.haf-juz-item:hover { transform: scale(1.15); }
.haf-status-hafal { background: #0c8a5f; color: #fff; }
.haf-status-murajaah { background: #6a1b9a; color: #fff; }
.haf-status-setengah_hafal { background: #e65100; color: #fff; }
.haf-status-baru { background: #1565c0; color: #fff; }
.haf-status-belum { background: #e5e5e5; color: #aaa; }

.haf-legend { display: flex; gap: 16px; flex-wrap: wrap; font-size: 11px; color: #666; }
.haf-legend span { display: inline-flex; align-items: center; gap: 6px; }
.haf-legend-dot { width: 14px; height: 14px; border-radius: 4px; }

.haf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.haf-table th {
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
.haf-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.haf-table tr:hover td { background: #f8faf8; }

.haf-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}
.haf-badge-hafal { background: #d4edda; color: #155724; }
.haf-badge-murajaah { background: #f3e5f5; color: #6a1b9a; }
.haf-badge-setengah_hafal { background: #e3f2fd; color: #1565c0; }
.haf-badge-baru { background: #fff3cd; color: #856404; }

@media (max-width: 640px) {
    .haf-juz-grid { grid-template-columns: repeat(10, 1fr); }
}
</style>

<div class="sd-head" style="margin-bottom: 20px;">
    <div>
        <h1 class="sd-title" style="font-size: 22px;">&#128218; Hafalan Al-Quran</h1>
        <p class="sd-sub">Pantau progres hafalan Anda</p>
    </div>
</div>

{{-- Progress Juz --}}
<div class="sd-section" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 class="sd-section-title" style="margin: 0;">Progress Juz ({{ $totalJuzHafal }}/30)</h2>
        @if($juzAktif)
        <span style="font-size: 12px; color: #0c8a5f; background: #e8f5e9; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
            &#128308; Setoran Terakhir: Juz {{ $juzAktif->juz }} ({{ \App\Models\HafalanTahfidz::labelStatus($juzAktif->status) }})
        </span>
        @endif
    </div>

    <div class="haf-juz-grid">
        @foreach($progressJuz as $pj)
            <div class="haf-juz-item haf-status-{{ $pj['status'] ?? 'belum' }}"
                 title="Juz {{ $pj['juz'] }}: {{ $pj['status'] ? \App\Models\HafalanTahfidz::labelStatus($pj['status']) : 'Belum' }}{{ $pj['surat'] ? ' - ' . $pj['surat'] : '' }}">
                {{ $pj['juz'] }}
            </div>
        @endforeach
    </div>

    <div class="haf-legend">
        <span><span class="haf-legend-dot" style="background:#0c8a5f;"></span> Hafal</span>
        <span><span class="haf-legend-dot" style="background:#6a1b9a;"></span> Murojaah</span>
        <span><span class="haf-legend-dot" style="background:#e65100;"></span> Setengah Hafal</span>
        <span><span class="haf-legend-dot" style="background:#1565c0;"></span> Baru</span>
        <span><span class="haf-legend-dot" style="background:#e5e5e5;"></span> Belum</span>
    </div>
</div>

{{-- Riwayat Setoran --}}
<div class="sd-section">
    <h2 class="sd-section-title">Riwayat Setoran ({{ $hafalanList->count() }})</h2>

    @if($hafalanList->isEmpty())
        <div style="text-align: center; padding: 40px; color: #888;">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
            <p>Belum ada data setoran hafalan.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="haf-table">
                <thead>
                    <tr>
                        <th>Juz</th>
                        <th>Surat</th>
                        <th>Ayat</th>
                        <th>Status</th>
                        <th>Kualitas</th>
                        <th>Tanggal</th>
                        <th>Guru</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hafalanList as $h)
                    <tr>
                        <td><strong style="color: #0c8a5f;">Juz {{ $h->juz }}</strong></td>
                        <td>{{ $h->surat?->nama_latin ?? '-' }}</td>
                        <td>{{ $h->ayat_mulai }}{{ $h->ayat_selesai ? '-' . $h->ayat_selesai : '' }}</td>
                        <td><span class="haf-badge haf-badge-{{ $h->status }}">{{ \App\Models\HafalanTahfidz::labelStatus($h->status) }}</span></td>
                        <td>{{ \App\Models\HafalanTahfidz::labelKualitas($h->kualitas) }}</td>
                        <td style="color: #888; font-size: 12px;">{{ $h->tanggal_hafalan?->format('d/m/Y') }}</td>
                        <td style="color: #888; font-size: 12px;">{{ $h->guru?->nama ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
