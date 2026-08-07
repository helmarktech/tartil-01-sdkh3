<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - TartilPro SD Khadijah 3</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/tartil.css') }}" rel="stylesheet">
    @stack('styles')

    {{-- Mobile Responsive Overrides --}}
    <style>
    /* ===== 480px and below: small phones ===== */
    @media (max-width: 480px) {
        /* All 2-column grids → 1 column */
        [style*="grid-template-columns: 1fr 1fr"],
        [style*="grid-template-columns:1fr 1fr"],
        [style*="grid-template-columns: repeat(2"],
        [style*="grid-template-columns:repeat(2"] {
            grid-template-columns: 1fr !important;
        }

        /* 3-column grids → 1 column */
        [style*="grid-template-columns: repeat(3"],
        [style*="grid-template-columns:repeat(3"] {
            grid-template-columns: 1fr !important;
        }

        /* Fixed ratio grids → 1 column */
        [style*="grid-template-columns: 2fr 1fr"],
        [style*="grid-template-columns:2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }

        .tartil-content { padding: 12px !important; }
        .page-title-display { font-size: 18px !important; }
        .page-subtitle { font-size: 12px !important; }

        /* Stat cards full width */
        .stat-card { padding: 12px !important; }
        .stat-value { font-size: 18px !important; }

        /* Table horizontal scroll */
        .table-responsive { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }
        .table-tartil { min-width: 600px !important; font-size: 12px !important; }
        .table-tartil th, .table-tartil td { padding: 6px 8px !important; white-space: nowrap; }

        /* Buttons stack */
        .page-header .btn-tartil,
        .page-header form { width: 100% !important; }
        .page-header { flex-direction: column !important; gap: 8px !important; }

        /* Filter forms stack */
        form[style*="display: grid"],
        form[style*="display: flex"] { flex-direction: column !important; }

        /* Precalculate card */
        .precalc-bar { flex-wrap: wrap !important; gap: 6px !important; padding: 6px 10px !important; }

        /* Card grids */
        [style*="grid-template-columns: repeat(4"] { grid-template-columns: repeat(2, 1fr) !important; }
    }

    /* ===== 768px and below: tablets + phones ===== */
    @media (max-width: 768px) {
        /* 2-column grids → 1 column (catch-all) */
        [style*="grid-template-columns: 1fr 1fr"]:not(.sidebar-brand):not(.user-card):not(.btn-tartil),
        [style*="grid-template-columns:1fr 1fr"]:not(.sidebar-brand):not(.user-card):not(.btn-tartil) {
            grid-template-columns: 1fr !important;
        }

        /* 3-column → 1 column */
        [style*="grid-template-columns: repeat(3"]:not(.sidebar-brand) {
            grid-template-columns: 1fr !important;
        }

        /* Table compact */
        .table-tartil { min-width: 560px; }

        /* Content padding */
        .tartil-content { padding: 16px; }

        /* Date text hidden */
        .date-text { display: none; }

        /* Page header */
        .page-title-display { font-size: 20px; }
        .page-subtitle { font-size: 13px; }

        /* Kop surat preview */
        [style*="grid-template-columns: 1fr 1fr"][style*="gap: 20px"] {
            grid-template-columns: 1fr !important;
        }

        /* Guru/siswa filter 3-col → 1-col */
        [style*="grid-template-columns: repeat(3"] {
            grid-template-columns: 1fr !important;
        }

        /* Button groups wrap */
        .d-flex.gap-2, .d-flex.gap-3 { flex-wrap: wrap !important; }
    }

    /* ===== Touch-friendly ===== */
    @media (hover: none) and (pointer: coarse) {
        .btn-tartil, .btn-tartil-success, .btn-tartil-warning { min-height: 44px; }
        .nav-item { min-height: 44px; }
        .nav-subitem { min-height: 40px; }
        select, input[type="text"], input[type="date"], input[type="number"] { font-size: 16px !important; }
    }
    </style>
</head>
<body>
    <div class="tartil-wrapper">
        {{-- Sidebar --}}
        <aside class="tartil-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('guru.dashboard') }}" class="sidebar-brand">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent)"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span class="brand-text">Tartil</span>
                    <button type="button" class="sidebar-close" id="sidebarClose" style="margin-left: auto; background: none; border: none; color: var(--text-muted); cursor: pointer; display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </a>
            </div>

            <nav class="sidebar-nav">
                @php
                $isAdmin = auth()->user()->isAdmin();
                $p = request()->path();

                // ====== KELAS REGULER GROUP ======
                $kelasRegulerOpen = str_contains($p, 'admin/kelas-reguler');

                // ====== KELAS TARTIL GROUP ======
                // Halaman kelas tartil: /admin/kelas (CRUD kelas tartil)
                $isKelasTartil = $p === 'admin/kelas' || str_starts_with($p, 'admin/kelas/');
                $isPindahTartil = str_contains($p, 'admin/perpindahan-tartil');
                $isRekapKelasTartil = str_contains($p, 'admin/rekap-kelas-tartil');
                $isPengaturanKelas = str_contains($p, 'admin/pengaturan-kelas') && !str_contains($p, 'aktifkan');

                $kelasTartilOpen = $isKelasTartil
                    || $isPindahTartil
                    || $isRekapKelasTartil
                    || $isPengaturanKelas;

                // ====== DATA GURU GROUP ======
                $dataGuruOpen = str_contains($p, 'admin/guru-reguler')
                    || str_contains($p, 'admin/manajemen/guru')
                    || str_contains($p, 'admin/guru/import');

                // ====== MUNAQOSYAH GROUP ======
                $munaqosyahOpen = str_contains($p, 'admin/munaqosyah') || str_contains($p, 'admin/munaqosyah-rekap');

                // ====== JURNAL GROUP ======
                $jurnalOpen = str_contains($p, 'admin/rekap-jurnal') || str_contains($p, 'admin/progress-jurnal') || str_contains($p, 'admin/progress-absensi') || str_contains($p, 'admin/jurnal') || str_contains($p, 'admin/jurnal-bulanan') || str_contains($p, 'admin/monitoring-guru');

                // ====== RIWAYAT SISWA GROUP ======
                $riwayatSiswaOpen = str_contains($p, 'admin/riwayat-siswa') || str_contains($p, 'admin/track-record');
                @endphp

                @if($isAdmin)
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ str_contains($p, 'admin/dashboard') ? 'active' : '' }}">
                        <span>&#127968; Dashboard</span>
                    </a>

                    {{-- Data Siswa (submenu) --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ str_contains($p, 'admin/manajemen/siswa') || str_contains($p, 'admin/siswa/import') || str_contains($p, 'admin/siswa/penempatan') ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128101; Data Siswa</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ str_contains($p, 'admin/manajemen/siswa') || str_contains($p, 'admin/siswa/import') || str_contains($p, 'admin/siswa/penempatan') ? 'open' : '' }}">
                            <a href="{{ route('admin.manajemen.siswa') }}" class="nav-subitem {{ str_contains($p, 'admin/manajemen/siswa') ? 'active' : '' }}">Daftar Siswa</a>
                            <a href="{{ route('admin.siswa.import') }}" class="nav-subitem {{ str_contains($p, 'admin/siswa/import') ? 'active' : '' }}">Import Excel</a>
                            <a href="{{ route('admin.siswa.penempatan') }}" class="nav-subitem {{ str_contains($p, 'admin/siswa/penempatan') ? 'active' : '' }}">Penempatan Tartil</a>
                        </div>
                    </div>

                    {{-- Data Guru (submenu) --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $dataGuruOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128104;&#8205;&#127979; Data Guru</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $dataGuruOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.guru-reguler.index') }}" class="nav-subitem {{ str_contains($p, 'admin/guru-reguler') ? 'active' : '' }}">Guru Reguler</a>
                            <a href="{{ route('admin.guru.import', ['jenis' => 'reguler']) }}" class="nav-subitem {{ str_contains($p, 'admin/guru/import') && request('jenis') === 'reguler' ? 'active' : '' }}">Import Guru Reguler</a>
                            <a href="{{ route('admin.manajemen.guru') }}" class="nav-subitem {{ str_contains($p, 'admin/manajemen/guru') ? 'active' : '' }}">Guru Tartil</a>
                            <a href="{{ route('admin.guru.import', ['jenis' => 'tartil']) }}" class="nav-subitem {{ str_contains($p, 'admin/guru/import') && request('jenis') !== 'reguler' ? 'active' : '' }}">Import Guru Tartil</a>
                        </div>
                    </div>

                    {{-- Kelas Reguler --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $kelasRegulerOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#127979; Kelas Reguler</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $kelasRegulerOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.kelas-reguler.daftar') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/daftar') ? 'active' : '' }}">Daftar Kelas Reguler</a>
                            <a href="{{ route('admin.kelas-reguler.keterangan') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/keterangan') ? 'active' : '' }}">Keterangan Kelas</a>
                            <a href="{{ route('admin.kelas-reguler.pindah-index') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/pindah') ? 'active' : '' }}">Pindah Kelas</a>
                        </div>
                    </div>

                    {{-- Kelas Tartil --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $kelasTartilOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128218; Kelas Tartil</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $kelasTartilOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.kelas.index') }}" class="nav-subitem {{ $isKelasTartil ? 'active' : '' }}">Kelas Tartil</a>
                            <a href="{{ route('admin.pengaturan-kelas.index') }}" class="nav-subitem {{ $isPengaturanKelas ? 'active' : '' }}">Pengaturan Indikator</a>
                            <a href="{{ route('admin.perpindahan-tartil.admin') }}" class="nav-subitem {{ $isPindahTartil ? 'active' : '' }}">Pindah Tartil</a>
                            <a href="{{ route('admin.rekap-kelas-tartil') }}" class="nav-subitem {{ $isRekapKelasTartil ? 'active' : '' }}">Rekap Kelas Tartil</a>
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <a href="{{ route('admin.tahun-ajaran.index') }}" class="nav-item {{ str_contains($p, 'admin/tahun-ajaran') || str_contains($p, 'admin/semester') ? 'active' : '' }}">
                        <span>&#128197; Tahun Ajaran</span>
                    </a>

                    {{-- Audit: Tahun Ajaran + Statistik --}}
                    <div class="nav-section-title" style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; padding: 12px 12px 4px;">Audit &amp; Statistik</div>
                    <a href="{{ route('admin.audit-semester.pilih-ta') }}" class="nav-item {{ str_contains($p, 'admin/audit-semester') ? 'active' : '' }}">
                        <span>&#128203; Tahun Ajaran</span>
                    </a>
                    <a href="{{ route('admin.statistik.index') }}" class="nav-item {{ str_contains($p, 'admin/statistik') ? 'active' : '' }}">
                        <span>&#128202; Statistik</span>
                    </a>

                    {{-- Riwayat Siswa (gabungan) --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $riwayatSiswaOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128099; Riwayat Siswa</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $riwayatSiswaOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.riwayat-siswa.index') }}" class="nav-subitem {{ str_contains($p, 'admin/riwayat-siswa') ? 'active' : '' }}">By Kelas Reguler</a>
                            <a href="{{ route('admin.track-record.index') }}" class="nav-subitem {{ str_contains($p, 'admin/track-record') ? 'active' : '' }}">By Kelas Tartil</a>
                        </div>
                    </div>

                    {{-- Munaqosyah --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $munaqosyahOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#127891; Munaqosyah</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $munaqosyahOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.munaqosyah.index') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah') && !str_contains($p, 'munaqosyah/daftar') && !str_contains($p, 'approval') ? 'active' : '' }}">Ujian</a>
                            <a href="{{ route('admin.munaqosyah.daftar') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah/daftar') ? 'active' : '' }}">Daftar Siswa</a>
                            <a href="{{ route('admin.munaqosyah.approval.index') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah-approval') || str_contains($p, 'munaqosyah/approval') ? 'active' : '' }}">Approval Pendaftaran</a>
                            <a href="{{ route('admin.munaqosyah.rekap') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah-rekap') ? 'active' : '' }}">Rekap History</a>
                        </div>
                    </div>

                    {{-- Tahfidz & Hafalan --}}
                    <a href="{{ route('admin.tahfidz.index') }}" class="nav-item {{ str_contains($p, 'admin/tahfidz') ? 'active' : '' }}">
                        <span>&#128218; Tahfidz & Hafalan</span>
                    </a>
                    <a href="{{ route('admin.pendampingan-ortu.index') }}" class="nav-item {{ str_contains($p, 'admin/pendampingan-ortu') ? 'active' : '' }}">
                        <span>&#128106; Pendampingan Ortu</span>
                    </a>

                    {{-- Penilaian Rapor (submenu) --}}
                    @php
                        $penilaianRaporOpen = str_contains($p, 'admin/penilaian-rapor-internal') || str_contains($p, 'penilaian-rapor-internal-rekap') || str_contains($p, 'admin/cetak-rapor') || str_contains($p, 'admin/kop-surat-rapor');
                    @endphp
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $penilaianRaporOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128203; Penilaian Rapor</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $penilaianRaporOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.penilaian-rapor-internal.index') }}" class="nav-subitem {{ $p == 'admin/penilaian-rapor-internal' ? 'active' : '' }}">Ujian Internal</a>
                            <a href="{{ route('admin.penilaian-rapor-internal.rekap') }}" class="nav-subitem {{ str_contains($p, 'penilaian-rapor-internal-rekap') ? 'active' : '' }}">Progress Rapor
                                @php $badgeR = \App\Services\PrecalculateReminderService::getMenuBadge('admin.penilaian-rapor-internal.rekap'); @endphp
                                @if($badgeR)<span class="badge {{ $badgeR['class'] }} ms-1" style="font-size:0.75rem; padding: 0.2em 0.4em;" title="{{ $badgeR['title'] }}">{{ $badgeR['text'] }}</span>@endif
                            </a>
                            <a href="{{ route('admin.cetak-rapor.pilih') }}" class="nav-subitem {{ str_contains($p, 'admin/cetak-rapor') ? 'active' : '' }}">Cetak Rapor
                                @php $badge = \App\Services\PrecalculateReminderService::getMenuBadge('admin.cetak-rapor.pilih'); @endphp
                                @if($badge)<span class="badge {{ $badge['class'] }} ms-1" style="font-size:0.75rem; padding: 0.2em 0.4em;" title="{{ $badge['title'] }}">{{ $badge['text'] }}</span>@endif
                            </a>
                            <a href="{{ route('admin.kop-surat-rapor.index') }}" class="nav-subitem {{ str_contains($p, 'admin/kop-surat-rapor') ? 'active' : '' }}">Kop Surat Rapor</a>
                        </div>
                    </div>

                    {{-- Monitoring & Rekap --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $jurnalOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128200; Monitoring & Rekap</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $jurnalOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.progress.jurnal') }}" class="nav-subitem {{ str_contains($p, 'admin/progress-jurnal') ? 'active' : '' }}">Progress Jurnal
                                @php $badge2 = \App\Services\PrecalculateReminderService::getMenuBadge('admin.progress.jurnal'); @endphp
                                @if($badge2)<span class="badge {{ $badge2['class'] }} ms-1" style="font-size:0.75rem; padding: 0.2em 0.4em;" title="{{ $badge2['title'] }}">{{ $badge2['text'] }}</span>@endif
                            </a>
                            <a href="{{ route('admin.progress.absensi') }}" class="nav-subitem {{ str_contains($p, 'admin/progress-absensi') ? 'active' : '' }}">Progress Absensi</a>
                            <a href="{{ route('admin.monitoring.guru') }}" class="nav-subitem {{ str_contains($p, 'admin/monitoring-guru') ? 'active' : '' }}">Monitoring Guru
                                @php $badge3 = \App\Services\PrecalculateReminderService::getMenuBadge('admin.monitoring.guru'); @endphp
                                @if($badge3)<span class="badge {{ $badge3['class'] }} ms-1" style="font-size:0.75rem; padding: 0.2em 0.4em;" title="{{ $badge3['title'] }}">{{ $badge3['text'] }}</span>@endif
                            </a>
                            <a href="{{ route('admin.jurnal-bulanan') }}" class="nav-subitem {{ str_contains($p, 'admin/jurnal-bulanan') ? 'active' : '' }}">Rekap Jurnal</a>
                            <a href="{{ route('admin.rekap-jurnal') }}" class="nav-subitem {{ str_contains($p, 'admin/rekap-jurnal') ? 'active' : '' }}">Rekap Absensi</a>
                        </div>
                    </div>

                    {{-- System Setup: HIDDEN dari menu, akses manual via URL /admin/system/setup --}}

                @else
                    {{-- GURU MENU --}}
                    <a href="{{ route('guru.dashboard') }}" class="nav-item {{ str_contains($p, 'guru/dashboard') ? 'active' : '' }}">
                        <span>&#127968; Dashboard</span>
                    </a>
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ str_contains($p, 'guru/jurnal') ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128221; Jurnal & Absensi</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ str_contains($p, 'guru/jurnal') ? 'open' : '' }}">
                            <a href="{{ route('guru.jurnal.index') }}" class="nav-subitem {{ str_contains($p, 'guru/jurnal') && !str_contains($p, 'jurnal/rekap') && !str_contains($p, 'jurnal/bulanan') ? 'active' : '' }}">Isi Jurnal</a>
                            <a href="{{ route('guru.jurnal.bulanan') }}" class="nav-subitem {{ str_contains($p, 'guru/jurnal/bulanan') ? 'active' : '' }}">Jurnal Bulanan</a>
                            <a href="{{ route('guru.jurnal.rekap') }}" class="nav-subitem {{ str_contains($p, 'guru/jurnal/rekap') ? 'active' : '' }}">Rekap Absensi</a>
                        </div>
                    </div>
                    {{-- Penilaian Rapor (sistem internal baru) --}}
                    @php
                        $isPenilaianRapor = str_contains($p, 'guru/penilaian-rapor')
                            || str_contains($p, 'guru/rekap-nilai-rapor');
                    @endphp
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $isPenilaianRapor ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128203; Penilaian Rapor</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $isPenilaianRapor ? 'open' : '' }}">
                            <a href="{{ route('guru.penilaian-rapor.index') }}" class="nav-subitem {{ str_contains($p, 'guru/penilaian-rapor') ? 'active' : '' }}">Isi Nilai</a>
                            <a href="{{ route('guru.penilaian-rapor.rekap') }}" class="nav-subitem {{ str_contains($p, 'guru/rekap-nilai-rapor') ? 'active' : '' }}">Rekap Nilai</a>
                        </div>
                    </div>
                    <a href="{{ route('guru.track-record.index') }}" class="nav-item {{ str_contains($p, 'guru/track-record') ? 'active' : '' }}">
                        <span>&#128099; Track Record Siswa</span>
                    </a>
                    <a href="{{ route('guru.tahfidz.index') }}" class="nav-item {{ str_contains($p, 'guru/tahfidz') ? 'active' : '' }}">
                        <span>&#128218; Tahfidz & Hafalan</span>
                    </a>
                    <a href="{{ route('guru.pendampingan-ortu.index') }}" class="nav-item {{ str_contains($p, 'guru/pendampingan-ortu') ? 'active' : '' }}">
                        <span>&#128106; Konfirmasi Ortu</span>
                    </a>
                    <a href="{{ route('guru.siswa.index') }}" class="nav-item {{ str_contains($p, 'guru/siswa') ? 'active' : '' }}">
                        <span>&#128101; Data Siswa</span>
                    </a>
                    <a href="{{ route('guru.password.edit') }}" class="nav-item {{ str_contains($p, 'guru/password') ? 'active' : '' }}">
                        <span>&#128274; Ganti Password</span>
                    </a>
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ str_contains($p, 'guru/perpindahan') ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#128260; Pindah Kelas</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ str_contains($p, 'guru/perpindahan') ? 'open' : '' }}">
                            <a href="{{ route('guru.perpindahan.create') }}" class="nav-subitem {{ str_contains($p, 'guru/perpindahan/create') ? 'active' : '' }}">Pindah Individu</a>
                            <a href="{{ route('guru.perpindahan.massal') }}" class="nav-subitem {{ str_contains($p, 'guru/perpindahan/massal') ? 'active' : '' }}">Pindah Massal (3 Langkah)</a>
                            <a href="{{ route('guru.perpindahan.approval') }}" class="nav-subitem {{ str_contains($p, 'guru/perpindahan/approval') ? 'active' : '' }}">Approval Pindah</a>
                        </div>
                    </div>
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ str_contains($p, 'guru/munaqosyah') ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>&#127891; Munaqosyah</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ str_contains($p, 'guru/munaqosyah') ? 'open' : '' }}">
                            <a href="{{ route('guru.munaqosyah.index') }}" class="nav-subitem {{ str_contains($p, 'guru/munaqosyah') && !str_contains($p, 'approval-rekap') ? 'active' : '' }}">Ujian</a>
                            <a href="{{ route('guru.munaqosyah.approval-rekap') }}" class="nav-subitem {{ str_contains($p, 'guru/munaqosyah/approval-rekap') ? 'active' : '' }}">Rekap Approval</a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar">{{ substr(auth()->user()->nama, 0, 2) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->nama }}</div>
                        <div class="user-role">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Guru' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="tartil-main">
            <header class="tartil-topbar">
                <div style="display: flex; align-items: center;">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <div class="breadcrumb">
                        <span>Tartil</span>
                        <svg class="sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--border)"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="current">@yield('title', 'Dashboard')</span>
                    </div>
                </div>
                <div class="date-text">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
            </header>

            <main class="tartil-content">
                @if(session('success'))
                <div class="alert-tartil alert-success" style="margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert-tartil alert-error" style="margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert-tartil alert-error" style="margin-bottom: 20px;">
                    <strong>Terjadi kesalahan:</strong>
                    <ul style="margin: 6px 0 0 16px; padding: 0;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Precalculate Info Card: hanya muncul di menu krusial --}}
                @php
                    $currentRouteName = request()->route()?->getName();
                    $isCriticalRoute = $currentRouteName && isset(\App\Services\PrecalculateReminderService::$criticalMenus[$currentRouteName]);
                @endphp
                @if($isCriticalRoute)
                    @include('components.precalculate-info-card')
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        // Pastikan overlay tidak aktif saat page load
        document.addEventListener('DOMContentLoaded', function() {
            overlay.classList.remove('show');
            sidebar.classList.remove('mobile-open');
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
        });

        // Override open/close untuk set pointer-events
        function openSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('show');
            overlay.style.display = 'block';
            overlay.style.pointerEvents = 'auto';
        }
        function closeSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
        }

        if (toggle) toggle.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')

    {{-- Precalculate Reminder Modal --}}
    {{-- Muncul otomatis di menu krusial kalau cache R2 basi --}}
    @include('components.precalculate-modal')
</body>
</html>
