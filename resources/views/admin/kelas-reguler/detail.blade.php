@extends('layouts.admin')
@section('title', 'Detail Kelas ' . $kelasReguler->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $kelasReguler->nama }}</h1>
            <p class="page-subtitle">{{ $kelasReguler->jenjang }} {{ $kelasReguler->tingkat }} - {{ $siswas->total() }} siswa aktif</p>
        </div>
        <a href="{{ route('admin.kelas-reguler.keterangan') }}" class="btn-tartil-outline">Kembali</a>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Tempat, Tgl Lahir</th>
                    <th>Nama Ayah</th>
                    <th>Kelas Tartil</th>
                    <th>Mata Pelajaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $i => $s)
                <tr>
                    <td>{{ $siswas->firstItem() + $i }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>{{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    <td>{{ $s->tempat_lahir ?? '-' }}, {{ $s->tanggal_lahir ? $s->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                    <td>{{ $s->nama_ayah ?? '-' }}</td>
                    <td>
                        @if($s->kelasTartil)
                        <span class="badge-subject" style="background: #E8D5B5;">{{ $s->kelasTartil->nama }}</span>
                        @else
                        <span class="badge-warning" style="font-size: 10px;">Belum masuk</span>
                        @endif
                    </td>
                    <td>{{ $s->kelasTartil->mata_pelajaran ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Tidak ada siswa aktif di kelas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $siswas->links() }}
</div>
@endsection
