@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="login-container">
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 24px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <h1 style="font-family: 'DM Serif Display', serif; font-size: 24px; margin: 8px 0 4px;">Tartil</h1>
            <p style="color: var(--text-muted); font-size: 13px;">Login Admin / Guru</p>
        </div>

        @if(session('error'))
        <div class="alert-error" style="margin-bottom: 16px;">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert-error" style="margin-bottom: 16px;">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder="email@domain.com">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required placeholder="••••••••">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" id="remember" style="accent-color: var(--accent);">
                <label for="remember" style="font-size: 13px; color: var(--text-secondary); cursor: pointer;">Ingat saya</label>
            </div>

            <button type="submit" class="btn-tartil" style="width: 100%; justify-content: center;">
                Masuk
            </button>
        </form>

        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('siswa.login') }}" class="link-tartil">Login sebagai Siswa</a>
        </div>
    </div>
</div>
@endsection
