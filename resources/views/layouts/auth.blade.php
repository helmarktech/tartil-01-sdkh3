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
            --shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.02);
            --radius: 16px;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* ═══ Login Card ═══ */
        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px 36px;
            box-shadow: var(--shadow);
        }

        .login-head {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-head .logo-mark {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--accent-soft);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .login-head .logo-mark svg { width: 26px; height: 26px; color: var(--accent-dark); }
        .login-head h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        .login-head h1 em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .login-head p {
            font-size: 14px;
            color: var(--ink-muted);
        }

        /* ═══ Form ═══ */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-secondary);
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fff;
            color: var(--ink);
            outline: none;
            transition: all 0.2s;
        }
        .form-input::placeholder { color: var(--ink-faint); }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(12,138,95,0.08);
        }

        /* ═══ Button ═══ */
        .btn-tartil {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: var(--ink);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            gap: 8px;
        }
        .btn-tartil:hover { background: var(--ink-secondary); transform: translateY(-1px); }

        /* ═══ Alert ═══ */
        .alert-error {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            margin-bottom: 16px;
        }

        /* ═══ Footer Links ═══ */
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
        }
        .login-footer a {
            color: var(--ink-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }
        .login-footer a:hover { color: var(--ink); }

        /* ═══ Remember Me ═══ */
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }
        .form-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }
        .form-check label {
            font-size: 13px;
            color: var(--ink-muted);
            cursor: pointer;
        }

        /* ═══ Responsive ═══ */
        @media (max-width: 480px) {
            html, body { padding: 16px; }
            .login-card { padding: 28px 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
