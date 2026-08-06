@extends('layouts.admin')

@section('title', 'Ganti Password')

@section('content')
<div class="page-header">
    <h1 class="page-title-display">Ganti Password</h1>
    <p class="page-subtitle">Perbarui password akun guru Anda</p>
</div>

<div class="card-tartil" style="max-width: 480px;">
    <form method="POST" action="{{ route('guru.password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="password_lama" class="form-label">Password Lama</label>
            <input type="password" id="password_lama" name="password_lama" class="form-input @error('password_lama') is-invalid @enderror" required autofocus>
            @error('password_lama')
            <span style="display: block; margin-top: 6px; font-size: 12px; color: var(--danger);">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="password_baru" class="form-label">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" class="form-input @error('password_baru') is-invalid @enderror" required minlength="6">
            @error('password_baru')
            <span style="display: block; margin-top: 6px; font-size: 12px; color: var(--danger);">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="password_baru_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" class="form-input" required minlength="6">
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="submit" class="btn-tartil">Simpan Password</button>
            <a href="{{ route('guru.dashboard') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
        </div>
    </form>
</div>
@endsection
