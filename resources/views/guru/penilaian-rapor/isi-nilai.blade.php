@extends('layouts.admin')
@section('title', 'Isi Nilai - ' . $kelas->nama)

@section('content')
<style>
/* ── Progress ── */
.progress-track {
    width: 100%;
    height: 8px;
    background: var(--bg-elevated);
    border-radius: 4px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* ── Siswa Card ── */
.siswa-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 14px;
    box-shadow: 0 1px 3px rgba(37,33,29,0.04);
}
.siswa-header {
    padding: 14px 18px;
    background: linear-gradient(90deg, var(--bg-elevated) 0%, var(--bg-hover) 100%);
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.siswa-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.siswa-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--accent);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}
.siswa-nama {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
}
.siswa-count {
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: var(--bg-elevated);
    color: var(--text-muted);
}
.siswa-count.selesai { background: #E9F0E9; color: var(--success); }
.siswa-count.proses { background: #FFF3E0; color: var(--warning); }

/* ── Indikator Row ── */
.indikator-list {
    padding: 14px 18px;
}
.indikator-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.indikator-row:last-child {
    margin-bottom: 0;
}
.indikator-label {
    flex: 1;
    font-size: 13px;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    padding-right: 4px;
}
.nilai-input {
    width: 70px;
    height: 42px;
    text-align: center;
    padding: 6px;
    font-size: 15px;
    font-weight: 600;
    border: 2px solid var(--border);
    border-radius: 10px;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    flex-shrink: 0;
    -webkit-appearance: none;
}
.nilai-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(107,94,81,0.12);
}
.nilai-input:read-only {
    background: var(--bg-elevated);
    border-color: var(--border);
    color: var(--text-muted);
    cursor: not-allowed;
}
.nilai-input:read-only:focus {
    border-color: var(--border);
    box-shadow: none;
}
.nilai-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
    transition: all 0.2s;
}

/* ── Sticky Footer ── */
.sticky-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: var(--bg-card);
    padding: 12px 16px;
    border-top: 1px solid var(--border);
    margin: 20px -16px -16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    box-shadow: 0 -4px 16px rgba(37,33,29,0.06);
}

/* ── Legend ── */
.legend-bar {
    display: flex;
    gap: 16px;
    font-size: 11px;
    color: var(--text-muted);
    flex-wrap: wrap;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
}
.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
</style>

