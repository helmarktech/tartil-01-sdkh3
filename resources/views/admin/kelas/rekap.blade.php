@extends('layouts.admin')
@section('title', 'Keterangan Kelas Tartil')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Keterangan Kelas Tartil</h1>
            <p class="page-subtitle">
                @if($semesterAktif)
                    Semester aktif: <strong>{{ $semesterAktif->nama }}</strong> ({{ $semesterAktif->tahun_ajaran }})
                @else
                    <span style="color: #c62828;">Tidak ada semester aktif</span>
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.keterangan-kelas-tartil.export') }}" class="btn-tartil" style="text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Excel
            </a>
            <a href="{{ route('admin.kelas.index') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                Kelola Kelas
            </a>
        </div>
    </div>

    @if(!$semesterAktif)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted); font-size: 14px;">Tidak dapat menampilkan data. Tidak ada semester yang aktif saat ini.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buka Tahun Ajaran</a>
    </div>
    @else
        {{-- Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div class="card-tartil" style="text-align: center;">
                <div style="font-size: 28px; font-weight: 600; color: var(--accent);">{{ $totalSiswa }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Siswa Aktif</div>
            </div>
            <div class="card-tartil" style="text-align: center;">
                <div style="font-size: 28px; font-weight: 600; color: #5A7D5A;">{{ $kelasList->count() }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Kelas Aktif</div>
            </div>
            @foreach(['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'] as $jenis)
                @if($jumlahPerJenis->has($jenis))
                <div class="card-tartil" style="text-align: center;">
                    <div style="font-size: 28px; font-weight: 600; color: #6B5E51;">{{ $jumlahPerJenis[$jenis] }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $jenis }}</div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- Detail per Kelas --}}
        @forelse($kelasList as $k)
        <div class="card-tartil" style="margin-bottom: 16px; padding: 24px;">
            {{-- Kelas Header --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <h3 style="font-size: 16px; margin: 0; color: var(--text-primary);">{{ $k->nama }}</h3>
                    <span class="badge-subject" style="background: #E9E6E1; color: #6B5E51;">{{ $k->jenis }}</span>
                    <span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">{{ $k->siswas_count }} siswa</span>
                    @if($k->guru)
                    <span class="badge-subject" style="background: #E8D5B5; color: #6B5E51;">Guru: {{ $k->guru->nama }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.kelas.edit', $k) }}" class="btn-tartil-outline" style="padding: 6px 14px; font-size: 12px;">Lihat Detail</a>
            </div>

            {{-- Siswa Detail Table --}}
            @if($k->siswas->count() > 0)
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>L/P</th>
                            <th>Kelas Reguler</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($k->siswas as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td>{{ $s->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                            <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                            <td>{{ $s->no_hp ?? '-' }}</td>
                            <td>
                                <span class="badge-subject" style="background: {{ $s->status == 'aktif' ? '#E9F0E9' : '#F0E9E9' }}; color: {{ $s->status == 'aktif' ? '#5A7D5A' : '#A85A52' }};">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td>{{ $s->tanggal_masuk ? $s->tanggal_masuk->format('d/m/Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p style="color: var(--text-muted); text-align: center; padding: 16px; font-size: 13px;">Tidak ada siswa aktif di kelas ini.</p>
            @endif
        </div>
        @empty
        <div class="card-tartil" style="padding: 32px; text-align: center;">
            <p style="color: var(--text-muted); font-size: 14px;">Belum ada kelas tartil aktif.</p>
        </div>
        @endforelse
    @endif
</div>
@endsection
