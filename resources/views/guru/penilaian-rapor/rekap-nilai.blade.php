@extends('layouts.admin')
@section('title', 'Rekap Nilai Rapor')

@section('content')
<style>
.rekap-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.rekap-table {
    font-size: 13px;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: 700px;
}
.rekap-table th {
    padding: 12px 8px;
    text-align: center;
    background: var(--accent);
    color: #fff;
    border-bottom: 2px solid var(--accent-hover);
    font-weight: 600;
    font-size: 11px;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.rekap-table th:first-child {
    text-align: left;
    padding-left: 14px;
    border-radius: 8px 0 0 0;
}
.rekap-table th:last-child { border-radius: 0 8px 0 0; }
.rekap-table th.nilai-col { min-width: 55px; }
.rekap-table th.r2-col {
    background: linear-gradient(180deg, #5A4F45 0%, var(--accent-hover) 100%);
    font-size: 10px;
    min-width: 58px;
}
.rekap-table th.akhir-col {
    background: linear-gradient(180deg, #4A6D4A 0%, #5A7D5A 100%);
    font-size: 10px;
    min-width: 62px;
}
.rekap-table td {
    padding: 10px 8px;
    text-align: center;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.rekap-table td:first-child {
    text-align: left;
    padding-left: 14px;
    font-weight: 500;
    white-space: nowrap;
}
.rekap-table tbody tr:hover td { background: rgba(107,94,81,0.04); }
.rekap-table tbody tr:last-child td:first-child { border-radius: 0 0 0 8px; }
.rekap-table tbody tr:last-child td:last-child { border-radius: 0 0 8px 0; }

.nilai-angka {
    font-size: 14px;
    font-weight: 600;
    min-width: 24px;
    display: inline-block;
}
.nilai-angka.na { color: #CCC; font-weight: 400; font-size: 13px; }

.r2-cell {
    background: var(--bg-hover);
    font-weight: 700;
}
.r2-cell .angka {
    font-size: 15px;
}
.akhir-cell {
    background: #E9F0E9;
    font-weight: 700;
}
.akhir-cell .angka {
    font-size: 16px;
}
.r2-bar {
    width: 36px;
    height: 3px;
    background: #DDD;
    border-radius: 2px;
    margin: 3px auto 0;
    overflow: hidden;
}
.r2-fill {
    height: 100%;
    border-radius: 2px;
}

.warna-a { color: #5A7D5A; }
.warna-b { color: #8B9A4A; }
.warna-c { color: #C4953A; }
.warna-k { color: #A85A52; }

.ringkasan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 16px;
}
.ringkasan-item {
    padding: 14px 10px;
    border-radius: 12px;
    text-align: center;
}
.ringkasan-item .angka { font-size: 22px; font-weight: 700; }
.ringkasan-item .label { font-size: 11px; margin-top: 4px; }

@media (max-width: 768px) {
    .rekap-table th { font-size: 10px; padding: 8px 4px; }
    .rekap-table td { padding: 8px 4px; font-size: 12px; }
    .rekap-table th:first-child, .rekap-table td:first-child { padding-left: 8px; }
    .nilai-angka { font-size: 12px; }
    .r2-cell .angka { font-size: 13px; }
    .akhir-cell .angka { font-size: 14px; }
}
</style>

<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Rekap Nilai Rapor</h1>
            <p class="page-subtitle">Nilai akhir = (R2 Rapor + R2 Harian) / 2</p>
        </div>
    </div>

    {{-- Filter Kelas + Semester — LONG TERM: support filter semester historis --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Pilih Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="font-size: 13px;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->siswas_count }} siswa)
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 220px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Semester (opsional — untuk data historis)</label>
                <select name="semester_id" class="form-input" onchange="this.form.submit()" style="font-size: 13px;">
                    <option value="">-- Semester Terbaru --</option>
                    @foreach($semesterList as $sem)
                    <option value="{{ $sem->id }}" {{ ($semesterId ?? '') == $sem->id ? 'selected' : '' }}>
                        {{ $sem->nama }} {{ $sem->status == 'ditutup' ? '(ditutup)' : ($sem->is_aktif ? '[AKTIF]' : '') }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($semesterFilter)
            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">
                <span class="badge-subject">{{ $semesterFilter->nama }}</span>
            </div>
            @endif
        </form>
    </div>

    @if($kelasAktif && $semesterPenilaian)
    {{-- Info --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <div>
            <strong style="color: var(--text-primary); font-size: 15px;">{{ $kelasAktif->nama }}</strong>
            <span class="badge-subject" style="margin-left: 8px;">{{ $kelasAktif->jenis }}</span>
        </div>
        <div style="font-size: 12px; color: var(--text-muted);">
            {{ $semesterPenilaian->semester->nama ?? '-' }} | {{ $semesterPenilaian->keterangan ?? '' }}
        </div>
    </div>

    @if($siswas->isNotEmpty() && $indikators->isNotEmpty())
    {{-- Tabel Rekap Numeric --}}
    <div class="card-tartil rekap-scroll" style="padding: 0; overflow: hidden;">
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="min-width: 150px;">NAMA SISWA</th>
                    @foreach($indikators as $ind)
                    <th class="nilai-col" title="{{ $ind->nama_indikator }}">
                        {{ $loop->iteration }}
                    </th>
                    @endforeach
                    <th class="r2-col">R2<br>RAPOR</th>
                    <th class="r2-col">R2<br>HARIAN</th>
                    <th class="akhir-col">NILAI<br>AKHIR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswas as $i => $siswa)
                @php
                    $totalNilai = 0;
                    $jumlahIndikator = $indikators->count();
                @endphp
                <tr>
                    <td>
                        <span style="color: var(--text-muted); font-size: 11px; margin-right: 6px;">{{ $i + 1 }}.</span>
                        {{ $siswa->nama }}
                    </td>
                    @foreach($indikators as $ind)
                    @php
                        $n = $nilaiMap[$siswa->id][$ind->id] ?? ['nilai_angka' => null];
                        $angka = $n['nilai_angka'];
                        // Nilai kosong/null = 0 (K), tetap dihitung
                        $totalNilai += $angka ?? 0;
                        $warna = match(true) {
                            $angka === null => 'na',
                            $angka >= 85 => 'warna-a',
                            $angka >= 70 => 'warna-b',
                            $angka >= 60 => 'warna-c',
                            default => 'warna-k',
                        };
                    @endphp
                    <td>
                        <span class="nilai-angka {{ $angka === null ? 'na' : $warna }}">
                            {{ $angka !== null ? $angka : '0' }}
                        </span>
                    </td>
                    @endforeach
                    @php
                        $r2Rapor = $jumlahIndikator > 0 ? round($totalNilai / $jumlahIndikator) : 0;
                        $r2Harian = $rataHarianMap[$siswa->id] ?? 0;
                        $nilaiAkhir = round(($r2Rapor + $r2Harian) / 2);
                    @endphp
                    {{-- R2 Rapor --}}
                    <td class="r2-cell">
                        <span class="angka {{ $r2Rapor >= 85 ? 'warna-a' : ($r2Rapor >= 70 ? 'warna-b' : ($r2Rapor >= 60 ? 'warna-c' : 'warna-k')) }}">
                            {{ $r2Rapor }}
                        </span>
                        <div class="r2-bar">
                            <div class="r2-fill" style="width: {{ min($r2Rapor, 100) }}%; background: {{ $r2Rapor >= 85 ? '#5A7D5A' : ($r2Rapor >= 70 ? '#8B9A4A' : ($r2Rapor >= 60 ? '#C4953A' : '#A85A52')) }};"></div>
                        </div>
                    </td>
                    {{-- R2 Harian --}}
                    <td class="r2-cell">
                        <span class="angka {{ $r2Harian >= 85 ? 'warna-a' : ($r2Harian >= 70 ? 'warna-b' : ($r2Harian >= 60 ? 'warna-c' : 'warna-k')) }}">
                            {{ $r2Harian > 0 ? round($r2Harian) : '-' }}
                        </span>
                        <div class="r2-bar">
                            <div class="r2-fill" style="width: {{ min($r2Harian, 100) }}%; background: {{ $r2Harian >= 85 ? '#5A7D5A' : ($r2Harian >= 70 ? '#8B9A4A' : ($r2Harian >= 60 ? '#C4953A' : '#A85A52')) }};"></div>
                        </div>
                    </td>
                    {{-- Nilai Akhir --}}
                    <td class="akhir-cell">
                        <span class="angka {{ $nilaiAkhir >= 85 ? 'warna-a' : ($nilaiAkhir >= 70 ? 'warna-b' : ($nilaiAkhir >= 60 ? 'warna-c' : 'warna-k')) }}">
                            {{ $nilaiAkhir }}
                        </span>
                        <div class="r2-bar">
                            <div class="r2-fill" style="width: {{ min($nilaiAkhir, 100) }}%; background: {{ $nilaiAkhir >= 85 ? '#5A7D5A' : ($nilaiAkhir >= 70 ? '#8B9A4A' : ($nilaiAkhir >= 60 ? '#C4953A' : '#A85A52')) }};"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Keterangan indikator --}}
    <div style="margin-top: 12px; font-size: 11px; color: var(--text-muted);">
        Nomor indikator:
        @foreach($indikators as $ind)
        <span style="margin-right: 12px;"><strong>{{ $loop->iteration }}.</strong> {{ $ind->nama_indikator }}</span>
        @endforeach
    </div>

    {{-- Ringkasan Kelas --}}
    @php
        $jumlahA = 0; $jumlahB = 0; $jumlahC = 0; $jumlahK = 0; $totalNilaiKelas = 0; $countTotal = 0;
        foreach ($siswas as $siswa) {
            foreach ($indikators as $ind) {
                $angka = $nilaiMap[$siswa->id][$ind->id]['nilai_angka'] ?? null;
                if ($angka !== null) {
                    $totalNilaiKelas += $angka; $countTotal++;
                    if ($angka >= 85) $jumlahA++;
                    elseif ($angka >= 70) $jumlahB++;
                    elseif ($angka >= 60) $jumlahC++;
                    else $jumlahK++;
                }
            }
        }
        $rataKelas = $countTotal > 0 ? round($totalNilaiKelas / $countTotal) : 0;
    @endphp
    <div class="ringkasan-grid">
        <div class="ringkasan-item" style="background: #E9F0E9;">
            <div class="angka warna-a">A: {{ $jumlahA }}</div>
            <div class="label warna-a">≥ 85 (Sangat Baik)</div>
        </div>
        <div class="ringkasan-item" style="background: #FFF8E1;">
            <div class="angka warna-b">B: {{ $jumlahB }}</div>
            <div class="label warna-b">70-84 (Baik)</div>
        </div>
        <div class="ringkasan-item" style="background: #FFF3E0;">
            <div class="angka warna-c">C: {{ $jumlahC }}</div>
            <div class="label warna-c">60-69 (Cukup)</div>
        </div>
        <div class="ringkasan-item" style="background: #FFEBEE;">
            <div class="angka warna-k">K: {{ $jumlahK }}</div>
            <div class="label warna-k">&lt; 60 (Kurang)</div>
        </div>
        <div class="ringkasan-item" style="background: var(--bg-elevated);">
            <div class="angka" style="color: var(--text-primary);">{{ $rataKelas }}</div>
            <div class="label" style="color: var(--text-muted);">R2 Kelas</div>
        </div>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Belum ada data nilai untuk kelas ini.</div>
    </div>
    @endif
    @elseif($kelasId)
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Tidak ada penilaian rapor aktif untuk semester ini.</div>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Pilih kelas untuk melihat rekap nilai rapor.</div>
    </div>
    @endif
</div>
@endsection
