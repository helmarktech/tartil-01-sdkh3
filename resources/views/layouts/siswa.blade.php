<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TartilPro SD Khadijah 3</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <style>
        /* ════════════════════════════════════════════
           BASE — Layout Siswa (mandiri, tanpa tartil.css)
           ════════════════════════════════════════════ */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #f8f7f5;
            --bg-card: #ffffff;
            --ink: #1c1917;
            --ink-secondary: #44403c;
            --ink-muted: #78716c;
            --ink-faint: #a8a29e;
            --border: #e7e5e4;
            --border-light: #f5f5f4;
            --accent: #0c8a5f;
            --accent-soft: #d1fae5;
            --accent-dark: #065f43;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ═══ Layout Structure ═══ */
        .tartil-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .tartil-main {
            flex: 1;
            margin-left: 0;
            display: flex;
            flex-direction: column;
        }

        /* ═══ Topbar ═══ */
        .tartil-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .tartil-topbar .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--ink);
        }
        .tartil-topbar .brand svg {
            width: 20px;
            height: 20px;
            color: var(--accent);
            flex-shrink: 0;
        }
        .tartil-topbar .brand-text-stack {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .tartil-topbar .brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.2px;
        }
        .tartil-topbar .brand-subtitle {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10px;
            color: var(--ink-muted);
            font-weight: 500;
        }
        .tartil-topbar .btn-logout {
            background: none;
            border: none;
            color: var(--ink-muted);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.15s;
        }
        .tartil-topbar .btn-logout:hover {
            background: var(--border-light);
            color: var(--ink);
        }

        /* ═══ Content ═══ */
        .tartil-content {
            flex: 1;
            padding: 24px;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* ═══ Student Profile Header ═══ */
        .student-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .student-name {
            font-weight: 700;
            font-size: 15px;
            color: var(--ink);
        }
        .student-nis {
            font-size: 11px;
            color: var(--ink-muted);
        }

        /* ═══ Navigation ═══ */
        .siswa-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 8px 0 4px;
            margin-bottom: 16px;
        }
        .siswa-nav a {
            white-space: nowrap;
            padding: 8px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: var(--ink-muted);
            background: var(--bg-card);
            border: 1px solid var(--border);
            transition: all 0.15s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .siswa-nav a:hover {
            background: var(--border-light);
            border-color: #d6d3d1;
            color: var(--ink-secondary);
        }
        .siswa-nav a.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* ═══ Alerts ═══ */
        .alert-tartil {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-success {
            background: var(--accent-soft);
            color: var(--accent-dark);
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: var(--danger-soft);
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* ═══ Form Elements ═══ */
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-secondary);
            margin-bottom: 4px;
        }
        .form-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #d4d4d4;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fff;
            color: var(--ink);
            outline: none;
            transition: all 0.15s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(12,138,95,0.08);
        }
        .form-group { margin-bottom: 16px; }

        /* ═══ Buttons ═══ */
        .btn-tartil {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            background: var(--ink);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-tartil:hover { background: var(--ink-secondary); transform: translateY(-1px); }

        .btn-tartil-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 18px; border-radius: 10px; border: 1px solid var(--border);
            background: transparent; color: var(--ink);
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: all 0.15s;
        }
        .btn-tartil-outline:hover { background: var(--bg-body); border-color: var(--accent); color: var(--accent); }

        /* ═══ Tables ═══ */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-tartil {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .table-tartil th {
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ink-muted);
            border-bottom: 2px solid var(--border);
            background: var(--border-light);
        }
        .table-tartil td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-light);
            color: var(--ink-secondary);
        }
        .table-tartil tr:hover td { background: var(--border-light); }

        /* ═══ Pagination ═══ */
        .pagination { display: flex; gap: 4px; list-style: none; margin-top: 16px; }
        .pagination li a, .pagination li span {
            display: inline-flex;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: var(--ink-secondary);
            border: 1px solid var(--border);
        }
        .pagination li.active span {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* ═══ Typography ═══ */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
        }

        /* ═══ Links ═══ */
        .link-tartil {
            color: var(--ink-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: color 0.15s;
        }
        .link-tartil:hover { color: var(--ink); }

        /* ═══ Consistent Siswa Page Header ═══ */
        .siswa-page-header {
            display: flex; align-items: center; gap: 16px;
            margin-bottom: 24px; padding-bottom: 16px;
            border-bottom: 1px solid var(--border-light);
        }
        .siswa-page-icon {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, #0c8a5f, #065f43);
            color: #fff; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; box-shadow: 0 4px 12px rgba(12,138,95,0.18);
        }
        .siswa-page-icon svg { width: 24px; height: 24px; }
        .siswa-page-title {
            font-size: 20px; font-weight: 800; color: var(--ink);
            margin: 0 0 3px; letter-spacing: -0.3px;
        }
        .siswa-page-subtitle {
            font-size: 13px; color: var(--ink-muted); margin: 0;
        }

        /* ════════════════════════════════════════════
           SHARED ADMIN-STYLE CLASSES (untuk view yang dipakai di layout siswa)
           ════════════════════════════════════════════ */
        :root {
            --success: #0c8a5f;
            --success-soft: #d1fae5;
            --info: #1565c0;
            --info-soft: #dbeafe;
            --text-muted: #78716c;
            --bg-body: #f5f5f4;
        }

        .page-header { margin-bottom: 20px; }
        .page-title-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px; font-weight: 800;
            letter-spacing: -0.5px; color: var(--ink);
            margin: 0 0 4px;
        }
        .page-subtitle { font-size: 13px; color: var(--ink-muted); margin: 0; }

        .card-tartil {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 14px; padding: 20px; margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .badge-success, .badge-warning, .badge-error, .badge-primary, .badge-subject {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
        }
        .badge-success { background: var(--success-soft); color: #065f43; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-error { background: var(--danger-soft); color: #991b1b; }
        .badge-primary { background: var(--info-soft); color: #1e40af; }
        .badge-subject { background: #f5f5f4; color: var(--ink-secondary); }

        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; padding: 16px; text-align: center;
        }
        .stat-label { font-size: 11px; color: var(--ink-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .stat-value { font-size: 22px; font-weight: 800; color: var(--ink); }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-tartil {
            width: 100%; border-collapse: collapse; font-size: 13px;
        }
        .table-tartil th {
            padding: 10px 12px; text-align: left;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--ink-muted); border-bottom: 2px solid var(--border); background: var(--bg-body);
        }
        .table-tartil td {
            padding: 10px 12px; border-bottom: 1px solid var(--border-light); color: var(--ink-secondary);
        }
        .table-tartil tbody tr:last-child td { border-bottom: none; }
        .table-tartil tbody tr:hover td { background: var(--bg-body); }

        /* ═══ Mobile ═══ */
        @media (max-width: 640px) {
            .tartil-content { padding: 16px; }
            .tartil-topbar { padding: 10px 16px; }
            .siswa-nav { gap: 5px; }
            .siswa-nav a { padding: 7px 12px; font-size: 11px; }
        }
        @media (hover: none) and (pointer: coarse) {
            .btn-tartil { min-height: 44px; }
            select, input, textarea { font-size: 16px !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="tartil-wrapper">
        <div class="tartil-main">
            <header class="tartil-topbar">
                <div class="brand">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <div class="brand-text-stack">
                        <span class="brand-title">TartilPro</span>
                        <span class="brand-subtitle">SD Khadijah 3</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('siswa.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-logout">Keluar</button>
                </form>
            </header>

            <main class="tartil-content">
                <div class="student-header">
                    <div class="student-avatar">{{ auth('siswa')->user()->initials }}</div>
                    <div>
                        <div class="student-name">{{ auth('siswa')->user()->nama }}</div>
                        <div class="student-nis">NIS: {{ auth('siswa')->user()->nis }}</div>
                    </div>
                </div>

                <nav class="siswa-nav">
                    <a href="{{ route('siswa.dashboard') }}" class="{{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}">&#127968; Dashboard</a>
                    <a href="{{ route('siswa.nilai') }}" class="{{ request()->routeIs('siswa.nilai') ? 'active' : '' }}">&#128196; Rapor</a>
                    @if(auth('siswa')->user()?->kelas_tartil_id)
                    <a href="{{ route('siswa.hafalan') }}" class="{{ request()->routeIs('siswa.hafalan') ? 'active' : '' }}">&#128218; Hafalan</a>
                    <a href="{{ route('siswa.pendampingan-ortu.index') }}" class="{{ request()->routeIs('siswa.pendampingan-ortu.*') ? 'active' : '' }}">&#128106; Pendampingan Ortu</a>
                    @endif
                    <a href="{{ route('siswa.perpindahan') }}" class="{{ request()->routeIs('siswa.perpindahan') ? 'active' : '' }}">&#128260; Riwayat Kelas</a>
                    <a href="{{ route('siswa.track-record') }}" class="{{ request()->routeIs('siswa.track-record') ? 'active' : '' }}">&#128099; Track Record</a>
                    <a href="{{ route('siswa.munaqosyah') }}" class="{{ request()->routeIs('siswa.munaqosyah') ? 'active' : '' }}">&#127942; Riwayat Munaqosyah</a>
                    <a href="{{ route('siswa.no-hp.edit') }}" class="{{ request()->routeIs('siswa.no-hp.*') ? 'active' : '' }}">&#128100; Profil</a>
                </nav>

                @if(session('success'))
                <div class="alert-tartil alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert-tartil alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
