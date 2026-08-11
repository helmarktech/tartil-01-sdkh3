@extends('layouts.admin')
@section('title', 'Kelas Tartil')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Kelas Tartil</h1>
            <p class="page-subtitle">Kelola kelas tartil, tingkatan BQ, dan jumlah siswa</p>
        </div>
        <a href="{{ route('admin.kelas.create') }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Kelas
        </a>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Jenis</th>
                    <th>Guru</th>
                    <th>Siswa Aktif</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas as $i => $k)
                <tr>
                    <td>{{ $kelas->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $k->nama }}</td>
                    <td><span class="badge-subject">{{ $k->jenis }}</span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="student-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                {{ substr($k->guru->nama ?? '-', 0, 2) }}
                            </div>
                            <span>{{ $k->guru->nama ?? '-' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">
                            {{ $k->siswas_count }} siswa
                        </span>
                    </td>
                    <td>
                        @if($k->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-error">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <a href="{{ route('admin.kelas.edit', $k->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                            <form method="POST" action="{{ route('admin.kelas.update', $k->id) }}" style="display: inline;">
                                @csrf @method('PUT')
                                <input type="hidden" name="nama" value="{{ $k->nama }}">
                                <input type="hidden" name="jenis" value="{{ $k->jenis }}">
                                <input type="hidden" name="guru_id" value="{{ $k->guru_id }}">
                                <input type="hidden" name="status" value="{{ $k->status == 'aktif' ? 'nonaktif' : 'aktif' }}">
                                <button type="submit" class="btn-tartil-{{ $k->status == 'aktif' ? 'warning' : 'outline' }}" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm({{ json_encode(($k->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan').' kelas '.$k->nama.'?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})">
                                    {{ $k->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        <p>Belum ada kelas tartil.</p>
                        <a href="{{ route('admin.kelas.create') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buat Kelas Pertama</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $kelas->links() }}
</div>
@endsection
