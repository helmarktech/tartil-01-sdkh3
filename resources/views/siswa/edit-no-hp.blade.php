@extends('layouts.siswa')

@section('title', 'Edit Nomor HP')

@section('content')
<div style="max-width: 480px;">
    <div class="siswa-page-header">
    <div class="siswa-page-icon" style="background: linear-gradient(135deg, #475569, #1e293b);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div>
        <h1 class="siswa-page-title">Edit Nomor HP</h1>
        <p class="siswa-page-subtitle">Perbarui nomor handphone akun siswa Anda.</p>
    </div>
</div>

    <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 20px;">
        <form method="POST" action="{{ route('siswa.no-hp.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="text" id="no_hp" name="no_hp" class="form-input"
                       value="{{ old('no_hp', $siswa->no_hp) }}"
                       placeholder="Contoh: 081234567890" required maxlength="15">
                @error('no_hp')
                <span style="display: block; margin-top: 6px; font-size: 12px; color: var(--danger);">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="btn-tartil">Simpan Perubahan</button>
                <a href="{{ route('siswa.dashboard') }}" class="link-tartil" style="padding: 10px 8px;">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
