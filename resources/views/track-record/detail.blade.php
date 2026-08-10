@extends($role == 'siswa' ? 'layouts.siswa' : 'layouts.admin')
@section('title', 'Detail Track Record - ' . $siswa->nama)

@section('content')
<div class="tr-wrap">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Track Record Siswa</h1>
            <p class="page-subtitle">Detail riwayat kelas dan performa akademik</p>
        </div>
        @if($role != 'siswa')
        <a href="{{ url()->previous() }}" class="btn-tartil-outline">Kembali</a>
        @endif
    </div>

    {{-- Profile Card --}}
    <div class="card-tartil tr-profile">
        <div class="tr-profile-avatar">
            {{ strtoupper(substr($siswa->nama, 0, 1)) }}
        </div>
        <div class="tr-profile-body">
            <h2 class="tr-profile-name">{{ $siswa->nama }}</h2>
            <div class="tr-profile-meta">
                <div class="tr-profile-chip">
                    <span class="tr-chip-label">NIS</span>
                    <span class="tr-chip-value">{{ $siswa->nis ?? '-' }}</span>
                </div>
                <div class="tr-profile-chip">
                    <span class="tr-chip-label">Kelas Reguler</span>
                    <span class="tr-chip-value">{{ $siswa->kelasReguler->nama ?? '-' }}</span>
                </div>
                <div class="tr-profile-chip">
                    <span class="tr-chip-label">Kelas Tartil</span>
                    <span class="tr-chip-value">{{ $siswa->kelasTartil->nama ?? '-' }}</span>
                </div>
                <div class="tr-profile-chip">
                    <span class="tr-chip-label">Status</span>
                    <span class="tr-chip-value">
                        @if($siswa->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @elseif($siswa->status == 'lulus')
                            <span class="badge-primary">Lulus</span>
                        @else
                            <span class="badge-warning">{{ ucfirst($siswa->status) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelas History Timeline --}}
    <div class="tr-section-header">
        <div class="tr-section-icon">&#128260;</div>
        <h3 class="tr-section-title">Riwayat Perpindahan Kelas</h3>
    </div>

    @if(count($kelasHistory) > 0)
    <div class="tr-timeline">
        @foreach($kelasHistory as $h)
        <div class="tr-timeline-item">
            <div class="tr-timeline-dot"></div>
            <div class="card-tartil tr-timeline-card">
                <div class="tr-timeline-date">
                    {{ $h['tanggal']->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    <span class="tr-timeline-sem">{{ $h['semester'] }}</span>
                </div>
                <div class="tr-timeline-route">
                    <span class="tr-timeline-from">{{ $h['kelas_lama'] }}</span>
                    <span class="tr-timeline-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                    <span class="tr-timeline-to">{{ $h['kelas_baru'] }}</span>
                    <span class="badge-subject">Naik Kelas</span>
                </div>
                @if($h['alasan'] && $h['alasan'] !== '-')
                <div class="tr-timeline-note">
                    <strong>Alasan:</strong> {{ $h['alasan'] }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card-tartil tr-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        <p>Belum ada riwayat perpindahan kelas.</p>
    </div>
    @endif

    {{-- Rekap Per Semester --}}
    <div class="tr-section-header">
        <div class="tr-section-icon">&#128200;</div>
        <h3 class="tr-section-title">Rekap Performa Per Semester</h3>
    </div>

    @if(count($rekapPerSemester) > 0)
    <div class="tr-recap-grid">
        @foreach($rekapPerSemester as $r)
        @php
            $rata = $r['rata_rata'];
            $rataColor = $rata >= 80 ? '#0c8a5f' : ($rata >= 60 ? '#b45309' : '#dc2626');
            $rataBg = $rata >= 80 ? '#f0fdf4' : ($rata >= 60 ? '#fefce8' : '#fef2f2');
        @endphp
        <div class="card-tartil tr-recap-card">
            <div class="tr-recap-header">
                <div>
                    <h4 class="tr-recap-semester">{{ $r['semester']->nama }}</h4>
                    <div class="tr-recap-bulan">{{ $r['bulan_count'] }} bulan aktif</div>
                </div>
                <div class="tr-recap-score" style="background: {{ $rataBg }}; color: {{ $rataColor }};">
                    {{ $rata }}%
                </div>
            </div>

            <div class="tr-recap-progress">
                <div class="tr-recap-progress-track">
                    <div class="tr-recap-progress-fill" style="width: {{ min($rata, 100) }}%; background: {{ $rataColor }};"></div>
                </div>
                <div class="tr-recap-progress-label">Rata-rata nilai jurnal</div>
            </div>

            <div class="tr-recap-stats">
                <div class="tr-recap-stat tr-recap-stat-b">
                    <div class="tr-recap-stat-num">{{ $r['count_b'] }}</div>
                    <div class="tr-recap-stat-label">Baik</div>
                </div>
                <div class="tr-recap-stat tr-recap-stat-c">
                    <div class="tr-recap-stat-num">{{ $r['count_c'] }}</div>
                    <div class="tr-recap-stat-label">Cukup</div>
                </div>
                <div class="tr-recap-stat tr-recap-stat-k">
                    <div class="tr-recap-stat-num">{{ $r['count_k'] }}</div>
                    <div class="tr-recap-stat-label">Kurang</div>
                </div>
            </div>

            <div class="tr-recap-footer">
                Total pertemuan: <strong>{{ $r['total_hadir'] }} hari</strong>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card-tartil tr-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        <p>Belum ada data rekap performa.</p>
    </div>
    @endif
</div>

<style>
.tr-wrap { width: 100%; }

/* Profile */
.tr-profile {
    display: flex; align-items: center; gap: 20px;
    padding: 24px; margin-bottom: 24px;
}
.tr-profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, #0c8a5f, #065f43);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 28px; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(12,138,95,0.25);
}
.tr-profile-name { font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 12px; }
.tr-profile-meta { display: flex; gap: 10px; flex-wrap: wrap; }
.tr-profile-chip {
    display: flex; align-items: center; gap: 6px;
    background: #fafaf9; border: 1px solid var(--border); border-radius: 999px;
    padding: 5px 12px; font-size: 12px;
}
.tr-chip-label { color: var(--ink-muted); font-weight: 500; }
.tr-chip-value { color: var(--ink); font-weight: 700; }

/* Section header */
.tr-section-header {
    display: flex; align-items: center; gap: 10px;
    margin: 28px 0 16px;
}
.tr-section-icon {
    width: 32px; height: 32px; border-radius: 10px;
    background: #f0fdf4; color: #0c8a5f;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.tr-section-title { font-size: 16px; font-weight: 800; color: var(--ink); margin: 0; }

/* Timeline */
.tr-timeline { position: relative; padding-left: 20px; }
.tr-timeline::before {
    content: ''; position: absolute; left: 7px; top: 8px; bottom: 24px;
    width: 2px; background: linear-gradient(to bottom, #0c8a5f, #d1fae5); border-radius: 2px;
}
.tr-timeline-item { position: relative; margin-bottom: 16px; }
.tr-timeline-dot {
    position: absolute; left: -18px; top: 18px;
    width: 12px; height: 12px; border-radius: 50%;
    background: #0c8a5f; border: 2px solid #fff;
    box-shadow: 0 0 0 2px #d1fae5;
}
.tr-timeline-card { padding: 16px 18px; transition: transform 0.15s, box-shadow 0.15s; }
.tr-timeline-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
.tr-timeline-date {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    font-size: 12px; color: var(--ink-muted); margin-bottom: 8px;
}
.tr-timeline-sem {
    background: #f5f5f4; color: var(--ink-secondary);
    padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;
}
.tr-timeline-route {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    font-size: 14px; margin-bottom: 6px;
}
.tr-timeline-from { font-weight: 600; color: var(--ink-muted); }
.tr-timeline-to { font-weight: 800; color: #0c8a5f; }
.tr-timeline-arrow { color: var(--ink-muted); display: flex; align-items: center; }
.tr-timeline-note {
    font-size: 12px; color: var(--ink-muted); margin-top: 6px;
    padding-top: 8px; border-top: 1px dashed var(--border-light);
}

/* Recap cards */
.tr-recap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.tr-recap-card { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.tr-recap-header { display: flex; justify-content: space-between; align-items: flex-start; }
.tr-recap-semester { font-size: 15px; font-weight: 800; color: var(--ink); margin: 0 0 4px; }
.tr-recap-bulan { font-size: 11px; color: var(--ink-muted); font-weight: 600; }
.tr-recap-score {
    width: 56px; height: 56px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800;
}
.tr-recap-progress { margin-bottom: 4px; }
.tr-recap-progress-track {
    height: 8px; background: var(--bg-body); border-radius: 4px; overflow: hidden; margin-bottom: 6px;
}
.tr-recap-progress-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
.tr-recap-progress-label { font-size: 11px; color: var(--ink-muted); }
.tr-recap-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.tr-recap-stat { text-align: center; padding: 10px; border-radius: 10px; }
.tr-recap-stat-b { background: #f0fdf4; }
.tr-recap-stat-c { background: #fefce8; }
.tr-recap-stat-k { background: #fef2f2; }
.tr-recap-stat-num { font-size: 20px; font-weight: 800; }
.tr-recap-stat-b .tr-recap-stat-num { color: #166534; }
.tr-recap-stat-c .tr-recap-stat-num { color: #854d0e; }
.tr-recap-stat-k .tr-recap-stat-num { color: #991b1b; }
.tr-recap-stat-label { font-size: 10px; color: var(--ink-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
.tr-recap-footer { font-size: 12px; color: var(--ink-muted); text-align: center; padding-top: 10px; border-top: 1px solid var(--border-light); }
.tr-recap-footer strong { color: var(--ink); }

/* Empty state */
.tr-empty { text-align: center; padding: 48px 20px; color: var(--ink-muted); }
.tr-empty svg { margin-bottom: 16px; opacity: 0.35; }
.tr-empty p { font-size: 14px; margin: 0; line-height: 1.6; }

/* Mobile */
@media (max-width: 640px) {
    .tr-profile { flex-direction: column; text-align: center; gap: 14px; }
    .tr-profile-meta { justify-content: center; }
    .tr-recap-grid { grid-template-columns: 1fr; }
    .tr-timeline-route { font-size: 13px; }
}
</style>
@endsection
