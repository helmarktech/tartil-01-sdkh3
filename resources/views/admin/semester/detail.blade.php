@extends('layouts.admin')
@section('title', 'Detail Semester ' . $semester->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $semester->nama }}</h1>
            <p class="page-subtitle">{{ $semester->tanggal_mulai->format('d/m/Y') }} - {{ $semester->tanggal_selesai->format('d/m/Y') }}</p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if($semester->status != 'ditutup')
                @if(!$semester->is_aktif)
                <form method="POST" action="{{ route('admin.semester.aktifkan', $semester->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-tartil-success" style="padding: 8px 16px; font-size: 12px;">Aktifkan</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.semester.tutup', $semester->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-tartil-danger" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('Yakin tutup semester ini? Data akan bersifat permanen.')">Tutup Semester</button>
                </form>
            @endif
            <a href="{{ route('admin.semester.index') }}" class="btn-tartil-outline">Kembali</a>
        </div>
    </div>

    {{-- Info Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $semester->kelasTartils->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Kelas Tartil</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #5A7D5A;">{{ $semester->siswas->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Siswa</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--text-secondary);">
                @if($semester->status == 'aktif')
                    <span class="badge-success">Aktif</span>
                @elseif($semester->status == 'ditutup')
                    <span class="badge-error">Ditutup</span>
                @else
                    <span class="badge-warning">Nonaktif</span>
                @endif
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Status</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #C4953A;">
                {{ $semester->tanggal_mulai->diffInDays($semester->tanggal_selesai) }}
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Hari</div>
        </div>
    </div>

    {{-- Kelas Tartil --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Kelas Tartil</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Jumlah Siswa</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semester->kelasTartils as $i => $k)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight: 500;">{{ $k->nama }}</td>
                    <td>{{ $k->mata_pelajaran }}</td>
                    <td>{{ $k->guru->nama ?? '-' }}</td>
                    <td>{{ $k->pivot->jumlah_siswa }} siswa</td>
                    <td>{{ $k->pivot->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada kelas terdaftar di semester ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Siswa --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Siswa</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas Reguler</th>
                    <th>Kelas Tartil</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semester->siswas as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                    <td>{{ $s->kelasTartil->nama ?? '-' }}</td>
                    <td>
                        @if($s->pivot->status_siswa == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @elseif($s->pivot->status_siswa == 'pindah')
                            <span class="badge-warning">Pindah</span>
                        @elseif($s->pivot->status_siswa == 'keluar')
                            <span class="badge-error">Keluar</span>
                        @else
                            <span class="badge-error">Nonaktif</span>
                        @endif
                    </td>
                    <td>{{ $s->pivot->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada siswa terdaftar di semester ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
