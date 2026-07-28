@extends('layouts.admin')
@section('title', 'Progress Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Progress Rapor</h1>
            <p class="page-subtitle">Monitoring pengisian nilai rapor per guru dan kelas</p>
        </div>
    </div>

    {{-- STEP 1: Pilih Penilaian --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Step 1: Pilih Penilaian</h3>
        <form method="GET" action="{{ route('admin.penilaian-rapor-internal.rekap') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 250px;">
                <select name="penilaian_id" class="form-input" required onchange="this.form.submit()">
                    <option value="" disabled {{ !$penilaianId ? 'selected' : '' }}>-- Pilih Penilaian --</option>
                    @foreach($penilaians as $p)
                    <option value="{{ $p->id }}" {{ $penilaianId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->semester->nama ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
            @if($penilaianId)
            <a href="{{ route('admin.penilaian-rapor-internal.rekap') }}" class="btn-tartil-outline" style="text-decoration: none;">Pilih Ulang</a>
            @endif
        </form>

        @if($penilaianTerpilih)
        <div style="margin-top: 12px; padding: 10px 14px; background: #E9F0E9; border-radius: 8px; font-size: 13px; color: #5A7D5A;">
            <strong>Penilaian terpilih:</strong> {{ $penilaianTerpilih->nama }} — Semester {{ $penilaianTerpilih->semester->nama ?? '-' }}
        </div>
        @endif
    </div>

    {{-- STEP 2: Progress per Guru-Kelas (hanya kalau sudah pilih penilaian) --}}
    @if($penilaianId && $penilaianTerpilih)

        {{-- Info ringkas --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="font-size: 14px; margin: 0; color: var(--text-primary); font-weight: 600;">Step 2: Progress Guru & Kelas</h3>
            <span style="font-size: 12px; color: var(--text-muted);">Total {{ $gurus->total() }} guru</span>
        </div>

        @forelse($gurus as $guru)
        <div class="card-tartil" style="margin-bottom: 16px; padding: 0; overflow: hidden;">
            {{-- Header Guru --}}
            <div style="background: var(--surface); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                        {{ strtoupper(substr($guru->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 14px;">{{ $guru->nama }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $guru->kelas->count() }} kelas</div>
                    </div>
                </div>
                @php
                    $totalSiswaGuru = $guru->kelas->sum('total_siswa');
                    $totalDiisiGuru = $guru->kelas->sum('sudah_dinilai');
                    $persenGuru = $totalSiswaGuru > 0 ? round(($totalDiisiGuru / $totalSiswaGuru) * 100) : 0;
                @endphp
                <div style="text-align: right;">
                    <div style="font-size: 18px; font-weight: 600; color: {{ $persenGuru == 100 ? '#5A7D5A' : ($persenGuru > 0 ? 'var(--accent)' : 'var(--text-muted)') }};">
                        {{ $persenGuru }}%
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $totalDiisiGuru }}/{{ $totalSiswaGuru }} siswa</div>
                </div>
            </div>

            {{-- Daftar Kelas --}}
            <div style="padding: 12px 20px;">
                @foreach($guru->kelas as $kelas)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 500; color: var(--text-primary);">
                            {{ $kelas->nama }}
                            <span class="badge-subject" style="font-size: 10px;">{{ $kelas->jenis }}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $kelas->total_siswa }} siswa — {{ $kelas->jumlah_indikator }} indikator</div>
                    </div>
                    <div style="width: 200px; margin: 0 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 3px;">
                            <span>{{ $kelas->sudah_dinilai }}/{{ $kelas->total_siswa }}</span>
                            <span>{{ $kelas->progress_persen }}%</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: var(--surface); border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $kelas->progress_persen }}%; height: 100%; background: {{ $kelas->progress_persen == 100 ? '#5A7D5A' : ($kelas->progress_persen > 0 ? 'var(--accent)' : '#ddd') }}; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <div style="min-width: 60px; text-align: right;">
                        @if($kelas->progress_persen == 100)
                            <span class="badge-success" style="font-size: 10px;">Selesai</span>
                        @elseif($kelas->progress_persen > 0)
                            <span class="badge-warning" style="font-size: 10px;">Proses</span>
                        @else
                            <span class="badge-muted" style="font-size: 10px;">Belum</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="card-tartil" style="text-align: center; padding: 40px;">
            <p style="color: var(--text-muted);">Tidak ada guru dengan kelas aktif.</p>
        </div>
        @endforelse

        {{-- Paging --}}
        @if($gurus->hasPages())
        <div style="margin-top: 20px;">
            {{ $gurus->appends(['penilaian_id' => $penilaianId])->links() }}
        </div>
        @endif

    @else

        {{-- Belum pilih penilaian --}}
        <div class="card-tartil" style="text-align: center; padding: 60px 40px;">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128203;</div>
            <h3 style="font-size: 16px; color: var(--text-primary); margin-bottom: 8px;">Silakan Pilih Penilaian</h3>
            <p style="color: var(--text-muted); font-size: 14px; max-width: 400px; margin: 0 auto;">
                Pilih penilaian rapor dari dropdown di atas untuk melihat progress pengisian nilai per guru dan kelas.
            </p>
        </div>

    @endif
</div>
@endsection
