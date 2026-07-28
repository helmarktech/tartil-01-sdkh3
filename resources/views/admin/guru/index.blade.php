@extends('layouts.admin')
@section('title', 'Manajemen Guru Tartil')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Manajemen Guru Tartil</h1>
            <p class="page-subtitle">Kelola data guru tartil pengajar</p>
        </div>
        <a href="{{ route('admin.guru.create') }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Guru
        </a>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>JK</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gurus as $i => $g)
                <tr>
                    <td>{{ $gurus->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $g->nama }}</td>
                    <td>{{ $g->nip ?? '-' }}</td>
                    <td>{{ $g->email }}</td>
                    <td>{{ $g->no_hp }}</td>
                    <td>{{ $g->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    <td>
                        <span class="badge-subject" style="background: {{ $g->is_aktif ? '#E9F0E9' : '#F0E9E9' }}; color: {{ $g->is_aktif ? '#5A7D5A' : '#A85A52' }};">
                            {{ $g->is_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.guru.edit', $g->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada data guru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $gurus->links() }}
</div>
@endsection
