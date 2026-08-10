@extends('layouts.siswa')

@section('title', 'Dashboard')

@section('content')
<div class="sd-wrap">

    {{-- Header --}}
    <div class="siswa-page-header" style="justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="siswa-page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <h1 class="siswa-page-title">Selamat Datang, {{ $siswa->nama }}</h1>
                <p class="siswa-page-subtitle">Pantau perkembangan pembelajaran Al-Quran Anda</p>
            </div>
        </div>
        <div class="sd-avatar">{{ $siswa->initials }}</div>
    </div>

    {{-- Semester Info --}}
    <div style="margin-bottom:20px;">
        @if($semester)
        <div class="sd-semester" style="margin-bottom:0;">
            <div class="sd-sem-badge {{ $semester->is_aktif ? 'active' : ($semester->status == 'ditutup' ? 'closed' : '') }}">
                {{ $semester->tahun_ajaran }} {{ ucfirst($semester->jenis) }} {{ $semester->is_aktif ? 'Aktif' : '' }}
            </div>
            @if($semester->status == 'ditutup')
            <span class="sd-sem-closed">Semester ditutup — data arsip</span>
            @endif
        </div>
        @endif
    </div>

    {{-- R2 Cards --}}
    <div class="sd-r2grid">
        <div class="sd-r2card">
            <div class="sd-r2icon" style="background:#d1fae5;color:#065f43;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="sd-r2value" style="color:#065f43;">{{ $r2Harian }}<span class="sd-r2unit">%</span></div>
            <div class="sd-r2label">R2 Harian <span style="font-size:9px;color:#a8a29e;font-weight:400;">(B=2, C=1, K=0)</span></div>
            <div class="sd-r2bar"><div class="sd-r2bar-fill" style="width:{{ $r2Harian }}%;background:#0c8a5f;"></div></div>
        </div>
        <div class="sd-r2card">
            <div class="sd-r2icon" style="background:#fef9c3;color:#854d0e;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            </div>
            <div class="sd-r2value" style="color:#854d0e;">{{ $r2Penilaian }}<span class="sd-r2unit">/100</span></div>
            <div class="sd-r2label">R2 Penilaian</div>
            <div class="sd-r2bar"><div class="sd-r2bar-fill" style="width:{{ $r2Penilaian }}%;background:#b48a3e;"></div></div>
        </div>
        <div class="sd-r2card sd-r2card-featured">
            <div class="sd-r2icon" style="background:#1c1917;color:#fff;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="sd-r2value">{{ $r2Akhir }}<span class="sd-r2unit">%</span></div>
            <div class="sd-r2label">R2 Akhir Setelah Penilaian</div>
            <div class="sd-r2desc">Rata-rata R2 Harian & R2 Penilaian</div>
            <div class="sd-r2bar"><div class="sd-r2bar-fill" style="width:{{ $r2Akhir }}%;"></div></div>
            <div class="sd-r2breakdown">
                <span>Sebelum penilaian: <strong>{{ $r2Harian }}%</strong></span>
                <span class="sd-r2sep">&rarr;</span>
                <span>Setelah penilaian: <strong>{{ $r2Akhir }}%</strong></span>
            </div>
        </div>
    </div>

    {{-- Tahfidz Progress --}}
    @if($siswa->kelas_tartil_id && !empty($tahfidzProgress))
    <div class="sd-section" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="sd-section-title" style="margin: 0;">&#128218; Progress Hafalan Al-Quran</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                @if($tahfidzJuzAktif)
                <span style="font-size: 12px; color: #e65100; background: #fff3cd; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                    &#128308; Sedang: Juz {{ $tahfidzJuzAktif['juz'] }} ({{ \App\Models\HafalanTahfidz::labelStatus($tahfidzJuzAktif['status']) }})
                </span>
                @endif
                <span style="font-size: 13px; color: #0c8a5f; font-weight: 700;">{{ $tahfidzTotalJuz }}/30 Juz</span>
            </div>
        </div>

        {{-- Juz 1-30 Grid --}}
        <div class="sd-juz-grid">
            @foreach($tahfidzProgress as $pj)
                @php
                    $bg = match($pj['status']) {
                        'hafal' => '#0c8a5f',
                        'murajaah' => '#6a1b9a',
                        'setengah_hafal' => '#e65100',
                        'baru' => '#1565c0',
                        default => '#e5e5e5',
                    };
                    $tooltip = $pj['status']
                        ? "Juz {$pj['juz']}\n" . \App\Models\HafalanTahfidz::labelStatus($pj['status'])
                            . ($pj['surat'] ? " - {$pj['surat']}" : '')
                            . ($pj['tanggal'] ? "\n{$pj['tanggal']}" : '')
                        : "Juz {$pj['juz']} - Belum";
                @endphp
                <div title="{{ $tooltip }}" style="
                    aspect-ratio: 1;
                    border-radius: 8px;
                    background: {{ $bg }};
                    color: {{ $pj['status'] ? '#fff' : '#aaa' }};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: 700;
                    cursor: default;
                    transition: transform 0.15s;
                " onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                    {{ $pj['juz'] }}
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 11px; color: #666;">
            <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; border-radius: 4px; background: #0c8a5f;"></span> Hafal</span>
            <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; border-radius: 4px; background: #6a1b9a;"></span> Murojaah</span>
            <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; border-radius: 4px; background: #e65100;"></span> Setengah Hafal</span>
            <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; border-radius: 4px; background: #1565c0;"></span> Baru</span>
            <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; border-radius: 4px; background: #e5e5e5;"></span> Belum</span>
        </div>
    </div>
    @endif

    {{-- B/C/K Stats + Chart --}}
    <div class="sd-row">
        <div class="sd-col-left">
            {{-- Penilaian B/C/K --}}
            <div class="sd-section">
                <h2 class="sd-section-title">Penilaian B/C/K</h2>
                <div class="sd-bck-grid">
                    <div class="sd-bck-card sd-bck-b">
                        <div class="sd-bck-num">{{ $bCount }}</div>
                        <div class="sd-bck-label">Baik (B) = 2 Poin</div>
                        <div class="sd-bck-pct">{{ $totalJurnal > 0 ? round(($bCount/$totalJurnal)*100) : 0 }}%</div>
                    </div>
                    <div class="sd-bck-card sd-bck-c">
                        <div class="sd-bck-num">{{ $cCount }}</div>
                        <div class="sd-bck-label">Cukup (C) = 1 Poin</div>
                        <div class="sd-bck-pct">{{ $totalJurnal > 0 ? round(($cCount/$totalJurnal)*100) : 0 }}%</div>
                    </div>
                    <div class="sd-bck-card sd-bck-k">
                        <div class="sd-bck-num">{{ $kCount }}</div>
                        <div class="sd-bck-label">Kurang (K) = 0 Poin</div>
                        <div class="sd-bck-pct">{{ $totalJurnal > 0 ? round(($kCount/$totalJurnal)*100) : 0 }}%</div>
                    </div>
                </div>

                @if($totalJurnal > 0)
                <div class="sd-bck-chart">
                    <div class="sd-bck-row">
                        <span class="sd-bck-tag sd-bck-tag-b">B</span>
                        <div class="sd-bck-track"><div class="sd-bck-fill sd-bck-fill-b" style="width:{{ round(($bCount/$totalJurnal)*100) }}%"></div></div>
                        <span class="sd-bck-val">{{ round(($bCount/$totalJurnal)*100) }}%</span>
                    </div>
                    <div class="sd-bck-row">
                        <span class="sd-bck-tag sd-bck-tag-c">C</span>
                        <div class="sd-bck-track"><div class="sd-bck-fill sd-bck-fill-c" style="width:{{ round(($cCount/$totalJurnal)*100) }}%"></div></div>
                        <span class="sd-bck-val">{{ round(($cCount/$totalJurnal)*100) }}%</span>
                    </div>
                    <div class="sd-bck-row">
                        <span class="sd-bck-tag sd-bck-tag-k">K</span>
                        <div class="sd-bck-track"><div class="sd-bck-fill sd-bck-fill-k" style="width:{{ round(($kCount/$totalJurnal)*100) }}%"></div></div>
                        <span class="sd-bck-val">{{ round(($kCount/$totalJurnal)*100) }}%</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Progress Bulanan --}}
            @if(count($bulanData) > 0)
            <div class="sd-section">
                <h2 class="sd-section-title">Progress Bulanan (% B)</h2>
                <div class="sd-monthly">
                    @foreach($bulanData as $bd)
                    <div class="sd-monthly-item">
                        <div class="sd-monthly-label">{{ $bd['label'] }}</div>
                        <div class="sd-monthly-track">
                            <div class="sd-monthly-fill" style="height:{{ $bd['pct'] }}%;"></div>
                        </div>
                        <div class="sd-monthly-pct">{{ $bd['pct'] }}%</div>
                        @if($bd['perubahan'])
                        <div class="sd-monthly-change {{ match($bd['perubahan']) { 'Meningkat' => 'up', 'Stabil Meningkat' => 'up-soft', 'Menurun' => 'down', 'Stabil Menurun' => 'down-soft', default => '' } }}">
                            @if($bd['selisih'] > 0)&#8593;@elseif($bd['selisih'] < 0)&#8595;@endif
                            {{ abs($bd['selisih']) }}% {{ $bd['perubahan'] }}
                        </div>
                        @else
                        <div class="sd-monthly-change dash">&mdash;</div>
                        @endif
                        <div class="sd-monthly-count">{{ $bd['b'] }}/{{ $bd['total'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="sd-col-right">
            {{-- Info Kelas --}}
            <div class="sd-section">
                <h2 class="sd-section-title">Info Kelas</h2>
                <div class="sd-info-list">
                    <div class="sd-info-item">
                        <span class="sd-info-label">Kelas Reguler</span>
                        <span class="sd-info-value">{{ $siswa->kelasReguler->nama ?? '-' }}</span>
                    </div>
                    <div class="sd-info-item">
                        <span class="sd-info-label">Kelas Tartil</span>
                        <span class="sd-info-value">{{ $siswa->kelasTartil->nama ?? '-' }}</span>
                    </div>
                    <div class="sd-info-item">
                        <span class="sd-info-label">Guru Tartil</span>
                        <span class="sd-info-value">{{ $siswa->kelasTartil->guru->nama ?? '-' }}</span>
                    </div>
                    <div class="sd-info-item">
                        <span class="sd-info-label">NIS</span>
                        <span class="sd-info-value sd-info-mono">{{ $siswa->nis }}</span>
                    </div>
                    <div class="sd-info-item">
                        <span class="sd-info-label">No. HP</span>
                        <span class="sd-info-value">{{ $siswa->no_hp ?? '-' }}</span>
                    </div>
                    <div class="sd-info-item">
                        <span class="sd-info-label">Status</span>
                        <span class="sd-info-badge {{ $siswa->status == 'aktif' ? 'active' : '' }}">{{ ucfirst($siswa->status) }}</span>
                    </div>
                    @if($siswa->tanggal_masuk_kelas_tartil)
                    <div class="sd-info-item">
                        <span class="sd-info-label">Masuk Kelas Tartil</span>
                        <span class="sd-info-value">{{ $siswa->tanggal_masuk_kelas_tartil->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Munaqosyah --}}
            @if($munaqosyah->count() > 0)
            <div class="sd-section">
                <h2 class="sd-section-title">Ujian Munaqosyah</h2>
                <div class="sd-munaqosyah-list">
                    @foreach($munaqosyah as $mp)
                    <div class="sd-munaqosyah-item">
                        <div class="sd-munaqosyah-name">{{ $mp->munaqosyah->surat_mulai }} - {{ $mp->munaqosyah->surat_selesai }}</div>
                        <div class="sd-munaqosyah-status {{ $mp->status == 'L' ? 'lulus' : ($mp->status == 'TL' ? 'tidak-lulus' : 'pending') }}">
                            {{ $mp->status == 'L' ? 'Lulus' : ($mp->status == 'TL' ? 'Tidak Lulus' : 'Pending') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Ringkasan --}}
            <div class="sd-section">
                <h2 class="sd-section-title">Ringkasan</h2>
                <div class="sd-summary">
                    <div class="sd-summary-item">
                        <span class="sd-summary-num">{{ $totalJurnal }}</span>
                        <span class="sd-summary-label">Total Jurnal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info semester --}}
    @if($semester)
    <div style="background:{{ $semester->is_aktif ? '#f0fdf4' : '#fefce8' }};border:1px solid {{ $semester->is_aktif ? '#bbf7d0' : '#fde68a' }};border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:{{ $semester->is_aktif ? '#065f43' : '#854d0e' }};display:flex;justify-content:space-between;align-items:center;">
        <span>Semester: <strong>{{ $semester->tahun_ajaran }} {{ ucfirst($semester->jenis) }}</strong></span>
        <span style="font-size:11px;">{{ $semester->is_aktif ? 'Aktif' : 'Berlangsung' }}</span>
    </div>
    @else
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#991b1b;">
        Tidak ada semester yang tersedia. Hubungi admin untuk mengaktifkan semester.
    </div>
    @endif

    {{-- Jurnal Terbaru — Ringkas + Detail Modal --}}
    @if($jurnals->count() > 0)
    <div class="sd-section">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 class="sd-section-title" style="margin:0;">Jurnal Terbaru</h2>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:11px;color:#a8a29e;">{{ $jurnals->count() }} entri</span>
                @if($semester)
                <span style="font-size:11px;color:#78716c;background:#f5f5f4;padding:2px 10px;border-radius:999px;">{{ $semester->tahun_ajaran }} {{ ucfirst($semester->jenis) }}</span>
                @endif
            </div>
        </div>
        {{-- Timeline compact view --}}
        <div class="sd-journal-list">
            @foreach($jurnals as $index => $j)
            <div class="sd-journal-item" onclick="openJournalDetail({{ $index }})" style="cursor:pointer;">
                <div class="sd-journal-main">
                    <div class="sd-journal-date">{{ $j->tanggal?->format('d M Y') }}</div>
                    <div class="sd-journal-dot {{ $j->penilaian == 'B' ? 'dot-b' : ($j->penilaian == 'C' ? 'dot-c' : 'dot-k') }}"></div>
                    <div class="sd-journal-badge">{!! \App\Models\JurnalHarian::penilaianBadge($j->penilaian) !!}</div>
                    <div class="sd-journal-mini">
                        @if($j->surat?->nama)
                        <span>{{ $j->surat->nama }}</span>
                        @else
                        <span style="color:#d4d4d4;">Surat tidak dicatat</span>
                        @endif
                    </div>
                    <button class="sd-journal-detail-btn" type="button" onclick="event.stopPropagation();openJournalDetail({{ $index }})" title="Lihat detail">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Detail
                    </button>
                </div>
                @if($j->catatan)
                <div class="sd-journal-note" onclick="event.stopPropagation();">
                    <span class="sd-journal-note-label">Catatan Guru:</span>
                    {{ $j->catatan }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Journal Detail Modal --}}
    <div id="journalModal" class="sd-modal" onclick="closeJournalModal(event)">
        <div class="sd-modal-content" onclick="event.stopPropagation()">
            <div class="sd-modal-header">
                <h3 class="sd-modal-title">Detail Jurnal</h3>
                <button class="sd-modal-close" onclick="closeJournalModal()" type="button">&times;</button>
            </div>
            <div class="sd-modal-body" id="journalModalBody">
                {{-- Content filled by JS --}}
            </div>
        </div>
    </div>

    <script>
    const journalData = {!! json_encode($jurnals->map(function($j) {
        return [
            'tanggal' => optional($j->tanggal)->format('d M Y'),
            'hari' => optional($j->tanggal)->translatedFormat('l'),
            'penilaian' => $j->penilaian,
            'penilaian_label' => $j->penilaian == 'B' ? 'Baik' : ($j->penilaian == 'C' ? 'Cukup' : 'Kurang'),
            'penilaian_class' => $j->penilaian == 'B' ? 'sd-badge-b' : ($j->penilaian == 'C' ? 'sd-badge-c' : 'sd-badge-k'),
            'surat' => optional($j->surat)->nama,
            'ayat' => $j->ayat_mulai ? $j->ayat_mulai . ($j->ayat_selesai && $j->ayat_selesai != $j->ayat_mulai ? ' - ' . $j->ayat_selesai : '') : null,
            'halaman' => $j->halaman,
            'materi' => $j->materi,
            'topik' => $j->topik,
            'rencana' => $j->rencana,
            'catatan' => $j->catatan ?? null,
        ];
    })->values()) !!};

    function openJournalDetail(index) {
        const d = journalData[index];
        if (!d) return;
        const body = document.getElementById('journalModalBody');
        body.innerHTML = `
            <div style="margin-bottom:20px;">
                <div style="font-size:12px;color:#78716c;margin-bottom:4px;">${d.hari}, ${d.tanggal}</div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="sd-badge ${d.penilaian_class}" style="font-size:16px;padding:4px 16px;">${d.penilaian}</span>
                    <span style="font-size:15px;font-weight:600;color:#1c1917;">${d.penilaian_label}</span>
                </div>
            </div>
            <div class="sd-detail-grid">
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Surat</span>
                    <span class="sd-detail-value">${d.surat ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Ayat</span>
                    <span class="sd-detail-value">${d.ayat ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Halaman</span>
                    <span class="sd-detail-value">${d.halaman ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Materi Pembelajaran</span>
                    <span class="sd-detail-value">${d.materi ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Topik</span>
                    <span class="sd-detail-value">${d.topik ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Rencana</span>
                    <span class="sd-detail-value">${d.rencana ?? '<span style="color:#d4d4d4;">Belum dicatat</span>'}</span>
                </div>
                <div class="sd-detail-row">
                    <span class="sd-detail-label">Catatan Guru</span>
                    <span class="sd-detail-value">${d.catatan ?? '<span style="color:#d4d4d4;">Tidak ada catatan</span>'}</span>
                </div>
            </div>
        `;
        document.getElementById('journalModal').style.display = 'flex';
    }

    function closeJournalModal(e) {
        if (!e || e.target === document.getElementById('journalModal')) {
            document.getElementById('journalModal').style.display = 'none';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeJournalModal();
    });
    </script>
    @else
    @if($semester?->id && $siswa->kelas_tartil_id)
    <div class="sd-section" style="text-align:center;padding:40px 20px;color:#a8a29e;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:0.4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <p style="font-size:13px;margin:0;">Belum ada data jurnal untuk semester ini.<br>Data akan muncul setelah guru menginput jurnal harian.</p>
        @if($semester)
        <p style="font-size:11px;color:#d4d4d4;margin-top:8px;">{{ $semester->tahun_ajaran }} {{ ucfirst($semester->jenis) }}</p>
        @endif
    </div>
    @endif
    @endif

    {{-- Popup Konfirmasi Monitoring Orang Tua --}}
    @if($hafalanBelumDikonfirmasi->isNotEmpty())
    <div id="konfirmasiOrtuModal" class="sd-modal" style="display: flex;" onclick="closeKonfirmasiOrtu(event)">
        <div class="sd-modal-content" style="max-width: 520px;" onclick="event.stopPropagation()">
            <div class="sd-modal-header">
                <h3 class="sd-modal-title">&#128100; Konfirmasi Monitoring Orang Tua</h3>
                <button type="button" class="sd-modal-close" onclick="document.getElementById('konfirmasiOrtuModal').style.display='none'" title="Tutup">&times;</button>
            </div>
            <div class="sd-modal-body">
                <p style="font-size: 12px; color: #78716c; margin-bottom: 16px;">
                    Berikut setoran hafalan yang belum dikonfirmasi. Silakan centang setoran yang sudah dipantau, lalu klik tombol konfirmasi.
                </p>
                <form method="POST" action="{{ route('siswa.hafalan.konfirmasi') }}" id="formKonfirmasiOrtu">
                    @csrf
                    <input type="hidden" name="redirect" value="dashboard">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12px; color: #92400e; font-weight: 600;">{{ $hafalanBelumDikonfirmasi->count() }} setoran belum dikonfirmasi</span>
                        <label style="font-size: 12px; color: #78716c; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="checkbox" id="pilihSemuaOrtu" style="cursor: pointer;">
                            Pilih semua
                        </label>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; max-height: 300px; overflow-y: auto; padding-right: 4px;">
                        @foreach($hafalanBelumDikonfirmasi as $h)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; cursor: pointer; font-size: 13px; flex-wrap: wrap;">
                            <input type="checkbox" name="hafalan_ids[]" value="{{ $h->id }}" class="checkbox-ortu">
                            <div style="flex: 1; min-width: 0; word-break: break-word;">
                                <strong>Juz {{ $h->juz }}</strong>
                                @if($h->surat) · {{ $h->surat->nama_latin }}@endif
                                · {{ $h->ayat_mulai }}{{ $h->ayat_selesai ? '-'.$h->ayat_selesai : '' }}
                                <div style="font-size: 11px; color: #78716c; margin-top: 2px;">
                                    Setoran: {{ $h->tanggal_hafalan?->format('d/m/Y') }} · Guru: {{ $h->guru?->nama ?? '-' }}
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="sd-btn-cetak" style="width: 100%; justify-content: center; border: none; cursor: pointer;" onclick="return confirm('Konfirmasi setoran yang dipilih?')">
                        Konfirmasi Setoran Terpilih
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function closeKonfirmasiOrtu(e) {
        if (!e || e.target === document.getElementById('konfirmasiOrtuModal')) {
            document.getElementById('konfirmasiOrtuModal').style.display = 'none';
        }
    }
    document.getElementById('pilihSemuaOrtu').addEventListener('change', function() {
        document.querySelectorAll('.checkbox-ortu').forEach(cb => cb.checked = this.checked);
    });
    </script>
    @endif

