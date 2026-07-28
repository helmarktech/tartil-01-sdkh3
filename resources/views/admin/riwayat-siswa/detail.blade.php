@extends('layouts.admin')
@section('title', 'Riwayat ' . $siswa->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Riwayat Semester</h1>
            <p class="page-subtitle">{{ $siswa->nama }} (NIS: {{ $siswa->nis }})</p>
        </div>
        <a href="{{ route('admin.riwayat-siswa.index') }}" class="btn-tartil-outline">Kembali</a>
    </div>

    {{-- Info Card --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $records->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total Semester</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #5A7D5A;">{{ $records->where('status_siswa', 'aktif')->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Semester Aktif</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--text-secondary);">
                {{ $siswa->kelasReguler->nama ?? '-' }}
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Kelas Reguler (Saat Ini)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #C4953A;">
                {{ $siswa->kelasTartil->nama ?? '-' }}
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Kelas Tartil (Saat Ini)</div>
        </div>
    </div>

    {{-- Timeline Riwayat --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Riwayat Per Semester</h3>
    @forelse($records as $r)
    <div class="card-tartil" style="margin-bottom: 12px; padding: 20px; {{ $r->status_siswa == 'aktif' ? 'border-left: 4px solid #5A7D5A;' : 'border-left: 4px solid #C4953A;' }}">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
            <div>
                <h4 style="font-size: 15px; margin: 0; color: var(--text-primary); font-weight: 600;">
                    {{ $r->semester->nama ?? '-' }}
                </h4>
                <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0;">
                    {{ $r->semester->tanggal_mulai ? $r->semester->tanggal_mulai->format('d/m/Y') : '-' }} - {{ $r->semester->tanggal_selesai ? $r->semester->tanggal_selesai->format('d/m/Y') : '-' }}
                </p>
            </div>
            <div style="display: flex; gap: 6px; align-items: center;">
                @if($r->status_siswa == 'aktif')
                    <span class="badge-success">Aktif</span>
                @elseif($r->status_siswa == 'pindah')
                    <span class="badge-warning">Pindah</span>
                @elseif($r->status_siswa == 'keluar')
                    <span class="badge-error">Keluar</span>
                @else
                    <span class="badge-error">Nonaktif</span>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="background: #f9f8f6; border-radius: 8px; padding: 12px;">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.5px;">Kelas Reguler</p>
                <p style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0;">
                    {{ $r->kelasReguler->nama ?? '-' }}
                    @if($r->kelasReguler)
                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 400;">({{ $r->kelasReguler->jenjang }} {{ $r->kelasReguler->tingkat }})</span>
                    @endif
                </p>
            </div>
            <div style="background: #f9f8f6; border-radius: 8px; padding: 12px;">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.5px;">Kelas Tartil</p>
                <p style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0;">
                    {{ $r->kelasTartil->nama ?? '-' }}
                    @if($r->kelasTartil)
                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 400;">({{ $r->kelasTartil->mata_pelajaran }})</span>
                    @endif
                </p>
            </div>
        </div>

        @if($r->keterangan)
        <p style="font-size: 12px; color: var(--text-muted); margin: 10px 0 0;">
            Keterangan: {{ $r->keterangan }}
        </p>
        @endif
    </div>
    @empty
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">Belum ada data riwayat semester untuk siswa ini.</p>
        <p style="font-size: 12px; color: var(--text-muted);">Data akan muncul setelah semester dibuat dan siswa terdaftar di semester tersebut.</p>
    </div>
    @endforelse
</div>
@endsection