<div>
    {{-- Header (non-sticky) --}}
    <div style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $semesterPenilaian->semester->nama ?? '-' }}</div>
                <div style="font-weight: 600; color: var(--text-primary); font-size: 16px;">{{ $kelas->nama }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($isLocked)
                <span class="badge-subject" style="background: #FFEBEE; color: #A85A52; font-size: 11px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 3px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Terkunci
                </span>
                @endif
                <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $semesterPenilaian->id) }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 11px; padding: 6px 12px;">← Kelas Lain</a>
            </div>
        </div>

        @if($isLocked)
        <div class="card-tartil" style="padding: 12px 16px; margin-bottom: 12px; background: #FFEBEE; border-color: #E8A0A0;">
            <div style="font-size: 12px; color: #A85A52; font-weight: 500;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                {{ $semesterPenilaian->lockReason() }}. Nilai rapor tidak dapat diubah.
            </div>
        </div>
        @endif

        {{-- Progress --}}
        <div class="card-tartil" style="padding: 12px 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">Progress</span>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $progressPersen }}%; background: {{ $progressPersen >= 80 ? 'var(--success)' : ($progressPersen >= 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                </div>
                <strong style="font-size: 13px; color: var(--text-primary); white-space: nowrap;">{{ $progressPersen }}%</strong>
                <span style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $filledEntry }}/{{ $totalEntry }}</span>
            </div>
        </div>

        {{-- Legend --}}
        <div class="legend-bar" style="margin-top: 10px;">
            <span class="legend-item"><span class="legend-dot" style="background: var(--success);"></span> A 85-100</span>
            <span class="legend-item"><span class="legend-dot" style="background: #8B9A4A;"></span> B 70-84</span>
            <span class="legend-item"><span class="legend-dot" style="background: var(--warning);"></span> C 60-69</span>
            <span class="legend-item"><span class="legend-dot" style="background: var(--danger);"></span> K &lt;60</span>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('guru.penilaian-rapor.simpan-nilai', [$semesterPenilaian->id, $kelas->id]) }}" style="padding-bottom: 80px;">
        @csrf

        @foreach($siswas as $i => $siswa)
        @php
            $siswaFilled = 0;
            foreach ($nilaiMap[$siswa->id] as $d) { if ($d['is_diisi']) $siswaFilled++; }
            $siswaTotal = count($nilaiMap[$siswa->id]);
            $siswaPersen = $siswaTotal > 0 ? round(($siswaFilled / $siswaTotal) * 100) : 0;
        @endphp
        <div class="siswa-card">
            {{-- Card Header --}}
            <div class="siswa-header">
                <div class="siswa-header-left">
                    <div class="siswa-number">{{ $i + 1 }}</div>
                    <span class="siswa-nama">{{ $siswa->nama }}</span>
                </div>
                <span class="siswa-count {{ $siswaPersen === 100 ? 'selesai' : ($siswaPersen > 0 ? 'proses' : '') }}">
                    {{ $siswaFilled }}/{{ $siswaTotal }}
                </span>
            </div>

            {{-- Indikator Inputs --}}
            <div class="indikator-list">
                @foreach($indikators as $ind)
                @php $n = $nilaiMap[$siswa->id][$ind->id] ?? ['nilai_angka' => null, 'is_diisi' => false]; @endphp
                <div class="indikator-row">
                    <label class="indikator-label" title="{{ $ind->nama_indikator }}">{{ $ind->nama_indikator }}</label>
                    <input type="number"
                           name="nilai[{{ $siswa->id }}][{{ $ind->id }}]"
                           value="{{ $n['nilai_angka'] }}"
                           min="0" max="100"
                           placeholder="0"
                           inputmode="numeric"
                           class="nilai-input"
                           {{ $isLocked ? 'readonly' : '' }}
                           oninput="updateBadge(this)">
                    @if($n['is_diisi'] && $n['nilai_angka'] !== null)
                    <span class="nilai-badge" style="background: {{ $n['nilai_angka'] >= 85 ? '#E9F0E9' : ($n['nilai_angka'] >= 70 ? '#FFF8E1' : ($n['nilai_angka'] >= 60 ? '#FFF3E0' : '#FFEBEE')) }}; color: {{ $n['nilai_angka'] >= 85 ? 'var(--success)' : ($n['nilai_angka'] >= 70 ? '#8B9A4A' : ($n['nilai_angka'] >= 60 ? 'var(--warning)' : 'var(--danger)')) }};">
                        {{ \App\Models\PenilaianRapor::angkaKeHuruf($n['nilai_angka']) }}
                    </span>
                    @else
                    <span class="nilai-badge" style="background: transparent; color: transparent;"></span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if($isLocked)
        {{-- Locked: no save button --}}
        <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Nilai rapor terkunci — hanya dapat dilihat
        </div>
        @else
        {{-- Sticky Footer --}}
        <div class="sticky-footer">
            <span style="font-size: 11px; color: var(--text-muted);">Kosong = diisi K otomatis</span>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $semesterPenilaian->id) }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px; padding: 10px 18px;">Batal</a>
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 10px 24px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan
                </button>
            </div>
        </div>
        @endif
    </form>
</div>

<script>
function updateBadge(input) {
    const val = parseInt(input.value);
    let badge = input.parentElement.querySelector('.nilai-badge');
    if (!badge) return;

    if (isNaN(val) || input.value === '') {
        badge.style.background = 'transparent';
        badge.style.color = 'transparent';
        badge.textContent = '';
        return;
    }

    let bg, color, huruf;
    if (val >= 85) { bg = '#E9F0E9'; color = '#5A7D5A'; huruf = 'A'; }
    else if (val >= 70) { bg = '#FFF8E1'; color = '#8B9A4A'; huruf = 'B'; }
    else if (val >= 60) { bg = '#FFF3E0'; color = '#C4953A'; huruf = 'C'; }
    else { bg = '#FFEBEE'; color = '#A85A52'; huruf = 'K'; }

    badge.style.background = bg;
    badge.style.color = color;
    badge.textContent = huruf;
}
</script>
@endsection
