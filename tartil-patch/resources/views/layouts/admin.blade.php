<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Tartil</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/tartil.css') }}" rel="stylesheet">
    @stack('styles')
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
                $kelasRegulerOpen = str_contains($p, 'admin/guru-reguler')
                    || str_contains($p, 'admin/kelas-reguler');

                // ====== KELAS TARTIL GROUP ======
                // Halaman kelas tartil: /admin/kelas (CRUD kelas tartil)
                $isKelasTartil = $p === 'admin/kelas' || str_starts_with($p, 'admin/kelas/');
                $isPindahTartil = str_contains($p, 'admin/perpindahan-tartil');
                $isRekapKelasTartil = str_contains($p, 'admin/rekap-kelas-tartil');

                $kelasTartilOpen = str_contains($p, 'admin/manajemen/guru')
                    || $isKelasTartil
                    || $isPindahTartil
                    || $isRekapKelasTartil;

                // ====== MUNAQOSYAH GROUP ======
                $munaqosyahOpen = str_contains($p, 'admin/munaqosyah');
                @endphp

                @if($isAdmin)
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ str_contains($p, 'admin/dashboard') ? 'active' : '' }}">
                        <span>Dashboard</span>
                    </a>

                    {{-- Daftar Siswa --}}
                    <a href="{{ route('admin.manajemen.siswa') }}" class="nav-item {{ str_contains($p, 'admin/manajemen/siswa') ? 'active' : '' }}">
                        <span>Daftar Siswa</span>
                    </a>

                    {{-- Kelas Reguler --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $kelasRegulerOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>Kelas Reguler</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $kelasRegulerOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.guru-reguler.index') }}" class="nav-subitem {{ str_contains($p, 'admin/guru-reguler') ? 'active' : '' }}">Guru Reguler</a>
                            <a href="{{ route('admin.kelas-reguler.daftar') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/daftar') ? 'active' : '' }}">Daftar Kelas Reguler</a>
                            <a href="{{ route('admin.kelas-reguler.keterangan') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/keterangan') ? 'active' : '' }}">Keterangan Kelas</a>
                            <a href="{{ route('admin.kelas-reguler.pindah-index') }}" class="nav-subitem {{ str_contains($p, 'admin/kelas-reguler/pindah') ? 'active' : '' }}">Pindah Kelas</a>
                        </div>
                    </div>

                    {{-- Kelas Tartil --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $kelasTartilOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>Kelas Tartil</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $kelasTartilOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.manajemen.guru') }}" class="nav-subitem {{ str_contains($p, 'admin/manajemen/guru') ? 'active' : '' }}">Guru Tartil</a>
                            <a href="{{ route('admin.kelas.index') }}" class="nav-subitem {{ $isKelasTartil ? 'active' : '' }}">Kelas Tartil</a>
                            <a href="{{ route('admin.perpindahan-tartil.admin') }}" class="nav-subitem {{ $isPindahTartil ? 'active' : '' }}">Pindah Tartil</a>
                            <a href="{{ route('admin.rekap-kelas-tartil') }}" class="nav-subitem {{ $isRekapKelasTartil ? 'active' : '' }}">Rekap Kelas Tartil</a>
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <a href="{{ route('admin.tahun-ajaran.index') }}" class="nav-item {{ str_contains($p, 'admin/tahun-ajaran') || str_contains($p, 'admin/semester') ? 'active' : '' }}">
                        <span>Tahun Ajaran</span>
                    </a>

                    {{-- Riwayat Siswa --}}
                    <a href="{{ route('admin.riwayat-siswa.index') }}" class="nav-item {{ str_contains($p, 'admin/riwayat-siswa') ? 'active' : '' }}">
                        <span>Riwayat Siswa</span>
                    </a>

                    {{-- Munaqosyah --}}
                    <div class="nav-group">
                        <button type="button" class="nav-item nav-toggle {{ $munaqosyahOpen ? 'active open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">
                            <span>Munaqosyah</span>
                            <svg class="nav-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="nav-submenu {{ $munaqosyahOpen ? 'open' : '' }}">
                            <a href="{{ route('admin.munaqosyah.index') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah') && !str_contains($p, 'approval') ? 'active' : '' }}">Ujian</a>
                            <a href="{{ route('admin.munaqosyah.approval.index') }}" class="nav-subitem {{ str_contains($p, 'admin/munaqosyah-approval') || str_contains($p, 'munaqosyah/approval') ? 'active' : '' }}">Approval Pendaftaran</a>
                        </div>
                    </div>
                @else
                    {{-- GURU MENU --}}
                    <a href="{{ route('guru.dashboard') }}" class="nav-item {{ str_contains($p, 'guru/dashboard') ? 'active' : '' }}">
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('guru.jurnal.index') }}" class="nav-item {{ str_contains($p, 'guru/jurnal') ? 'active' : '' }}">
                        <span>Jurnal</span>
                    </a>
                    <a href="{{ route('guru.absensi.index') }}" class="nav-item {{ str_contains($p, 'guru/absensi') ? 'active' : '' }}">
                        <span>Absensi</span>
                    </a>
                    <a href="{{ route('guru.rapor.pilih') }}" class="nav-item {{ str_contains($p, 'guru/rapor') ? 'active' : '' }}">
                        <span>Rapor</span>
                    </a>
                    <a href="{{ route('guru.perpindahan.create') }}" class="nav-item {{ str_contains($p, 'guru/perpindahan/create') ? 'active' : '' }}">
                        <span>Pindah Kelas</span>
                    </a>
                    <a href="{{ route('guru.perpindahan.approval') }}" class="nav-item {{ str_contains($p, 'guru/perpindahan/approval') ? 'active' : '' }}">
                        <span>Approval Pindah</span>
                    </a>
                    <a href="{{ route('guru.munaqosyah.index') }}" class="nav-item {{ str_contains($p, 'guru/munaqosyah') ? 'active' : '' }}">
                        <span>Munaqosyah</span>
                    </a>
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

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        function openSidebar() { sidebar.classList.add('mobile-open'); overlay.classList.add('show'); }
        function closeSidebar() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('show'); }

        if (toggle) toggle.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')
</body>
</html>
