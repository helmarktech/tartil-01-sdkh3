@extends('layouts.admin')
@section('title', 'Data Siswa')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Data Siswa</h1>
            <p class="page-subtitle">Kelola data siswa dan kelasnya</p>
        </div>
        <a href="{{ route('admin.siswa.create') }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Siswa
        </a>
    </div>

    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.siswa.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; margin: 0;">
            <div style="position: relative; flex: 1; min-width: 200px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" name="search" placeholder="Cari nama atau NIS..." value="{{ request('search') }}" style="width: 100%; padding: 10px 12px 10px 36px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; font-family: 'Inter', sans-serif; outline: none;">
            </div>
            <select name="kelas_reguler" onchange="this.form.submit()" style="padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; min-width: 160px;">
                <option value="">Semua Kelas</option>
                @foreach($kelasRegulars as $kr)
                <option value="{{ $kr->id }}" {{ request('kelas_reguler') == $kr->id ? 'selected' : '' }}>{{ $kr->nama }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" style="padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; min-width: 140px;">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="mutasi_keluar" {{ request('status') == 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
            </select>
        </form>
    </div>

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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $i => $s)
                <tr>
                    <td>{{ $siswas->firstItem() + $i }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                    <td>{{ $s->kelasTartil->nama ?? '-' }}</td>
                    <td>
                        <span class="badge-subject" style="background: {{ $s->status == 'aktif' ? '#E9F0E9' : '#F0E9E9' }}; color: {{ $s->status == 'aktif' ? '#5A7D5A' : '#A85A52' }};">
                            {{ ucfirst($s->status) }}
                        </span>
                    </td>
                    <td style="display: flex; gap: 6px;">
                        <a href="{{ route('admin.siswa.show', $s->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Detail</a>
                        <a href="{{ route('admin.siswa.edit', $s->id) }}" class="btn-tartil" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $siswas->links() }}
</div>
@endsection
