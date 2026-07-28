@extends('layouts.siswa')

@section('title', 'Rapor Saya')

@section('content')
<div class="sr-wrap">

    {{-- Header --}}
    <div class="sr-head">
        <h1 class="sr-title">Rapor Saya</h1>
        <p class="sr-sub">Unduh rapor hasil belajar per semester yang telah ditutup</p>
    </div>

    {{-- Daftar Rapor --}}
    @if(count($raporList) > 0)
    <div class="sr-list">
        @foreach($raporList as $r)
        <div class="sr-card">
            {{-- Badge Semester --}}
            <div class="sr-card-badge">{{ $r['semester']->tahun_ajaran }} {{ ucfirst($r['semester']->jenis) }}</div>

            {{-- Info Grid — Data murni dari rekap terkunci --}}
            <div class="sr-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="sr-stat">
                    <div class="sr-stat-num {{ $r['r2_akhir'] >= 70 ? 'good' : ($r['r2_akhir'] >= 60 ? 'mid' : 'low') }}">
                        {{ $r['r2_akhir'] }}
                    </div>
                    <div class="sr-stat-label">R2 Akhir</div>
                </div>
                <div class="sr-stat">
                    <div class="sr-stat-num">{{ $r['r2_harian'] }}</div>
                    <div class="sr-stat-label">R2 Harian</div>
                </div>
                <div class="sr-stat">
                    <div class="sr-stat-num">{{ $r['r2_penilaian'] }}</div>
                    <div class="sr-stat-label">R2 Penilaian</div>
                </div>
            </div>

            {{-- Predikat --}}
            <div class="sr-predikat {{ match(true) { $r['r2_akhir'] >= 85 => 'a', $r['r2_akhir'] >= 70 => 'b', $r['r2_akhir'] >= 60 => 'c', default => 'd' } }}">
                {{ $r['predikat'] }}
            </div>

            {{-- Lihat & Download Rapor --}}
            <a href="{{ route('siswa.rapor', ['semester_id' => $r['semester']->id]) }}" class="sr-download" target="_blank" rel="noopener">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Lihat Rapor PDF
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div class="sr-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
        <h3>Belum Ada Rapor</h3>
        <p>Rapor akan tersedia setelah semester ditutup oleh admin.<br>Semester yang sedang berlangsung belum dapat diunduh.</p>
    </div>
    @endif

</div>

<style>
/* sr-wrap — mengikuti lebar .tartil-content, tidak pakai max-width sendiri */
.sr-wrap { width: 100%; }
.sr-head { margin-bottom: 24px; }
.sr-title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
.sr-sub { font-size: 13px; color: #78716c; margin: 4px 0 0; }

.sr-list { display: flex; flex-direction: column; gap: 16px; }

.sr-card {
    background: #fff; border: 1px solid #e7e5e4; border-radius: 14px;
    padding: 24px; position: relative;
    transition: all 0.2s;
}
.sr-card:hover { border-color: #d6d3d1; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }

.sr-card-badge {
    position: absolute; top: -1px; right: 20px;
    background: #1c1917; color: #fff;
    padding: 4px 14px; border-radius: 0 0 10px 10px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
}

.sr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px; }
@media (max-width: 560px) { .sr-grid { grid-template-columns: repeat(2, 1fr); } }

.sr-stat { text-align: center; }
.sr-stat-num { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: #1c1917; }
.sr-stat-num.good { color: #166534; }
.sr-stat-num.mid { color: #854d0e; }
.sr-stat-num.low { color: #991b1b; }
.sr-stat-label { font-size: 11px; color: #78716c; margin-top: 2px; }

.sr-bck-mini { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.sr-bck-tag { padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
.sr-bck-tag-b { background: #f0fdf4; color: #166534; }
.sr-bck-tag-c { background: #fefce8; color: #854d0e; }
.sr-bck-tag-k { background: #fef2f2; color: #991b1b; }

.sr-predikat { padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; margin-bottom: 16px; text-align: center; }
.sr-predikat.a { background: #f0fdf4; color: #166534; }
.sr-predikat.b { background: #e0f2fe; color: #075985; }
.sr-predikat.c { background: #fefce8; color: #854d0e; }
.sr-predikat.d { background: #fef2f2; color: #991b1b; }

.sr-download {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; background: #1c1917; color: #fff;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all 0.15s;
    width: 100%; justify-content: center;
}
.sr-download:hover { background: #44403c; transform: translateY(-1px); }

.sr-empty { text-align: center; padding: 60px 20px; color: #a8a29e; }
.sr-empty svg { margin-bottom: 16px; opacity: 0.3; }
.sr-empty h3 { font-size: 16px; color: #44403c; margin-bottom: 8px; }
.sr-empty p { font-size: 13px; line-height: 1.6; }
</style>
@endsection
