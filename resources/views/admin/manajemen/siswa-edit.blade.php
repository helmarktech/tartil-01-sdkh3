@extends('layouts.admin')

@section('title', 'Edit Siswa: ' . $siswa->nama)

@section('content')
<div class="se-wrap">

    {{-- Header --}}
    <div class="se-head">
        <div>
            <a href="{{ route('admin.manajemen.siswa') }}" class="se-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="se-title">Edit Siswa</h1>
            <p class="se-sub">{{ $siswa->nis }} &middot; {{ $siswa->nama }}</p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="se-alert se-alert-ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="se-alert se-alert-err">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="se-alert se-alert-err">
        <ul style="margin:0;padding-left:16px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Form Card --}}
    <form method="POST" action="{{ route('admin.manajemen.siswa.update', $siswa) }}" class="se-form" id="formEditSiswa">
        @csrf @method('PUT')

        <div class="se-grid">
            {{-- NIS --}}
            <div class="se-field">
                <label class="se-label">NIS (Nomor Induk Siswa) <span class="se-req">*</span></label>
                <input type="text" name="nis" id="inputNis" value="{{ old('nis', $siswa->nis) }}" class="se-input" required maxlength="30">
                <small class="se-hint">Digunakan untuk login siswa. Semua data (jurnal, penilaian, absensi) tetap aman &mdash; terhubung via ID internal.</small>
            </div>

            {{-- Tanggal Masuk --}}
            <div class="se-field">
                <label class="se-label">Tanggal Masuk <span class="se-req">*</span></label>
                <div class="se-date-wrap">
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $siswa->tanggal_masuk instanceof \Carbon\Carbon ? $siswa->tanggal_masuk->format('Y-m-d') : ($siswa->tanggal_masuk ?: '')) }}" class="se-input se-date" required>
                    <button type="button" class="se-date-clear" data-target="tanggal_masuk" title="Hapus tanggal">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Nama --}}
            <div class="se-field">
                <label class="se-label">Nama Lengkap <span class="se-req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $siswa->nama) }}" class="se-input" required maxlength="100">
            </div>

            {{-- No HP --}}
            <div class="se-field">
                <label class="se-label">No HP <span class="se-req">*</span></label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $siswa->no_hp) }}" class="se-input" required maxlength="15">
            </div>

            {{-- Jenis Kelamin --}}
            <div class="se-field">
                <label class="se-label">Jenis Kelamin <span class="se-req">*</span></label>
                <select name="jenis_kelamin" class="se-input" required>
                    <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            {{-- Kelas Reguler --}}
            <div class="se-field">
                <label class="se-label">Kelas Reguler <span class="se-req">*</span></label>
                <select name="kelas_reguler_id" class="se-input" required>
                    @foreach($kelasRegulars as $kr)
                    <option value="{{ $kr->id }}" {{ old('kelas_reguler_id', $siswa->kelas_reguler_id) == $kr->id ? 'selected' : '' }}>{{ $kr->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Kelas Tartil --}}
            <div class="se-field">
                <label class="se-label">Kelas Tartil</label>
                <select name="kelas_tartil_id" class="se-input">
                    <option value="">-- Pilih Kelas Tartil --</option>
                    @foreach($kelasTartils as $kt)
                    <option value="{{ $kt->id }}" {{ old('kelas_tartil_id', $siswa->kelas_tartil_id) == $kt->id ? 'selected' : '' }}>{{ $kt->nama }} ({{ $kt->jenis }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Tempat Lahir --}}
            <div class="se-field">
                <label class="se-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" class="se-input" maxlength="100">
            </div>

            {{-- Tanggal Lahir --}}
            <div class="se-field">
                <label class="se-label">Tanggal Lahir</label>
                <div class="se-date-wrap">
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir instanceof \Carbon\Carbon ? $siswa->tanggal_lahir->format('Y-m-d') : ($siswa->tanggal_lahir ?: '')) }}" class="se-input se-date">
                    <button type="button" class="se-date-clear" data-target="tanggal_lahir" title="Hapus tanggal">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Nama Ayah --}}
            <div class="se-field">
                <label class="se-label">Nama Ayah</label>
                <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $siswa->nama_ayah) }}" class="se-input" maxlength="100">
            </div>
        </div>

        {{-- Opsi Update Password --}}
        <div class="se-options">
            <label class="se-check" id="labelUpdatePassword" style="display:none;">
                <input type="checkbox" name="update_password_nis" value="1" {{ old('update_password_nis') ? 'checked' : '' }}>
                <span class="se-check-box"></span>
                <span class="se-check-label">Perbarui password login siswa sesuai NIS baru</span>
            </label>
            <small class="se-hint" id="hintUpdatePassword" style="display:none;">Password siswa akan diubah menjadi hash dari NIS yang baru. Siswa harus login dengan NIS baru.</small>
        </div>

        {{-- Info Read-Only Sistem --}}
        <div class="se-readonly">
            <div class="se-ro-title">Informasi Sistem</div>
            <div class="se-ro-grid">
                <div><span class="se-ro-label">Status</span><span class="se-ro-val"><span class="se-badge se-badge-{{ $siswa->status == 'aktif' ? 'ok' : 'warn' }}">{{ $siswa->status }}</span></span></div>
                <div><span class="se-ro-label">ID Internal</span><span class="se-ro-val">#{{ $siswa->id }}</span></div>
                <div><span class="se-ro-label">Kelas Tartil</span><span class="se-ro-val">{{ $siswa->kelasTartil?->nama ?? '-' }} {{ $siswa->kelasTartil?->jenis ? '('.$siswa->kelasTartil->jenis.')' : '' }}</span></div>
                <div><span class="se-ro-label">Tanggal Masuk Kelas Tartil</span><span class="se-ro-val">{{ $siswa->tanggal_masuk_kelas_tartil instanceof \Carbon\Carbon ? $siswa->tanggal_masuk_kelas_tartil->format('d/m/Y') : ($siswa->tanggal_masuk_kelas_tartil ?: '-') }} <span class="se-badge se-badge-{{ $siswa->tanggal_masuk_kelas_tartil ? 'mutasi' : 'auto' }}">{{ $siswa->tanggal_masuk_kelas_tartil ? 'Mutasi' : 'Otomatis' }}</span></span></div>
                <div><span class="se-ro-label">Dibuat</span><span class="se-ro-val">{{ $siswa->created_at instanceof \Carbon\Carbon ? $siswa->created_at->format('d/m/Y H:i') : '-' }}</span></div>
                <div><span class="se-ro-label">Terakhir Update</span><span class="se-ro-val">{{ $siswa->updated_at instanceof \Carbon\Carbon ? $siswa->updated_at->format('d/m/Y H:i') : '-' }}</span></div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="se-actions">
            <a href="{{ route('admin.manajemen.siswa') }}" class="se-btn se-btn-ghost">Batal</a>
            <button type="submit" class="se-btn se-btn-primary">Simpan Perubahan</button>
        </div>
    </form>

