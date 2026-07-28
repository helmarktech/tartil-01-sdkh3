@extends('layouts.admin')
@section('title', 'Pilih Kelas: ' . $penilaian->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $penilaian->nama }}</h1>
            <p class="page-subtitle">Semester {{ $penilaian->semester->nama ?? '-' }} — Pilih kelas untuk mengisi nilai</p>
        </div>
        <a href="{{ route('guru.penilaian-rapor.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        @forelse($kelasList as $kelas)
        <a href="{{ route('guru.penilaian-rapor.isi-nilai', [$penilaian->id, $kelas->id]) }}"
           class="card-tartil"
           style="text-decoration: none; display: block; transition: transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';"
           onmouseout="this.style.transform='';this.style.boxShadow='';">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">{{ $kelas->nama }}</h3>
                <span class="badge-subject" style="font-size: 10px;">{{ $kelas->jenis }}</span>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 12px 0;">
                {{ $kelas->total_siswa }} siswa — {{ $kelas->jumlah_indikator }} indikator
            </p>

            {{-- Progress bar --}}
            <div style="margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Progress: {{ $kelas->sudah_dinilai }}/{{ $kelas->total_siswa }}</span>
                    <span>{{ $kelas->progress_persen }}%</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--surface); border-radius: 3px; overflow: hidden;">
                    <div style="width: {{ $kelas->progress_persen }}%; height: 100%; background: {{ $kelas->progress_persen == 100 ? '#5A7D5A' : 'var(--accent)' }}; border-radius: 3px;"></div>
                </div>
            </div>

            <div style="margin-top: 8px; font-size: 12px; color: var(--accent); font-weight: 500;">
                @if($kelas->progress_persen == 100)
                    ✓ Semua sudah dinilai
                @elseif($kelas->sudah_dinilai > 0)
                    Lanjutkan penilaian →
                @else
                    Mulai penilaian →
                @endif
            </div>
        </a>
        @empty
        <div class="card-tartil" style="text-align: center; padding: 40px;">
            <p style="color: var(--text-muted); font-size: 14px;">Anda tidak memiliki kelas aktif.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
