@extends('layouts.siswa')
@section('title', 'Absensi')

@section('content')
<div class="sa-wrap">

    {{-- Header --}}
    <div class="siswa-page-header">
        <div class="siswa-page-icon" style="background: linear-gradient(135deg, #0891b2, #155e75);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <h1 class="siswa-page-title">Absensi</h1>
            <p class="siswa-page-subtitle">Riwayat kehadiran di kelas tartil</p>
        </div>
    </div>

    {{-- Filter Semester --}}
    <div class="card-tartil sa-filter-card">
        <form method="GET" action="{{ route('siswa.absensi') }}" class="sa-filter-form">
            <div class="sa-filter-group">
                <label class="sa-filter-label">Pilih Semester</label>
                <select name="semester" class="sa-filter-select" onchange="this.form.submit()">
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>
                        {{ $s->tahun_ajaran }} {{ ucfirst($s->jenis) }}
                        {{ $s->is_aktif ? '[AKTIF]' : ($s->status == 'ditutup' ? '(DITUTUP)' : '') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="sa-filter-hint">
                Pilih semester untuk melihat riwayat kehadiran pada periode tersebut.
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    @if($absensis->count() > 0)
    @php
        $hadir = $absensis->where('status', 'Hadir')->count();
        $sakit = $absensis->where('status', 'Sakit')->count();
        $izin = $absensis->where('status', 'Izin')->count();
        $alpha = $absensis->where('status', 'Alpha')->count();
    @endphp
    <div class="sa-summary">
        <div class="stat-card sa-summary-stat" style="background: #f0fdf4; border-color: #bbf7d0;">
            <div class="stat-value" style="color: #166534;">{{ $hadir }}</div>
            <div class="stat-label" style="color: #166534;">Hadir</div>
        </div>
        <div class="stat-card sa-summary-stat" style="background: #eff6ff; border-color: #bfdbfe;">
            <div class="stat-value" style="color: #1e40af;">{{ $sakit }}</div>
            <div class="stat-label" style="color: #1e40af;">Sakit</div>
        </div>
        <div class="stat-card sa-summary-stat" style="background: #fefce8; border-color: #fde68a;">
            <div class="stat-value" style="color: #854d0e;">{{ $izin }}</div>
            <div class="stat-label" style="color: #854d0e;">Izin</div>
        </div>
        <div class="stat-card sa-summary-stat" style="background: #fef2f2; border-color: #fecaca;">
            <div class="stat-value" style="color: #991b1b;">{{ $alpha }}</div>
            <div class="stat-label" style="color: #991b1b;">Alpha</div>
        </div>
    </div>
    @endif

    {{-- Daftar Absensi --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        @forelse($absensis as $a)
        <div class="sa-item">
            <div class="sa-item-main">
                <div class="sa-item-date">
                    <div class="sa-item-day">{{ $a->tanggal->format('d') }}</div>
                    <div class="sa-item-month">{{ $a->tanggal->format('M Y') }}</div>
                </div>
                <div class="sa-item-body">
                    <div class="sa-item-title">{{ $a->tanggal->translatedFormat('l') }}</div>
                    <div class="sa-item-sub">Kelas: {{ $a->kelas->nama ?? '-' }}</div>
                </div>
            </div>
            <span class="sa-status {{ strtolower($a->status) }}">
                {{ $a->status }}
            </span>
        </div>
        @empty
        <div class="sa-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <p>Belum ada data absensi untuk semester ini.</p>
        </div>
        @endforelse
    </div>

    {{ $absensis->links() }}
</div>

<style>
.sa-wrap { width: 100%; }
.sa-header { margin-bottom: 20px; }

.sa-filter-card { padding: 18px 20px; margin-bottom: 16px; }
.sa-filter-form { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: space-between; }
.sa-filter-group { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 220px; }
.sa-filter-label { font-size: 12px; font-weight: 700; color: var(--ink-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.sa-filter-select {
    width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);
    font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ink);
    background: var(--bg-card); outline: none; cursor: pointer;
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2378716c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
    padding-right: 40px;
}
.sa-filter-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(12,138,95,0.08); }
.sa-filter-hint { font-size: 12px; color: var(--ink-muted); max-width: 300px; line-height: 1.5; }

.sa-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.sa-summary-stat { padding: 16px 10px; }

.sa-item {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px 18px; border-bottom: 1px solid var(--border-light);
    transition: background 0.15s;
}
.sa-item:last-child { border-bottom: none; }
.sa-item:hover { background: #fafaf9; }
.sa-item-main { display: flex; align-items: center; gap: 14px; flex: 1; }
.sa-item-date {
    width: 48px; height: 48px; border-radius: 10px; background: #f5f5f4;
    display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;
}
.sa-item-day { font-size: 18px; font-weight: 800; color: var(--ink); line-height: 1; }
.sa-item-month { font-size: 9px; font-weight: 700; color: var(--ink-muted); text-transform: uppercase; }
.sa-item-title { font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.sa-item-sub { font-size: 12px; color: var(--ink-muted); }
.sa-status {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 5px 12px; border-radius: 999px;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.sa-status.hadir { background: #f0fdf4; color: #166534; }
.sa-status.sakit { background: #eff6ff; color: #1e40af; }
.sa-status.izin { background: #fefce8; color: #854d0e; }
.sa-status.alpha { background: #fef2f2; color: #991b1b; }

.sa-empty { text-align: center; padding: 48px 20px; color: var(--ink-muted); }
.sa-empty svg { margin-bottom: 16px; opacity: 0.35; }
.sa-empty p { font-size: 14px; margin: 0; }

@media (max-width: 640px) {
    .sa-filter-form { flex-direction: column; align-items: stretch; }
    .sa-filter-hint { max-width: 100%; }
    .sa-summary { grid-template-columns: repeat(2, 1fr); }
    .sa-item { align-items: flex-start; }
    .sa-item-date { width: 44px; height: 44px; }
    .sa-item-day { font-size: 16px; }
}
</style>
@endsection
