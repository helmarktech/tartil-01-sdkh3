@extends('layouts.admin')
@section('title', 'Detail Siswa')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Detail Siswa</h1>
            <p class="page-subtitle">{{ $siswa->nama }} - NIS: {{ $siswa->nis }}</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.siswa.edit', $siswa) }}" class="btn-tartil" style="text-decoration: none;">Edit</a>
            <a href="{{ route('admin.siswa.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
        </div>
    </div>

    <div style="display: grid; gap: 16px;">
        {{-- Info Dasar --}}
        <div class="card-tartil" style="padding: 20px;">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0 0 16px; color: var(--text-primary);">Informasi Siswa</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                <div><span style="font-size: 12px; color: var(--text-muted);">NIS</span><div style="font-weight: 500;">{{ $siswa->nis }}</div></div>
                <div><span style="font-size: 12px; color: var(--text-muted);">Nama</span><div style="font-weight: 500;">{{ $siswa->nama }}</div></div>
                <div><span style="font-size: 12px; color: var(--text-muted);">Jenis Kelamin</span><div style="font-weight: 500;">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div></div>
                <div><span style="font-size: 12px; color: var(--text-muted);">No HP</span><div style="font-weight: 500;">{{ $siswa->no_hp }}</div></div>
                <div><span style="font-size: 12px; color: var(--text-muted);">Kelas Reguler</span><div style="font-weight: 500;">{{ $siswa->kelasReguler->nama ?? '-' }}</div></div>
                <div><span style="font-size: 12px; color: var(--text-muted);">Kelas Tartil</span><div style="font-weight: 500;">{{ $siswa->kelasTartil->nama ?? '-' }}</div></div>
                @if($siswa->isMutasi)
                <div>
                    <span style="font-size: 12px; color: #856404;">Mutasi Masuk</span>
                    <div style="font-weight: 500; color: #856404;">{{ $siswa->tanggal_masuk_kelas_tartil->format('d/m/Y') }}</div>
                    @if($siswa->keterangan_mutasi)
                    <span style="font-size: 10px; color: #856404;">{{ $siswa->keterangan_mutasi }}</span>
                    @else
                    <span style="font-size: 10px; color: #856404;">Jurnal dihitung sejak tanggal ini</span>
                    @endif
                </div>
                @endif
                <div><span style="font-size: 12px; color: var(--text-muted);">Status</span>
                    <span class="badge-subject" style="background: {{ $siswa->status == 'aktif' ? '#E9F0E9' : '#F0E9E9' }}; color: {{ $siswa->status == 'aktif' ? '#5A7D5A' : '#A85A52' }};">{{ ucfirst($siswa->status) }}</span>
                </div>
                <div><span style="font-size: 12px; color: var(--text-muted);">Tanggal Masuk</span><div style="font-weight: 500;">{{ $siswa->tanggal_masuk->format('d/m/Y') }}</div></div>
            </div>
        </div>

        {{-- Track Record Kelas --}}
        <div class="card-tartil">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Riwayat Perpindahan Kelas</h2>
            </div>
            @forelse($siswa->perpindahanKelas as $p)
            <div style="padding: 14px 20px; border-bottom: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 500;">{{ $p->kelasLama->nama }} → {{ $p->kelasBaru->nama }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $p->semester->tahun_ajaran }} {{ ucfirst($p->semester->jenis) }}</div>
                    </div>
                    <span class="badge-subject" style="background: {{ $p->status == 'disetujui' ? '#E9F0E9' : ($p->status == 'pending' ? '#F0ECE9' : '#F0E9E9') }}; color: {{ $p->status == 'disetujui' ? '#5A7D5A' : ($p->status == 'pending' ? '#8A7A6B' : '#A85A52') }};">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div style="padding: 24px; text-align: center; color: var(--text-muted);">Belum ada riwayat perpindahan kelas.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
