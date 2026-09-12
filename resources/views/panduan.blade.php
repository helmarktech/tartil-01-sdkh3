<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pengguna - TartilPro SD Khadijah 3</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0c8a5f">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TartilPro">
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
            --gold: #b48a3e;
            --shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
            --shadow-lg: 0 4px 24px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.02);
            --radius: 14px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* ===== TOPBAR ===== */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }
        .topbar-inner {
            max-width: 960px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .topbar-logo {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-logo svg { width: 17px; height: 17px; color: #fff; }
        .topbar-name { font-size: 17px; font-weight: 800; letter-spacing: -0.5px; }
        .topbar-name em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            border: 1.5px solid var(--border);
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-secondary);
            transition: all 0.15s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent-dark); background: var(--accent-soft); }
        .btn-back svg { width: 14px; height: 14px; }

        /* ===== HEADER ===== */
        .page-head {
            max-width: 960px;
            margin: 0 auto;
            padding: 64px 24px 40px;
            text-align: center;
        }
        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            background: var(--accent-soft);
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            color: var(--accent-dark);
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .page-head h1 {
            font-size: 40px;
            font-weight: 800;
            letter-spacing: -1.5px;
            line-height: 1.15;
            margin-bottom: 14px;
        }
        .page-head h1 em {
            font-family: 'Instrument Serif', serif;
            font-weight: 400;
            font-style: italic;
            color: var(--accent);
        }
        .page-head p {
            font-size: 15px;
            color: var(--ink-muted);
            max-width: 560px;
            margin: 0 auto 24px;
        }
        .btn-pdf {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            background: var(--ink);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-pdf:hover { background: var(--ink-secondary); transform: translateY(-1px); }
        .btn-pdf svg { width: 16px; height: 16px; }

        /* ===== TOC ===== */
        .toc {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 24px 48px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .toc-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            transition: all 0.2s;
            display: block;
        }
        .toc-card:hover {
            border-color: #d6d3d1;
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        .toc-icon {
            width: 42px; height: 42px;
            border-radius: 11px;
            background: var(--accent-soft);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 14px;
        }
        .toc-icon svg { width: 20px; height: 20px; color: var(--accent-dark); }
        .toc-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .toc-card p { font-size: 12.5px; color: var(--ink-muted); line-height: 1.6; }
        .toc-num {
            font-family: 'Instrument Serif', serif;
            font-style: italic;
            color: var(--gold);
            font-size: 13px;
            font-weight: 600;
        }

        /* ===== CONTENT ===== */
        .guide {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 24px 24px;
        }
        .guide-section {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px;
            margin-bottom: 24px;
        }
        .guide-section-head {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
        }
        .guide-num {
            flex-shrink: 0;
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--accent);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Instrument Serif', serif;
            font-style: italic;
            font-size: 20px;
        }
        .guide-section-head h2 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .guide-section-head p {
            font-size: 13px;
            color: var(--ink-muted);
            margin-top: 4px;
        }

        .guide-sub {
            margin-top: 28px;
        }
        .guide-sub:first-of-type { margin-top: 0; }
        .guide-sub h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .guide-sub h3 .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
        }
        .guide-sub > p {
            font-size: 13.5px;
            color: var(--ink-secondary);
            margin-bottom: 12px;
        }

        .steps {
            list-style: none;
            counter-reset: step;
            margin: 12px 0;
        }
        .steps li {
            counter-increment: step;
            display: flex;
            gap: 12px;
            padding: 10px 0;
            font-size: 13.5px;
            color: var(--ink-secondary);
            line-height: 1.6;
        }
        .steps li::before {
            content: counter(step);
            flex-shrink: 0;
            width: 24px; height: 24px;
            border-radius: 50%;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }
        .steps li strong { color: var(--ink); }

        .check-list {
            list-style: none;
            margin: 12px 0;
        }
        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13.5px;
            color: var(--ink-secondary);
            padding: 6px 0;
            line-height: 1.6;
        }
        .check-list li::before {
            content: '';
            width: 16px; height: 16px;
            border-radius: 50%;
            background: var(--accent-soft);
            flex-shrink: 0;
            margin-top: 3px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23065f43' stroke-width='3'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }

        .note {
            margin-top: 16px;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.7;
        }
        .note.info {
            background: var(--accent-soft);
            color: var(--accent-dark);
            border: 1px solid #a7f3d0;
        }
        .note.warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .note strong { font-weight: 700; }
        .note ul { margin: 6px 0 0 18px; }
        .note li { margin-bottom: 4px; }

        .badge-tag {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-tag.green { background: var(--accent-soft); color: var(--accent-dark); }
        .badge-tag.gold { background: #fef3c7; color: #92400e; }

        /* ===== FOOTER ===== */
        .page-footer {
            border-top: 1px solid var(--border);
            margin-top: 40px;
            padding: 32px 24px;
            text-align: center;
            font-size: 12px;
            color: var(--ink-faint);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .page-head { padding: 44px 20px 32px; }
            .page-head h1 { font-size: 30px; }
            .toc { grid-template-columns: 1fr; padding: 0 20px 36px; }
            .guide { padding: 0 20px 20px; }
            .guide-section { padding: 28px 22px; }
            .guide-section-head { flex-direction: column; gap: 12px; }
            .guide-section-head h2 { font-size: 19px; }
        }
    </style>
</head>
<body>

    <!-- ===== TOPBAR ===== -->
    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-brand">
                <div class="topbar-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <span class="topbar-name">Tartil<em>Pro</em></span>
            </div>
            <a href="/" class="btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali ke Login
            </a>
        </div>
    </header>

    <!-- ===== HEADER ===== -->
    <section class="page-head">
        <div class="page-badge">Pusat Bantuan</div>
        <h1>Panduan <em>Pengguna</em></h1>
        <p>Panduan lengkap instalasi aplikasi TartilPro di Android dan iPhone, cara mengaktifkan notifikasi, serta panduan penggunaan sistem untuk siswa dan orang tua.</p>
        <a href="{{ route('panduan.buku-siswa') }}" target="_blank" class="btn-pdf">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Unduh Buku Panduan Siswa (PDF)
        </a>
    </section>

    <!-- ===== DAFTAR ISI ===== -->
    <nav class="toc">
        <a href="#android" class="toc-card">
            <div class="toc-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            </div>
            <span class="toc-num">Bagian 1</span>
            <h3>Install Aplikasi di Android</h3>
            <p>Cara memasang aplikasi TartilPro di HP Android melalui Google Chrome dan mengaktifkan notifikasi.</p>
        </a>
        <a href="#ios" class="toc-card">
            <div class="toc-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            </div>
            <span class="toc-num">Bagian 2</span>
            <h3>Install Aplikasi di iPhone</h3>
            <p>Cara memasang aplikasi TartilPro di iPhone/iPad melalui Safari dan mengaktifkan notifikasi.</p>
        </a>
        <a href="#siswa" class="toc-card">
            <div class="toc-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <span class="toc-num">Bagian 3</span>
            <h3>Panduan Siswa &amp; Orang Tua</h3>
            <p>Panduan lengkap penggunaan sistem: dashboard, rapor, hafalan, pendampingan ortu, dan lainnya.</p>
        </a>
    </nav>

    <main class="guide">

        <!-- ===== BAGIAN 1: ANDROID ===== -->
        <section class="guide-section" id="android">
            <div class="guide-section-head">
                <div class="guide-num">1</div>
                <div>
                    <h2>Install Aplikasi di Android</h2>
                    <p>TartilPro adalah aplikasi web (PWA) — tidak perlu unduh dari Play Store, cukup pasang langsung dari browser.</p>
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>Langkah Instalasi</h3>
                <ol class="steps">
                    <li>Buka aplikasi <strong>Google Chrome</strong> di HP Android Anda.</li>
                    <li>Ketik alamat website TartilPro yang diberikan sekolah, lalu <strong>login sebagai siswa</strong> menggunakan NIS dan nomor HP terdaftar.</li>
                    <li>Ketuk ikon <strong>titik tiga (&#8942;)</strong> di pojok kanan atas Chrome.</li>
                    <li>Pilih menu <strong>"Tambahkan ke layar utama"</strong> atau <strong>"Install aplikasi"</strong>.</li>
                    <li>Ketuk <strong>"Tambah"</strong> / <strong>"Install"</strong> untuk konfirmasi.</li>
                    <li>Ikon <strong>TartilPro</strong> akan muncul di layar utama HP — buka aplikasi dari ikon tersebut seperti aplikasi biasa.</li>
                </ol>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>Mengaktifkan Notifikasi <span class="badge-tag green">Penting</span></h3>
                <p>Agar orang tua/siswa menerima pemberitahuan saat guru menginput jurnal harian, menambahkan setoran hafalan, atau mengkonfirmasi laporan pendampingan, aktifkan notifikasi dengan cara:</p>
                <ol class="steps">
                    <li>Buka aplikasi TartilPro yang sudah terpasang, lalu <strong>login</strong>.</li>
                    <li>Di bagian atas halaman (topbar), akan muncul tombol <strong>"Aktifkan Notifikasi"</strong> di samping ikon lonceng.</li>
                    <li>Ketuk tombol tersebut, lalu pilih <strong>"Izinkan"</strong> (Allow) saat browser meminta izin notifikasi.</li>
                    <li>Selesai — notifikasi akan masuk ke HP Anda meskipun aplikasi sedang tidak dibuka.</li>
                </ol>
                <div class="note info">
                    <strong>Jika tombol "Aktifkan Notifikasi" tidak muncul</strong> atau Anda pernah menolak izin notifikasi, aktifkan manual melalui:
                    <ul>
                        <li>Chrome &rarr; <strong>Setelan</strong> &rarr; <strong>Setelan Situs</strong> &rarr; <strong>Notifikasi</strong></li>
                        <li>Cari alamat website TartilPro, lalu pilih <strong>Izinkan</strong>.</li>
                    </ul>
                </div>
                <div class="note warning">
                    <strong>Catatan:</strong> Fitur notifikasi hanya aktif jika aplikasi diakses melalui alamat <strong>HTTPS</strong> (alamat resmi dari sekolah). Pastikan juga koneksi internet aktif agar notifikasi dapat diterima.
                </div>
            </div>
        </section>

        <!-- ===== BAGIAN 2: iPHONE ===== -->
        <section class="guide-section" id="ios">
            <div class="guide-section-head">
                <div class="guide-num">2</div>
                <div>
                    <h2>Install Aplikasi di iPhone / iPad</h2>
                    <p>Di perangkat Apple, pemasangan aplikasi wajib dilakukan melalui browser <strong>Safari</strong>.</p>
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>Langkah Instalasi</h3>
                <ol class="steps">
                    <li>Buka aplikasi <strong>Safari</strong> di iPhone/iPad Anda (bukan Chrome atau browser lain).</li>
                    <li>Ketik alamat website TartilPro yang diberikan sekolah, lalu <strong>login sebagai siswa</strong> menggunakan NIS dan nomor HP terdaftar.</li>
                    <li>Ketuk ikon <strong>Bagikan (Share)</strong> — ikon kotak dengan panah ke atas di bagian bawah layar.</li>
                    <li>Gulir ke bawah, lalu pilih <strong>"Tambahkan ke Layar Utama"</strong> (Add to Home Screen).</li>
                    <li>Ketuk <strong>"Tambah"</strong> (Add) di pojok kanan atas.</li>
                    <li>Ikon <strong>TartilPro</strong> akan muncul di layar utama — selalu buka aplikasi dari ikon ini.</li>
                </ol>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>Mengaktifkan Notifikasi <span class="badge-tag green">Penting</span></h3>
                <p>Notifikasi di iPhone memerlukan <strong>iOS versi 16.4 atau lebih baru</strong>, dan <strong>hanya berfungsi dari aplikasi yang sudah terpasang di layar utama</strong> — tidak berfungsi jika dibuka dari tab Safari biasa.</p>
                <ol class="steps">
                    <li>Pastikan aplikasi sudah terpasang di layar utama (ikuti langkah instalasi di atas).</li>
                    <li>Buka aplikasi TartilPro <strong>dari ikon di layar utama</strong>, lalu login.</li>
                    <li>Ketuk tombol <strong>"Aktifkan Notifikasi"</strong> yang muncul di bagian atas halaman.</li>
                    <li>Pilih <strong>"Izinkan"</strong> (Allow) saat sistem meminta izin notifikasi.</li>
                    <li>Selesai — notifikasi jurnal, setoran hafalan, dan konfirmasi pendampingan akan masuk ke iPhone Anda.</li>
                </ol>
                <div class="note info">
                    <strong>Jika notifikasi tidak masuk</strong>, periksa pengaturan berikut:
                    <ul>
                        <li><strong>Pengaturan iPhone</strong> &rarr; <strong>Notifikasi</strong> &rarr; cari <strong>TartilPro</strong> &rarr; aktifkan <strong>Izinkan Notifikasi</strong>.</li>
                        <li>Pastikan mode <strong>Fokus / Jangan Ganggu</strong> tidak memblokir notifikasi.</li>
                        <li>Pastikan iOS sudah versi <strong>16.4 ke atas</strong> (Pengaturan &rarr; Umum &rarr; Mengenai).</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ===== BAGIAN 3: PANDUAN SISWA ===== -->
        <section class="guide-section" id="siswa">
            <div class="guide-section-head">
                <div class="guide-num">3</div>
                <div>
                    <h2>Panduan Siswa &amp; Orang Tua</h2>
                    <p>Panduan lengkap penggunaan sistem TartilPro untuk siswa dan orang tua — dari Buku Panduan Siswa resmi.</p>
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.1 Login dan Navigasi Siswa</h3>
                <p>Siswa login menggunakan <strong>NIS</strong> dan <strong>nomor HP</strong> yang terdaftar di sistem.</p>
                <ol class="steps">
                    <li>Buka halaman login, lalu pilih tab <strong>"Siswa"</strong>.</li>
                    <li>Masukkan NIS.</li>
                    <li>Masukkan nomor HP (sesuai yang terdaftar di sistem).</li>
                    <li>Klik <strong>Masuk</strong>.</li>
                </ol>
                <div class="note info">
                    <strong>Catatan:</strong>
                    <ul>
                        <li>Jika nomor HP berubah, hubungi guru atau admin untuk diperbarui.</li>
                        <li>Siswa juga dapat mengedit nomor HP sendiri di menu <strong>Profil</strong>.</li>
                    </ul>
                </div>
                <p style="margin-top:12px;">Setelah login, siswa melihat menu di bagian atas: <strong>Dashboard, Rapor, Hafalan, Pendampingan Ortu, Riwayat Kelas, Track Record,</strong> dan <strong>Profil</strong>.</p>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.2 Dashboard Siswa</h3>
                <p>Dashboard menampilkan ringkasan pembelajaran siswa: R2 Harian, R2 Penilaian, R2 Akhir, progress hafalan, statistik B/C/K, grafik bulanan, dan jurnal terbaru.</p>
                <ul class="check-list">
                    <li><span><strong>R2 Harian</strong> = hasil penilaian jurnal harian (B=2, C=1, K=0).</span></li>
                    <li><span><strong>R2 Penilaian</strong> = hasil penilaian rapor internal.</span></li>
                    <li><span><strong>R2 Akhir</strong> = rata-rata dari R2 Harian dan R2 Penilaian.</span></li>
                    <li><span><strong>R2 Akhir Sebelum Penilaian</strong> = nilai R2 Harian.</span></li>
                    <li><span><strong>R2 Akhir Setelah Penilaian</strong> = nilai R2 Akhir gabungan.</span></li>
                </ul>
                <p><strong>Jurnal Terbaru:</strong> menampilkan 30 jurnal terbaru dengan tanggal, penilaian B/C/K, surat, dan catatan guru. Catatan guru yang ditulis saat input jurnal akan langsung muncul di sini agar siswa/orang tua dapat memantau keterangan dari guru.</p>
                <p><strong>Progress Hafalan:</strong> jika siswa memiliki kelas tartil, dashboard menampilkan progress juz 1–30 dengan warna status: hafal, murajaah, setengah hafal, baru, atau belum.</p>
                <p><strong>Popup Konfirmasi Orang Tua:</strong> jika ada setoran hafalan yang belum dikonfirmasi, popup akan muncul otomatis di dashboard untuk memudahkan konfirmasi.</p>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.3 Rapor</h3>
                <p>Halaman daftar rapor semester yang sudah ditutup oleh admin.</p>
                <ol class="steps">
                    <li>Klik menu <strong>"Rapor"</strong>.</li>
                    <li>Pilih semester yang tersedia.</li>
                    <li>Klik <strong>"Lihat Rapor PDF"</strong> untuk mengunduh atau melihat rapor.</li>
                </ol>
                <div class="note info">
                    <strong>Catatan:</strong>
                    <ul>
                        <li>Rapor hanya tersedia untuk semester yang sudah ditutup.</li>
                        <li>Rapor semester yang sedang berlangsung tidak dapat diunduh.</li>
                    </ul>
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.4 Hafalan Al-Quran</h3>
                <p>Halaman untuk memantau progress hafalan, melihat setoran, dan mengkonfirmasi setoran oleh orang tua.</p>
                <ul class="check-list">
                    <li><span><strong>Progress Juz:</strong> grid juz 1–30 dengan warna sesuai status. Klik juz untuk memilih filter.</span></li>
                    <li><span><strong>Surat yang Telah Dihafal:</strong> daftar surat yang ditandai status hafal oleh guru. Hanya surat dengan status hafal 100% yang muncul di sini.</span></li>
                    <li><span><strong>Riwayat Setoran:</strong> tabel riwayat setoran lengkap — juz, surat, ayat, status, kualitas, tanggal setoran, tanggal konfirmasi ortu, dan nama guru.</span></li>
                </ul>
                <p><strong>Konfirmasi Orang Tua:</strong></p>
                <ol class="steps">
                    <li>Lihat daftar setoran yang belum dikonfirmasi.</li>
                    <li>Centang setoran yang sudah dipantau di rumah.</li>
                    <li>Klik <strong>"Konfirmasi Setoran Terpilih"</strong>.</li>
                </ol>
                <div class="note info">
                    Setelah dikonfirmasi, status di kolom <strong>"Tanggal Konfirmasi Ortu"</strong> akan terisi, dan guru dapat melihat status konfirmasi tersebut di halaman tahfidz guru.
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.5 Pendampingan Orang Tua</h3>
                <p>Halaman untuk melaporkan kegiatan tadarus dan murajaah yang dilakukan siswa bersama orang tua di rumah.</p>
                <ol class="steps">
                    <li>Klik menu <strong>"Pendampingan Ortu"</strong>.</li>
                    <li>Pilih jenis kegiatan: <strong>Tadarus</strong> atau <strong>Murajaah</strong>.</li>
                    <li>Pilih surat.</li>
                    <li>Isi ayat mulai dan ayat selesai (opsional).</li>
                    <li>Pilih tanggal kegiatan.</li>
                    <li>Tambahkan catatan jika perlu.</li>
                    <li>Klik <strong>"Kirim Laporan"</strong>.</li>
                </ol>
                <div class="note info">
                    Laporan akan masuk ke guru. Setelah guru mengkonfirmasi, status laporan muncul di halaman ini.
                </div>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.6 Riwayat Kelas</h3>
                <p>Menampilkan kelas saat ini (reguler, tartil, guru) dan riwayat perpindahan kelas tartil. Perpindahan kelas tartil diajukan oleh guru, disetujui admin atau guru kelas tujuan, dan riwayatnya muncul di halaman ini.</p>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.7 Track Record</h3>
                <p>Menampilkan profil siswa, riwayat perpindahan kelas, dan rekap performa per semester (rata-rata jurnal, statistik B/C/K, total pertemuan).</p>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.8 Munaqosyah</h3>
                <p>Menampilkan riwayat ujian munaqosyah yang pernah diikuti, status lulus/tidak lulus/pending, dan catatan.</p>
                <ul class="check-list">
                    <li><span>Admin membuat ujian.</span></li>
                    <li><span>Guru mendaftarkan siswa.</span></li>
                    <li><span>Admin menyetujui pendaftaran.</span></li>
                    <li><span>Guru memberi nilai lulus/tidak lulus.</span></li>
                    <li><span>Siswa melihat hasil akhir di halaman ini.</span></li>
                </ul>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.9 Notifikasi</h3>
                <p>Siswa/orang tua menerima notifikasi secara otomatis saat:</p>
                <ul class="check-list">
                    <li><span>Guru menginput <strong>jurnal harian</strong>.</span></li>
                    <li><span>Guru menambahkan <strong>setoran hafalan</strong>.</span></li>
                    <li><span>Guru mengkonfirmasi <strong>laporan pendampingan orang tua</strong>.</span></li>
                </ul>
                <p>Notifikasi dapat dilihat melalui <strong>ikon lonceng</strong> di bagian atas halaman, atau pada halaman <strong>Notifikasi</strong>. Agar notifikasi juga masuk sebagai pemberitahuan di HP (push notification), ikuti panduan instalasi di <a href="#android" style="color: var(--accent-dark); font-weight: 600;">Bagian 1 (Android)</a> atau <a href="#ios" style="color: var(--accent-dark); font-weight: 600;">Bagian 2 (iPhone)</a>.</p>
            </div>

            <div class="guide-sub">
                <h3><span class="dot"></span>3.10 Profil</h3>
                <p>Siswa dapat mengedit nomor HP sendiri.</p>
                <ol class="steps">
                    <li>Klik menu <strong>"Profil"</strong>.</li>
                    <li>Masukkan nomor HP baru.</li>
                    <li>Klik <strong>"Simpan Perubahan"</strong>.</li>
                </ol>
            </div>
        </section>

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="page-footer">
        &copy; {{ date('Y') }} SD Khadijah 3 Surabaya &mdash; Sistem Penilaian Tartil Online
    </footer>

</body>
</html>
