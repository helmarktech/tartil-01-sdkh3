@extends('layouts.admin')
@section('title', 'Track Record Nilai Rapor')

@section('content')
<style>
.filter-card {
    padding: 16px 20px;
    margin-bottom: 20px;
}
.siswa-list {
    max-height: 65vh;
    overflow-y: auto;
}
.siswa-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    margin-bottom: 6px;
    transition: all 0.15s;
    border: 1px solid transparent;
}
.siswa-item:hover {
    background: var(--bg-hover);
    border-color: var(--border);
}
.siswa-item.active {
    background: var(--bg-hover);
    border-color: var(--accent);
}
.siswa-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
    flex-shrink: 0;
}
.semester-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 16px;
}
.semester-header {
    padding: 14px 18px;
    background: linear-gradient(90deg, var(--accent) 0%, var(--accent-hover) 100%);
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.semester-nama { font-weight: 600; font-size: 14px; }
.semester-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    background: rgba(255,255,255,0.2);
}
.nilai-table {
    width: 100%;
    font-size: 13px;
    border-collapse: collapse;
}
.nilai-table th {
    padding: 10px 12px;
    background: var(--bg-elevated);
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    color: var(--text-secondary);
    text-align: center;
}
.nilai-table th:first-child { text-align: left; padding-left: 16px; }
.nilai-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    text-align: center;
}
.nilai-table td:first-child { text-align: left; padding-left: 16px; font-weight: 500; }
.nilai-table tbody tr:last-child td { border-bottom: none; }
.nilai-angka { font-size: 14px; font-weight: 600; }
.nilai-angka.a { color: #5A7D5A; }
.nilai-angka.b { color: #8B9A4A; }
.nilai-angka.c { color: #C4953A; }
.nilai-angka.k { color: #A85A52; }
.nilai-angka.na { color: #CCC; font-weight: 400; }
.rata-box {
    display: flex;
    gap: 20px;
    padding: 12px 18px;
    background: var(--bg-elevated);
    border-top: 1px solid var(--border);
}
.rata-item { text-align: center; }
.rata-item .nilai { font-size: 20px; font-weight: 700; }
.rata-item .label { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.rata-bar {
    width: 50px;
    height: 4px;
    background: #DDD;
    border-radius: 2px;
    margin: 3px auto 0;
    overflow: hidden;
}
.rata-fill { height: 100%; border-radius: 2px; }

@media (max-width: 768px) {
    .layout-grid { grid-template-columns: 1fr !important; }
    .siswa-sidebar { margin-bottom: 16px; }
}
</style>

<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Track Record Nilai Rapor</h1>
            <p class="page-subtitle">Riwayat nilai rapor siswa per semester (selamanya)</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-tartil filter-card">
        @if($isAdmin)
        {{-- Admin: Search nama/NIS --}}
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Cari Nama atau NIS Siswa</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-input" placeholder="Ketik nama atau NIS..." style="font-size: 13px;">
            </div>
            <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 16px; align-self: flex-end;">Cari</button>
            @if($search)
            <a href="{{ route('guru.penilaian-rapor.track-record') }}" class="btn-tartil-outline" style="font-size: 12px; padding: 8px 16px; align-self: flex-end; text-decoration: none;">Reset</a>
            @endif
        </form>
        @else
        {{-- Guru: Pilih kelas dulu --}}
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Pilih Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="font-size: 13px;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
        @endif
    </div>

    {{-- Layout: Sidebar + Content --}}
    <div class="layout-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 16px;">
        {{-- Sidebar: Daftar Siswa --}}
        <div class="siswa-sidebar">
            <div class="card-tartil" style="padding: 14px;">
                <h3 style="font-size: 13px; color: var(--text-primary); margin-bottom: 10px; font-weight: 600;">
                    @if($isAdmin)
                        Hasil Pencarian ({{ $siswaList->count() }})
                    @else
                        Siswa {{ $kelasId ? 'di Kelas Ini' : '(Pilih Kelas Dulu)' }}
                    @endif
                </h3>
                <div class="siswa-list">
                    @forelse($siswaList as $s)
                    <a href="{{ route('guru.penilaian-rapor.track-record', [
                        'kelas_id' => $isAdmin ? ($s->kelas_tartil_id ?? '') : $kelasId,
                        'siswa_id' => $s->id,
                        'search' => $search ?? ''
                    ]) }}"
                       class="siswa-item {{ $siswaId == $s->id ? 'active' : '' }}">
                        <div class="siswa-avatar">{{ substr($s->nama, 0, 2) }}</div>
                        <div style="min-width: 0;">
                            <div style="font-weight: 500; font-size: 13px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $s->nama }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $s->nis }}</div>
                        </div>
                    </a>
                    @empty
                    <div style="color: var(--text-muted); font-size: 12px; text-align: center; padding: 24px;">
                        @if($isAdmin)
                            Ketik nama atau NIS untuk mencari siswa
                        @else
                            Pilih kelas untuk melihat daftar siswa
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Content: Track Record --}}
        <div>
            @if($siswaPilih)
            <div style="margin-bottom: 16px;">
                <h2 style="font-size: 18px; color: var(--text-primary); font-weight: 600;">{{ $siswaPilih->nama }}</h2>
                <div style="font-size: 12px; color: var(--text-muted);">
                    NIS: {{ $siswaPilih->nis }} | Kelas: {{ $siswaPilih->kelasTartil?->nama ?? '-' }}
                </div>
            </div>

            @forelse($riwayatPerSemester as $r)
            <div class="semester-card">
                <div class="semester-header">
                    <div class="semester-nama">{{ $r['semester']->nama ?? '-' }}</div>
                    <span class="semester-badge">{{ $r['status'] === 'aktif' ? 'Aktif' : 'Selesai' }}</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="nilai-table">
                        <thead>
                            <tr>
                                <th>INDIKATOR</th>
                                <th style="width: 60px;">NILAI</th>
                                <th style="width: 50px;">GRADE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($r['nilai_lengkap'] as $n)
                            @php
                                $angka = $n['nilai_angka'];
                                $huruf = $n['nilai_huruf'];
                                $cls = match(strtoupper($huruf)) {
                                    'A' => 'a', 'B' => 'b', 'C' => 'c', 'K' => 'k',
                                    default => 'na',
                                };
                                $isKosong = $angka === 0 && $huruf === 'K';
                            @endphp
                            <tr>
                                <td>{{ $n['indikator']->nama_indikator }}</td>
                                <td>
                                    <span class="nilai-angka {{ $isKosong ? 'na' : $cls }}">
                                        {{ $isKosong ? '0' : $angka }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-subject" style="font-size: 11px; background: {{ $cls === 'a' ? '#E9F0E9' : ($cls === 'b' ? '#FFF8E1' : ($cls === 'c' ? '#FFF3E0' : ($cls === 'k' ? '#FFEBEE' : '#F5F5F5'))) }}; color: {{ $cls === 'a' ? '#5A7D5A' : ($cls === 'b' ? '#8B9A4A' : ($cls === 'c' ? '#C4953A' : ($cls === 'k' ? '#A85A52' : '#999'))) }};">
                                        {{ $huruf }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="rata-box">
                    <div class="rata-item">
                        <div class="nilai" style="color: {{ $r['rata_nilai_rapor'] >= 85 ? '#5A7D5A' : ($r['rata_nilai_rapor'] >= 70 ? '#8B9A4A' : ($r['rata_nilai_rapor'] >= 60 ? '#C4953A' : '#A85A52')) }};">
                            {{ $r['rata_nilai_rapor'] }}
                        </div>
                        <div class="label">R2 Rapor</div>
                        <div class="rata-bar">
                            <div class="rata-fill" style="width: {{ min($r['rata_nilai_rapor'], 100) }}%; background: {{ $r['rata_nilai_rapor'] >= 85 ? '#5A7D5A' : ($r['rata_nilai_rapor'] >= 70 ? '#8B9A4A' : ($r['rata_nilai_rapor'] >= 60 ? '#C4953A' : '#A85A52')) }};"></div>
                        </div>
                    </div>
                    <div class="rata-item">
                        <div class="nilai" style="color: {{ $r['rata_nilai_harian'] >= 85 ? '#5A7D5A' : ($r['rata_nilai_harian'] >= 70 ? '#8B9A4A' : ($r['rata_nilai_harian'] >= 60 ? '#C4953A' : '#A85A52')) }};">
                            {{ $r['rata_nilai_harian'] > 0 ? $r['rata_nilai_harian'] : '-' }}
                        </div>
                        <div class="label">R2 Harian</div>
                        <div class="rata-bar">
                            <div class="rata-fill" style="width: {{ min($r['rata_nilai_harian'], 100) }}%; background: {{ $r['rata_nilai_harian'] >= 85 ? '#5A7D5A' : ($r['rata_nilai_harian'] >= 70 ? '#8B9A4A' : ($r['rata_nilai_harian'] >= 60 ? '#C4953A' : '#A85A52')) }};"></div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card-tartil" style="text-align: center; padding: 48px;">
                <div style="color: var(--text-muted);">Belum ada riwayat nilai rapor untuk siswa ini.</div>
            </div>
            @endforelse
            @else
            <div class="card-tartil" style="text-align: center; padding: 64px;">
                <div style="color: var(--text-muted); margin-bottom: 8px;">
                    @if($isAdmin)
                        Cari siswa dengan nama atau NIS di panel kiri
                    @else
                        Pilih kelas, lalu pilih siswa untuk melihat track record
                    @endif
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">Data nilai rapor tersimpan selamanya di sistem</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
