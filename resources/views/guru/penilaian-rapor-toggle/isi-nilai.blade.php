@extends('layouts.admin')
@section('title', 'Isi Nilai Rapor - ' . $kelas->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Penilaian Rapor: {{ $kelas->nama }}</h1>
            <p class="page-subtitle">Semester {{ $semester->nama }} — {{ $sudahDinilai }}/{{ $totalSiswa }} dinilai ({{ $progressPersen }}%)</p>
        </div>
        <a href="{{ route('guru.penilaian-rapor-toggle.pilih-kelas', $semester->id) }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    {{-- Progress Bar --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 16px;">
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">
            <span>Progress Penilaian</span>
            <span>{{ $progressPersen }}% ({{ $sudahDinilai }} dari {{ $totalSiswa }} siswa)</span>
        </div>
        <div style="width: 100%; height: 8px; background: var(--surface); border-radius: 4px; overflow: hidden;">
            <div style="width: {{ $progressPersen }}%; height: 100%; background: {{ $progressPersen == 100 ? '#5A7D5A' : 'var(--accent)' }}; border-radius: 4px; transition: width 0.3s;"></div>
        </div>
    </div>

    {{-- Statistik --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 22px; font-weight: 600; color: var(--accent);">{{ $totalSiswa }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Total Siswa</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 22px; font-weight: 600; color: #5A7D5A;">
                {{ $penilaianToggles->where('status', 'L')->count() }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Lulus (L)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 22px; font-weight: 600; color: #A85A52;">
                {{ $penilaianToggles->where('status', 'TL')->count() }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Tidak Lulus (TL)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 22px; font-weight: 600; color: var(--text-secondary);">
                {{ $penilaianToggles->where('status', 'T')->count() }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Terdaftar (T)</div>
        </div>
    </div>

    {{-- Form Input Nilai Toggle --}}
    <div class="card-tartil" style="padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Input Nilai Toggle</h3>
        
        <form method="POST" action="{{ route('guru.penilaian-rapor-toggle.simpan-nilai', [$semester->id, $kelas->id]) }}" id="form-nilai">
            @csrf
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th style="text-align: center;">Status Toggle</th>
                            <th>Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $i => $s)
                        @php
                            $toggle = $penilaianToggles->get($s->id);
                            $currentStatus = $toggle?->status ?? 'T';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td style="text-align: center;">
                                <input type="hidden" name="nilai[{{ $s->id }}][status]" id="status-{{ $s->id }}" value="{{ $currentStatus }}">
                                
                                {{-- Toggle Button Group: T | L | TL --}}
                                <div class="toggle-group" style="display: inline-flex; gap: 2px; background: var(--surface); border-radius: 8px; padding: 2px;">
                                    <button type="button" 
                                        class="toggle-btn {{ $currentStatus == 'T' ? 'active' : '' }}" 
                                        data-status="T" 
                                        data-siswa="{{ $s->id }}"
                                        onclick="setStatus('{{ $s->id }}', 'T')"
                                        title="Terdaftar"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $currentStatus == 'T' ? '#D4A373' : 'transparent' }}; color: {{ $currentStatus == 'T' ? '#fff' : 'var(--text-muted)' }};">
                                        T
                                    </button>
                                    <button type="button" 
                                        class="toggle-btn {{ $currentStatus == 'L' ? 'active' : '' }}" 
                                        data-status="L" 
                                        data-siswa="{{ $s->id }}"
                                        onclick="setStatus('{{ $s->id }}', 'L')"
                                        title="Lulus"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $currentStatus == 'L' ? '#5A7D5A' : 'transparent' }}; color: {{ $currentStatus == 'L' ? '#fff' : 'var(--text-muted)' }};">
                                        L
                                    </button>
                                    <button type="button" 
                                        class="toggle-btn {{ $currentStatus == 'TL' ? 'active' : '' }}" 
                                        data-status="TL" 
                                        data-siswa="{{ $s->id }}"
                                        onclick="setStatus('{{ $s->id }}', 'TL')"
                                        title="Tidak Lulus"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $currentStatus == 'TL' ? '#A85A52' : 'transparent' }}; color: {{ $currentStatus == 'TL' ? '#fff' : 'var(--text-muted)' }};">
                                        TL
                                    </button>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="nilai[{{ $s->id }}][nilai]" 
                                    value="{{ $toggle?->nilai }}" 
                                    class="form-input" min="0" max="100" style="max-width: 80px;">
                            </td>
                            <td>
                                <input type="text" name="nilai[{{ $s->id }}][catatan]" 
                                    value="{{ $toggle?->catatan }}" 
                                    class="form-input" placeholder="Catatan..." style="min-width: 120px;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;">
                <button type="submit" class="btn-tartil">Simpan Nilai</button>
                <button type="button" class="btn-tartil-success" onclick="lulusSemua()">Lulus Semua (T)</button>
                <button type="button" class="btn-tartil-danger" onclick="tidakLulusSemua()">Tidak Lulus Semua (T)</button>
            </div>
        </form>

        {{-- Hidden forms for batch actions --}}
        <form method="POST" action="{{ route('guru.penilaian-rapor-toggle.lulus-semua', [$semester->id, $kelas->id]) }}" id="form-lulus-semua" style="display:none;">
            @csrf
        </form>
        <form method="POST" action="{{ route('guru.penilaian-rapor-toggle.tidak-lulus-semua', [$semester->id, $kelas->id]) }}" id="form-tidak-lulus-semua" style="display:none;">
            @csrf
        </form>
    </div>

    {{-- Daftar Nilai Saat Ini --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Nilai</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Nilai</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $i => $s)
                @php $toggle = $penilaianToggles->get($s->id); @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>
                        @if($toggle)
                            <span class="{{ $toggle->status_badge_class }}">{{ $toggle->status_label }}</span>
                        @else
                            <span class="badge-warning">Terdaftar</span>
                        @endif
                    </td>
                    <td>{{ $toggle?->nilai ?? '-' }}</td>
                    <td>{{ $toggle?->catatan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Tidak ada siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle Status: T (Terdaftar) | L (Lulus) | TL (Tidak Lulus)
function setStatus(siswaId, status) {
    // Update hidden input
    const input = document.getElementById('status-' + siswaId);
    if (input) input.value = status;
    
    // Update button styles
    const buttons = document.querySelectorAll('.toggle-btn[data-siswa="' + siswaId + '"]');
    buttons.forEach(btn => {
        const btnStatus = btn.getAttribute('data-status');
        if (btnStatus === status) {
            // Active style
            if (status === 'T') {
                btn.style.background = '#D4A373';
                btn.style.color = '#fff';
            } else if (status === 'L') {
                btn.style.background = '#5A7D5A';
                btn.style.color = '#fff';
            } else if (status === 'TL') {
                btn.style.background = '#A85A52';
                btn.style.color = '#fff';
            }
            btn.classList.add('active');
        } else {
            // Inactive style
            btn.style.background = 'transparent';
            btn.style.color = 'var(--text-muted)';
            btn.classList.remove('active');
        }
    });
}

function lulusSemua() {
    if (confirm('Luluskan semua siswa yang masih Terdaftar (T)?')) {
        document.getElementById('form-lulus-semua').submit();
    }
}

function tidakLulusSemua() {
    if (confirm('Tidak luluskan semua siswa yang masih Terdaftar (T)?')) {
        document.getElementById('form-tidak-lulus-semua').submit();
    }
}
</script>
@endpush
