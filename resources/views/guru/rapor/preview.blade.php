@extends('layouts.admin')
@section('title', 'Preview Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Preview Rapor</h1>
            <p class="page-subtitle">{{ $kelas->nama }} | Semester {{ $semester->tahun_ajaran }} {{ ucfirst($semester->jenis) }} | {{ $jenis == 'tengah' ? 'Tengah' : 'Akhir' }} Semester</p>
        </div>
        <a href="{{ route('guru.rapor.pdf.kelas', ['kelas_id' => $kelas->id, 'semester_id' => $semester->id, 'jenis' => $jenis]) }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download PDF Kelas
        </a>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>B</th>
                    <th>C</th>
                    <th>K</th>
                    <th>Rata</th>
                    <th>Predikat</th>
                    <th>Hadir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataRapor as $i => $r)
                @php $na = $r['rata_akhir']; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight: 500;">{{ $r['siswa']->nama }}</td>
                    <td>{{ $r['siswa']->nis }}</td>
                    <td>{{ $r['rata_b'] }}</td>
                    <td>{{ $r['rata_c'] }}</td>
                    <td>{{ $r['rata_k'] }}</td>
                    <td><strong>{{ $r['rata_akhir'] }}</strong></td>
                    <td>
                        <span class="badge-subject" style="background: {{ $na >= 85 ? '#E9F0E9' : ($na >= 75 ? '#E9EEF0' : ($na >= 65 ? '#F0ECE9' : '#F0E9E9')) }}; color: {{ $na >= 85 ? '#5A7D5A' : ($na >= 75 ? '#5A7A8A' : ($na >= 65 ? '#8A7A6B' : '#A85A52')) }};">
                            {{ substr($r['predikat'], 0, 1) }}
                        </span>
                    </td>
                    <td>{{ $r['persentase_hadir'] }}%</td>
                    <td>
                        <a href="{{ route('guru.rapor.pdf.siswa', ['siswa_id' => $r['siswa']->id, 'kelas_id' => $kelas->id, 'semester_id' => $semester->id, 'jenis' => $jenis]) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 11px;">PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
