@extends('layouts.admin')

@section('title', 'System Setup')

@section('content')
@php
    use App\Services\PrecalculateReminderService;
    $lastTime = PrecalculateReminderService::getLastPrecalculateTime();
    $cachedCount = PrecalculateReminderService::getCachedCount();
    $totalSiswa = PrecalculateReminderService::getTotalSiswaAktif();
    $totalJurnal = 0;
    try { $totalJurnal = \Illuminate\Support\Facades\DB::table('jurnal_harians')->count(); } catch(\Throwable $e) {}
    $totalNilai = 0;
    try { $totalNilai = \Illuminate\Support\Facades\DB::table('penilaian_rapor_nilais')->whereNotNull('nilai')->count(); } catch(\Throwable $e) {}
    $coverage = $totalSiswa > 0 ? round(($cachedCount / $totalSiswa) * 100) : 0;
@endphp

<div class="su-wrap">

    {{-- Header --}}
    <div class="su-head">
        <h1 class="su-title">System Setup</h1>
        <p class="su-sub">Konfigurasi sistem dan manajemen cache. Halaman ini tidak tampil di menu sidebar.</p>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="su-alert su-alert-ok">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div class="su-alert su-alert-warn">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="su-alert su-alert-err">{{ session('error') }}</div>
    @endif

    {{-- Stats Row --}}
    <div class="su-stats">
        <div class="su-stat">
            <span class="su-stat-num">{{ number_format($totalSiswa) }}</span>
            <span class="su-stat-label">Siswa Aktif</span>
        </div>
        <div class="su-stat">
            <span class="su-stat-num">{{ number_format($totalJurnal) }}</span>
            <span class="su-stat-label">Jurnal Harian</span>
        </div>
        <div class="su-stat">
            <span class="su-stat-num">{{ number_format($totalNilai) }}</span>
            <span class="su-stat-label">Nilai Rapor</span>
        </div>
        <div class="su-stat">
            <span class="su-stat-num">{{ number_format($cachedCount) }}</span>
            <span class="su-stat-label">R2 Cached</span>
        </div>
    </div>

    {{-- Precalculate Section --}}
    <div class="su-card">
        <div class="su-card-head">
            <div>
                <h2 class="su-card-title">R2 Precalculate</h2>
                <p class="su-card-desc">Hitung ulang cache R2 untuk performa optimal di menu krusial.</p>
            </div>
            <form action="{{ route('admin.system.r2-precalculate') }}" method="POST">
                @csrf
                <input type="hidden" name="async" value="0">
                <button type="submit" class="su-btn su-btn-primary" id="btnPrecalc">
                    <span class="btn-txt">Precalculate</span>
                    <span class="btn-load d-none"></span>
                </button>
            </form>
        </div>
        <div class="su-progress-track">
            <div class="su-progress-fill" style="width:{{ $coverage }}%"></div>
        </div>
        <div class="su-progress-meta">
            <span>{{ $coverage }}% cached ({{ number_format($cachedCount) }} / {{ number_format($totalSiswa) }} siswa)</span>
            @if($lastTime)
            <span>Terakhir: {{ $lastTime->format('d M Y H:i') }} &middot; {{ $lastTime->diffForHumans() }}</span>
            @else
            <span>Belum pernah</span>
            @endif
        </div>
    </div>

    {{-- Grid 2 col --}}
    <div class="su-grid">

        {{-- Cache Management --}}
        <div class="su-card">
            <h2 class="su-card-title">Cache</h2>
            <p class="su-card-desc">Clear cache aplikasi setelah perubahan konfigurasi.</p>
            <div class="su-actions">
                <form action="{{ route('admin.system.clear-cache') }}" method="POST">@csrf<button type="submit" class="su-btn su-btn-ghost">Clear All Cache</button></form>
                <form action="{{ route('admin.system.optimize') }}" method="POST">@csrf<button type="submit" class="su-btn su-btn-ghost">Optimize</button></form>
            </div>
        </div>

        {{-- Artisan Command --}}
        <div class="su-card">
            <h2 class="su-card-title">Artisan Command</h2>
            <p class="su-card-desc">Jalankan command artisan terbatas dari browser.</p>
            <form action="{{ route('admin.system.artisan') }}" method="POST" class="su-artisan">
                @csrf
                <select name="command" class="su-select" required>
                    <option value="">Pilih command...</option>
                    <optgroup label="Cache">
                        <option value="cache:clear">cache:clear</option>
                        <option value="config:cache">config:cache</option>
                        <option value="route:cache">route:cache</option>
                        <option value="view:cache">view:cache</option>
                    </optgroup>
                    <optgroup label="Database">
                        <option value="migrate">migrate --force</option>
                        <option value="migrate:status">migrate:status</option>
                    </optgroup>
                    <optgroup label="R2">
                        <option value="r2:precalculate">r2:precalculate</option>
                    </optgroup>
                </select>
                <button type="submit" class="su-btn su-btn-ghost">Jalankan</button>
            </form>
        </div>

        {{-- Full Setup --}}
        <div class="su-card">
            <h2 class="su-card-title">Full Setup</h2>
            <p class="su-card-desc">Jalankan semua migrasi dan setup tabel. Aman dijalankan ulang.</p>
            <form action="{{ route('admin.system.setup.run') }}" method="POST"
                  onsubmit="return confirm('Jalankan full setup? Ini akan menjalankan migrasi dan setup semua tabel.')">
                @csrf
                <button type="submit" class="su-btn su-btn-warn">Run Full Setup</button>
            </form>
        </div>

        {{-- Reset R2 --}}
        <div class="su-card">
            <h2 class="su-card-title">Reset R2 Cache</h2>
            <p class="su-card-desc">Hapus semua cache R2 dan hitung ulang dari awal.</p>
            <form action="{{ route('admin.system.r2-reset') }}" method="POST"
                  onsubmit="return confirm('Yakin reset dan hitung ulang semua R2?')">
                @csrf
                <button type="submit" class="su-btn su-btn-danger">Reset & Hitung Ulang</button>
            </form>
        </div>

    </div>

