{{-- 
    Precalculate Reminder Modal
    Muncul otomatis di menu krusial kalau cache R2 sudah basi atau belum ada.
    Cara pakai: include di layout utama atau blade menu krusial.
--}}

@php
    use App\Services\PrecalculateReminderService;
    $popup = PrecalculateReminderService::getPopupData();
@endphp

@if($popup['show'] ?? false)
@php $menu = $popup['menu']; @endphp

{{-- Backdrop --}}
<div id="precalculateModal" class="precalculate-modal-backdrop" style="display: flex;">
    {{-- Modal Card --}}
    <div class="precalculate-modal-card animate-pop-in">
        
        {{-- Header dengan gradient --}}
        <div class="precalculate-modal-header bg-{{ $menu['warna'] }}">
            <div class="precalculate-modal-icon">
                <i class="bi bi-{{ $menu['icon'] }}"></i>
            </div>
            <h5 class="precalculate-modal-title">
                <i class="bi bi-lightning-charge-fill me-2"></i>Precalculate Diperlukan
            </h5>
            <p class="precalculate-modal-subtitle mb-0">Menu: {{ $menu['label'] }}</p>
        </div>

        {{-- Body --}}
        <div class="precalculate-modal-body">
            {{-- Deskripsi --}}
            <p class="text-muted mb-3">{{ $menu['deskripsi'] }}</p>

            {{-- Info Box --}}
            <div class="precalculate-info-box">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="precalculate-stat">
                            <div class="precalculate-stat-value text-{{ $popup['needs_precalculate'] ? 'danger' : 'success' }}">
                                {!! $popup['last_precalculate_html'] !!}
                            </div>
                            <div class="precalculate-stat-label">Terakhir Precalculate</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="precalculate-stat">
                            <div class="precalculate-stat-value text-primary">
                                {{ number_format($popup['cached_count']) }}
                            </div>
                            <div class="precalculate-stat-label">Siswa Ter-cache</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="precalculate-stat">
                            <div class="precalculate-stat-value text-dark">
                                {{ number_format($popup['total_siswa']) }}
                            </div>
                            <div class="precalculate-stat-label">Total Siswa Aktif</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress bar coverage --}}
            @php
                $coverage = $popup['total_siswa'] > 0 
                    ? round(($popup['cached_count'] / $popup['total_siswa']) * 100) 
                    : 0;
                $progressColor = $coverage >= 90 ? 'success' : ($coverage >= 50 ? 'warning' : 'danger');
            @endphp
            <div class="mt-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Coverage Cache</small>
                    <small class="fw-bold text-{{ $progressColor }}">{{ $coverage }}%</small>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                         style="width: {{ $coverage }}%" 
                         aria-valuenow="{{ $coverage }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>

            {{-- Warning message --}}
            @if($popup['needs_precalculate'] ?? false)
                {{-- > 3 hari: badge merah, warning kuat --}}
                <div class="alert alert-danger d-flex align-items-start mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div class="small">
                        <strong>Cache sudah sangat basi (> 3 hari).</strong> Badge merah aktif di sidebar. Klik "Precalculate Sekarang" segera untuk memastikan data R2 akurat.
                    </div>
                </div>
            @elseif($popup['needs_popup'] ?? false)
                {{-- 6 jam - 3 hari: popup reminder, warning ringan --}}
                <div class="alert alert-{{ $menu['warna'] }} d-flex align-items-start mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div class="small">
                        <strong>Cache sudah > 6 jam.</strong> Disarankan precalculate untuk performa optimal. Badge belum muncul (muncul kalau > 3 hari).
                    </div>
                </div>
            @else
                <div class="alert alert-success d-flex align-items-start mt-3 mb-0 py-2">
                    <i class="bi bi-check-circle-fill me-2 mt-1"></i>
                    <div class="small">
                        Cache masih fresh, namun precalculate ulang tidak ada salahnya untuk data paling akurat.
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer / Actions --}}
        <div class="precalculate-modal-footer">
            <form action="{{ route('admin.system.r2-precalculate') }}" method="POST" class="flex-grow-1 me-2" id="precalculateForm">
                @csrf
                <input type="hidden" name="async" value="1">
                <button type="submit" class="btn btn-{{ $menu['warna'] }} w-100" id="btnPrecalculateYa">
                    <i class="bi bi-lightning-charge-fill me-1"></i>
                    <span class="btn-text">Precalculate Sekarang</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>Memproses...
                    </span>
                </button>
            </form>
            
            <form action="{{ route('admin.system.precalculate-dismiss') }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary" id="btnPrecalculateTidak">
                    <i class="bi bi-x-lg me-1"></i>Nanti Saja
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Backdrop */
.precalculate-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.2s ease;
}

/* Modal Card */
.precalculate-modal-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 480px;
    width: 100%;
    overflow: hidden;
}

/* Header */
.precalculate-modal-header {
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
    color: white;
    position: relative;
}
.precalculate-modal-header.bg-danger { background: linear-gradient(135deg, #dc3545, #c82333) !important; }
.precalculate-modal-header.bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800) !important; color: #212529 !important; }
.precalculate-modal-header.bg-info { background: linear-gradient(135deg, #0dcaf0, #0aabcc) !important; }

.precalculate-modal-icon {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.5rem;
}

.precalculate-modal-title {
    font-weight: 700;
    margin-bottom: 0.25rem;
}
.precalculate-modal-subtitle {
    font-size: 0.875rem;
    opacity: 0.9;
}

/* Body */
.precalculate-modal-body {
    padding: 1.25rem 1.5rem;
}

.precalculate-info-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
}

.precalculate-stat-value {
    font-size: 0.9375rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}
.precalculate-stat-label {
    font-size: 0.6875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Footer */
.precalculate-modal-footer {
    padding: 1rem 1.5rem 1.25rem;
    display: flex;
    gap: 0.5rem;
    border-top: 1px solid #e9ecef;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-pop-in {
    animation: popIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.85) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
(function() {
    const modal = document.getElementById('precalculateModal');
    const form = document.getElementById('precalculateForm');
    const btnYa = document.getElementById('btnPrecalculateYa');
    
    // Close modal ketika klik backdrop (opsional - bisa dihapus kalau mau wajib pilih)
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            // Uncomment baris di bawah kalau mau bisa dismiss dengan klik backdrop
            // modal.style.display = 'none';
        }
    });

    // Loading state saat klik Precalculate
    if (form) {
        form.addEventListener('submit', function() {
            btnYa.disabled = true;
            btnYa.querySelector('.btn-text').classList.add('d-none');
            btnYa.querySelector('.btn-loading').classList.remove('d-none');
            document.getElementById('btnPrecalculateTidak').disabled = true;
        });
    }

    // Auto-refresh halaman setelah 3 detik kalau precalculate berhasil
    // (Controller akan redirect dengan flash message)
})();
</script>
@endif
