@extends('layouts.admin')
@section('title', 'Detail Jurnal')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Detail Jurnal</h1>
            <p class="page-subtitle">{{ $jurnal->kelas->nama }} | {{ $jurnal->tanggal->format('d F Y') }}</p>
        </div>
        <a href="{{ route('guru.jurnal.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    <div class="card-tartil" style="padding: 20px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
            <div><span style="font-size: 12px; color: var(--text-muted);">Surat</span><div style="font-weight: 500;">{{ $jurnal->surat }}</div></div>
            <div><span style="font-size: 12px; color: var(--text-muted);">Ayat</span><div style="font-weight: 500;">{{ $jurnal->ayat }}</div></div>
            <div><span style="font-size: 12px; color: var(--text-muted);">Guru</span><div style="font-weight: 500;">{{ $jurnal->guru->nama }}</div></div>
            <div><span style="font-size: 12px; color: var(--text-muted);">Semester</span><div style="font-weight: 500;">{{ $jurnal->semester->tahun_ajaran }} {{ ucfirst($jurnal->semester->jenis) }}</div></div>
            <div><span style="font-size: 12px; color: var(--text-muted);">Jenis</span>
                <span class="badge-subject" style="background: #E9E6E1; color: #6B5E51;">{{ str_replace('_', ' ', $jurnal->jenis_penilaian) }}</span>
            </div>
        </div>
        @if($jurnal->materi)
        <div style="margin-top: 12px;">
            <span style="font-size: 12px; color: var(--text-muted);">Materi:</span>
            <div>{{ $jurnal->materi }}</div>
        </div>
        @endif
    </div>

    <div class="card-tartil">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Penilaian Siswa (B, C, K)</h2>
        </div>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>B (Bacaan)</th>
                        <th>C (Catatan)</th>
                        <th>K (Keterampilan)</th>
                        <th>Rata-rata</th>
                        <th>Predikat</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnal->details as $i => $d)
                    @php $na = $d->nilai_akhir; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $d->siswa->nama }}</td>
                        <td>{{ $d->nilai_b }}</td>
                        <td>{{ $d->nilai_c }}</td>
                        <td>{{ $d->nilai_k }}</td>
                        <td><strong>{{ round($na) }}</strong></td>
                        <td>
                            <span class="badge-subject" style="background: {{ $na >= 85 ? '#E9F0E9' : ($na >= 75 ? '#E9EEF0' : ($na >= 65 ? '#F0ECE9' : '#F0E9E9')) }}; color: {{ $na >= 85 ? '#5A7D5A' : ($na >= 75 ? '#5A7A8A' : ($na >= 65 ? '#8A7A6B' : '#A85A52')) }};">
                                {{ $d->predikat }}
                            </span>
                        </td>
                        <td>{{ $d->catatan ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-tartil" style="margin-top: 20px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Absensi</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); text-align: center;">
            @php $a = $jurnal->absensis->groupBy('status')->map->count(); @endphp
            <div style="padding: 12px;"><div style="font-size: 20px; font-weight: 700; color: #5A7D5A;">{{ $a['Hadir'] ?? 0 }}</div><div style="font-size: 11px; color: var(--text-muted);">Hadir</div></div>
            <div style="padding: 12px;"><div style="font-size: 20px; font-weight: 700; color: #C4953A;">{{ $a['Sakit'] ?? 0 }}</div><div style="font-size: 11px; color: var(--text-muted);">Sakit</div></div>
            <div style="padding: 12px;"><div style="font-size: 20px; font-weight: 700; color: #5A7A8A;">{{ $a['Izin'] ?? 0 }}</div><div style="font-size: 11px; color: var(--text-muted);">Izin</div></div>
            <div style="padding: 12px;"><div style="font-size: 20px; font-weight: 700; color: #A85A52;">{{ $a['Alpha'] ?? 0 }}</div><div style="font-size: 11px; color: var(--text-muted);">Alpha</div></div>
        </div>
    </div>
</div>
@endsection
