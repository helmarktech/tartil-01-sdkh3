<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor {{ $siswa->nama }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        
        /* ═══ Kop Surat ═══ */
        .kop-surat { width: 100%; border-bottom: 3px double #1c1917; padding-bottom: 15px; margin-bottom: 20px; }
        .kop-surat td { vertical-align: middle; }
        .kop-logo { width: 60px; text-align: center; padding-right: 15px; }
        .kop-logo img { width: 55px; height: 55px; object-fit: contain; }
        .kop-text { text-align: center; }
        .kop-text .judul { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .kop-text .sub-judul { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .kop-text .nama-sekolah { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .kop-text .alamat { font-size: 10px; color: #666; }
        
        /* ═══ Header ═══ */
        .header-rapor { text-align: center; margin-bottom: 20px; }
        .header-rapor h1 { font-size: 16px; margin: 0; letter-spacing: 2px; }
        .header-rapor h2 { font-size: 12px; margin: 5px 0 0; font-weight: normal; color: #666; }
        
        /* ═══ Info Siswa ═══ */
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 25%; color: #444; }
        
        .section-title { font-size: 13px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin: 20px 0 10px; }
        
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th { background: #f5f5f5; padding: 8px; text-align: left; font-size: 11px; border: 1px solid #ddd; }
        table.data td { padding: 8px; border: 1px solid #ddd; }
        
        .summary-box { background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        
        .nilai-grid { display: inline-block; width: 100%; }
        .nilai-item { float: left; width: 25%; text-align: center; padding: 10px; box-sizing: border-box; }
        .nilai-value { font-size: 28px; font-weight: bold; }
        .nilai-label { font-size: 11px; color: #888; margin-top: 5px; }
        .nilai-item:nth-child(1) .nilai-value { color: #166534; }
        .nilai-item:nth-child(2) .nilai-value { color: #854d0e; }
        .nilai-item:nth-child(3) .nilai-value { color: #991b1b; }
        .nilai-item:nth-child(4) .nilai-value { color: #1c1917; }
        .clear { clear: both; }
        
        .predikat { font-size: 16px; font-weight: bold; text-align: center; padding: 10px; background: #f5f5f5; border-radius: 8px; margin-top: 10px; }
        .keputusan { font-size: 12px; text-align: center; padding: 12px 16px; margin-top: 12px; border: 1px solid #e7e5e4; border-radius: 8px; line-height: 1.7; color: #44403c; }
        .keputusan strong { color: #171717; }
        
        /* ═══ Tanda Tangan ═══ */
        .ttd-section { margin-top: 40px; width: 100%; }
        .ttd-section td { vertical-align: top; width: 50%; }
        .ttd-box { text-align: center; }
        .ttd-label { font-size: 11px; margin-bottom: 60px; }
        .ttd-stempel { position: absolute; margin-left: -40px; margin-top: -30px; }
        .ttd-stempel img { width: 80px; height: 80px; object-fit: contain; opacity: 0.7; }
        .ttd-nama { font-size: 12px; font-weight: bold; text-decoration: underline; }
        .ttd-nip { font-size: 10px; color: #666; }
        
        .catatan-kaki { margin-top: 20px; padding: 10px; background: #fafafa; border: 1px dashed #ddd; font-size: 10px; color: #666; }
        
        .footer { margin-top: 20px; text-align: right; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    @if($kop && ($kop->logo_base64 || $kop->nama_sekolah || $kop->judul))
    <table class="kop-surat">
        <tr>
            @if($kop->logo_base64)
            <td class="kop-logo">
                <img src="{{ $kop->logo_base64 }}" alt="Logo">
            </td>
            @endif
            <td class="kop-text">
                @if($kop->judul)<div class="judul">{{ $kop->judul }}</div>@endif
                @if($kop->sub_judul)<div class="sub-judul">{{ $kop->sub_judul }}</div>@endif
                @if($kop->nama_sekolah)<div class="nama-sekolah">{{ $kop->nama_sekolah }}</div>@endif
                @if($kop->alamat || $kop->telepon || $kop->email)
                <div class="alamat">
                    {{ $kop->alamat }}{{ $kop->alamat && ($kop->telepon || $kop->email) ? ' | ' : '' }}
                    {{ $kop->telepon }}{{ $kop->telepon && $kop->email ? ' | ' : '' }}{{ $kop->email }}
                </div>
                @endif
            </td>
        </tr>
    </table>
    @endif

    {{-- Header Rapor --}}
    <div class="header-rapor">
        <h1>RAPOR HASIL BELAJAR</h1>
        <h2>Semester {{ ucfirst($semester->jenis) }} | Tahun Ajaran {{ $semester->tahun_ajaran }}</h2>
    </div>

    {{-- Info Siswa --}}
    <table class="info-table">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: {{ $siswa->nama }}</td>
            <td class="label">NIS</td>
            <td>: {{ $siswa->nis }}</td>
        </tr>
        <tr>
            <td class="label">Kelas Reguler</td>
            <td>: {{ $siswa->kelasReguler->nama ?? '-' }}</td>
            <td class="label">Kelas Tartil</td>
            <td>: {{ $kelas->nama }}</td>
        </tr>
        <tr>
            <td class="label">Guru</td>
            <td>: {{ $kelas->guru->nama ?? '-' }}</td>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $kop->tahun_ajaran ?? $semester->tahun_ajaran }}</td>
        </tr>
    </table>

    {{-- Nilai --}}
    <div class="section-title">Ringkasan Penilaian</div>
    <div class="nilai-grid">
        <div class="nilai-item">
            <div class="nilai-value">{{ $rapor['rata_b'] }}</div>
            <div class="nilai-label">Bacaan (B)</div>
        </div>
        <div class="nilai-item">
            <div class="nilai-value">{{ $rapor['rata_c'] }}</div>
            <div class="nilai-label">Catatan (C)</div>
        </div>
        <div class="nilai-item">
            <div class="nilai-value">{{ $rapor['rata_k'] }}</div>
            <div class="nilai-label">Keterampilan (K)</div>
        </div>
        <div class="nilai-item">
            <div class="nilai-value">{{ $rapor['rata_akhir'] }}</div>
            <div class="nilai-label">Nilai Akhir</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="predikat">Predikat: {{ $rapor['predikat'] }}</div>

    {{-- Keputusan --}}
    <div class="keputusan">
        @if($rapor['rata_akhir'] >= 80)
        Berdasarkan hasil evaluasi rapor, Ananda {{ $siswa->nama }} dinyatakan <strong>berhak melanjutkan pembelajaran pada jilid selanjutnya</strong>.
        @else
        Berdasarkan hasil evaluasi rapor, Ananda {{ $siswa->nama }} dinyatakan <strong>tetap melanjutkan pembelajaran pada jilid yang sama untuk pemantapan</strong>.
        @endif
    </div>

    {{-- Detail Penilaian --}}
    <div class="section-title">Detail Penilaian ({{ $rapor['jumlah_penilaian'] }} x pertemuan)</div>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Surat</th>
                <th>Ayat</th>
                <th>B</th>
                <th>C</th>
                <th>K</th>
                <th>Rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rapor['details'] as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->jurnal->tanggal->format('d/m/Y') }}</td>
                <td>{{ $d->jurnal->surat }}</td>
                <td>{{ $d->jurnal->ayat }}</td>
                <td>{{ $d->nilai_b }}</td>
                <td>{{ $d->nilai_c }}</td>
                <td>{{ $d->nilai_k }}</td>
                <td>{{ $d->nilai_akhir }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Kehadiran --}}
    <div class="section-title">Kehadiran</div>
    <div class="summary-box">
        <table class="info-table">
            <tr>
                <td class="label">Hadir</td><td>: {{ $rapor['absensi']['Hadir'] }} kali</td>
                <td class="label">Sakit</td><td>: {{ $rapor['absensi']['Sakit'] }} kali</td>
            </tr>
            <tr>
                <td class="label">Izin</td><td>: {{ $rapor['absensi']['Izin'] }} kali</td>
                <td class="label">Alpha</td><td>: {{ $rapor['absensi']['Alpha'] }} kali</td>
            </tr>
            <tr>
                <td class="label">Total Pertemuan</td><td>: {{ $rapor['total_pertemuan'] }} kali</td>
                <td class="label">Persentase Hadir</td><td>: {{ $rapor['persentase_hadir'] }}</td>
            </tr>
        </table>
    </div>

    {{-- Tanda Tangan --}}
    @if($kop && ($kop->kepala_sekolah || $kop->ttd_base64))
    <table class="ttd-section">
        <tr>
            <td></td>
            <td>
                <div class="ttd-box">
                    <div class="ttd-label">
                        {{ $kop->tanggal_cetak ? $kop->tanggal_cetak->format('d F Y') : now()->format('d F Y') }}<br>
                        Kepala Sekolah
                    </div>
                    @if($kop->ttd_base64)
                    <div class="ttd-stempel">
                        @if($kop->stempel_base64)
                        <img src="{{ $kop->stempel_base64 }}" alt="Stempel">
                        @endif
                        <img src="{{ $kop->ttd_base64 }}" alt="TTD" style="width:100px;height:50px;">
                    </div>
                    @endif
                    <div style="margin-top: {{ $kop->ttd_base64 ? '20px' : '60px' }};">
                        <div class="ttd-nama">{{ $kop->kepala_sekolah ?? '(________________________)' }}</div>
                        @if($kop->nip_kepala_sekolah)
                        <div class="ttd-nip">NIP. {{ $kop->nip_kepala_sekolah }}</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- Catatan Kaki --}}
    @if($kop && $kop->catatan_kaki)
    <div class="catatan-kaki">{{ $kop->catatan_kaki }}</div>
    @endif

    <div class="footer">
        Dicetak dari Sistem TartilPro | {{ now()->format('d F Y H:i') }}
    </div>
</body>
</html>
