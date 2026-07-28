@extends('layouts.admin')
@section('title', 'Penilaian Rapor Internal')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Penilaian Rapor Internal</h1>
            <p class="page-subtitle">Pilih penilaian untuk mengisi nilai siswa</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        @forelse($penilaians as $p)
        <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $p->id) }}"
           class="card-tartil"
           style="text-decoration: none; display: block; transition: transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';"
           onmouseout="this.style.transform='';this.style.boxShadow='';">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">{{ $p->nama }}</h3>
                <span class="badge-success" style="font-size: 10px;">Aktif</span>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">{{ $p->semester->nama ?? '-' }}</p>
            <div style="margin-top: 12px; font-size: 12px; color: var(--accent); font-weight: 500;">
                Pilih Kelas →
            </div>
        </a>
        @empty
        <div class="card-tartil" style="text-align: center; padding: 40px;">
            <p style="color: var(--text-muted); font-size: 14px;">Belum ada penilaian dari admin.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
