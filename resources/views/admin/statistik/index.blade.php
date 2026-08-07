@extends('layouts.admin')

@section('title', 'Statistik')

@section('content')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.stat-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.stat-card-title {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.chart-container {
    position: relative;
    height: 260px;
}
.ta-section {
    margin-bottom: 32px;
}
.ta-header {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #0c8a5f;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ta-header .ta-status {
    font-size: 11px;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 600;
}
.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.summary-box {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.summary-box .val {
    font-size: 24px;
    font-weight: 700;
    color: #0c8a5f;
}
.summary-box .lbl {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}
.grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}
.mq-bar-item {
    background: #f8faf8;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 8px;
    border-left: 4px solid #0c8a5f;
}
.mq-bar-label {
    font-size: 12px;
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
}
.mq-bar-track {
    height: 24px;
    background: #e8e8e8;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}
.mq-bar-fill {
    height: 100%;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 10px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    transition: width 1s ease;
}
.badge-green { background: #0c8a5f; }
.badge-blue { background: #1565c0; }
.badge-orange { background: #e65100; }
.empty-state {
    text-align: center;
    padding: 48px;
    color: #888;
    font-size: 14px;
}
.doughnut-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.page-intro {
    background: #e8f5e9;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">Statistik</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Dashboard grafik perkembangan 3 Tahun Ajaran terakhir</p>
    </div>
</div>

<div class="page-intro">
    <h2 style="font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px;">&#128202; Perbandingan 3 Tahun Ajaran</h2>
    <p style="font-size: 13px; color: #555; margin: 0;">Data ditampilkan per TA: TA aktif + 2 TA sebelumnya. TA tanpa data akan menunjukkan grafik kosong (nilai 0).</p>
</div>

@if(empty($chartData['taLabels']))
    <div class="empty-state">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128202;</div>
        <h3>Belum ada data statistik</h3>
        <p>Data akan muncul setelah ada semester yang ditutup dan di-lock.</p>
    </div>
@else

    {{-- ══════ GRAFIK PER SEMESTER (semua TA) ══════ --}}
    <div class="grid-2">
        <div class="stat-card">
            <div class="stat-card-title">&#128200; R2 Akhir per Semester</div>
            <div class="chart-container"><canvas id="chartR2Akhir"></canvas></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-title">&#128200; R2 Harian vs Penilaian</div>
            <div class="chart-container"><canvas id="chartR2Compare"></canvas></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="stat-card">
            <div class="stat-card-title">&#128101; Jumlah Siswa per Semester</div>
            <div class="chart-container"><canvas id="chartSiswa"></canvas></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-title">&#128218; Rata-rata Hari Mengaji per Semester</div>
            <div class="chart-container"><canvas id="chartJurnal"></canvas></div>
        </div>
    </div>

    {{-- ══════ TAHFIDZ & HAFALAN RINGKASAN ══════ --}}
    @if(!empty($chartData['tahfidz']) && ($chartData['tahfidz']['totalKelasTahfidz'] ?? 0) > 0)
    <div class="stat-card" style="margin-bottom: 24px;">
        <div class="stat-card-title">&#128218; Tahfidz & Hafalan — Ringkasan Kumulatif</div>
        <div class="summary-grid">
            <div class="summary-box">
                <div class="val">{{ $chartData['tahfidz']['totalKelasTahfidz'] ?? 0 }}</div>
                <div class="lbl">Kelas</div>
            </div>
            <div class="summary-box">
                <div class="val">{{ $chartData['tahfidz']['totalSiswaTahfidz'] ?? 0 }}</div>
                <div class="lbl">Siswa</div>
            </div>
            <div class="summary-box">
                <div class="val">{{ $chartData['tahfidz']['totalJuzEntries'] ?? 0 }}</div>
                <div class="lbl">Total Setoran Juz</div>
            </div>
            <div class="summary-box">
                <div class="val">
                    {{ !empty($chartData['tahfidz']['perKelas']) ? round(collect($chartData['tahfidz']['perKelas'])->avg('avgJuz'), 1) : 0 }}
                </div>
                <div class="lbl">Rata-rata Juz per Kelas</div>
            </div>
        </div>

        @if(!empty($chartData['tahfidz']['perKelas']))
        <div style="margin-top: 16px;">
            <div style="font-size: 12px; font-weight: 700; color: #555; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Per Kelas</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                @foreach($chartData['tahfidz']['perKelas'] as $kelas)
                <div style="background: #f8faf8; border-radius: 8px; padding: 14px; border-left: 3px solid #0c8a5f;">
                    <div style="font-weight: 700; color: #1a1a2e; font-size: 14px;">{{ $kelas['nama'] }}</div>
                    <div style="font-size: 11px; color: #888; margin-bottom: 10px;">Guru: {{ $kelas['guru'] }} &middot; {{ $kelas['totalSiswa'] }} siswa</div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #555;">Rata-rata juz</span>
                        <span style="font-weight: 700; color: #0c8a5f;">{{ $kelas['avgJuz'] }}</span>
                    </div>
                    @if(!empty($kelas['topSiswa']))
                    <div style="font-size: 10px; color: #555; margin-top: 8px; line-height: 1.5;">
                        <span style="font-weight: 600;">Top:</span>
                        @foreach($kelas['topSiswa'] as $s)
                            {{ $s['siswa']['nama'] ?? '-' }} ({{ $s['juzHafal'] }} juz){{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════ PER TA: Ringkasan + Munaqosyah ══════ --}}
    @foreach($chartData['taLabels'] as $taIdx => $ta)
        @php
            $taData = $chartData['perTA'][$ta] ?? null;
            $taMq = $chartData['munaqosyah'][$ta] ?? [];
            $isAktif = $taIdx === count($chartData['taLabels']) - 1;
        @endphp
        <div class="ta-section">
            <div class="ta-header">
                <span>TA {{ $ta }}</span>
                <span class="ta-status" style="background: {{ $isAktif ? '#d4edda' : '#f5f5f5' }}; color: {{ $isAktif ? '#155724' : '#888' }};">
                    {{ $isAktif ? 'TA AKTIF' : 'TA SELESAI' }}
                </span>
            </div>

            {{-- Ringkasan per TA --}}
            <div class="summary-grid">
                <div class="summary-box">
                    <div class="val">{{ $taData['totalSiswa'] ?? 0 }}</div>
                    <div class="lbl">Total Siswa</div>
                </div>
                <div class="summary-box">
                    <div class="val" style="color: #1565c0;">{{ $taData['totalSemester'] ?? 0 }}</div>
                    <div class="lbl">Semester</div>
                </div>
                <div class="summary-box">
                    <div class="val" style="color: #e65100;">{{ $taData['rataR2Akhir'] ?? 0 }}</div>
                    <div class="lbl">R2 Akhir Rata-rata</div>
                </div>
                <div class="summary-box">
                    <div class="val" style="color: #6a1b9a;">{{ $taData['rataMengaji'] ?? 0 }}</div>
                    <div class="lbl">Mengaji Rata-rata (hari)</div>
                </div>
            </div>

            {{-- Munaqosyah per TA --}}
            @if(!empty($taMq))
                <div class="stat-card" style="margin-bottom: 16px;">
                    <div class="stat-card-title">&#127942; Munaqosyah TA {{ $ta }} — Total & Persentase Kelulusan</div>
                    <div class="grid-2">
                        {{-- Bar Chart --}}
                        <div class="chart-container" style="height: 220px;">
                            <canvas id="chartMqBar{{ $taIdx }}"></canvas>
                        </div>
                        {{-- Progress Bars + Detail Ujian --}}
                        <div>
                            @foreach($taMq as $i => $mq)
                                <div class="mq-bar-item" style="border-left-color: {{ ['#0c8a5f', '#1565c0', '#e65100'][$loop->index % 3] }}">
                                    <div class="mq-bar-label">
                                        {{ $mq['label'] }}
                                        @if($mq['jumlahUjian'] > 1)
                                            <span style="font-size: 10px; color: #888; font-weight: 400;">({{ $mq['jumlahUjian'] }} ujian)</span>
                                        @endif
                                    </div>
                                    <div class="mq-bar-track">
                                        <div class="mq-bar-fill {{ ['badge-green', 'badge-blue', 'badge-orange'][$loop->index % 3] }}" style="width: {{ $mq['persentaseLulus'] }}%;">
                                            {{ $mq['persentaseLulus'] }}%
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: #666;">
                                        <span>Total: {{ $mq['total'] }} peserta</span>
                                        <span>Lulus: {{ $mq['lulus'] }} | Tidak: {{ $mq['tidakLulus'] }}</span>
                                    </div>

                                    {{-- Detail per ujian (expandable) --}}
                                    @if(!empty($mq['detailUjian']) && count($mq['detailUjian']) > 1)
                                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                                            <div style="font-size: 10px; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Detail per Ujian</div>
                                            @foreach($mq['detailUjian'] as $uj)
                                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: #f8faf8; border-radius: 6px; margin-bottom: 4px; font-size: 11px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $uj['persentaseLulus'] >= 70 ? '#0c8a5f' : ($uj['persentaseLulus'] >= 50 ? '#e65100' : '#ef5350') }};"></span>
                                                        <span style="font-weight: 600; color: #333;">{{ $uj['nama'] }}</span>
                                                        <span style="color: #888;">{{ $uj['tanggal'] }}</span>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <span style="color: #666;">{{ $uj['total'] }} peserta</span>
                                                        <span style="font-weight: 700; color: {{ $uj['persentaseLulus'] >= 70 ? '#0c8a5f' : ($uj['persentaseLulus'] >= 50 ? '#e65100' : '#ef5350') }};">{{ $uj['persentaseLulus'] }}%</span>
                                                        <span style="color: #888;">({{ $uj['lulus'] }}L / {{ $uj['tidakLulus'] }}TL)</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif(!empty($mq['detailUjian']) && count($mq['detailUjian']) === 1)
                                        @php $uj = $mq['detailUjian'][0]; @endphp
                                        <div style="margin-top: 8px; font-size: 11px; color: #888; padding-left: 4px;">
                                            {{ $uj['nama'] }} &middot; {{ $uj['tanggal'] }} &middot; {{ $uj['total'] }} peserta
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Doughnut Charts --}}
                    <div class="doughnut-grid" style="margin-top: 16px;">
                        @foreach($taMq as $i => $mq)
                            <div style="text-align: center;">
                                <div style="height: 160px; margin: 0 auto; max-width: 200px;">
                                    <canvas id="chartMqPie{{ $taIdx }}_{{ $loop->index }}"></canvas>
                                </div>
                                <div style="font-size: 12px; color: #555; margin-top: 8px;">
                                    <strong style="color: #0c8a5f;">{{ $mq['persentaseLulus'] }}%</strong> kelulusan
                                    <span style="color: #888;">({{ $mq['lulus'] }}/{{ $mq['total'] }})</span>
                                    @if($mq['jumlahUjian'] > 1)
                                        <div style="font-size: 10px; color: #888; margin-top: 2px;">{{ $mq['jumlahUjian'] }} ujian</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    <script>
    const labels = @json($chartData['semesterLabels'] ?? []);
    const colors = {
        green: '#0c8a5f', greenLight: 'rgba(12,138,95,0.2)',
        blue: '#1565c0', blueLight: 'rgba(21,101,192,0.2)',
        orange: '#e65100', orangeLight: 'rgba(230,81,0,0.2)',
        purple: '#6a1b9a', purpleLight: 'rgba(106,27,154,0.2)',
    };

    // Chart 1: R2 Akhir
    new Chart(document.getElementById('chartR2Akhir'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'R2 Akhir',
                data: @json($chartData['r2Akhir'] ?? []),
                borderColor: colors.green, backgroundColor: colors.greenLight,
                fill: true, tension: 0.3, pointRadius: 5, pointBackgroundColor: colors.green,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } },
                x: { ticks: { font: { size: 9 } } }
            }
        }
    });

    // Chart 2: R2 Harian vs Penilaian
    new Chart(document.getElementById('chartR2Compare'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'R2 Harian', data: @json($chartData['r2Harian'] ?? []), backgroundColor: colors.blue, borderRadius: 4 },
                { label: 'R2 Penilaian', data: @json($chartData['r2Penilaian'] ?? []), backgroundColor: colors.orange, borderRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } },
                x: { ticks: { font: { size: 9 } } }
            }
        }
    });

    // Chart 3: Siswa
    new Chart(document.getElementById('chartSiswa'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Jumlah Siswa', data: @json($chartData['siswa'] ?? []), backgroundColor: colors.purple, borderRadius: 4 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 9 } } } }
        }
    });

    // Chart 4: Jurnal
    new Chart(document.getElementById('chartJurnal'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Hari Mengaji (rata-rata)', data: @json($chartData['jurnalHari'] ?? []),
                borderColor: colors.orange, backgroundColor: colors.orangeLight,
                fill: true, tension: 0.3, pointRadius: 5, pointBackgroundColor: colors.orange,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 9 } } } }
        }
    });

    // Munaqosyah Charts per TA
    @foreach($chartData['taLabels'] as $taIdx => $ta)
        @if(!empty($chartData['munaqosyah'][$ta]))
            // Bar chart
            new Chart(document.getElementById('chartMqBar{{ $taIdx }}'), {
                type: 'bar',
                data: {
                    labels: @json(array_column($chartData['munaqosyah'][$ta], 'label')),
                    datasets: [
                        { label: 'Lulus', data: @json(array_column($chartData['munaqosyah'][$ta], 'lulus')), backgroundColor: colors.green, borderRadius: 4 },
                        { label: 'Tidak Lulus', data: @json(array_column($chartData['munaqosyah'][$ta], 'tidakLulus')), backgroundColor: '#ef5350', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
                    scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
                }
            });

            // Doughnut charts
            @foreach($chartData['munaqosyah'][$ta] as $i => $mq)
                new Chart(document.getElementById('chartMqPie{{ $taIdx }}_{{ $loop->index }}'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Lulus', 'Tidak Lulus'],
                        datasets: [{
                            data: [{{ $mq['lulus'] }}, {{ $mq['tidakLulus'] }}],
                            backgroundColor: [colors.green, '#ef5350'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } } }
                    }
                });
            @endforeach
        @endif
    @endforeach
    // Chart: Distribusi Kualitas Tahfidz
    @if(!empty($chartData['tahfidz']['distribusiKualitas']))
    new Chart(document.getElementById('chartKualitasTahfidz'), {
        type: 'doughnut',
        data: {
            labels: @json(array_map(fn($k) => \App\Models\HafalanTahfidz::labelKualitas($k), array_keys($chartData['tahfidz']['distribusiKualitas']))),
            datasets: [{
                data: @json(array_values($chartData['tahfidz']['distribusiKualitas'])),
                backgroundColor: ['#0c8a5f', '#1565c0', '#e65100', '#ef5350'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } } }
        }
    });
    @endif
    </script>

    {{-- ══════ TAHFIDZ SECTION ══════ --}}
    @if(!empty($chartData['tahfidz']['totalKelasTahfidz']) && $chartData['tahfidz']['totalKelasTahfidz'] > 0)
    <div class="ta-section" style="margin-top: 32px;">
        <div class="ta-header">
            <span>&#128218; Tahfidz</span>
        </div>

        <div class="summary-grid">
            <div class="summary-box">
                <div class="val">{{ $chartData['tahfidz']['totalKelasTahfidz'] }}</div>
                <div class="lbl">Kelas Tahfidz</div>
            </div>
            <div class="summary-box">
                <div class="val" style="color: #1565c0;">{{ $chartData['tahfidz']['totalSiswaTahfidz'] }}</div>
                <div class="lbl">Siswa Tahfidz</div>
            </div>
            <div class="summary-box">
                <div class="val" style="color: #e65100;">{{ $chartData['tahfidz']['totalJuzEntries'] }}</div>
                <div class="lbl">Total Juz Dihafal</div>
            </div>
            <div class="summary-box">
                <div class="val" style="color: #6a1b9a;">
                    {{ !empty($chartData['tahfidz']['distribusiKualitas']) ? round(array_sum($chartData['tahfidz']['distribusiKualitas']) / max(count($chartData['tahfidz']['distribusiKualitas']), 1), 0) : 0 }}
                </div>
                <div class="lbl">Rata-rata Entry</div>
            </div>
        </div>

        {{-- Distribusi Kualitas --}}
        @if(!empty($chartData['tahfidz']['distribusiKualitas']))
        <div class="stat-card" style="margin-bottom: 16px;">
            <div class="stat-card-title">&#127942; Distribusi Kualitas Hafalan</div>
            <div class="grid-2">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="chartKualitasTahfidz"></canvas>
                </div>
                <div>
                    @foreach($chartData['tahfidz']['distribusiKualitas'] as $kualitas => $total)
                        <div class="mq-bar-item" style="border-left-color: {{ ['#0c8a5f', '#1565c0', '#e65100', '#ef5350'][array_search($kualitas, array_keys($chartData['tahfidz']['distribusiKualitas'])) % 4] }};">
                            <div class="mq-bar-label">{{ \App\Models\HafalanTahfidz::labelKualitas($kualitas) }}</div>
                            <div class="mq-bar-track">
                                @php $maxVal = max($chartData['tahfidz']['distribusiKualitas']) ?: 1; @endphp
                                <div class="mq-bar-fill {{ ['badge-green', 'badge-blue', 'badge-orange', ''][array_search($kualitas, array_keys($chartData['tahfidz']['distribusiKualitas'])) % 4] }}" style="width: {{ ($total / $maxVal) * 100 }}%;">
                                    {{ $total }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Per Kelas --}}
        @if(!empty($chartData['tahfidz']['perKelas']))
        <div class="stat-card">
            <div class="stat-card-title">&#128218; Per Kelas Tahfidz</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                @foreach($chartData['tahfidz']['perKelas'] as $kt)
                <div style="background: #f8faf8; border: 1px solid #e8f5e9; border-radius: 10px; padding: 16px;">
                    <div style="font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px;">{{ $kt['nama'] }}</div>
                    <div style="font-size: 11px; color: #888; margin-bottom: 12px;">Guru: {{ $kt['guru'] }} &middot; {{ $kt['totalSiswa'] }} siswa &middot; Rata-rata {{ $kt['avgJuz'] }} juz</div>

                    @if(!empty($kt['topSiswa']))
                        <div style="font-size: 10px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Top Siswa</div>
                        @foreach($kt['topSiswa'] as $ts)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e8f5e9; font-size: 12px;">
                            <span style="font-weight: 600;">{{ $ts['siswa']['nama'] }}</span>
                            <span style="background: #0c8a5f; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">{{ $ts['juzHafal'] }} juz</span>
                        </div>
                        @endforeach
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

@endif
@endsection
