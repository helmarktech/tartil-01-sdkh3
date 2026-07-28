@php
    use App\Services\PrecalculateReminderService;
    use Illuminate\Support\Facades\DB;

    $lastTime = PrecalculateReminderService::getLastPrecalculateTime();
    $cachedCount = PrecalculateReminderService::getCachedCount();
    $totalSiswa = PrecalculateReminderService::getTotalSiswaAktif();

    $totalJurnal = 0;
    $totalNilaiRapor = 0;
    try {
        $totalJurnal = DB::table('jurnal_harians')->count();
        $totalNilaiRapor = DB::table('penilaian_rapor_nilais')->whereNotNull('nilai')->count();
    } catch (\Throwable $e) {}

    $coverage = $totalSiswa > 0 ? min(100, round(($cachedCount / $totalSiswa) * 100)) : 0;

    if (!$lastTime) {
        $status = 'none';
        $dotColor = '#dc2626';
        $barColor = 'linear-gradient(90deg, #dc2626, #ef4444)';
        $btnClass = 'btn-pc-danger';
        $btnText = 'Precalculate';
        $pctBadge = '<span class="pc-pct pc-pct-zero">0%</span>';
        $statusLabel = 'Belum pernah';
        $statusBg = '#fef2f2';
    } else {
        $diffHours = $lastTime->diffInHours(now());
        if ($diffHours < 6) {
            $status = 'fresh';
            $dotColor = '#16a34a';
            $barColor = 'linear-gradient(90deg, #16a34a, #22c55e)';
            $btnClass = 'btn-pc-ghost';
            $btnText = 'Hitung Ulang';
            $pctBadge = '<span class="pc-pct pc-pct-ok">Fresh</span>';
            $statusLabel = 'Aktual';
            $statusBg = '#f0fdf4';
        } elseif ($diffHours < 72) {
            $status = 'aging';
            $dotColor = '#ca8a04';
            $barColor = 'linear-gradient(90deg, #ca8a04, #eab308)';
            $btnClass = 'btn-pc-warn';
            $btnText = 'Precalculate';
            $pctBadge = '<span class="pc-pct pc-pct-warn">Aging</span>';
            $statusLabel = 'Perlu update';
            $statusBg = '#fefce8';
        } else {
            $status = 'stale';
            $dotColor = '#dc2626';
            $barColor = 'linear-gradient(90deg, #dc2626, #ef4444)';
            $btnClass = 'btn-pc-danger';
            $btnText = 'Precalculate';
            $pctBadge = '<span class="pc-pct pc-pct-zero">Stale</span>';
            $statusLabel = 'Sangat basi';
            $statusBg = '#fef2f2';
        }
    }
@endphp

<div class="pc-wrap">
    {{-- Header: Status + Buttons --}}
    <div class="pc-header">
        <div class="pc-status">
            <div class="pc-dot" style="background:{{ $dotColor }};"></div>
            <div>
                <span class="pc-time">
                    @if($lastTime)
                        {{ $lastTime->format('d M Y \p\k\l H:i') }}
                        <span class="pc-ago">({{ $lastTime->diffForHumans() }})</span>
                    @else
                        Belum pernah
                    @endif
                </span>
                <span class="pc-status-badge" style="background:{{ $statusBg }};color:{{ $dotColor }};">{{ $statusLabel }}</span>
            </div>
        </div>
        <div class="pc-actions">
            <button type="button" class="btn-pc btn-pc-detail" onclick="document.getElementById('pcReportModal').style.display='flex';">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Detail
            </button>
            <button type="button" class="btn-pc {{ $btnClass }}" id="btnPcTrigger" onclick="openPcConfirmModal();">
                <span class="pc-txt">{{ $btnText }}</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </button>
        </div>
    </div>

    {{-- Progress Bar Modern --}}
    <div class="pc-bar-wrap">
        <div class="pc-bar-track">
            <div class="pc-bar-fill" style="width:{{ $coverage }}%;background:{{ $barColor }};">
                <div class="pc-bar-shine"></div>
            </div>
        </div>
        <div class="pc-bar-pct" style="left:{{ min(92, max(8, $coverage)) }}%;">{{ $coverage }}%</div>
    </div>
    <div class="pc-bar-meta">
        <span>Cache Coverage</span>
        <span>{{ $cachedCount }} / {{ $totalSiswa }} siswa</span>
    </div>

    {{-- Stats Grid --}}
    <div class="pc-grid">
        <div class="pc-item" onclick="document.getElementById('pcReportModal').style.display='flex';" style="cursor:pointer;">
            <div class="pc-num {{ $cachedCount > 0 ? 'pc-num-ok' : '' }}">{{ number_format($cachedCount) }}</div>
            <div class="pc-label">R2 Cached</div>
        </div>
        <div class="pc-item" onclick="document.getElementById('pcReportModal').style.display='flex';" style="cursor:pointer;">
            <div class="pc-num {{ $totalSiswa > 0 ? 'pc-num-ok' : '' }}">{{ number_format($totalSiswa) }}</div>
            <div class="pc-label">Siswa Aktif</div>
        </div>
        <div class="pc-item" onclick="document.getElementById('pcReportModal').style.display='flex';" style="cursor:pointer;">
            <div class="pc-num {{ $totalJurnal > 0 ? 'pc-num-ok' : '' }}">{{ number_format($totalJurnal) }}</div>
            <div class="pc-label">Jurnal Harian</div>
        </div>
        <div class="pc-item" onclick="document.getElementById('pcReportModal').style.display='flex';" style="cursor:pointer;">
            <div class="pc-num {{ $totalNilaiRapor > 0 ? 'pc-num-ok' : '' }}">{{ number_format($totalNilaiRapor) }}</div>
            <div class="pc-label">Input Indikator</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
   POPUP: LAPORAN DETAIL PRECACULATE
   ════════════════════════════════════════════ --}}