</div>

<style>
.su-wrap { max-width: 960px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Header */
.su-head { margin-bottom: 28px; }
.su-title { font-size: 24px; font-weight: 700; color: #171717; margin: 0; letter-spacing: -0.5px; }
.su-sub { font-size: 13px; color: #737373; margin: 4px 0 0; }

/* Alert */
.su-alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
.su-alert-ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.su-alert-warn { background: #fefce8; color: #854d0e; border: 1px solid #fde68a; }
.su-alert-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Stats */
.su-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.su-stat { background: #fff; border: 1px solid #e5e5e5; border-radius: 10px; padding: 16px; text-align: center; }
.su-stat-num { display: block; font-size: 22px; font-weight: 700; color: #171717; line-height: 1.2; }
.su-stat-label { display: block; font-size: 11px; color: #737373; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

@media (max-width: 768px) { .su-stats { grid-template-columns: repeat(2, 1fr); } }

/* Card */
.su-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 10px; padding: 20px; }
.su-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
.su-card-title { font-size: 14px; font-weight: 600; color: #171717; margin: 0; }
.su-card-desc { font-size: 12px; color: #737373; margin: 2px 0 0; }

/* Progress */
.su-progress-track { height: 5px; background: #e5e5e5; border-radius: 3px; overflow: hidden; }
.su-progress-fill { height: 100%; background: #171717; border-radius: 3px; transition: width 0.4s ease; }
.su-progress-meta { display: flex; justify-content: space-between; font-size: 11px; color: #a3a3a3; margin-top: 6px; }

/* Grid */
.su-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px; }
@media (max-width: 768px) { .su-grid { grid-template-columns: 1fr; } }

/* Buttons */
.su-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 16px; border-radius: 6px; border: 1px solid transparent; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.15s; line-height: 1.5; white-space: nowrap; background: none; }
.su-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.su-btn-primary { background: #171717; color: #fff; }
.su-btn-primary:hover { background: #404040; }
.su-btn-ghost { background: #fff; color: #525252; border-color: #d4d4d4; }
.su-btn-ghost:hover { background: #f5f5f5; }
.su-btn-warn { background: #fff; color: #a16207; border-color: #d4d4d4; }
.su-btn-warn:hover { background: #fefce8; border-color: #ca8a04; }
.su-btn-danger { background: #fff; color: #dc2626; border-color: #d4d4d4; }
.su-btn-danger:hover { background: #fef2f2; border-color: #dc2626; }

/* Actions */
.su-actions { display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap; }

/* Select */
.su-select { padding: 6px 10px; border-radius: 6px; border: 1px solid #d4d4d4; font-size: 12px; color: #171717; background: #fff; outline: none; }
.su-select:focus { border-color: #a3a3a3; }
.su-artisan { display: flex; gap: 8px; margin-top: 14px; align-items: center; flex-wrap: wrap; }

/* Loading spinner */
.btn-load { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: su-spin 0.6s linear infinite; }
@keyframes su-spin { to { transform: rotate(360deg); } }
.d-none { display: none; }
</style>

<script>
(function(){
    var btn = document.getElementById('btnPrecalc');
    if(btn) btn.closest('form').addEventListener('submit', function(){
        btn.disabled = true;
        btn.querySelector('.btn-txt').classList.add('d-none');
        btn.querySelector('.btn-load').classList.remove('d-none');
    });
})();
</script>
@endsection
