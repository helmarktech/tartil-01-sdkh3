@extends('layouts.admin')
@section('title', 'Pilih Kelas - Penilaian Rapor')

@section('content')
<style>
.kelas-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    margin-bottom: 12px;
}
.kelas-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37, 33, 29, 0.08);
    border-color: var(--accent);
}
.kelas-card:active {
    transform: translateY(0);
}
.kelas-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(107, 94, 81, 0.25);
}
.kelas-info {
    flex: 1;
    min-width: 0;
}
.kelas-nama {
    font-weight: 600;
    font-size: 15px;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.kelas-meta {
    font-size: 12px;
    color: var(--text-muted);
}
.kelas-meta strong {
    color: var(--text-secondary);
}
.kelas-progress {
    text-align: right;
    flex-shrink: 0;
}
.kelas-persen {
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}
.kelas-persen.selesai { color: var(--success); }
.kelas-persen.proses { color: var(--warning); }
.kelas-persen.belum { color: var(--text-muted); }
.kelas-label {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}
.kelas-arrow {
    color: var(--text-muted);
    flex-shrink: 0;
    margin-left: 8px;
}
@media (max-width: 640px) {
    .kelas-card { padding: 14px 16px; gap: 12px; }
    .kelas-avatar { width: 48px; height: 48px; font-size: 12px; }
    .kelas-nama { font-size: 14px; }
    .kelas-persen { font-size: 20px; }
}
</style>

<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Pilih Kelas</h1>
            <p class="page-subtitle">{{ $semesterPenilaian->semester->nama ?? '-' }} {{ $semesterPenilaian->keterangan ? '| ' . $semesterPenilaian->keterangan : '' }}</p>
        </div>
        <a href="{{ route('guru.penilaian-rapor.index') }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px; padding: 8px 14px;">← Kembali</a>
    </div>

    {{-- Kelas Cards --}}
    @foreach($kelasList as $k)
    <a href="{{ route('guru.penilaian-rapor.isi-nilai', [$semesterPenilaian->id, $k->id]) }}" class="kelas-card">
        <div class="kelas-avatar">
            {{ str_replace(' ', '', $k->jenis ?? 'K') }}
        </div>
        <div class="kelas-info">
            <div class="kelas-nama">{{ $k->nama }}</div>
            <div class="kelas-meta">
                <strong>{{ $k->siswas_count }}</strong> siswa &nbsp;|&nbsp;
                <strong>{{ $k->progress_diisi }}</strong>/{{ $k->progress_total }} nilai terisi
            </div>
        </div>
        <div class="kelas-progress">
            <div class="kelas-persen {{ $k->progress_persen >= 80 ? 'selesai' : ($k->progress_persen > 0 ? 'proses' : 'belum') }}">
                {{ $k->progress_persen }}%
            </div>
            <div class="kelas-label">selesai</div>
        </div>
        <div class="kelas-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
    </a>
    @endforeach
</div>
@endsection