<div id="pcReportModal" class="pc-modal-backdrop" style="display:none;">
    <div class="pc-modal-card animate-pop-in">
        <div class="pc-modal-header" style="background:{{ $barColor }};">
            <div class="pc-modal-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <h3 class="pc-modal-title">Laporan Precalculate</h3>
            <p class="pc-modal-subtitle">Status cache R2 sistem</p>
        </div>
        <div class="pc-modal-body">
            {{-- Status Banner --}}
            <div class="pc-modal-banner" style="background:{{ $statusBg }};color:{{ $dotColor }};">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                @if($status == 'none')
                    Precalculate belum pernah dilakukan. Sistem perlu menghitung R2 untuk semua siswa.
                @elseif($status == 'fresh')
                    Cache masih aktual. Data R2 terhitung {{ $lastTime->diffForHumans() }}.
                @elseif($status == 'aging')
                    Cache sudah {{ $lastTime->diffForHumans() }}. Disarankan precalculate ulang.
                @else
                    Cache sangat basi (>3 hari). Precalculate segera untuk data akurat.
                @endif
            </div>

            {{-- Stats Cards --}}
            <div class="pc-modal-stats">
                <div class="pc-modal-stat">
                    <div class="pc-modal-stat-num" style="color:{{ $dotColor }};">{{ number_format($cachedCount) }}</div>
                    <div class="pc-modal-stat-label">R2 Cached</div>
                </div>
                <div class="pc-modal-stat">
                    <div class="pc-modal-stat-num">{{ number_format($totalSiswa) }}</div>
                    <div class="pc-modal-stat-label">Siswa Aktif</div>
                </div>
                <div class="pc-modal-stat">
                    <div class="pc-modal-stat-num">{{ number_format($totalJurnal) }}</div>
                    <div class="pc-modal-stat-label">Jurnal</div>
                </div>
                <div class="pc-modal-stat">
                    <div class="pc-modal-stat-num">{{ number_format($totalNilaiRapor) }}</div>
                    <div class="pc-modal-stat-label">Indikator</div>
                </div>
            </div>

            {{-- Coverage Bar --}}
            <div class="pc-modal-bar-wrap">
                <div class="pc-modal-bar-label">
                    <span>Coverage Cache</span>
                    <span style="color:{{ $dotColor }};font-weight:700;">{{ $coverage }}%</span>
                </div>
                <div class="pc-modal-bar-track">
                    <div class="pc-modal-bar-fill" style="width:{{ $coverage }}%;background:{{ $barColor }};"></div>
                </div>
                <div class="pc-modal-bar-hint">
                    {{ $cachedCount }} dari {{ $totalSiswa }} siswa sudah memiliki cache R2
                </div>
            </div>

            {{-- Last Calculate Info --}}
            @if($lastTime)
            <div class="pc-modal-last">
                <div class="pc-modal-last-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="pc-modal-last-label">Terakhir dihitung</div>
                    <div class="pc-modal-last-value">{{ $lastTime->format('d F Y \p\k\l H:i') }} ({{ $lastTime->diffForHumans() }})</div>
                </div>
            </div>
            @endif
        </div>
        <div class="pc-modal-footer">
            <button type="button" class="btn-pc btn-pc-ghost" onclick="document.getElementById('pcReportModal').style.display='none';">Tutup</button>
            <button type="button" class="btn-pc {{ $btnClass }}" onclick="document.getElementById('pcReportModal').style.display='none';openPcConfirmModal();">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                {{ $btnText }}
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
   POPUP: KONFIRMASI PRECACULATE
   ════════════════════════════════════════════ --}}
