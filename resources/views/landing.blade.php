<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TartilPro - SD Khadijah 3</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-sd-khadijah-3.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
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
            --gold: #b48a3e;
            --shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
            --shadow-lg: 0 4px 24px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.02);
            --radius: 14px;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* ===== HERO ===== */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 48px;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 48px 80px 0;
        }

        .hero-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }
        .hero-logo-mark {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
        }
        .hero-logo-mark svg { width: 20px; height: 20px; color: #fff; }
        .hero-logo-text { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .hero-logo-text em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .hero-logo-sekolah {
            width: 48px; height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent);
            box-shadow: 0 2px 8px rgba(12,138,95,0.15);
        }
        .hero-logo-cross {
            font-size: 20px;
            font-weight: 300;
            color: var(--ink-faint);
            user-select: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--accent-soft);
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            color: var(--accent-dark);
            letter-spacing: 0.5px;
            width: fit-content;
            margin-bottom: 28px;
        }
        .hero-badge-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--accent);
        }

        .hero h1 {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -2px;
            margin-bottom: 20px;
        }
        .hero h1 .accent {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .hero h1 .gold {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--gold);
        }

        .hero-desc {
            font-size: 15px;
            color: var(--ink-muted);
            line-height: 1.7;
            max-width: 420px;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            gap: 36px;
        }
        .hero-stat-num {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--ink);
        }
        .hero-stat-num span {
            font-family: 'Instrument Serif', serif;
            font-style: italic;
            color: var(--gold);
        }
        .hero-stat-label {
            font-size: 11px;
            color: var(--ink-faint);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* ===== RIGHT: LOGIN ===== */
        .hero-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0 60px 48px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px;
            box-shadow: var(--shadow-lg);
        }

        .login-tabs {
            display: flex;
            gap: 2px;
            margin-bottom: 28px;
            background: var(--border-light);
            border-radius: 10px;
            padding: 3px;
        }
        .login-tab {
            flex: 1;
            padding: 9px 14px;
            border: none;
            background: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-faint);
            cursor: pointer;
            transition: all 0.2s;
        }
        .login-tab:hover { color: var(--ink-muted); }
        .login-tab.active {
            background: #fff;
            color: var(--ink);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .login-panel { display: none; }
        .login-panel.active { display: block; }

        .login-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .login-sub {
            font-size: 13px;
            color: var(--ink-faint);
            margin-bottom: 24px;
        }

        .field { margin-bottom: 14px; }
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-secondary);
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            color: var(--ink);
            outline: none;
            transition: all 0.2s;
            background: #fff;
        }
        .field input::placeholder { color: var(--ink-faint); }
        .field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(12,138,95,0.08);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: var(--ink);
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 6px;
        }
        .btn-submit:hover { background: var(--ink-secondary); transform: translateY(-1px); }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            font-size: 11px;
            color: var(--ink-faint);
        }
        .login-divider::before, .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .login-hint {
            font-size: 12px;
            color: var(--ink-faint);
            line-height: 1.6;
            text-align: center;
        }
        .login-hint strong { color: var(--ink-muted); }

        .login-alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 14px;
            display: none;
        }
        .login-alert.show { display: block; }
        .login-alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .login-alert.warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* ===== FEATURES ===== */
        .features {
            padding: 100px 48px;
            max-width: 1280px;
            margin: 0 auto;
        }
        .features-head {
            text-align: center;
            max-width: 520px;
            margin: 0 auto 56px;
        }
        .features-head h2 {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }
        .features-head h2 em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .features-head p {
            font-size: 15px;
            color: var(--ink-muted);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            padding: 32px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            transition: all 0.25s;
        }
        .feature-card:hover {
            border-color: #d6d3d1;
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--accent-soft);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
        }
        .feature-icon svg { width: 20px; height: 20px; color: var(--accent-dark); }
        .feature-card h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .feature-card p {
            font-size: 13px;
            color: var(--ink-muted);
            line-height: 1.7;
        }
        .feature-benefit {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 12px;
            padding: 4px 10px;
            background: var(--accent-soft);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--accent-dark);
        }

        /* ===== MONITORING ORANG TUA ===== */
        .monitor {
            padding: 100px 48px;
            background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg) 100%);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .monitor-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        .monitor-head {
            text-align: center;
            max-width: 580px;
            margin: 0 auto 56px;
        }
        .monitor-head h2 {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }
        .monitor-head h2 em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .monitor-head p {
            font-size: 15px;
            color: var(--ink-muted);
            line-height: 1.7;
        }

        .monitor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .monitor-card {
            padding: 36px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .monitor-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .monitor-card h3 .badge-new {
            display: inline-flex;
            padding: 2px 8px;
            background: var(--accent-soft);
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            color: var(--accent-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .monitor-card p {
            font-size: 13px;
            color: var(--ink-muted);
            line-height: 1.7;
            margin-bottom: 16px;
        }
        .monitor-list {
            list-style: none;
        }
        .monitor-list li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
            color: var(--ink-secondary);
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .monitor-list li::before {
            content: '';
            width: 16px; height: 16px;
            border-radius: 50%;
            background: var(--accent-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23065f43' stroke-width='3'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }

        /* ===== COMPARISON ===== */
        .compare {
            padding: 100px 48px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .compare-head {
            text-align: center;
            max-width: 520px;
            margin: 0 auto 56px;
        }
        .compare-head h2 {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }
        .compare-head h2 em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .compare-head p {
            font-size: 15px;
            color: var(--ink-muted);
        }

        /* Side-by-side illustration */
        .compare-illust {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 56px;
        }
        .compare-side {
            padding: 32px;
            border-radius: var(--radius);
            text-align: center;
        }
        .compare-side.manual {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }
        .compare-side.digital {
            background: var(--accent-soft);
            border: 1px solid #a7f3d0;
        }
        .compare-side-icon {
            width: 64px; height: 64px;
            margin: 0 auto 16px;
        }
        .compare-side h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .compare-side.manual h3 { color: #991b1b; }
        .compare-side.digital h3 { color: var(--accent-dark); }
        .compare-side p { font-size: 12px; color: var(--ink-muted); }

        /* Chart rows */
        .chart-row {
            display: grid;
            grid-template-columns: 180px 1fr 1fr;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }
        .chart-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-secondary);
            text-align: right;
        }
        .chart-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chart-bar-wrap {
            position: relative;
            height: 40px;
            background: #f5f5f4;
            border-radius: 8px;
            overflow: hidden;
            flex: 1;
        }
        .chart-bar {
            height: 100%;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 600;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }
        .chart-bar.manual {
            background: linear-gradient(90deg, #fca5a5, #f87171);
            color: #7f1d1d;
            justify-content: flex-end;
        }
        .chart-bar.digital {
            background: linear-gradient(90deg, #a7f3d0, #6ee7b7);
            color: #1c1917;
        }
        .chart-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }
        .chart-value.manual-val { color: #991b1b; }
        .chart-value.digital-val { color: var(--accent); }

        .chart-arrow {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 24px;
            padding-left: 196px;
        }
        .chart-arrow-sym {
            font-size: 14px;
            font-weight: 800;
            color: var(--accent);
        }
        .chart-tag {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
        }
        .chart-tag.up {
            background: var(--accent-soft);
            color: var(--accent-dark);
        }
        .chart-tag.down {
            background: #fecaca;
            color: #991b1b;
        }

        /* Improvement Stats */
        .compare-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 48px;
        }
        .compare-stat {
            text-align: center;
            padding: 28px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .compare-stat-num {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--accent);
            line-height: 1;
        }
        .compare-stat-label {
            font-size: 12px;
            color: var(--ink-muted);
            margin-top: 8px;
        }

        /* ===== BREAKDOWN ===== */
        .breakdown {
            margin-top: 48px;
            padding: 32px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .breakdown-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink-secondary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .breakdown-title svg {
            width: 16px;
            height: 16px;
            color: var(--accent);
        }
        .breakdown-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 0;
        }
        .breakdown-head {
            font-size: 11px;
            font-weight: 700;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 12px;
            border-bottom: 2px solid var(--border);
        }
        .breakdown-cell {
            font-size: 13px;
            padding: 12px;
            border-bottom: 1px solid var(--border-light);
            color: var(--ink-secondary);
        }
        .breakdown-cell.task {
            font-weight: 600;
            color: var(--ink);
        }
        .breakdown-cell.time-manual {
            color: #991b1b;
            font-weight: 600;
        }
        .breakdown-cell.time-digital {
            color: var(--accent-dark);
            font-weight: 700;
        }
        .breakdown-cell.result {
            color: var(--accent);
            font-weight: 700;
        }
        .breakdown-total {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            margin-top: 0;
        }
        .breakdown-total .breakdown-cell {
            border-top: 2px solid var(--border);
            border-bottom: none;
            font-size: 14px;
            font-weight: 800;
            color: var(--ink);
            padding-top: 14px;
        }
        .breakdown-total .breakdown-cell.result {
            color: var(--accent);
            font-size: 16px;
        }
        .breakdown-formula {
            margin-top: 16px;
            padding: 14px 16px;
            background: var(--accent-soft);
            border-radius: 10px;
            font-size: 13px;
            color: var(--accent-dark);
            font-weight: 600;
            text-align: center;
        }
        .breakdown-formula code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            background: rgba(255,255,255,0.5);
            padding: 2px 6px;
            border-radius: 4px;
        }
        @media (max-width: 960px) {
            .breakdown-grid, .breakdown-total {
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
            }
            .breakdown { padding: 20px; }
        }
        @media (max-width: 640px) {
            .breakdown-grid, .breakdown-total {
                grid-template-columns: 1fr 1fr;
            }
            .breakdown-head:nth-child(3), .breakdown-head:nth-child(4),
            .breakdown-cell:nth-child(4n+3), .breakdown-cell:nth-child(4n+4) { display: none; }
            .breakdown-head:nth-child(2)::after { content: ' Digital'; }
        }

        /* ===== FOOTER ===== */
        .footer {
            border-top: 1px solid var(--border);
            padding: 48px 48px 32px;
            max-width: 1280px;
            margin: 0 auto;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .footer-brand-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
        }
        .footer-brand-icon svg { width: 15px; height: 15px; color: #fff; }
        .footer-brand-name { font-size: 16px; font-weight: 800; }
        .footer-brand-name em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .footer-about {
            font-size: 13px;
            color: var(--ink-muted);
            line-height: 1.6;
            max-width: 280px;
        }
        .footer-col h4 {
            font-size: 11px;
            font-weight: 700;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
        }
        .footer-col a {
            display: block;
            font-size: 13px;
            color: var(--ink-muted);
            margin-bottom: 10px;
            transition: color 0.15s;
        }
        .footer-col a:hover { color: var(--ink); }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }
        .footer-copy { font-size: 12px; color: var(--ink-faint); }
        .footer-company {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--ink-muted);
        }
        .footer-company strong { color: var(--ink); font-weight: 600; }
        .footer-company svg { width: 14px; height: 14px; color: var(--gold); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 960px) {
            .hero { grid-template-columns: 1fr; padding: 0 32px; }
            .hero-left { padding: 56px 0 0; }
            .hero h1 { font-size: 38px; }
            .hero-right { padding: 40px 0 56px; }
            .features { padding: 64px 32px; }
            .features-grid { grid-template-columns: 1fr; }
            .monitor { padding: 64px 32px; }
            .monitor-grid { grid-template-columns: 1fr; }
            .compare { padding: 64px 32px; }
            .compare-stats { grid-template-columns: repeat(2, 1fr); }
            .chart-row { grid-template-columns: 120px 1fr 1fr; gap: 10px; }
            .chart-arrow { padding-left: 136px; }
            .features-head h2, .compare-head h2, .monitor-head h2 { font-size: 28px; }
            .footer { padding: 40px 32px 24px; }
            .footer-grid { grid-template-columns: 1fr; gap: 28px; }
        }
        @media (max-width: 480px) {
            .hero { padding: 0 20px; }
            .hero h1 { font-size: 32px; }
            .hero-logo-sekolah { width: 36px; height: 36px; }
            .hero-logo-cross { font-size: 16px; }
            .hero-logo { gap: 8px; margin-bottom: 32px; }
            .hero-stats { gap: 24px; }
            .login-card { padding: 24px; }
            .features, .monitor, .compare { padding: 48px 20px; }
            .compare-stats { grid-template-columns: 1fr; }
            .chart-row { grid-template-columns: 1fr; gap: 6px; }
            .chart-row > .chart-label { text-align: left; }
            .chart-arrow { padding-left: 0; }
            .compare-illust { grid-template-columns: 1fr; }
            .footer { padding: 32px 20px 20px; }
        }
    </style>
</head>
<body>

    <!-- ===== HERO ===== -->
    <section class="hero">
        <div class="hero-left">
            <div class="hero-logo">
                <img src="{{ asset('images/logo-sd-khadijah-3.jpg') }}" alt="SD Khadijah 3" class="hero-logo-sekolah">
                <span class="hero-logo-cross">×</span>
                <div class="hero-logo-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <span class="hero-logo-text">Tartil<em>Pro</em></span>
            </div>

            <div class="hero-badge">
                <div class="hero-badge-dot"></div>
                <span>Sistem Aktif</span>
            </div>

            <h1>
                Sistem Penilaian<br>
                <span class="accent">Tartil</span> <span class="gold">Online</span><br>
                SD Khadijah 3
            </h1>

            <p class="hero-desc">
                Platform terintegrasi untuk manajemen penilaian harian, absensi, ujian munaqosyah, dan raport semester di SD Khadijah 3 Surabaya.
            </p>

            <div class="hero-stats">
                <div>
                    <div class="hero-stat-num">7.000<span>+</span></div>
                    <div class="hero-stat-label">Jurnal / Hari</div>
                </div>
                <div>
                    <div class="hero-stat-num">100<span>%</span></div>
                    <div class="hero-stat-label">Real-time</div>
                </div>
                <div>
                    <div class="hero-stat-num">R2<span></span></div>
                    <div class="hero-stat-label">Auto-calculate</div>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="login-card">
                <div class="login-tabs">
                    <button class="login-tab active" onclick="switchTab('admin', this)">Admin & Guru</button>
                    <button class="login-tab" onclick="switchTab('siswa', this)">Siswa</button>
                </div>

                <div class="login-panel active" id="panel-admin">
                    <div class="login-title">Selamat Datang</div>
                    <div class="login-sub">Masuk sebagai admin atau guru</div>

                    @if(session('error'))
                    <div class="login-alert error show">{{ session('error') }}</div>
                    @endif
                    @if(session('warning'))
                    <div class="login-alert warning show">{{ session('warning') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="field">
                            <label>Alamat Email</label>
                            <input type="email" name="email" placeholder="nama@sekolah.sch.id" required autofocus>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                        </div>
                        <button type="submit" class="btn-submit">Masuk ke Dashboard</button>
                    </form>
                    <div class="login-divider">atau</div>
                    <div class="login-hint">
                        <strong>Lupa password?</strong> Hubungi administrator sistem.
                    </div>
                </div>

                <div class="login-panel" id="panel-siswa">
                    <div class="login-title">Login Siswa</div>
                    <div class="login-sub">Akses rapor dan jurnal Anda</div>

                    @if(session('error'))
                    <div class="login-alert error show">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('siswa.login.post') }}">
                        @csrf
                        <div class="field">
                            <label>Nomor Induk Siswa</label>
                            <input type="text" name="nis" placeholder="Contoh: 2025001" required autofocus>
                        </div>
                        <div class="field">
                            <label>Nomor HP Terdaftar</label>
                            <input type="password" name="no_hp" placeholder="Nomor HP sebagai password" required>
                        </div>
                        <button type="submit" class="btn-submit">Masuk</button>
                    </form>
                    <div class="login-divider">informasi</div>
                    <div class="login-hint">
                        Password Anda adalah <strong>nomor HP</strong> yang terdaftar.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="features">
        <div class="features-head">
            <h2>Fitur <em>Unggulan</em></h2>
            <p>Dirancang khusus untuk efisiensi penilaian tahsin dan tartil di pondok pesantren dan TPQ</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <h3>Jurnal Harian Grid</h3>
                <p>Input penilaian harian untuk ratusan siswa dalam satu tampilan grid yang terstruktur. Guru dapat mengisi penilaian B, C, atau K untuk setiap siswa secara bersamaan, mencatat surat dan ayat yang dibaca, dengan tampilan keyboard-friendly untuk efisiensi maksimal.</p>
                <span class="feature-benefit">7.000+ jurnal per hari</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>Rekap R2 Otomatis</h3>
                <p>Sistem menghitung R2 Harian dari persentase penilaian B dalam jurnal dan R2 Penilaian dari rata-rata nilai indikator rapor secara otomatis. R2 Akhir dihasilkan dari rata-rata keduanya, tersimpan dalam cache 6 jam untuk performa optimal ribuan siswa.</p>
                <span class="feature-benefit">Real-time calculation</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <h3>Ujian Munaqosyah</h3>
                <p>Admin membuat jadwal ujian munaqosyah dengan menentukan surat dan kriteria kelulusan. Guru mendaftarkan siswa yang mengikuti ujian, admin melakukan approval, dan guru memberikan status Lulus atau Tidak Lulus secara real-time dengan sistem toggle.</p>
                <span class="feature-benefit">Approval & toggle system</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <h3>Cetak Rapor PDF</h3>
                <p>Cetak rapor semester 1 kelas penuh (30 siswa) dalam format PDF profesional dengan 1 klik — dari yang biasanya butuh 4 jam ketik manual, jadi hanya 30 detik. Kop surat, logo, stempel, tanda tangan digital, dan tanggal cetak dapat dikustomisasi penuh.</p>
                <span class="feature-benefit">1 klik = 30 siswa</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3>Multi Kelas & Mutasi</h3>
                <p>Kelola kelas reguler dan kelas tartil secara paralel dalam satu sistem. Siswa dapat dimutasi ke kelas tartil di pertengahan semester dengan perhitungan target dinamis berdasarkan tanggal masuk, tanpa merusak data kelas reguler.</p>
                <span class="feature-benefit">Target dinamis otomatis</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <h3>Monitoring Guru</h3>
                <p>Admin memantau progress pengisian jurnal dan absensi setiap guru secara real-time. Sistem mendeteksi otomatis guru yang belum mengisi jurnal hari ini, menampilkan persentase kelengkapan data, dan memberikan notifikasi reminder.</p>
                <span class="feature-benefit">Deteksi otomatis</span>
            </div>
        </div>
    </section>

    <!-- ===== MONITORING ORANG TUA ===== -->
    <section class="monitor">
        <div class="monitor-inner">
            <div class="monitor-head">
                <h2>Perkembangan Anak, <em>Transparan</em></h2>
                <p>Orang tua dapat memantau perkembangan pembelajaran Al-Quran anak secara langsung melalui akses login siswa yang terintegrasi</p>
            </div>

            <div class="monitor-grid">
                <div class="monitor-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Pantau Perkembangan Anak
                        <span class="badge-new">Baru</span>
                    </h3>
                    <p>Orang tua dapat login menggunakan NIS dan nomor HP anak untuk melihat perkembangan pembelajaran Al-Quran secara detail dan real-time.</p>
                    <ul class="monitor-list">
                        <li>Lihat riwayat penilaian harian (B, C, K) untuk setiap pertemuan</li>
                        <li>Pantau persentase ketuntasan bacaan per surat dan ayat</li>
                        <li>Lihat nilai R2 Harian dan R2 Penilaian semester berjalan</li>
                        <li>Unduh rapor semester dalam format PDF kapan saja</li>
                        <li>Lihat status kelulusan ujian munaqosyah secara langsung</li>
                    </ul>
                </div>

                <div class="monitor-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Kerjasama Orang Tua & Guru
                    </h3>
                    <p>Sistem membangun jembatan komunikasi antara orang tua dan guru tartil melalui data yang transparan dan terukur.</p>
                    <ul class="monitor-list">
                        <li>Orang tua melihat absensi kehadiran anak setiap hari</li>
                        <li>Tahu kapan anak mutasi masuk kelas tartil dan progressnya</li>
                        <li>Monitor capaian target bacaan mingguan dan bulanan</li>
                        <li>Terima notifikasi jika anak belum lulus ujian munaqosyah</li>
                        <li>Bandingkan progress anak dengan rata-rata kelas</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== COMPARISON: MANUAL vs TARTILPRO ===== -->
    <section class="compare">
        <div class="compare-head">
            <h2>Lebih Efisien & <em>Efektif</em> dari Manual</h2>
            <p>Bandingkan penilaian, absensi, dan cetak rapor manual dengan sistem TartilPro — dari 13,5 jam jadi 2,6 jam per semester</p>
        </div>

        <div class="compare-illust">
            <div class="compare-side manual">
                <svg class="compare-side-icon" viewBox="0 0 64 64" fill="none">
                    <rect x="8" y="4" width="48" height="56" rx="4" fill="#fecaca" stroke="#f87171" stroke-width="2"/>
                    <line x1="16" y1="16" x2="48" y2="16" stroke="#f87171" stroke-width="2" stroke-linecap="round"/>
                    <line x1="16" y1="24" x2="40" y2="24" stroke="#f87171" stroke-width="2" stroke-linecap="round"/>
                    <line x1="16" y1="32" x2="44" y2="32" stroke="#f87171" stroke-width="2" stroke-linecap="round"/>
                    <line x1="16" y1="40" x2="36" y2="40" stroke="#f87171" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="52" cy="52" r="10" fill="#fecaca" stroke="#ef4444" stroke-width="2"/>
                    <path d="M48 52l2.5 2.5L57 47" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3>Manual (Kertas)</h3>
                <p>Pencatatan di buku, hitung manual, risiko hilang, sulit ditelusuri</p>
            </div>
            <div class="compare-side digital">
                <svg class="compare-side-icon" viewBox="0 0 64 64" fill="none">
                    <rect x="4" y="8" width="56" height="40" rx="6" fill="#d1fae5" stroke="#34d399" stroke-width="2"/>
                    <rect x="12" y="4" width="40" height="4" rx="2" fill="#6ee7b7"/>
                    <rect x="14" y="20" width="20" height="4" rx="2" fill="#34d399"/>
                    <rect x="14" y="28" width="28" height="4" rx="2" fill="#6ee7b7"/>
                    <rect x="14" y="36" width="24" height="4" rx="2" fill="#34d399"/>
                    <circle cx="48" cy="28" r="8" fill="#d1fae5" stroke="#059669" stroke-width="2"/>
                    <path d="M45 28l2 2 5-5" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="20" y="52" width="24" height="8" rx="4" fill="#d1fae5" stroke="#34d399" stroke-width="1.5"/>
                </svg>
                <h3>TartilPro (Digital)</h3>
                <p>Input otomatis, perhitungan real-time, data tersimpan aman di cloud</p>
            </div>
        </div>

        <!-- Chart: Waktu Penilaian -->
        <div class="chart-row">
            <div class="chart-label">Waktu Penilaian</div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar manual" style="width:100%">13,5 jam (810 mnt)</div>
                </div>
                <span class="chart-value manual-val">810 mnt</span>
            </div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar digital" style="width:19.5%">2,6 jam (158 mnt)</div>
                </div>
                <span class="chart-value digital-val">158 mnt</span>
            </div>
        </div>
        <div class="chart-arrow">
            <span class="chart-arrow-sym">&lt;</span>
            <span class="chart-tag down">80% lebih cepat</span>
        </div>

        <!-- Chart: Error Data -->
        <div class="chart-row">
            <div class="chart-label">Error Data</div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar manual" style="width:100%">15% salah hitung</div>
                </div>
                <span class="chart-value manual-val">15%</span>
            </div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar digital" style="width:30%">&lt; 0,5% error</div>
                </div>
                <span class="chart-value digital-val">&lt;0,5%</span>
            </div>
        </div>
        <div class="chart-arrow">
            <span class="chart-arrow-sym">&lt;</span>
            <span class="chart-tag down">96,7% lebih akurat</span>
        </div>

        <!-- Chart: Penggunaan Kertas -->
        <div class="chart-row">
            <div class="chart-label">Penggunaan Kertas</div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar manual" style="width:100%">&gt; 10 rim / tahun</div>
                </div>
                <span class="chart-value manual-val">&gt;20 rim</span>
            </div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar digital" style="width:20%">0 rim</div>
                </div>
                <span class="chart-value digital-val">0 rim</span>
            </div>
        </div>
        <div class="chart-arrow">
            <span class="chart-arrow-sym">&lt;</span>
            <span class="chart-tag down">100% paperless</span>
        </div>

        <!-- Chart: Akses Data -->
        <div class="chart-row">
            <div class="chart-label">Akses Data</div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar manual" style="width:100%">Terbatas (di sekolah)</div>
                </div>
                <span class="chart-value manual-val">Terbatas</span>
            </div>
            <div class="chart-cell">
                <div class="chart-bar-wrap">
                    <div class="chart-bar digital" style="width:100%">24/7 Real-time</div>
                </div>
                <span class="chart-value digital-val">24/7</span>
            </div>
        </div>
        <div class="chart-arrow">
            <span class="chart-arrow-sym">&gt;</span>
            <span class="chart-tag up">Unlimited access</span>
        </div>

        <!-- Improvement Stats -->
        <div class="compare-stats">
            <div class="compare-stat">
                <div class="compare-stat-num">83%</div>
                <div class="compare-stat-label">Lebih Cepat<br>Input Jurnal</div>
            </div>
            <div class="compare-stat">
                <div class="compare-stat-num">67%</div>
                <div class="compare-stat-label">Lebih Cepat<br>Absensi & Catatan</div>
            </div>
            <div class="compare-stat">
                <div class="compare-stat-num">100%</div>
                <div class="compare-stat-label">Paperless<br>Tanpa Kertas</div>
            </div>
            <div class="compare-stat">
                <div class="compare-stat-num">5x</div>
                <div class="compare-stat-label">Lebih Cepat<br>Total Workflow</div>
            </div>
        </div>

        <!-- Breakdown: perhitungan 5x -->
        <div class="breakdown">
            <div class="breakdown-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Perhitungan "5x Lebih Cepat" — Jurnal + Absensi + Cetak Rapor
            </div>
            <div class="breakdown-grid">
                <div class="breakdown-head">Aktivitas Guru</div>
                <div class="breakdown-head">Manual (Kertas)</div>
                <div class="breakdown-head">TartilPro</div>
                <div class="breakdown-head">Hemat Waktu</div>

                <div class="breakdown-cell task">Input jurnal 1 kelas (30 hari)</div>
                <div class="breakdown-cell time-manual">90 detik/hari &times; 30 = 45 mnt</div>
                <div class="breakdown-cell time-digital">15 detik/hari &times; 30 = 7,5 mnt</div>
                <div class="breakdown-cell result">83%</div>

                <div class="breakdown-cell task">Pengisian absensi &amp; catatan 1 kelas (30 hari)</div>
                <div class="breakdown-cell time-manual">15 mnt/hari &times; 30 = 450 mnt</div>
                <div class="breakdown-cell time-digital">5 mnt/hari &times; 30 = 150 mnt</div>
                <div class="breakdown-cell result">67%</div>

                <div class="breakdown-cell task">Rekap &amp; monitoring progress</div>
                <div class="breakdown-cell time-manual">30 menit</div>
                <div class="breakdown-cell time-digital">5 detik (real-time)</div>
                <div class="breakdown-cell result">97%</div>

                <div class="breakdown-cell task">Rekap &amp; hitung persentase B/C/K</div>
                <div class="breakdown-cell time-manual">45 menit</div>
                <div class="breakdown-cell time-digital">0 detik (auto)</div>
                <div class="breakdown-cell result">100%</div>

                <div class="breakdown-cell task">Cetak rapor 1 kelas (30 siswa)</div>
                <div class="breakdown-cell time-manual">240 menit (4 jam) ketik &amp; format manual</div>
                <div class="breakdown-cell time-digital">1 klik (30 detik) batch PDF otomatis</div>
                <div class="breakdown-cell result">99,8%</div>
            </div>
            <div class="breakdown-total">
                <div class="breakdown-cell">Total perbandingan</div>
                <div class="breakdown-cell time-manual">810 mnt (13,5 jam)</div>
                <div class="breakdown-cell time-digital">158 mnt (2,6 jam)</div>
                <div class="breakdown-cell result">= 5x</div>
            </div>
            <div class="breakdown-formula">
                <code>810 menit &divide; 158 menit = 5,1x</code> lebih cepat (jurnal + absensi + cetak rapor) dengan 1 klik batch PDF
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">
                    <img src="{{ asset('images/logo-sd-khadijah-3.jpg') }}" alt="SD Khadijah 3" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--accent);">
                    <span class="footer-brand-name">SD Khadijah 3 <em style="font-family: 'Instrument Serif', serif; font-weight: 400; font-style: italic; color: var(--accent);">× TartilPro</em></span>
                </div>
                <p class="footer-about">Sistem penilaian tartil online SD Khadijah 3 Surabaya. Platform terintegrasi untuk manajemen penilaian tahsin dan tartil.</p>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="{{ route('siswa.login') }}">Login Siswa</a>
            </div>
            <div class="footer-col">
                <h4>Bantuan</h4>
                <a href="#">Panduan Pengguna</a>
                <a href="#">Hubungi Kami</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span class="footer-copy">&copy; {{ date('Y') }} SD Khadijah 3 Surabaya. All rights reserved.</span>
            <span class="footer-company">
                <img src="{{ asset('images/logo-sd-khadijah-3.jpg') }}" alt="" style="width: 14px; height: 14px; border-radius: 50%; object-fit: cover;">
                Sistem Penilaian Tartil Online — <strong>SD Khadijah 3</strong>
            </span>
        </div>
    </footer>

    <script>
        function switchTab(tab, el) {
            document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.login-panel').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('panel-' + tab).classList.add('active');
        }
    </script>

</body>
</html>
