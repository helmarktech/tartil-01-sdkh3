@extends('layouts.admin')
@section('title', 'Jurnal Bulanan')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title-display">Jurnal Bulanan</h1>
        <p class="page-subtitle">Rekap jurnal pertemuan per bulan dengan hasil belajar</p>
    </div>

    {{-- Filter --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 20px;">
        <form method="GET" class="form-inline" style="gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Semester</label>
                <select name="semester_id" class="form-input" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">-- Pilih Semester --</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Bulan (opsional)</label>
                <select name="bulan" class="form-input" onchange="this.form.submit()" style="min-width: 160px;">
                    <option value="">-- Semua Bulan --</option>
                    @php
                        $bulanList = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                      '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                    @endphp
                    @foreach($bulanList as $bln => $nama)
                    <option value="{{ $bln }}" {{ $bulan == $bln ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($warningBulan ?? false)
    <div class="card-tartil" style="padding: 12px 16px; margin-bottom: 16px; background: #FFF8E1; border-left: 4px solid #B8860B;">
        <div style="font-size: 13px; color: #6B5E51;">
            <strong>Perhatian:</strong> {{ $warningBulan }}
        </div>
    </div>
    @endif

    @if($semesterId && $kelasId && $kelasAktif)
    @php
        $bulanList = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                      '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        if ($bulan && isset($bulanList[$bulan])) {
            $bulanLabel = $bulanList[$bulan];
        } else {
            $bulanLabel = 'Semua Bulan';
        }
    @endphp

    {{-- Info Header --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="color: var(--text-primary);">{{ $kelasAktif->nama }}</strong>
                <span style="color: var(--text-muted); margin-left: 8px;">| {{ $kelasAktif->jenis ?? '-' }}</span>
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                Mu'allim: <strong style="color: var(--text-primary);">{{ $kelasAktif->guru->nama ?? '-' }}</strong>
                <span style="margin-left: 16px;">{{ $bulanLabel }}</span>
            </div>
        </div>
    </div>

    @if(count($jurnalRows) > 0)
    {{-- Tabel Jurnal Bulanan --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 12px;">
                <thead>
                    <tr style="background: var(--bg-body);">
                        <th rowspan="2" style="width: 36px; text-align: center; vertical-align: middle;">NO</th>
                        <th rowspan="2" style="width: 70px; text-align: center; vertical-align: middle;">HARI</th>
                        <th rowspan="2" style="width: 50px; text-align: center; vertical-align: middle;">TGL</th>
                        <th rowspan="2" style="width: 50px; text-align: center; vertical-align: middle;">TM</th>
                        <th rowspan="2" style="min-width: 120px; vertical-align: middle;">HAL</th>
                        <th rowspan="2" style="min-width: 200px; vertical-align: middle;">MATERI PEMBELAJARAN</th>
                        <th colspan="3" style="text-align: center;">HASIL BELAJAR</th>
                        <th rowspan="2" style="width: 45px; text-align: center; vertical-align: middle;">%</th>
                        <th rowspan="2" style="min-width: 100px; vertical-align: middle;">RENCANA</th>
                        <th rowspan="2" style="min-width: 100px; vertical-align: middle;">CATATAN</th>
                    </tr>
                    <tr style="background: var(--bg-body);">
                        <th style="width: 36px; text-align: center; color: #5A7D5A;">B</th>
                        <th style="width: 36px; text-align: center; color: #B8860B;">C</th>
                        <th style="width: 36px; text-align: center; color: #C62828;">K</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnalRows as $i => $row)
                    <tr>
                        <td style="text-align: center; color: var(--text-muted);">{{ $i + 1 }}</td>
                        <td style="text-align: center; font-weight: 500;">{{ $row['hari'] }}</td>
                        <td style="text-align: center;">{{ $row['tgl_short'] }}</td>
                        <td style="text-align: center;"><span class="badge-subject" style="font-size: 10px;">{{ $row['pertemuan_ke'] }}</span></td>
                        <td style="font-size: 11px;">{{ $row['hal'] }}</td>
                        <td style="font-weight: 500;">{{ $row['materi'] }}</td>
                        <td style="text-align: center; color: #5A7D5A; font-weight: 600;">{{ $row['b'] }}</td>
                        <td style="text-align: center; color: #B8860B; font-weight: 600;">{{ $row['c'] }}</td>
                        <td style="text-align: center; color: #C62828; font-weight: 600;">{{ $row['k'] }}</td>
                        <td style="text-align: center; font-weight: 700;">
                            <span class="{{ $row['persen'] >= 80 ? 'badge-success' : ($row['persen'] >= 60 ? 'badge-warning' : 'badge-error') }}" style="font-size: 10px;">{{ $row['persen'] }}%</span>
                        </td>
                        <td style="font-size: 11px; color: var(--text-muted);">{{ $row['rencana'] }}</td>
                        <td style="font-size: 11px; color: var(--text-muted);">{{ $row['catatan'] }}</td>
                    </tr>
                    @endforeach

                    {{-- Baris Total --}}
                    @php
                        $sumB = array_sum(array_column($jurnalRows, 'b'));
                        $sumC = array_sum(array_column($jurnalRows, 'c'));
                        $sumK = array_sum(array_column($jurnalRows, 'k'));
                        $totalAll = $sumB + $sumC + $sumK;
                        $avgPersen = count($jurnalRows) > 0 ? round(array_sum(array_column($jurnalRows, 'persen')) / count($jurnalRows)) : 0;
                    @endphp
                    <tr style="background: #ebe5db; font-weight: 700;">
                        <td colspan="6" style="text-align: right; padding-right: 14px; font-size: 11px;">TOTAL</td>
                        <td style="text-align: center; color: #5A7D5A;">{{ $sumB }}</td>
                        <td style="text-align: center; color: #B8860B;">{{ $sumC }}</td>
                        <td style="text-align: center; color: #C62828;">{{ $sumK }}</td>
                        <td style="text-align: center;">{{ $avgPersen }}%</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Siswa --}}
    <div style="margin-top: 12px; font-size: 12px; color: var(--text-muted);">
        Total siswa: {{ $totalSiswa }} | Total pertemuan: {{ count($jurnalRows) }}
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Belum ada jurnal untuk kelas dan bulan ini.</div>
    </div>
    @endif
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Pilih semester, kelas, dan bulan untuk melihat jurnal bulanan.</div>
    </div>
    @endif
</div>
@endsection