<div id="pcConfirmModal" class="pc-modal-backdrop" style="display:none;">
    <div class="pc-modal-card animate-pop-in">
        <div class="pc-modal-header" style="background:{{ $barColor }};">
            <div class="pc-modal-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <h3 class="pc-modal-title">{{ $btnText }}</h3>
            <p class="pc-modal-subtitle">Konfirmasi perhitungan R2</p>
        </div>
        <div class="pc-modal-body">
            <div class="pc-confirm-list">
                <div class="pc-confirm-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Menghitung R2 Harian dari {{ number_format($totalJurnal) }} jurnal</span>
                </div>
                <div class="pc-confirm-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Menghitung R2 Penilaian dari {{ number_format($totalNilaiRapor) }} input indikator</span>
                </div>
                <div class="pc-confirm-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Menghitung R2 Akhir untuk {{ number_format($totalSiswa) }} siswa aktif</span>
                </div>
                <div class="pc-confirm-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Membersihkan cache lama dan menyimpan hasil baru</span>
                </div>
            </div>
            @if($status == 'fresh')
            <div class="pc-modal-banner" style="background:#f0fdf4;color:#166534;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Cache masih aktual. Hitung ulang hanya jika ada data baru yang signifikan.
            </div>
            @endif
        </div>
        <div class="pc-modal-footer">
            <button type="button" class="btn-pc btn-pc-ghost" onclick="document.getElementById('pcConfirmModal').style.display='none';">Batal</button>
            <form action="{{ route('admin.system.r2-precalculate') }}" method="POST" style="margin:0;" id="pcConfirmForm">
                @csrf
                <input type="hidden" name="async" value="0">
                <button type="submit" class="btn-pc {{ $btnClass }}" id="btnPcConfirm">
                    <span class="pc-txt">{{ $btnText }}</span>
                    <span class="pc-spin d-none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4m0 12v4m-6-6H2m20 0h-4m-1.5-5.5L17 12m-10 0-1.5 1.5M12 6a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* ═══ Info Card ═══ */
.pc-wrap {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.pc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}
.pc-status {
    display: flex;
    align-items: center;
    gap: 10px;
}
.pc-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    animation: pc-pulse 2s infinite;
}
@keyframes pc-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.pc-time {
    font-size: 13px;
    font-weight: 600;
    color: #171717;
}
.pc-ago {
    font-weight: 400;
    color: #737373;
    margin-left: 2px;
}
.pc-status-badge {
    display: inline-flex;
    padding: 1px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 6px;
}
.pc-actions {
    display: flex;
    gap: 6px;
}

