@extends('layouts.admin')
@section('title', 'Isi Nilai: ' . $penilaian->nama . ' - ' . $kelas->nama)

@section('content')
<style>
.matrix-table { font-size: 12px; border-collapse: collapse; width: 100%; }
.matrix-table th {
    background: var(--surface);
    padding: 6px 4px;
    text-align: center;
    font-size: 10px;
    font-weight: 600;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
    min-width: 60px;
}
.matrix-table th:first-child,
.matrix-table th:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 2;
    background: var(--surface);
    min-width: 30px;
}
.matrix-table th:nth-child(2) { left: 30px; min-width: 140px; }
.matrix-table td {
    padding: 4px 3px;
    border-bottom: 1px solid #f0ece6;
    vertical-align: middle;
}
.matrix-table td:first-child,
.matrix-table td:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 1;
    background: #fff;
    font-weight: 600;
}
.matrix-table td:nth-child(2) { left: 30px; }
.matrix-table tr:hover td { background: #faf8f5; }
.matrix-table tr:hover td:first-child,
.matrix-table tr:hover td:nth-child(2) { background: #faf8f5; }
.matrix-input {
    width: 55px;
    padding: 4px 2px;
    font-size: 12px;
    text-align: center;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    transition: border-color 0.2s;
}
.matrix-input:focus { border-color: var(--accent); outline: none; }
.matrix-input.filled { border-color: #5A7D5A; background: #f0f7f0; }
.ind-label {
    display: block;
    max-width: 80px;
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    margin: 0 auto;
    font-size: 9px;
}
.d-none { display: none !important; }
</style>

<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $penilaian->nama }}</h1>
            <p class="page-subtitle">{{ $kelas->nama }} ({{ $kelas->jenis }}) — {{ $siswaLengkap }}/{{ $totalSiswa }} siswa lengkap — {{ $indikators->count() }} indikator</p>
        </div>
        <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $penilaian->id) }}" class="btn-tartil-outline" style="text-decoration: none;">Pilih Kelas Lain</a>
    </div>

    {{-- Import Nilai via Excel (hanya kelas ini) --}}
    <div class="card-tartil" style="margin-bottom: 16px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 12px; color: var(--text-muted);">
            Download template berisi {{ $totalSiswa }} siswa kelas ini, isi nilai per indikator, lalu upload kembali.
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('guru.penilaian-rapor.import.template', [$penilaian->id, $kelas->id]) }}" class="btn-tartil-outline" style="font-size: 12px; padding: 6px 14px; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Template Import
            </a>
            <button type="button" class="btn-tartil" style="font-size: 12px; padding: 6px 14px;" onclick="document.getElementById('formImportNilaiRapor').classList.toggle('d-none');">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Nilai
            </button>
        </div>
    </div>

    <div id="formImportNilaiRapor" class="{{ session('import_errors') ? '' : 'd-none' }}" style="margin-bottom: 16px;">
        <form method="POST" action="{{ route('guru.penilaian-rapor.import.proses', [$penilaian->id, $kelas->id]) }}" enctype="multipart/form-data" class="card-tartil" style="padding: 14px 16px;">
            @csrf
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <input type="file" name="file" accept=".xlsx,.xls" class="form-input" style="flex: 1; min-width: 220px; font-size: 12px;" required>
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 20px;">Proses Import</button>
            </div>
            @if(session('import_errors'))
            <div style="margin-top: 12px; padding: 10px 14px; background: #FDF2F0; border-radius: 8px; font-size: 12px; color: #A85A52;">
                <strong>Baris yang dilewati:</strong>
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </form>
    </div>

    {{-- Progress --}}
    <div class="card-tartil" style="margin-bottom: 16px; padding: 14px;">
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">
            <span>Progress: {{ $siswaLengkap }}/{{ $totalSiswa }} siswa lengkap</span>
            <span>{{ $progress }}%</span>
        </div>
        <div style="width: 100%; height: 6px; background: var(--surface); border-radius: 3px; overflow: hidden;">
            <div style="width: {{ $progress }}%; height: 100%; background: {{ $progress == 100 ? '#5A7D5A' : 'var(--accent)' }}; border-radius: 3px;"></div>
        </div>
    </div>

    {{-- Form Nilai Matrix --}}
    <div class="card-tartil" style="padding: 16px;">
        <form method="POST" action="{{ route('guru.penilaian-rapor.simpan-nilai', [$penilaian->id, $kelas->id]) }}">
            @csrf
            <div style="overflow-x: auto; margin: -16px; padding: 16px;">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">No</th>
                            <th style="min-width: 140px; text-align: left; padding-left: 8px;">Nama</th>
                            @foreach($indikators as $ind)
                            <th>
                                <span class="ind-label" title="{{ $ind->nama_indikator }}">
                                    {{ $ind->nama_indikator }}
                                </span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $i => $s)
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td style="padding-left: 8px; white-space: nowrap;">
                                {{ $s->nama }}
                                <span style="font-size: 9px; color: var(--text-muted); display: block;">{{ $s->nis }}</span>
                            </td>
                            @foreach($indikators as $ind)
                            @php
                                $val = $nilaiMap[$s->id][$ind->id] ?? '';
                                $isFilled = $val !== '' && $val !== null;
                            @endphp
                            <td style="text-align: center;">
                                <input type="number"
                                    name="nilai[{{ $s->id }}][{{ $ind->id }}]"
                                    value="{{ $val }}"
                                    min="1"
                                    max="100"
                                    placeholder="-"
                                    class="matrix-input {{ $isFilled ? 'filled' : '' }}"
                                    onchange="this.classList.toggle('filled', this.value !== '')">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;">
                <button type="submit" class="btn-tartil">Simpan Nilai</button>
                <a href="{{ route('guru.penilaian-rapor.pilih-kelas', $penilaian->id) }}" class="btn-tartil-outline" style="text-decoration: none;">Pilih Kelas Lain</a>
            </div>
        </form>
    </div>
</div>
@endsection
