@extends('layouts.siswa')
@section('title', 'Riwayat Kelas')

@section('content')
<div class="sp-wrap">

    {{-- Header --}}
    <div class="sp-head">
        <h1 class="sp-title">Riwayat Kelas</h1>
        <p class="sp-sub">Riwayat perpindahan kelas tartil dan kelas saat ini</p>
    </div>

    {{-- Kelas Saat Ini — SELALU tampil --}}
    <div class="sp-section">
        <h2 class="sp-section-title">Kelas Saat Ini</h2>
        <div class="sp-current">
            <div class="sp-current-item">
                <div class="sp-current-icon" style="background:#f0fdf4;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 4 3 6 3s6-1.34 6-3v-5"/></svg>
                </div>
                <div class="sp-current-info">
                    <div class="sp-current-label">Kelas Reguler</div>
                    <div class="sp-current-value">{{ auth('siswa')->user()->kelasReguler->nama ?? 'Belum ditetapkan' }}</div>
                </div>
            </div>
            <div class="sp-current-item">
                <div class="sp-current-icon" style="background:#d1fae5;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div class="sp-current-info">
                    <div class="sp-current-label">Kelas Tartil</div>
                    <div class="sp-current-value">{{ auth('siswa')->user()->kelasTartil->nama ?? 'Belum ditetapkan' }}</div>
                    @if(auth('siswa')->user()->kelasTartil)
                    <div class="sp-current-meta">{{ auth('siswa')->user()->kelasTartil->jenis ?? '' }}</div>
                    @endif
                </div>
            </div>
            <div class="sp-current-item">
                <div class="sp-current-icon" style="background:#fef9c3;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="sp-current-info">
                    <div class="sp-current-label">Guru Tartil</div>
                    <div class="sp-current-value">{{ auth('siswa')->user()->kelasTartil->guru->nama ?? 'Belum ditetapkan' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Perpindahan --}}
    <div class="sp-section">
        <h2 class="sp-section-title">Riwayat Perpindahan</h2>
        @if($perpindahans->count() > 0)
        <div class="sp-history">
            @foreach($perpindahans as $p)
            <div class="sp-history-item">
                <div class="sp-history-arrow">
                    <span class="sp-history-from">{{ $p->kelasLama->nama ?? '-' }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    <span class="sp-history-to">{{ $p->kelasBaru->nama ?? '-' }}</span>
                </div>
                <div class="sp-history-meta">
                    <span class="sp-history-sem">{{ $p->semester->tahun_ajaran ?? '' }} {{ ucfirst($p->semester->jenis ?? '') }}</span>
                    <span class="sp-history-status {{ $p->status }}">{{ ucfirst($p->status) }}</span>
                </div>
                @if($p->alasan)
                <div class="sp-history-reason">Alasan: {{ $p->alasan }}</div>
                @endif
                @if($p->catatan)
                <div class="sp-history-note">Catatan: {{ $p->catatan }}</div>
                @endif
            </div>
            @endforeach
        </div>
        {{ $perpindahans->links() }}
        @else
        <div class="sp-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
            <p>Belum ada riwayat perpindahan kelas.<br>Siswa masih berada di kelas tartil pertama.</p>
        </div>
        @endif
    </div>

</div>

<style>
.sp-wrap { width: 100%; }
.sp-head { margin-bottom: 20px; }
.sp-title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
.sp-sub { font-size: 13px; color: #78716c; margin: 4px 0 0; }

.sp-section {
    background: #fff; border: 1px solid #e7e5e4; border-radius: 12px;
    padding: 20px; margin-bottom: 16px;
}
.sp-section-title {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: #78716c; margin: 0 0 16px;
}

/* Kelas Saat Ini */
.sp-current { display: flex; flex-direction: column; gap: 12px; }
.sp-current-item { display: flex; align-items: center; gap: 14px; padding: 14px; background: #fafaf9; border-radius: 10px; }
.sp-current-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #44403c; flex-shrink: 0;
}
.sp-current-label { font-size: 11px; color: #78716c; font-weight: 500; }
.sp-current-value { font-size: 14px; font-weight: 700; color: #1c1917; }
.sp-current-meta { font-size: 11px; color: #a8a29e; margin-top: 1px; }

/* Riwayat */
.sp-history { display: flex; flex-direction: column; gap: 12px; }
.sp-history-item { padding: 16px; background: #fafaf9; border-radius: 10px; }
.sp-history-arrow { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
.sp-history-from, .sp-history-to { font-size: 14px; font-weight: 700; color: #1c1917; }
.sp-history-arrow svg { color: #a8a29e; flex-shrink: 0; }
.sp-history-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.sp-history-sem { font-size: 11px; color: #78716c; background: #f5f5f4; padding: 2px 8px; border-radius: 999px; }
.sp-history-status { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
.sp-history-status.disetujui { background: #f0fdf4; color: #166534; }
.sp-history-status.pending { background: #fef9c3; color: #854d0e; }
.sp-history-status.ditolak { background: #fef2f2; color: #991b1b; }
.sp-history-reason { font-size: 12px; color: #78716c; margin-top: 6px; }
.sp-history-note { font-size: 11px; color: #a8a29e; font-style: italic; margin-top: 4px; }

/* Empty */
.sp-empty { text-align: center; padding: 32px 20px; color: #a8a29e; }
.sp-empty svg { margin-bottom: 10px; opacity: 0.4; }
.sp-empty p { font-size: 13px; line-height: 1.6; }
</style>
@endsection