/* Buttons */
.btn-pc {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    line-height: 1.5;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn-pc:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-pc svg { vertical-align: middle; }
.btn-pc-danger { background: #dc2626; color: #fff; }
.btn-pc-danger:hover { background: #b91c1c; }
.btn-pc-warn { background: #fefce8; color: #854d0e; border-color: #fde68a; }
.btn-pc-warn:hover { background: #fef9c3; border-color: #ca8a04; }
.btn-pc-ghost { background: #fff; color: #525252; border-color: #e7e5e4; }
.btn-pc-ghost:hover { background: #f5f5f4; }
.btn-pc-detail { background: #f5f5f4; color: #525252; border-color: #e7e5e4; }
.btn-pc-detail:hover { background: #e7e5e4; }

/* Progress Bar Modern */
.pc-bar-wrap { position: relative; margin-bottom: 4px; }
.pc-bar-track {
    height: 8px;
    background: #f0f0f0;
    border-radius: 999px;
    overflow: hidden;
}
.pc-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.pc-bar-shine {
    position: absolute;
    top: 0; left: -100%; width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: pc-shine 2.5s infinite;
}
@keyframes pc-shine {
    0% { left: -60%; }
    100% { left: 160%; }
}
.pc-bar-pct {
    position: absolute;
    top: -20px;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    background: #171717;
    padding: 2px 8px;
    border-radius: 4px;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}
.pc-wrap:hover .pc-bar-pct { opacity: 1; }
.pc-bar-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #a3a3a3;
    margin-bottom: 14px;
}

/* Detail grid */
.pc-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}
.pc-item {
    background: #fafaf9;
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    padding: 14px 8px;
    text-align: center;
    transition: all 0.15s;
}
.pc-item:hover { background: #f5f5f4; border-color: #e7e5e4; transform: translateY(-1px); }
.pc-num {
    font-size: 20px;
    font-weight: 800;
    color: #a3a3a3;
    line-height: 1.2;
    transition: color 0.3s;
}
.pc-num-ok { color: #171717; }
.pc-label {
    font-size: 11px;
    color: #78716c;
    margin-top: 4px;
    font-weight: 500;
}

/* ═══ Modal Backdrop ═══ */
.pc-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: pc-fadeIn 0.2s ease;
}
@keyframes pc-fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Modal Card */
.pc-modal-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 25px 60px -15px rgba(0,0,0,0.25);
    max-width: 460px;
    width: 100%;
    overflow: hidden;
    animation: pc-popIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes pc-popIn {
    from { opacity: 0; transform: scale(0.88) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.pc-modal-header {
    padding: 24px 24px 18px;
    text-align: center;
    color: #fff;
    position: relative;
}
.pc-modal-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.pc-modal-title { font-size: 17px; font-weight: 700; margin: 0 0 3px; }
.pc-modal-subtitle { font-size: 13px; opacity: 0.85; margin: 0; }

/* Modal Body */
.pc-modal-body { padding: 20px 24px; }
.pc-modal-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
    margin-bottom: 16px;
}
.pc-modal-banner svg { flex-shrink: 0; margin-top: 1px; }

/* Stats */
.pc-modal-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}
.pc-modal-stat {
    text-align: center;
    padding: 12px 6px;
    background: #fafaf9;
    border-radius: 10px;
}
.pc-modal-stat-num { font-size: 18px; font-weight: 800; color: #171717; line-height: 1.2; }
.pc-modal-stat-label { font-size: 10px; color: #78716c; margin-top: 3px; font-weight: 500; }

/* Coverage Bar in Modal */
.pc-modal-bar-wrap { margin-bottom: 16px; }
.pc-modal-bar-label { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #44403c; margin-bottom: 6px; }
.pc-modal-bar-track { height: 8px; background: #f0f0f0; border-radius: 999px; overflow: hidden; }
.pc-modal-bar-fill { height: 100%; border-radius: 999px; transition: width 0.8s ease; }
.pc-modal-bar-hint { font-size: 11px; color: #a3a3a3; margin-top: 5px; }

/* Last calculate */
.pc-modal-last {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #fafaf9;
    border-radius: 10px;
}
.pc-modal-last-icon { width: 32px; height: 32px; border-radius: 8px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pc-modal-last-label { font-size: 10px; color: #78716c; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.pc-modal-last-value { font-size: 12px; color: #171717; font-weight: 600; }

/* Confirm items */
.pc-confirm-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
.pc-confirm-item { display: flex; align-items: flex-start; gap: 10px; font-size: 12px; color: #44403c; line-height: 1.5; }
.pc-confirm-item svg { flex-shrink: 0; margin-top: 1px; color: #16a34a; }

/* Modal Footer */
.pc-modal-footer {
    padding: 14px 24px 20px;
    display: flex;
    gap: 8px;
    border-top: 1px solid #f0f0f0;
    justify-content: flex-end;
}

/* Loading spin */
.pc-spin svg { animation: pc-spin-anim 1s linear infinite; }
@keyframes pc-spin-anim { to { transform: rotate(360deg); } }
.d-none { display: none !important; }
</style>

<script>
function openPcConfirmModal() {
    document.getElementById('pcConfirmModal').style.display = 'flex';
}

// Close modal on backdrop click
document.querySelectorAll('.pc-modal-backdrop').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});

// Loading state saat submit confirm
(function(){
    var form = document.getElementById('pcConfirmForm');
    var btn = document.getElementById('btnPcConfirm');
    if(form && btn) {
        form.addEventListener('submit', function(){
            btn.disabled = true;
            btn.querySelector('.pc-txt').classList.add('d-none');
            btn.querySelector('.pc-spin').classList.remove('d-none');
        });
    }
})();
</script>
