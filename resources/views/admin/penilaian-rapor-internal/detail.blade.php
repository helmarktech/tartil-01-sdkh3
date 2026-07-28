@extends('layouts.admin')
@section('title', 'Detail Penilaian Rapor Internal')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $ujian->nama }}</h1>
            <p class="page-subtitle">{{ ucfirst($ujian->tingkat) }} — {{ $ujian->tanggal_ujian ? date('d/m/Y', strtotime($ujian->tanggal_ujian)) : '-' }}</p>
        </div>
        <a href="{{ route('admin.penilaian-rapor-internal.index') }}" class="btn-tartil-outline">Kembali</a>
    </div>

    {{-- Statistik --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 24px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--accent);">{{ $ujian->pesertas->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Siswa Terdaftar</div>
        </div>
    </div>

    {{-- Daftar Peserta --}}
    <h3 style="font-size: 16px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Peserta</h3>
    @if($ujian->pesertas->count() > 0)
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas Reguler</th>
                    <th>Kelas Tartil</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ujian->pesertas as $i => $ps)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $ps->siswa->nis ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $ps->siswa->nama ?? '-' }}</td>
                    <td>{{ $ps->siswa->kelasReguler->nama ?? '-' }}</td>
                    <td>{{ $ps->siswa->kelasTartil->nama ?? '-' }}</td>
                    <td>{{ $ps->catatan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">Belum ada peserta. Guru akan mendaftarkan siswa dari kelasnya.</p>
    </div>
    @endif
</div>
@endsection
