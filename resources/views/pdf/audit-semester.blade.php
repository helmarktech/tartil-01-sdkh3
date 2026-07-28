<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Audit {{ $semester->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 9px; color: #333; line-height: 1.5; }
        .kop { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #0c8a5f; padding-bottom: 10px; }
        .kop h1 { font-size: 14px; margin-bottom: 4px; color: #0c8a5f; }
        .kop p { font-size: 9px; color: #666; }
        .kop .periode { font-size: 10px; font-weight: 600; margin-top: 6px; }
        h2 { font-size: 11px; margin: 14px 0 6px; color: #0c8a5f; border-bottom: 1px solid #0c8a5f; padding-bottom: 3px; }
        h3 { font-size: 10px; margin: 10px 0 4px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; text-align: left; font-size: 8px; }
        th { background: #f0f0f0; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-box { margin: 10px 0; padding: 8px; background: #f8f9fa; border-left: 3px solid #0c8a5f; }
        .summary-grid { display: table; width: 100%; margin-bottom: 8px; }
        .summary-grid > div { display: table-cell; width: 25%; padding: 6px; text-align: center; background: #f0f0f0; border: 1px solid #ddd; }
        .summary-grid .val { font-size: 14px; font-weight: bold; color: #0c8a5f; }
        .summary-grid .lbl { font-size: 8px; color: #666; }
        .badge { padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-L { background: #d4edda; color: #155724; }
        .badge-TL { background: #f8d7da; color: #721c24; }
        .badge-T { background: #fff3cd; color: #856404; }
        .section-header { background: #e8f5e9; padding: 4px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; border-left: 3px solid #0c8a5f; }
        .kelas-header { background: #e3f2fd; padding: 3px 6px; font-weight: bold; font-size: 8px; margin: 4px 0; }
        .footer { margin-top: 12px; font-size: 7px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
        .page-break { page-break-after: always; }
        .indikator-tag { display: inline-block; background: #e8f5e9; padding: 1px 4px; border-radius: 3px; font-size: 7px; margin-right: 3px; }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div class="kop">
        <h1>{{ $kop->judul ?? 'LAPORAN AUDIT SEMESTER' }}</h1>
        <p>{{ $kop->sub_judul ?? 'Program Pembelajaran Al-Qur\'an' }}</p>
        <p><strong>{{ $kop->nama_sekolah ?? 'SD Khadijah 3 Surabaya' }}</strong></p>
        <div class="periode">{{ $semester->nama }} &middot; {{ $semester->tahun_ajaran }} &middot; {{ $semester->tanggal_mulai?->format('d/m/Y') ?? '-' }} - {{ $semester->tanggal_selesai?->format('d/m/Y') ?? '-' }}</div>
    </div>

    {{-- STATUS DATA --}}
    <div class="summary-box">
        <strong>Status Data:</strong> {{ $semester->status === 'ditutup' ? 'TERKUNCI (Snapshot - tidak berubah)' : 'REAL-TIME (Aktif - bisa berubah)' }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- RINGKASAN --}}
    <div class="summary-grid">
        <div><div class="val">{{ $rekapData['totalSiswa'] ?? 0 }}</div><div class="lbl">Total Siswa</div></div>
        <div><div class="val">{{ $rekapData['rataR2Akhir'] ?? 0 }}</div><div class="lbl">R2 Akhir Rata-rata</div></div>
        <div><div class="val">{{ $rekapData['rataMengaji'] ?? 0 }}</div><div class="lbl">Mengaji Rata-rata (hari)</div></div>
        <div><div class="val">{{ count($rekapData['munaqosyahList'] ?? []) }}</div><div class="lbl">Munaqosyah</div></div>
    </div>

    {{-- MUNAQOSYAH --}}
    @if(!empty($rekapData['munaqosyahList']))
        <h2>&#127942; MUNAQOSYAH</h2>
        @foreach($rekapData['munaqosyahList'] as $mq)
            <div class="section-header">{{ $mq['ujian']->nama }} &middot; {{ $mq['ujian']->tingkat }} &middot; {{ $mq['ujian']->tanggal_ujian?->format('d/m/Y') ?? '-' }}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">No</th>
                        <th style="width:10%">NIS</th>
                        <th style="width:25%">Nama</th>
                        <th style="width:8%" class="text-center">Nilai</th>
                        <th style="width:10%" class="text-center">Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mq['peserta'] as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $p['siswa']->nis ?? '-' }}</td>
                            <td>{{ $p['siswa']->nama ?? '-' }}</td>
                            <td class="text-center">{{ $p['nilai'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge badge-{{ $p['status'] }}">{{ $p['status'] === 'L' ? 'Lulus' : ($p['status'] === 'TL' ? 'Tidak Lulus' : 'Terdaftar') }}</span></td>
                            <td>{{ $p['catatan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada peserta</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div style="text-align: right; font-size: 8px; margin-bottom: 8px;">
                <strong>Ringkasan:</strong> {{ $mq['total'] }} peserta &middot; {{ $mq['lulus'] }} lulus &middot; {{ $mq['tidakLulus'] }} tidak lulus &middot; Rata-rata nilai: {{ $mq['rataNilai'] }}
            </div>
        @endforeach
    @endif

    {{-- PENILAIAN RAPOR --}}
    @if(!empty($rekapData['penilaianList']))
        <h2>&#128221; PENILAIAN RAPOR</h2>
        @foreach($rekapData['penilaianList'] as $pn)
            <div class="section-header">{{ $pn['penilaian']->nama }} &middot; {{ $pn['totalSiswa'] }} siswa total</div>
            @foreach($pn['perKelasTartil'] ?? [] as $pkt)
                <div class="kelas-header" style="background: #e8f5e9; color: #0c8a5f;">Kelas {{ $pkt['jenisKelas'] }} &mdash; {{ $pkt['totalSiswa'] }} siswa</div>
                @if(!empty($pkt['indikatorNames']))
                    <div style="font-size: 8px; margin-bottom: 4px;">
                        Indikator: @foreach($pkt['indikatorNames'] as $ind)<span class="indikator-tag">{{ $ind }}</span>@endforeach
                    </div>
                @endif
                <table>
                    <thead>
                        <tr>
                            <th style="width:4%">No</th>
                            <th style="width:10%">NIS</th>
                            <th style="width:22%">Nama</th>
                            <th style="width:8%" class="text-center">Rata-rata</th>
                            @if(!empty($pkt['nilaiPerSiswa'][0]['detail']))
                                @foreach($pkt['nilaiPerSiswa'][0]['detail'] as $d)
                                    <th style="width:8%" class="text-center">{{ $d['indikator'] }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pkt['nilaiPerSiswa'] as $i => $ns)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $ns['siswa']->nis ?? '-' }}</td>
                                <td>{{ $ns['siswa']->nama ?? '-' }}</td>
                                <td class="text-center"><strong>{{ $ns['nilaiRata'] }}</strong></td>
                                @foreach($ns['detail'] as $d)
                                    <td class="text-center">{{ $d['nilai'] }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ 4 + count($pkt['nilaiPerSiswa'][0]['detail'] ?? []) }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        @endforeach
    @endif

    <div class="page-break"></div>

    {{-- SISWA PER KELAS REGULER --}}
    @if(!empty($rekapData['siswaPerKelasReguler']))
        <h2>&#128101; SISWA PER KELAS REGULER</h2>
        @foreach($rekapData['siswaPerKelasReguler'] as $kelasRegulerNama => $siswaKelas)
            <div class="kelas-header">{{ $kelasRegulerNama }} &mdash; {{ count($siswaKelas) }} siswa</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">No</th>
                        <th style="width:8%">NIS</th>
                        <th style="width:18%">Nama</th>
                        <th style="width:10%">Kelas Tartil</th>
                        <th style="width:7%" class="text-center">R2 H</th>
                        <th style="width:7%" class="text-center">R2 P</th>
                        <th style="width:7%" class="text-center">R2 A</th>
                        <th style="width:7%" class="text-center">Mengaji</th>
                        <th style="width:7%" class="text-center">B/C/K</th>
                        <th style="width:10%">Munaqosyah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswaKelas as $i => $d)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $d['siswa']->nis }}</td>
                            <td>{{ $d['siswa']->nama }}</td>
                            <td>{{ $d['kelasTartil'] }}</td>
                            <td class="text-center">{{ $d['r2Harian'] }}</td>
                            <td class="text-center">{{ $d['r2Penilaian'] }}</td>
                            <td class="text-center"><strong>{{ $d['r2Akhir'] }}</strong></td>
                            <td class="text-center">{{ $d['totalHari'] }}</td>
                            <td class="text-center">{{ $d['countB'] }}/{{ $d['countC'] }}/{{ $d['countK'] }}</td>
                            <td>{{ $d['munaqosyahStatus'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p><strong>Dokumen ini adalah track record {{ $semester->status === 'ditutup' ? 'terkunci' : 'real-time' }} yang {{ $semester->status === 'ditutup' ? 'tidak dapat diubah' : 'dapat berubah' }}.</strong></p>
        <p>Generated by TartilPro - SD Khadijah 3 Surabaya &middot; {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
