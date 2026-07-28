@extends('layouts.admin')
@section('title', 'Ujian Munaqosyah')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Ujian Munaqosyah</h1>
            <p class="page-subtitle">Daftar ujian yang bisa didaftarkan siswa dari kelas Anda</p>
        </div>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Nama Ujian</th>
                    <th>Tingkat</th>
                    <th>Tanggal</th>
                    <th>Semester</th>
                    <th>Jumlah Pendaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ujians as $u)
                <tr>
                    <td style="font-weight: 500;">{{ $u->nama }}</td>
                    <td><span class="badge-subject">{{ ucfirst($u->tingkat) }}</span></td>
                    <td>{{ $u->tanggal_ujian ? date('d/m/Y', strtotime($u->tanggal_ujian)) : '-' }}</td>
                    <td>{{ $u->semester->nama ?? '-' }}</td>
                    <td><span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">{{ $u->pendaftarans->count() }} siswa</span></td>
                    <td>
                        <a href="{{ route('guru.munaqosyah.detail', $u->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Detail & Daftarkan</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">
                    <p>Belum ada ujian yang disetujui.</p>
                    <p style="font-size: 12px;">Hubungi admin untuk membuat ujian munaqosyah.</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ujians->links() }}
</div>
@endsection
