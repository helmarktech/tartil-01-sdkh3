@extends('layouts.auth')
@section('title', 'Login Siswa')

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-head">
            <div class="logo-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <h1>Tartil<em>Pro</em></h1>
            <p>Login Siswa &mdash; Pantau perkembangan Al-Quran Anda</p>
        </div>

        @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('siswa.login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">NIS (Nomor Induk Siswa)</label>
                <input type="text" name="nis" class="form-input" value="{{ old('nis') }}" required autofocus placeholder="Contoh: 2025001">
            </div>

            <div class="form-group">
                <label class="form-label">Nomor HP</label>
                <input type="text" name="no_hp" class="form-input" required placeholder="08xxxxxxxxxx">
            </div>

            <div class="form-check">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-tartil">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Masuk ke Dashboard
            </button>
        </form>

        <div class="login-footer">
            <a href="/">Login sebagai Admin / Guru</a>
        </div>
    </div>
</div>
@endsection
