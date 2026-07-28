<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Kelas {{ $kelas->nama }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; line-height: 1.5; color: #333; margin: 0; padding: 0; }
        
        .kop-surat { width: 100%; border-bottom: 3px double #1c1917; padding-bottom: 12px; margin-bottom: 15px; }
        .kop-surat td { vertical-align: middle; }
        .kop-logo { width: 50px; text-align: center; padding-right: 12px; }
        .kop-logo img { width: 45px; height: 45px; object-fit: contain; }
        .kop-text { text-align: center; }
        .kop-text .judul { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-text .nama-sekolah { font-size: 12px; font-weight: bold; }
        .kop-text .alamat { font-size: 9px; color: #666; }
        
        .header-rapor { text-align: center; margin-bottom: 15px; }
        .header-rapor h1 { font-size: 14px; margin: 0; }
        .header-rapor h2 { font-size: 11px; margin: 3px 0 0; font-weight: normal; color: #666; }
        
        .info { margin-bottom: 15px; font-size: 11px; }
        .info strong { color: #444; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f5f5f5; padding: 6px; text-align: center; font-size: 9px; border: 1px solid #ddd; }
        td { padding: 5px 6px; border: 1px solid #ddd; text-align: center; }
        td:first-child, td:nth-child(2) { text-align: left; }
        .predikat-a { background: #f0fdf4; color: #166534; font-weight: bold; }
        .predikat-b { background: #fefce8; color: #854d0e; font-weight: bold; }
        .predikat-c { background: #fef2f2; color: #991b1b; font-weight: bold; }
        .predikat-d { background: #fef3c7; color: #92400e; font-weight: bold; }
        .predikat-e { background: #f3f4f6; color: #6b7280; font-weight: bold; }
        
        .ttd-section { margin-top: 30px; width: 100%; }
        .ttd-section td { width: 50%; vertical-align: top; }
        .ttd-box { text-align: center; }
        .ttd-label { font-size: 10px; margin-bottom: 50px; }
        .ttd-nama { font-size: 11px; font-weight: bold; text-decoration: underline; }
        .ttd-nip { font-size: 9px; color: #666; }
        
        .footer { margin-top: 15px; text-align: right; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    @if($kop && ($kop->logo_base64 || $kop->nama_sekolah || $kop->judul))
    <table class="kop-surat">
        <tr>
            @if($kop->logo_base64)
            <td class="kop-logo"><img src="{{ $kop->logo_base64 }}" alt="Logo"></td>
            @endif
            <td class="kop-text">
                @if($kop->judul)<div class="judul">{{ $kop->judul }}</div>@endif
                @if($kop->sub_judul)<div style="font-size:10px;">{{ $kop->sub_judul }}</div>@endif
                @if($kop->nama_sekolah)<div class="nama-sekolah">{{ $kop->nama_sekolah }}</div>@endif
                @if($kop->alamat)<div class="alamat">{{ $kop->alamat }}</div>@endif
            </td>
        </tr>
    </table>
    @endif

    <div class="header-rapor">
        <h1>REKAP RAPOR KELAS</h1>
        <h2>Semester {{ ucfirst($semester->jenis) }} | Tahun Ajaran {{ $semester->tahun_ajaran }}</h2>
    </div>

    <div class="info">
        <strong>Kelas:</strong> {{ $kelas->nama }} | 
        <strong>Mata Pelajaran:</strong> {{ $kelas->mata_pelajaran }} | 
        <strong>Guru:</strong> {{ $kelas->guru->nama ?? '-' }} | 
        <strong>Jumlah Siswa:</strong> {{ count($dataRapor) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>NIS</th>
                <th>B</th>
                <th>C</th>
                <th>K</th>
                <th>Rata</th>
                <th>Predikat</th>
                <th>Hadir</th>
                <th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataRapor as $i => $r)
            @php 
                $p = substr($r['predikat'], 0, 1);
                $predClass = match($p) { 'A' => 'predikat-a', 'B' => 'predikat-b', 'C' => 'predikat-c', 'D' => 'predikat-d', default => 'predikat-e' };
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r['siswa']->nama }}</td>
                <td>{{ $r['siswa']->nis }}</td>
                <td>{{ $r['rata_b'] }}</td>
                <td>{{ $r['rata_c'] }}</td>
                <td>{{ $r['rata_k'] }}</td>
                <td><strong>{{ $r['rata_akhir'] }}</strong></td>
                <td class="{{ $predClass }}">{{ $p }}</td>
                <td>{{ $r['absensi']['Hadir'] }}</td>
                <td>{{ $r['absensi']['Alpha'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    @if($kop && $kop->kepala_sekolah)
    <table class="ttd-section">
        <tr>
            <td>
                <div class="ttd-box">
                    <div class="ttd-label">Guru Kelas</div>
                    <div class="ttd-nama">{{ $kelas->guru->nama ?? '(________________________)' }}</div>
                </div>
            </td>
            <td>
                <div class="ttd-box">
                    <div class="ttd-label">
                        {{ $kop->tanggal_cetak ? $kop->tanggal_cetak->format('d F Y') : now()->format('d F Y') }}<br>
                        Kepala Sekolah
                    </div>
                    <div class="ttd-nama">{{ $kop->kepala_sekolah }}</div>
                    @if($kop->nip_kepala_sekolah)
                    <div class="ttd-nip">NIP. {{ $kop->nip_kepala_sekolah }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer">
        Dicetak dari Sistem TartilPro | {{ now()->format('d F Y H:i') }}
    </div>
</body>
</html>
