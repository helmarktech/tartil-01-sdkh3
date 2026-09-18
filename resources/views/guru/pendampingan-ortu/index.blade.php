@extends('layouts.admin')

@section('title', 'Konfirmasi Ortu - Guru')

@section('content')
<style>
.po-card {
    background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px;
    padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.po-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 16px; flex-wrap: wrap; gap: 12px;
}
.po-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; font-family: 'DM Serif Display', serif; }
.po-sub { font-size: 13px; color: #666; margin: 4px 0 0; }
.po-tabs {
    display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;
}
.po-tab {
    padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;
    text-decoration: none; background: #f5f5f4; color: #78716c; border: 1px solid transparent;
}
.po-tab.active { background: #0c8a5f; color: #fff; }
.po-table-wrap { overflow-x: auto; }
.po-table {
    width: 100%; border-collapse: collapse; font-size: 13px;
}
.po-table th {
    text-align: left; padding: 10px 12px; background: #f8faf8;
    font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0; white-space: nowrap;
}
.po-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
.po-table tr:hover td { background: #f8faf8; }
.po-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600;
}
.po-badge-pengajuan { background: #fff3cd; color: #856404; }
.po-badge-dikonfirmasi { background: #d4edda; color: #155724; }
.po-ayat { font-size: 12px; color: #78716c; }
.po-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 18px; background: #0c8a5f; color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
}
.po-btn:hover { background: #0a6b4a; }
.po-btn:disabled { background: #ccc; cursor: not-allowed; }
.po-checkbox { width: 18px; height: 18px; cursor: pointer; }

/* Custom dropdown (select siswa, kalender, pemilih bulan) */
.po-dropdown { position: relative; min-width: 250px; }
.po-dd-toggle {
    width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 10px;
    padding: 9px 14px; border: 1.5px solid #d6d3d1; border-radius: 10px; background: #fff;
    font-size: 13px; color: #1c1917; cursor: pointer; text-align: left;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.po-dd-toggle:hover { border-color: #0c8a5f; }
.po-dropdown.open .po-dd-toggle {
    border-color: #0c8a5f; box-shadow: 0 0 0 3px rgba(12,138,95,0.15);
}
.po-dd-toggle.error { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.12); }
.po-dd-toggle .po-dd-placeholder { color: #a8a29e; }
.po-dd-toggle svg { flex-shrink: 0; color: #78716c; }
.po-dd-panel {
    position: absolute; top: calc(100% + 6px); left: 0; z-index: 60;
    background: #fff; border: 1px solid #e7e5e4; border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.14); min-width: 100%; overflow: hidden;
}
.po-dd-search { padding: 8px; border-bottom: 1px solid #f0f0f0; }
.po-dd-search input {
    width: 100%; padding: 7px 10px; border: 1px solid #e7e5e4; border-radius: 8px;
    font-size: 13px; outline: none; box-sizing: border-box;
}
.po-dd-search input:focus { border-color: #0c8a5f; box-shadow: 0 0 0 3px rgba(12,138,95,0.12); }
.po-dd-list { max-height: 230px; overflow-y: auto; }
.po-dd-item {
    display: block; width: 100%; text-align: left; padding: 9px 14px; font-size: 13px;
    background: none; border: none; cursor: pointer; color: #1c1917;
}
.po-dd-item:hover { background: #f0faf5; }
.po-dd-item.selected { background: #e7f6ef; color: #0c8a5f; font-weight: 600; }
.po-dd-empty { padding: 14px; font-size: 12px; color: #a8a29e; text-align: center; }

/* Kalender */
.po-cal { padding: 12px; width: 264px; box-sizing: border-box; }
.po-cal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.po-cal-title { font-size: 13px; font-weight: 700; color: #1c1917; }
.po-cal-nav {
    width: 28px; height: 28px; border-radius: 8px; border: none; background: #f5f5f4;
    cursor: pointer; font-size: 15px; line-height: 1; color: #44403c;
    display: flex; align-items: center; justify-content: center;
}
.po-cal-nav:hover { background: #e7f6ef; color: #0c8a5f; }
.po-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.po-cal-dow {
    text-align: center; font-size: 10px; font-weight: 700; color: #a8a29e;
    padding: 4px 0; text-transform: uppercase;
}
.po-cal-day {
    text-align: center; padding: 6px 0; font-size: 12px; border-radius: 8px;
    border: none; background: none; cursor: pointer; color: #1c1917;
}
.po-cal-day:hover { background: #e7f6ef; }
.po-cal-day.muted { visibility: hidden; cursor: default; }
.po-cal-day.today { border: 1.5px solid #0c8a5f; color: #0c8a5f; font-weight: 700; }
.po-cal-day.selected { background: #0c8a5f; color: #fff; font-weight: 700; }

/* Pemilih bulan */
.po-month-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding: 0; }
.po-month-item {
    padding: 10px 4px; font-size: 12px; border: 1px solid #e7e5e4; border-radius: 8px;
    background: #fff; cursor: pointer; color: #1c1917;
}
.po-month-item:hover { border-color: #0c8a5f; color: #0c8a5f; }
.po-month-item.selected { background: #0c8a5f; border-color: #0c8a5f; color: #fff; font-weight: 700; }
.po-empty { text-align: center; padding: 48px; color: #888; }

/* Mobile cards */
.po-cards { display: none; }
@media (max-width: 768px) {
    .po-table-wrap { display: none; }
    .po-cards { display: flex; flex-direction: column; gap: 12px; }
    .po-card-item {
        background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; padding: 14px;
        display: flex; flex-direction: column; gap: 8px;
    }
    .po-card-row { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; }
    .po-card-label { color: #78716c; font-size: 12px; }
    .po-card-value { font-weight: 600; color: #1c1917; text-align: right; }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0;">&#128106; Konfirmasi Ortu</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Laporan tadarus & murajaah dari orang tua siswa kelas Anda</p>
    </div>
</div>

<div class="po-tabs">
    <a href="{{ route('guru.pendampingan-ortu.index') }}" class="po-tab {{ $status === 'semua' ? 'active' : '' }}">Semua</a>
    <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'pengajuan']) }}" class="po-tab {{ $status === 'pengajuan' ? 'active' : '' }}">Pengajuan Konfirmasi</a>
    <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi']) }}" class="po-tab {{ $status === 'dikonfirmasi' ? 'active' : '' }}">Telah Dikonfirmasi</a>
</div>

@if($status === 'dikonfirmasi')
    {{-- Filter data konfirmasi --}}
    <div class="po-card" style="margin-bottom: 12px;">
        <div style="font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">Filter Data Konfirmasi</div>
        <div class="po-tabs" style="margin-bottom: 12px;">
            <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi']) }}" class="po-tab {{ !$mode ? 'active' : '' }}">Semua Data</a>
            <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'siswa']) }}" class="po-tab {{ $mode === 'siswa' ? 'active' : '' }}">Per Siswa</a>
            <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'tanggal']) }}" class="po-tab {{ $mode === 'tanggal' ? 'active' : '' }}">Per Tanggal</a>
            <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'bulan']) }}" class="po-tab {{ $mode === 'bulan' ? 'active' : '' }}">Per Bulan</a>
        </div>

        @if($mode)
        <form method="GET" action="{{ route('guru.pendampingan-ortu.index') }}" id="formFilter" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="status" value="dikonfirmasi">
            <input type="hidden" name="mode" value="{{ $mode }}">
            @if($mode === 'siswa')
            <div class="po-dropdown" id="ddSiswa">
                <input type="hidden" name="siswa_id" id="ddSiswaValue" value="{{ request('siswa_id') }}">
                <button type="button" class="po-dd-toggle">
                    <span class="po-dd-label {{ $filterSiswa ? '' : 'po-dd-placeholder' }}">{{ $filterSiswa?->nama ?? '-- Pilih Siswa --' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="po-dd-panel" hidden>
                    <div class="po-dd-search">
                        <input type="text" placeholder="Cari nama siswa..." autocomplete="off">
                    </div>
                    <div class="po-dd-list">
                        @foreach($siswaList as $s)
                        <button type="button" class="po-dd-item {{ (string) request('siswa_id') === (string) $s->id ? 'selected' : '' }}" data-value="{{ $s->id }}" data-label="{{ $s->nama }}">{{ $s->nama }}</button>
                        @endforeach
                        <div class="po-dd-empty" hidden>Tidak ada siswa yang cocok.</div>
                    </div>
                </div>
            </div>
            @elseif($mode === 'tanggal')
            <div class="po-dropdown" id="ddTanggal">
                <input type="hidden" name="tanggal" id="ddTanggalValue" value="{{ request('tanggal') }}">
                <button type="button" class="po-dd-toggle">
                    <span class="po-dd-label po-dd-placeholder">-- Pilih Tanggal --</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </button>
                <div class="po-dd-panel" hidden></div>
            </div>
            @elseif($mode === 'bulan')
            <div class="po-dropdown" id="ddBulan">
                <input type="hidden" name="bulan" id="ddBulanValue" value="{{ request('bulan') }}">
                <button type="button" class="po-dd-toggle">
                    <span class="po-dd-label po-dd-placeholder">-- Pilih Bulan --</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </button>
                <div class="po-dd-panel" hidden></div>
            </div>
            @endif
            <button type="submit" class="po-btn" style="padding: 8px 16px;">Terapkan</button>
        </form>

        <script>
        (function() {
            const MODE = @json($mode);
            const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const DOW = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
            const pad = n => String(n).padStart(2, '0');

            function closeAllPanels() {
                document.querySelectorAll('.po-dd-panel').forEach(p => p.hidden = true);
                document.querySelectorAll('.po-dropdown.open').forEach(d => d.classList.remove('open'));
            }
            document.addEventListener('click', closeAllPanels);
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllPanels(); });

            function setupToggle(dd) {
                const toggle = dd.querySelector('.po-dd-toggle');
                const panel = dd.querySelector('.po-dd-panel');
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const willOpen = panel.hidden;
                    closeAllPanels();
                    if (willOpen) {
                        panel.hidden = false;
                        dd.classList.add('open');
                        const search = panel.querySelector('.po-dd-search input');
                        if (search) search.focus();
                        if (dd._onOpen) dd._onOpen();
                    }
                });
                panel.addEventListener('click', e => e.stopPropagation());
            }

            // ===== Dropdown siswa dengan pencarian =====
            if (MODE === 'siswa') {
                const dd = document.getElementById('ddSiswa');
                const value = document.getElementById('ddSiswaValue');
                const label = dd.querySelector('.po-dd-label');
                const search = dd.querySelector('.po-dd-search input');
                const items = Array.from(dd.querySelectorAll('.po-dd-item'));
                const emptyMsg = dd.querySelector('.po-dd-empty');

                search.addEventListener('input', function() {
                    const q = this.value.trim().toLowerCase();
                    let visible = 0;
                    items.forEach(item => {
                        const show = item.dataset.label.toLowerCase().includes(q);
                        item.hidden = !show;
                        if (show) visible++;
                    });
                    emptyMsg.hidden = visible > 0;
                });

                items.forEach(item => item.addEventListener('click', function() {
                    value.value = this.dataset.value;
                    label.textContent = this.dataset.label;
                    label.classList.remove('po-dd-placeholder');
                    dd.querySelector('.po-dd-toggle').classList.remove('error');
                    items.forEach(i => i.classList.toggle('selected', i === this));
                    closeAllPanels();
                }));

                setupToggle(dd);
            }

            // ===== Kalender per tanggal =====
            if (MODE === 'tanggal') {
                const dd = document.getElementById('ddTanggal');
                const value = document.getElementById('ddTanggalValue');
                const label = dd.querySelector('.po-dd-label');
                const panel = dd.querySelector('.po-dd-panel');
                const today = new Date();
                let viewY = today.getFullYear(), viewM = today.getMonth();

                function validDate(str) {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(str)) return null;
                    const d = new Date(str + 'T00:00:00');
                    return isNaN(d) ? null : d;
                }

                function syncFromValue() {
                    const d = validDate(value.value);
                    if (d) {
                        label.textContent = d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear();
                        label.classList.remove('po-dd-placeholder');
                        viewY = d.getFullYear();
                        viewM = d.getMonth();
                    }
                }

                function render() {
                    const firstDow = new Date(viewY, viewM, 1).getDay();
                    const daysInMonth = new Date(viewY, viewM + 1, 0).getDate();
                    let html = '<div class="po-cal">'
                        + '<div class="po-cal-head">'
                        + '<button type="button" class="po-cal-nav" data-nav="-1">&#8249;</button>'
                        + '<div class="po-cal-title">' + BULAN[viewM] + ' ' + viewY + '</div>'
                        + '<button type="button" class="po-cal-nav" data-nav="1">&#8250;</button>'
                        + '</div><div class="po-cal-grid">';
                    DOW.forEach(d => html += '<div class="po-cal-dow">' + d + '</div>');
                    for (let i = 0; i < firstDow; i++) html += '<div class="po-cal-day muted"></div>';
                    for (let day = 1; day <= daysInMonth; day++) {
                        const ymd = viewY + '-' + pad(viewM + 1) + '-' + pad(day);
                        let cls = 'po-cal-day';
                        if (ymd === value.value) cls += ' selected';
                        if (day === today.getDate() && viewM === today.getMonth() && viewY === today.getFullYear()) cls += ' today';
                        html += '<button type="button" class="' + cls + '" data-ymd="' + ymd + '">' + day + '</button>';
                    }
                    html += '</div></div>';
                    panel.innerHTML = html;

                    panel.querySelectorAll('.po-cal-nav').forEach(btn => btn.addEventListener('click', function() {
                        viewM += parseInt(this.dataset.nav, 10);
                        if (viewM < 0) { viewM = 11; viewY--; }
                        if (viewM > 11) { viewM = 0; viewY++; }
                        render();
                    }));
                    panel.querySelectorAll('[data-ymd]').forEach(btn => btn.addEventListener('click', function() {
                        value.value = this.dataset.ymd;
                        dd.querySelector('.po-dd-toggle').classList.remove('error');
                        syncFromValue();
                        closeAllPanels();
                    }));
                }

                syncFromValue();
                dd._onOpen = render;
                setupToggle(dd);
            }

            // ===== Pemilih bulan =====
            if (MODE === 'bulan') {
                const dd = document.getElementById('ddBulan');
                const value = document.getElementById('ddBulanValue');
                const label = dd.querySelector('.po-dd-label');
                const panel = dd.querySelector('.po-dd-panel');
                const today = new Date();
                let viewY = today.getFullYear();

                function syncFromValue() {
                    if (/^\d{4}-\d{2}$/.test(value.value)) {
                        const parts = value.value.split('-').map(Number);
                        if (parts[1] >= 1 && parts[1] <= 12) {
                            label.textContent = BULAN[parts[1] - 1] + ' ' + parts[0];
                            label.classList.remove('po-dd-placeholder');
                            viewY = parts[0];
                        }
                    }
                }

                function render() {
                    let html = '<div class="po-cal">'
                        + '<div class="po-cal-head">'
                        + '<button type="button" class="po-cal-nav" data-nav="-1">&#8249;</button>'
                        + '<div class="po-cal-title">' + viewY + '</div>'
                        + '<button type="button" class="po-cal-nav" data-nav="1">&#8250;</button>'
                        + '</div><div class="po-month-grid">';
                    for (let m = 0; m < 12; m++) {
                        const ym = viewY + '-' + pad(m + 1);
                        html += '<button type="button" class="po-month-item' + (ym === value.value ? ' selected' : '') + '" data-ym="' + ym + '">' + BULAN[m] + '</button>';
                    }
                    html += '</div></div>';
                    panel.innerHTML = html;

                    panel.querySelectorAll('.po-cal-nav').forEach(btn => btn.addEventListener('click', function() {
                        viewY += parseInt(this.dataset.nav, 10);
                        render();
                    }));
                    panel.querySelectorAll('[data-ym]').forEach(btn => btn.addEventListener('click', function() {
                        value.value = this.dataset.ym;
                        dd.querySelector('.po-dd-toggle').classList.remove('error');
                        syncFromValue();
                        closeAllPanels();
                    }));
                }

                syncFromValue();
                dd._onOpen = render;
                setupToggle(dd);
            }

            // ===== Validasi sebelum submit =====
            document.getElementById('formFilter').addEventListener('submit', function(e) {
                const map = { siswa: 'ddSiswa', tanggal: 'ddTanggal', bulan: 'ddBulan' };
                const dd = document.getElementById(map[MODE]);
                if (!dd.querySelector('input[type="hidden"]').value) {
                    e.preventDefault();
                    const toggle = dd.querySelector('.po-dd-toggle');
                    toggle.classList.add('error');
                    if (dd.querySelector('.po-dd-panel').hidden) toggle.click();
                }
            });
        })();
        </script>
        @else
        <p style="font-size: 13px; color: #666; margin: 0;">Menampilkan seluruh data konfirmasi. Gunakan filter untuk melihat data per siswa, per tanggal, atau per bulan.</p>
        @endif
    </div>

    @if($mode === 'siswa' && request()->filled('siswa_id') && ! $filterSiswa)
        <div class="po-card po-empty">
            <h3>Siswa tidak ditemukan</h3>
            <p>Siswa tersebut tidak terdaftar di kelas Anda.</p>
        </div>
    @elseif($mode === 'siswa' && $filterSiswa)
        {{-- Tampilan konfirmasi per siswa, dipisah per bulan di semester berjalan --}}
        <div class="po-card" style="margin-bottom: 12px;">
            <div style="font-size: 16px; font-weight: 700; color: #1a1a2e;">
                {{ $filterSiswa->nama }}
                <span style="display: inline-block; margin-left: 8px; padding: 3px 12px; border-radius: 999px; background: #e7f6ef; color: #0c8a5f; font-size: 12px; font-weight: 700; vertical-align: middle;">{{ $laporan->count() }} konfirmasi</span>
            </div>
            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                Total {{ $laporan->count() }} data telah dikonfirmasi pada semester berjalan{{ $semesterAktif ? ' ('.$semesterAktif->nama.')' : '' }}, dipisah per bulan.
            </div>
        </div>
        @if($laporanPerBulan->isEmpty())
            <div class="po-card po-empty">
                <div style="font-size: 48px; margin-bottom: 16px;">&#128106;</div>
                <h3>Belum ada data</h3>
                <p>Belum ada konfirmasi untuk siswa ini di semester berjalan.</p>
            </div>
        @else
            @foreach($laporanPerBulan as $bulanKey => $items)
            <div class="po-card">
                <h3 style="font-size: 15px; font-weight: 700; color: #0c8a5f; margin: 0 0 12px;">
                    {{ \Carbon\Carbon::parse($bulanKey.'-01')->locale('id')->translatedFormat('F Y') }}
                    <span style="font-weight: 400; color: #888; font-size: 12px;">({{ $items->count() }} konfirmasi)</span>
                </h3>
                <div style="overflow-x: auto;">
                    <table class="po-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Surat / Ayat</th>
                                <th>Catatan</th>
                                <th>Dikonfirmasi Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $l)
                            <tr>
                                <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                                <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                                <td>
                                    <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                                    <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                                </td>
                                <td style="max-width: 200px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                                <td class="po-ayat">{{ $l->guruKonfirmasi?->nama ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @endif
    @elseif($mode === 'bulan' && $laporanPerSiswa !== null)
        {{-- Tampilan per bulan: seluruh siswa kelas tampil, dengan atau tanpa data --}}
        <div class="po-card" style="margin-bottom: 12px;">
            <div style="font-size: 16px; font-weight: 700; color: #1a1a2e;">
                {{ \Carbon\Carbon::parse(request('bulan').'-01')->locale('id')->translatedFormat('F Y') }}
            </div>
            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                Data konfirmasi seluruh siswa pada bulan ini: {{ $laporan->count() }} konfirmasi dari {{ $laporanPerSiswa->count() }} siswa.
            </div>
        </div>
        @foreach($siswaList as $s)
            @php $items = $laporanPerSiswa->get($s->id, collect()); @endphp
            <div class="po-card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1a1a2e; margin: 0;">
                        {{ $s->nama }}
                        <span style="font-weight: 400; color: #888; font-size: 12px;">({{ $items->count() }} konfirmasi)</span>
                    </h3>
                    <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'siswa', 'siswa_id' => $s->id]) }}" class="po-btn" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Detail</a>
                </div>
                @if($items->isEmpty())
                    <p style="font-size: 13px; color: #a8a29e; margin: 0;">Belum ada data konfirmasi pada bulan ini.</p>
                @else
                <div style="overflow-x: auto;">
                    <table class="po-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Surat / Ayat</th>
                                <th>Catatan</th>
                                <th>Dikonfirmasi Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $l)
                            <tr>
                                <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                                <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                                <td>
                                    <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                                    <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                                </td>
                                <td style="max-width: 200px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                                <td class="po-ayat">{{ $l->guruKonfirmasi?->nama ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        @endforeach
    @elseif($laporan->isEmpty())
        <div class="po-card po-empty">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128106;</div>
            <h3>Tidak ada data</h3>
            <p>Tidak ada data konfirmasi yang cocok dengan filter.</p>
        </div>
    @else
    {{-- Tabel datar: semua data / per tanggal / per bulan, dengan link Detail ke tampilan per siswa --}}
    <div class="po-table-wrap">
        <table class="po-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Jenis</th>
                    <th>Surat / Ayat</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $l)
                <tr>
                    <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                    <td><strong>{{ $l->siswa?->nama ?? '-' }}</strong></td>
                    <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                    <td>
                        <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                        <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                    </td>
                    <td style="max-width: 200px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                    <td>
                        <span class="po-badge po-badge-dikonfirmasi">
                            {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                        </span>
                    </td>
                    <td>
                        @if($l->siswa_id)
                        <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'siswa', 'siswa_id' => $l->siswa_id]) }}" class="po-btn" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Detail</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="po-cards">
        @foreach($laporan as $l)
        <div class="po-card-item">
            <div class="po-card-row">
                <span class="po-card-label">Siswa</span>
                <span class="po-card-value">{{ $l->siswa?->nama ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Tanggal</span>
                <span class="po-card-value">{{ $l->tanggal?->format('d/m/Y') }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Jenis</span>
                <span class="po-card-value">{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Surat / Ayat</span>
                <span class="po-card-value" style="text-align: right;">
                    {{ $l->surat?->nama_latin ?? '-' }}<br>
                    <span class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</span>
                </span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Catatan</span>
                <span class="po-card-value" style="max-width: 60%; word-break: break-word; font-weight: 400;">{{ $l->catatan ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Guru Konfirmasi</span>
                <span class="po-card-value">{{ $l->guruKonfirmasi?->nama ?? '-' }}</span>
            </div>
            @if($l->siswa_id)
            <div style="margin-top: 4px;">
                <a href="{{ route('guru.pendampingan-ortu.index', ['status' => 'dikonfirmasi', 'mode' => 'siswa', 'siswa_id' => $l->siswa_id]) }}" class="po-btn" style="width: 100%; text-decoration: none;">Detail Konfirmasi Siswa</a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
@elseif($laporan->isEmpty())
    <div class="po-card po-empty">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128106;</div>
        <h3>Tidak ada laporan</h3>
        <p>Belum ada laporan pendampingan ortu yang masuk.</p>
    </div>
@else
<form method="POST" action="{{ route('guru.pendampingan-ortu.konfirmasi-bulk') }}" id="formKonfirmasiBulk">
    @csrf
    <div class="po-card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; cursor: pointer;">
            <input type="checkbox" id="pilihSemua" class="po-checkbox">
            Pilih semua
        </label>
        <button type="submit" class="po-btn" id="btnKonfirmasi" disabled onclick="return confirm('Konfirmasi laporan terpilih?')">
            Konfirmasi Terpilih
        </button>
    </div>

    <div class="po-table-wrap">
        <table class="po-table">
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Jenis</th>
                    <th>Surat / Ayat</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $i => $l)
                <tr>
                    <td>
                        @if($l->status === 'pengajuan_konfirmasi')
                            <input type="checkbox" name="laporan_ids[]" value="{{ $l->id }}" class="po-checkbox checkbox-item">
                        @endif
                    </td>
                    <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                    <td><strong>{{ $l->siswa?->nama ?? '-' }}</strong></td>
                    <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                    <td>
                        <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                        <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                    </td>
                    <td style="max-width: 200px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                    <td>
                        <span class="po-badge {{ $l->status === 'telah_dikonfirmasi' ? 'po-badge-dikonfirmasi' : 'po-badge-pengajuan' }}">
                            {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                        </span>
                    </td>
                    <td>
                        @if($l->status === 'pengajuan_konfirmasi')
                            <form method="POST" action="{{ route('guru.pendampingan-ortu.konfirmasi', $l) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="po-btn" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Konfirmasi laporan ini?')">Konfirmasi</button>
                            </form>
                        @else
                            <span class="po-ayat">{{ $l->guruKonfirmasi?->nama ?? '-' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="po-cards">
        @foreach($laporan as $l)
        <div class="po-card-item">
            <div class="po-card-row">
                <span class="po-card-label">Siswa</span>
                <span class="po-card-value">{{ $l->siswa?->nama ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Tanggal</span>
                <span class="po-card-value">{{ $l->tanggal?->format('d/m/Y') }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Jenis</span>
                <span class="po-card-value">{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Surat / Ayat</span>
                <span class="po-card-value" style="text-align: right;">
                    {{ $l->surat?->nama_latin ?? '-' }}<br>
                    <span class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</span>
                </span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Catatan</span>
                <span class="po-card-value" style="max-width: 60%; word-break: break-word; font-weight: 400;">{{ $l->catatan ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Status</span>
                <span class="po-card-value">
                    <span class="po-badge {{ $l->status === 'telah_dikonfirmasi' ? 'po-badge-dikonfirmasi' : 'po-badge-pengajuan' }}">
                        {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                    </span>
                </span>
            </div>
            @if($l->status === 'telah_dikonfirmasi')
            <div class="po-card-row">
                <span class="po-card-label">Guru Konfirmasi</span>
                <span class="po-card-value">{{ $l->guruKonfirmasi?->nama ?? '-' }}</span>
            </div>
            @endif
            <div class="po-card-row" style="margin-top: 4px;">
                @if($l->status === 'pengajuan_konfirmasi')
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="laporan_ids[]" value="{{ $l->id }}" class="po-checkbox checkbox-item">
                        Pilih untuk konfirmasi
                    </label>
                @else
                    <span class="po-ayat">Sudah dikonfirmasi</span>
                @endif
            </div>
            @if($l->status === 'pengajuan_konfirmasi')
            <div style="margin-top: 4px;">
                <form method="POST" action="{{ route('guru.pendampingan-ortu.konfirmasi', $l) }}">
                    @csrf
                    <button type="submit" class="po-btn" style="width: 100%;" onclick="return confirm('Konfirmasi laporan ini?')">Konfirmasi Laporan</button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</form>

<script>
(function() {
    const pilihSemua = document.getElementById('pilihSemua');
    const checkboxes = document.querySelectorAll('.checkbox-item');
    const btn = document.getElementById('btnKonfirmasi');

    function updateBtn() {
        const any = Array.from(checkboxes).some(cb => cb.checked);
        btn.disabled = !any;
    }

    pilihSemua.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBtn();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBtn));
})();
</script>
@endif
@endsection