</div>

<style>
.se-wrap { max-width: 720px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Header */
.se-head { margin-bottom: 24px; }
.se-back { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #737373; text-decoration: none; margin-bottom: 8px; transition: color 0.15s; }
.se-back:hover { color: #171717; }
.se-title { font-size: 22px; font-weight: 700; color: #171717; margin: 0; letter-spacing: -0.3px; }
.se-sub { font-size: 13px; color: #737373; margin: 4px 0 0; }

/* Alert */
.se-alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
.se-alert-ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.se-alert-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Form */
.se-form { background: #fff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 24px; }
.se-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .se-grid { grid-template-columns: 1fr; } }

.se-field { display: flex; flex-direction: column; gap: 5px; }
.se-label { font-size: 12px; font-weight: 500; color: #404040; }
.se-req { color: #dc2626; }
.se-hint { font-size: 11px; color: #a3a3a3; line-height: 1.45; margin-top: 2px; }
.se-input {
    padding: 8px 12px;
    border: 1px solid #d4d4d4;
    border-radius: 8px;
    font-size: 13px;
    color: #171717;
    background: #fff;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    font-family: inherit;
    width: 100%;
}
.se-input:focus { border-color: #a3a3a3; box-shadow: 0 0 0 3px rgba(0,0,0,0.04); }
select.se-input { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23737373' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }

/* Date input wrapper */
.se-date-wrap { position: relative; display: flex; align-items: center; }
.se-date-wrap .se-input { padding-right: 32px; }
.se-date-clear {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    border: none;
    border-radius: 5px;
    background: #e5e5e5;
    color: #737373;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s;
    padding: 0;
    z-index: 2;
}
.se-date-clear:hover { background: #dc2626; color: #fff; }
.se-date-wrap .se-input:not(:placeholder-shown) ~ .se-date-clear,
.se-date-wrap .se-date-clear.visible { display: flex; }
/* Always show clear button when date has value */
.se-date-wrap .se-input[value]:not([value=""]) ~ .se-date-clear { display: flex; }

/* Checkbox option */
.se-options { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e5e5; }
.se-check { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.se-check input[type="checkbox"] { display: none; }
.se-check-box {
    width: 18px; height: 18px; min-width: 18px;
    border: 2px solid #d4d4d4; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.se-check input:checked + .se-check-box { background: #171717; border-color: #171717; }
.se-check input:checked + .se-check-box::after {
    content: ''; width: 5px; height: 8px;
    border: solid #fff; border-width: 0 2px 2px 0;
    transform: rotate(45deg); margin-bottom: 2px;
}
.se-check-label { font-size: 13px; font-weight: 500; color: #404040; }

/* Readonly info */
.se-readonly { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5; }
.se-ro-title { font-size: 12px; font-weight: 600; color: #171717; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
.se-ro-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 640px) { .se-ro-grid { grid-template-columns: 1fr; } }
.se-ro-grid > div { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 12px; }
.se-ro-label { color: #737373; flex-shrink: 0; }
.se-ro-val { color: #171717; font-weight: 500; font-family: 'SF Mono', monospace; font-size: 11px; text-align: right; }

/* Badges */
.se-badge { display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.se-badge-ok { background: #f0fdf4; color: #166534; }
.se-badge-warn { background: #fef3c7; color: #92400e; }
.se-badge-mutasi { background: #dbeafe; color: #1e40af; }
.se-badge-auto { background: #f3f4f6; color: #6b7280; }

/* Actions */
.se-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e5e5; }
@media (max-width: 480px) { .se-actions { flex-direction: column; } }

.se-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 8px 20px; border-radius: 8px; border: 1px solid transparent;
    font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s;
    text-decoration: none; line-height: 1.5;
}
.se-btn-primary { background: #171717; color: #fff; }
.se-btn-primary:hover { background: #404040; }
.se-btn-ghost { background: #fff; color: #525252; border-color: #d4d4d4; }
.se-btn-ghost:hover { background: #f5f5f5; }
</style>

<script>
(function() {
    // ══════════════════════════════════════════════════════
    // 1. NIS CHANGE → SHOW/HIDE PASSWORD UPDATE OPTION
    // ══════════════════════════════════════════════════════
    var nisAwal = {{ \Illuminate\Support\Js::from($siswa->nis) }};
    var inputNis = document.getElementById('inputNis');
    var labelPw = document.getElementById('labelUpdatePassword');
    var hintPw = document.getElementById('hintUpdatePassword');

    function togglePasswordOption() {
        var nisBaru = inputNis.value.trim();
        if (nisBaru && nisBaru !== nisAwal) {
            labelPw.style.display = 'flex';
            hintPw.style.display = 'block';
        } else {
            labelPw.style.display = 'none';
            hintPw.style.display = 'none';
            document.querySelector('input[name="update_password_nis"]').checked = false;
        }
    }
    inputNis.addEventListener('input', togglePasswordOption);
    inputNis.addEventListener('change', togglePasswordOption);
    togglePasswordOption();

    // ══════════════════════════════════════════════════════
    // 2. DATE PICKER DISMISS ON OUTSIDE CLICK
    // ══════════════════════════════════════════════════════
    var activeDateInput = null;

    document.querySelectorAll('input[type="date"]').forEach(function(input) {
        // Track which date input is active
        input.addEventListener('focus', function() {
            activeDateInput = input;
        });

        // Allow clicking clear button without reopening picker
        input.addEventListener('mousedown', function(e) {
            if (input.value && e.offsetX > input.offsetWidth - 32) {
                e.preventDefault();
                input.value = '';
                input.dispatchEvent(new Event('change'));
                updateClearButtons();
            }
        });

        // Blur on change (close picker after selection)
        input.addEventListener('change', function() {
            input.blur();
            updateClearButtons();
        });
    });

    // Dismiss date picker when clicking outside
    document.addEventListener('mousedown', function(e) {
        if (activeDateInput && !activeDateInput.contains(e.target)) {
            activeDateInput.blur();
            activeDateInput = null;
        }
    });

    // ══════════════════════════════════════════════════════
    // 3. CLEAR BUTTON FOR DATE INPUTS
    // ══════════════════════════════════════════════════════
    function updateClearButtons() {
        document.querySelectorAll('.se-date-clear').forEach(function(btn) {
            var targetName = btn.getAttribute('data-target');
            var input = document.querySelector('input[name="' + targetName + '"]');
            if (input && input.value) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        });
    }

    document.querySelectorAll('.se-date-clear').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var targetName = btn.getAttribute('data-target');
            var input = document.querySelector('input[name="' + targetName + '"]');
            if (input) {
                input.value = '';
                input.blur();
                updateClearButtons();
            }
        });
    });

    // Initial state
    updateClearButtons();
})();
</script>
@endsection
