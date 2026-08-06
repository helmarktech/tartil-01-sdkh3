@extends('layouts.siswa')

@section('title', 'Edit Nomor HP')

@section('content')
<div style="max-width: 480px;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 800; color: var(--ink); margin-bottom: 4px;">Edit Nomor HP</h1>
        <p style="font-size: 13px; color: var(--ink-muted);">Perbarui nomor handphone akun siswa Anda.</p>
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
