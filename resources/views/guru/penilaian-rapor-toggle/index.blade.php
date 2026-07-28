@extends('layouts.admin')
@section('title', 'Penilaian Rapor Toggle')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Penilaian Rapor</h1>
            <p class="page-subtitle">Pilih semester untuk melakukan penilaian rapor siswa (sistem toggle L/TL)</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        @forelse($semesterList as $semester)
        <a href="{{ route('guru.penilaian-rapor-toggle.pilih-kelas', $semester->id) }}" 
           class="card-tartil" 
           style="text-decoration: none; display: block; transition: transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';"
           onmouseout="this.style.transform='';this.style.boxShadow='';">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">{{ $semester->nama }}</h3>
                @if($semester->status == 'aktif')
                    <span class="badge-success" style="font-size: 10px;">Aktif</span>
                @elseif($semester->status == 'selesai')
                    <span class="badge-subject" style="font-size: 10px;">Selesai</span>
                @else
                    <span class="badge-muted" style="font-size: 10px;">{{ $semester->status }}</span>
                @endif
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ $semester->tanggal_mulai ? date('d/m/Y', strtotime($semester->tanggal_mulai)) : '-' }} 
                s/d 
                {{ $semester->tanggal_selesai ? date('d/m/Y', strtotime($semester->tanggal_selesai)) : '-' }}
            </p>
            <div style="margin-top: 12px; font-size: 12px; color: var(--accent); font-weight: 500;">
                Klik untuk pilih kelas →
            </div>
        </a>
        @empty
        <div class="card-tartil" style="text-align: center; padding: 40px;">
            <p style="color: var(--text-muted); font-size: 14px;">Belum ada semester yang tersedia.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