</div>

<style>
.sd-wrap { max-width: 960px; margin: 0 auto; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

.sd-btn-cetak {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; background: #1c1917; color: #fff;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all 0.2s;
}
.sd-btn-cetak:hover { background: #44403c; transform: translateY(-1px); }

.sd-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.sd-title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
.sd-sub { font-size: 13px; color: #78716c; margin: 2px 0 0; }
.sd-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: #0c8a5f; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700;
}

.sd-semester { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.sd-sem-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 999px;
    font-size: 11px; font-weight: 600;
    background: #f5f5f4; color: #78716c;
}
.sd-sem-badge.active { background: #d1fae5; color: #065f43; }
.sd-sem-badge.closed { background: #fecaca; color: #991b1b; }
.sd-sem-closed { font-size: 12px; color: #a8a29e; }

.sd-r2grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
@media (max-width: 640px) { .sd-r2grid { grid-template-columns: 1fr; } }

.sd-r2card {
    background: #fff; border: 1px solid #e7e5e4; border-radius: 12px;
    padding: 20px; position: relative; overflow: hidden;
}
.sd-r2card-featured {
    background: #1c1917; border-color: #1c1917; color: #fff;
}
.sd-r2icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
}
.sd-r2value {
    font-size: 32px; font-weight: 800; letter-spacing: -1px; line-height: 1;
    margin-bottom: 4px;
}
.sd-r2card-featured .sd-r2value { color: #fff; }
.sd-r2unit { font-size: 14px; font-weight: 600; margin-left: 2px; opacity: 0.7; }
.sd-r2label { font-size: 12px; color: #78716c; font-weight: 500; }
.sd-r2card-featured .sd-r2label { color: #a8a29e; }
.sd-r2desc { font-size: 11px; color: #78716c; margin: 4px 0 10px; }
.sd-r2card-featured .sd-r2desc { color: #a8a29e; }
.sd-r2bar {
    height: 4px; background: #f5f5f4; border-radius: 2px; margin-top: 12px; overflow: hidden;
}
.sd-r2card-featured .sd-r2bar { background: #333; }
.sd-r2bar-fill {
    height: 100%; background: #0c8a5f; border-radius: 2px;
    transition: width 0.8s ease;
}
.sd-r2breakdown {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    margin-top: 10px; padding-top: 10px; border-top: 1px solid #333;
    font-size: 11px; color: #a8a29e; flex-wrap: wrap; text-align: center;
}
.sd-r2breakdown strong { color: #fff; font-weight: 700; }
.sd-r2sep { color: #0c8a5f; font-weight: 700; }

.sd-row { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px; margin-bottom: 20px; }
@media (max-width: 768px) { .sd-row { grid-template-columns: 1fr; } }

.sd-section {
    background: #fff; border: 1px solid #e7e5e4; border-radius: 12px;
    padding: 20px; margin-bottom: 16px;
}
.sd-section-title {
    font-size: 13px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: #78716c; margin: 0 0 16px;
}

.sd-bck-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
.sd-bck-card { text-align: center; padding: 16px; border-radius: 10px; }
.sd-bck-b { background: #f0fdf4; }
.sd-bck-c { background: #fefce8; }
.sd-bck-k { background: #fef2f2; }
.sd-bck-num { font-size: 24px; font-weight: 800; letter-spacing: -1px; }
.sd-bck-b .sd-bck-num { color: #166534; }
.sd-bck-c .sd-bck-num { color: #854d0e; }
.sd-bck-k .sd-bck-num { color: #991b1b; }
.sd-bck-label { font-size: 11px; color: #78716c; margin-top: 2px; }
.sd-bck-pct { font-size: 12px; font-weight: 600; margin-top: 4px; }

.sd-bck-chart { display: flex; flex-direction: column; gap: 8px; }
.sd-bck-row { display: flex; align-items: center; gap: 8px; }
.sd-bck-tag { width: 22px; height: 22px; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.sd-bck-tag-b { background: #f0fdf4; color: #166534; }
.sd-bck-tag-c { background: #fefce8; color: #854d0e; }
.sd-bck-tag-k { background: #fef2f2; color: #991b1b; }
.sd-bck-track { flex: 1; height: 8px; background: #f5f5f4; border-radius: 4px; overflow: hidden; }
.sd-bck-fill { height: 100%; border-radius: 4px; }
.sd-bck-fill-b { background: #86efac; }
.sd-bck-fill-c { background: #fde047; }
.sd-bck-fill-k { background: #fca5a5; }
.sd-bck-val { font-size: 12px; font-weight: 600; color: #44403c; width: 36px; text-align: right; }

.sd-monthly { display: flex; align-items: flex-end; gap: 6px; height: 140px; padding-top: 20px; }
.sd-monthly-item { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sd-monthly-label { font-size: 9px; color: #a8a29e; text-align: center; white-space: nowrap; }
.sd-monthly-track { width: 24px; height: 80px; background: #f5f5f4; border-radius: 4px; position: relative; overflow: hidden; }
.sd-monthly-fill { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, #0c8a5f, #34d399); border-radius: 4px; transition: height 0.6s ease; }
.sd-monthly-pct { font-size: 10px; font-weight: 700; color: #1c1917; }
.sd-monthly-count { font-size: 9px; color: #a8a29e; }

/* Perubahan persentase bulanan */
.sd-monthly-change { font-size: 9px; font-weight: 600; padding: 1px 5px; border-radius: 4px; white-space: nowrap; }
.sd-monthly-change.up { background: #f0fdf4; color: #166534; }
.sd-monthly-change.up-soft { background: #ecfdf5; color: #15803d; }
.sd-monthly-change.down { background: #fef2f2; color: #991b1b; }
.sd-monthly-change.down-soft { background: #fff7ed; color: #9a3412; }
.sd-monthly-change.dash { color: #d4d4d4; }

.sd-info-list { display: flex; flex-direction: column; gap: 2px; }
.sd-info-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f5f5f4; }
.sd-info-item:last-child { border-bottom: none; }
.sd-info-label { font-size: 12px; color: #78716c; }
.sd-info-value { font-size: 13px; font-weight: 600; color: #1c1917; }
.sd-info-mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.sd-info-badge { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #f5f5f4; color: #78716c; }
.sd-info-badge.active { background: #d1fae5; color: #065f43; }

.sd-munaqosyah-list { display: flex; flex-direction: column; gap: 8px; }
.sd-munaqosyah-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #fafaf9; border-radius: 8px; }
.sd-munaqosyah-name { font-size: 12px; font-weight: 600; }
.sd-munaqosyah-status { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
.sd-munaqosyah-status.lulus { background: #d1fae5; color: #065f43; }
.sd-munaqosyah-status.tidak-lulus { background: #fecaca; color: #991b1b; }
.sd-munaqosyah-status.pending { background: #fef9c3; color: #854d0e; }

.sd-summary { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; text-align: center; }
.sd-summary-num { font-size: 24px; font-weight: 800; color: #1c1917; display: block; }
.sd-summary-label { font-size: 11px; color: #78716c; margin-top: 2px; }

.sd-table-wrap { overflow-x: auto; }
.sd-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.sd-table thead th {
    padding: 8px 10px; text-align: left;
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
    color: #78716c; border-bottom: 2px solid #e7e5e4;
}
.sd-table tbody td { padding: 8px 10px; border-bottom: 1px solid #f5f5f4; color: #44403c; }
.sd-table tbody tr:last-child td { border-bottom: none; }

/* ════════════════════════════════════════════
   PENILAIAN BADGES — B / C / K
   ════════════════════════════════════════════ */
.sd-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 24px;
    padding: 0 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    font-family: 'JetBrains Mono', monospace;
}
.sd-badge-b { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.sd-badge-c { background: #fefce8; color: #854d0e; border: 1px solid #fde68a; }
.sd-badge-k { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.sd-badge-muted { background: #f5f5f4; color: #a8a29e; border: 1px solid #e7e5e4; }

/* ════════════════════════════════════════════
   JUZ GRID — Progress Hafalan
   ════════════════════════════════════════════ */
.sd-juz-grid { display: grid; grid-template-columns: repeat(15, 1fr); gap: 4px; margin-bottom: 16px; }

@media (max-width: 640px) {
    .sd-juz-grid { grid-template-columns: repeat(10, 1fr); }
}
@media (max-width: 420px) {
    .sd-juz-grid { grid-template-columns: repeat(6, 1fr); }
}

/* ════════════════════════════════════════════
   JURNAL TIMELINE — Compact + Detail Modal
   ════════════════════════════════════════════ */
.sd-journal-list { display: flex; flex-direction: column; }
.sd-journal-item {
    display: flex; flex-direction: column;
    padding: 12px 14px;
    border-bottom: 1px solid #f5f5f4; transition: background 0.15s;
}
.sd-journal-item:last-child { border-bottom: none; }
.sd-journal-item:hover { background: #fafaf9; }
.sd-journal-main {
    display: grid; grid-template-columns: 80px 20px 50px 1fr auto;
    align-items: center; gap: 10px;
}
.sd-journal-date { font-size: 12px; color: #78716c; font-weight: 500; }
.sd-journal-dot { width: 10px; height: 10px; border-radius: 50%; justify-self: center; }
.sd-journal-dot.dot-b { background: #86efac; }
.sd-journal-dot.dot-c { background: #fde047; }
.sd-journal-dot.dot-k { background: #fca5a5; }
.sd-journal-mini { font-size: 12px; color: #78716c; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sd-journal-note {
    margin-top: 10px; padding: 10px 12px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
    font-size: 12px; color: #92400e; line-height: 1.5;
}
.sd-journal-note-label {
    font-weight: 700; margin-right: 4px;
}
.sd-journal-detail-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; background: #f5f5f4; color: #78716c;
    border: none; border-radius: 6px; font-size: 11px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; flex-shrink: 0;
}
.sd-journal-detail-btn:hover { background: #e7e5e4; color: #44403c; }

/* Modal */
.sd-modal {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);
    align-items: center; justify-content: center; padding: 20px;
}
.sd-modal-content {
    background: #fff; border-radius: 16px; width: 100%; max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: sdModalIn 0.25s ease;
}
@keyframes sdModalIn {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.sd-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; border-bottom: 1px solid #f5f5f4;
}
.sd-modal-title { font-size: 15px; font-weight: 700; color: #1c1917; margin: 0; }
.sd-modal-close {
    width: 30px; height: 30px; border-radius: 8px; border: none;
    background: #f5f5f4; color: #78716c; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.sd-modal-close:hover { background: #e7e5e4; color: #44403c; }
.sd-modal-body { padding: 20px; }
.sd-detail-grid { display: flex; flex-direction: column; gap: 2px; }
.sd-detail-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 12px 0; border-bottom: 1px solid #f5f5f4;
}
.sd-detail-row:last-child { border-bottom: none; }
.sd-detail-label { font-size: 12px; color: #78716c; font-weight: 500; flex-shrink: 0; }
.sd-detail-value { font-size: 13px; color: #1c1917; font-weight: 600; text-align: right; max-width: 60%; word-wrap: break-word; overflow-wrap: break-word; }

@media (max-width: 640px) {
    .sd-journal-main { grid-template-columns: 70px 16px 40px 1fr auto; gap: 6px; }
    .sd-journal-item { padding: 10px; }
    .sd-journal-note { font-size: 12px; padding: 8px 10px; }
    .sd-journal-mini { display: none; }
    .sd-r2grid { grid-template-columns: 1fr; }
    .sd-modal-content { padding: 16px; width: 95%; }
    .sd-modal-body { padding: 16px; }
    .sd-modal-title { font-size: 14px; }
}
</style>
@endsection
