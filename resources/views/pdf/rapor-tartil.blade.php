<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Rapor {{ $siswa->nama }}</title>
<style>
@page { margin: 10mm 10mm 30mm 10mm; size: A4 portrait; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #333; margin: 0; padding: 0; }

/* Kop */
.kop-table { width: 100%; border-bottom: 2px solid #2c3e50; margin-bottom: 8px; padding-bottom: 6px; }
.kop-table td { vertical-align: middle; padding: 0; }
.kop-logo { width: 45px; height: 45px; object-fit: contain; }
.kop-logo-box { width: 45px; height: 45px; border: 1px solid #ccc; text-align: center; line-height: 45px; font-size: 8px; color: #999; }
.kop-title { font-size: 12px; font-weight: 700; color: #2c3e50; text-transform: uppercase; text-align: center; }
.kop-subtitle { font-size: 9px; color: #555; text-align: center; }
.kop-school { font-size: 10px; font-weight: 700; color: #2c3e50; text-align: center; margin-top: 2px; }
.kop-meta { font-size: 7px; color: #777; text-align: center; margin-top: 1px; }

/* Info */
.info-box { width: 100%; border: 1px solid #ccc; border-radius: 3px; padding: 6px 8px; margin-bottom: 8px; background: #fafafa; }
.info-box td { font-size: 8px; padding: 1px 4px; vertical-align: top; }
.info-box .label { font-weight: 600; color: #444; width: 18%; }
.info-box .value { width: 32%; }

/* Section */
.section-title { font-size: 9px; font-weight: 700; color: #2c3e50; margin: 8px 0 4px; padding-bottom: 2px; border-bottom: 1px solid #2c3e50; }

/* Data Table */
.data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.data-table th { background: #2c3e50; color: #fff; font-size: 7px; font-weight: 600; padding: 3px 5px; border: 1px solid #2c3e50; text-align: left; }
.data-table td { padding: 2px 5px; font-size: 8px; border: 1px solid #ccc; vertical-align: middle; }
.data-table tr:nth-child(even) td { background: #f9f9f9; }
.tc { text-align: center; }

/* Badge */
.badge { display: inline-block; padding: 1px 4px; border-radius: 2px; font-size: 7px; font-weight: 600; }
.bb { background: #e8f5e9; color: #2e7d32; }
.bc { background: #fff8e1; color: #f57f17; }
.bk { background: #fbe9e7; color: #c62828; }

/* R2 */
.r2-table { width: 100%; border-collapse: collapse; margin: 4px 0 6px; }
.r2-table td { width: 33.33%; text-align: center; padding: 5px 3px; border: 1px solid #ddd; }
.r2-label { font-size: 6px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
.r2-value { font-size: 14px; font-weight: 700; }
.r2-desc { font-size: 6px; color: #999; }

/* Predikat */
.predikat { text-align: center; padding: 5px; border: 1px solid #2c3e50; border-radius: 3px; margin: 4px 0 8px; }
.predikat .pl { font-size: 7px; color: #666; text-transform: uppercase; }
.predikat .pv { font-size: 11px; font-weight: 700; color: #2c3e50; margin-top: 1px; }
.keputusan { font-size: 9px; text-align: center; padding: 8px 12px; margin: 8px 0; border: 1px solid #d4d4d4; border-radius: 4px; line-height: 1.6; color: #44403c; }
.keputusan strong { color: #171717; }

/* TTD */
.ttd-spacer { height: 15px; }
.ttd-table { width: 100%; border-collapse: collapse; }
.ttd-table td { width: 50%; text-align: center; vertical-align: bottom; font-size: 8px; padding: 0 15px; border: none; }
.ttd-jabatan { margin-bottom: 30px; line-height: 1.6; }
.ttd-nama { font-weight: 700; }
.ttd-nip { font-size: 7px; color: #666; margin-top: 2px; }

.catatan { font-size: 7px; color: #888; font-style: italic; text-align: center; margin-top: 6px; padding: 4px; background: #f5f5f5; border-radius: 2px; }
</style>
</head>
<body>

{{-- Kop Surat --}}
<table class="kop-table">
    <tr>
        <td style="width: 50px;">
            @if($kop->logo_base64)
            <img src="{{ $kop->logo_base64 }}" class="kop-logo">
            @else
            <div class="kop-logo-box">No Logo</div>
            @endif
        </td>
        <td>
            <div class="kop-title">{{ $kop->judul }}</div>
            <div class="kop-subtitle">{{ $kop->sub_judul }}</div>
            <div class="kop-school">{{ $kop->nama_sekolah }}</div>
            @if($kop->alamat || $kop->telepon)
            <div class="kop-meta">{{ $kop->alamat }}{{ $kop->alamat && $kop->telepon ? ' | Telp: ' : '' }}{{ $kop->telepon }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- Info Siswa --}}
<table class="info-box">
    <tr>
        <td class="label">Nama</td>
        <td class="value">: {{ $siswa->nama }}</td>
        <td class="label">NIS</td>
        <td class="value">: {{ $siswa->nis }}</td>
    </tr>
    <tr>
        <td class="label">Kelas Reguler</td>
        <td class="value">: {{ $siswa->kelasReguler->nama ?? '-' }}</td>
        <td class="label">Kelas Tartil</td>
        <td class="value">: {{ $kelas->nama }} ({{ $kelas->jenis }})</td>
    </tr>
    <tr>
        <td class="label">Guru Tartil</td>
        <td class="value">: {{ $kelas->guru->nama ?? '-' }}</td>
        <td class="label">Semester</td>
        <td class="value">: {{ $penilaian->semester->nama ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Tahun Ajaran</td>
        <td class="value">: {{ $kop->tahun_ajaran ?? '-' }}</td>
        <td class="label">Tanggal Cetak</td>
        <td class="value">: {{ $kop->tanggal_cetak?->format('d/m/Y') ?? date('d/m/Y') }}</td>
    </tr>
    @if($rekap['is_mutasi'])
    <tr>
        <td colspan="4" style="background: #FFF8E1; color: #856404; font-size: 7px; padding: 2px 4px; text-align: center;">
            Siswa mutasi masuk — Jurnal dihitung sejak {{ $rekap['tanggal_masuk_kelas_tartil']?->format('d/m/Y') ?? '-' }}
        </td>
    </tr>
    @endif
</table>

{{-- Section A --}}
<div class="section-title">A. Nilai Penilaian Per Indikator</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;" class="tc">No</th>
            <th style="width: 55%;">Indikator Penilaian</th>
            <th style="width: 20%;" class="tc">Nilai</th>
            <th style="width: 20%;" class="tc">Predikat</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($rekap['nilai_per_indikator'] as $indId => $nilaiData)
        <tr>
            <td class="tc">{{ $i++ }}</td>
            <td>{{ $nilaiData['nama'] }}</td>
            <td class="tc">
                @if($nilaiData['nilai'] !== null)<strong>{{ $nilaiData['nilai'] }}</strong>@else<span style="color:#aaa">-</span>@endif
            </td>
            <td class="tc">
                @if($nilaiData['nilai'] !== null)
                    @if($nilaiData['nilai'] >= 85)<span class="badge bb">Amat Baik</span>
                    @elseif($nilaiData['nilai'] >= 70)<span class="badge bb">Baik</span>
                    @elseif($nilaiData['nilai'] >= 60)<span class="badge bc">Cukup</span>
                    @else<span class="badge bk">Perlu Bimbingan</span>
                    @endif
                @else<span style="color:#aaa">-</span>@endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Section B --}}
<div class="section-title">B. Rekapitulasi Nilai (R2)</div>
<table class="r2-table">
    <tr>
        <td style="background:#e3f2fd">
            <div class="r2-label">R2 Harian</div>
            <div class="r2-value" style="color:#1565c0">{{ $rekap['r2_harian'] }}</div>
            <div class="r2-desc">Persentase B dari jurnal</div>
        </td>
        <td style="background:#e8f5e9">
            <div class="r2-label">R2 Penilaian</div>
            <div class="r2-value" style="color:#2e7d32">{{ $rekap['r2_penilaian'] }}</div>
            <div class="r2-desc">Rata-rata nilai indikator</div>
        </td>
        <td style="background:#fff3e0">
            <div class="r2-label">R2 Akhir</div>
            <div class="r2-value" style="color:#ef6c00">{{ $rekap['r2_akhir'] }}</div>
            <div class="r2-desc">(Harian + Penilaian) / 2</div>
        </td>
    </tr>
</table>

{{-- Predikat --}}
<div class="predikat">
    <div class="pl">Predikat Akhir</div>
    <div class="pv">
        @if($rekap['r2_akhir'] >= 85) Amat Baik
        @elseif($rekap['r2_akhir'] >= 70) Baik
        @elseif($rekap['r2_akhir'] >= 60) Cukup
        @else Perlu Bimbingan
        @endif
    </div>
</div>

{{-- Keputusan --}}
<div class="keputusan">
    @if($rekap['r2_akhir'] >= 80)
    Berdasarkan hasil evaluasi rapor, Ananda {{ $siswa->nama }} dinyatakan&nbsp;<strong>berhak melanjutkan pembelajaran pada jilid selanjutnya</strong>.
    @else
    Berdasarkan hasil evaluasi rapor, Ananda {{ $siswa->nama }} dinyatakan&nbsp;<strong>tetap melanjutkan pembelajaran pada jilid yang sama untuk pemantapan</strong>.
    @endif
</div>

{{-- TTD --}}
<div class="ttd-spacer"></div>
<table class="ttd-table">
    <tr>
        <td>
            <div class="ttd-jabatan">Guru Tartil<br>{{ $kelas->guru->nama ?? '........................' }}</div>
            <div class="ttd-nama">({{ $kelas->guru->nama ?? '........................' }})</div>
        </td>
        <td style="position: relative;">
            @if($kop->stempel_base64)
            <img src="{{ $kop->stempel_base64 }}" style="position: absolute; top: -10px; right: 10px; height: 70px; width: auto; opacity: 0.7; z-index: 1;">
            @endif
            <div class="ttd-jabatan">Kepala Sekolah<br>{{ $kop->kepala_sekolah ?? '' }}</div>
            @if($kop->ttd_base64)
            <img src="{{ $kop->ttd_base64 }}" style="height: 35px; width: auto; margin: 2px 0;">
            @else
            <div style="height: 20px;"></div>
            @endif
            <div class="ttd-nama"><u>{{ $kop->kepala_sekolah ?? '........................' }}</u></div>
            @if($kop->nip_kepala_sekolah && $kop->nip_kepala_sekolah !== '-')
            <div class="ttd-nip">NIP. {{ $kop->nip_kepala_sekolah }}</div>
            @endif
        </td>
    </tr>
</table>

@if($kop->catatan_kaki)
<div class="catatan">{{ $kop->catatan_kaki }}</div>
@endif

</body>
</html>
