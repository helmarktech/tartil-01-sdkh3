@extends('layouts.admin')
@section('title', 'Import Siswa dari Excel')

@section('content')
<style>
.import-card { padding: 24px; }
.import-info { background: var(--bg-elevated); border-radius: 10px; padding: 16px; margin-bottom: 20px; }
.import-info h4 { font-size: 13px; color: var(--text-primary); margin-bottom: 10px; }
.import-info ul { font-size: 12px; color: var(--text-muted); padding-left: 18px; margin: 0; }
.import-info ul li { margin-bottom: 4px; }
.required { color: #C62828; font-weight: 600; }
.error-list { max-height: 300px; overflow-y: auto; }
.error-item { padding: 8px 12px; border-bottom: 1px solid var(--border); font-size: 12px; color: #A85A52; }
.error-item:last-child { border-bottom: none; }
</style>

<div>
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Import Siswa</h1>
            <p class="page-subtitle">Unggah file Excel untuk menambahkan siswa baru</p>
        </div>
        <a href="{{ route('admin.siswa.penempatan') }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px;">→ Penempatan Tartil</a>
    </div>

    {{-- Info --}}
    <div class="import-info">
        <h4>Format Kolom Wajib (Header Baris Pertama)</h4>
        <ul>
            <li><span class="required">NIS*</span> — Nomor Induk Siswa (unik)</li>
            <li><span class="required">NAMA*</span> — Nama lengkap siswa</li>
            <li><span class="required">JENIS_KELAMIN*</span> — L atau P</li>
            <li><span class="required">KELAS_NAMA*</span> — Nama kelas reguler (contoh: 1A)</li>
            <li><span class="required">KELAS_JENJANG*</span> — Angka jenjang (1, 2, 3...)</li>
            <li><span class="required">KELAS_TINGKAT*</span> — Huruf tingkat (A, B, C...)</li>
            <li><span class="required">TANGGAL_LAHIR*</span> — Format YYYY-MM-DD</li>
            <li><span class="required">TEMPAT_LAHIR*</span> — Kota/kabupaten lahir</li>
            <li><span class="required">NAMA_AYAH*</span> — Nama ayah kandung/wali</li>
            <li>NO_HP — Nomor HP (opsional)</li>
            <li>TANGGAL_MASUK — Format YYYY-MM-DD (opsional, default hari ini)</li>
        </ul>
        <div style="margin-top: 10px; font-size: 11px; color: var(--text-muted);">
            Kelas tartil akan diisi nanti melalui menu <strong>Penempatan Tartil</strong>.
            Pastikan data kelas reguler sudah dibuat sebelum import.
            <br><br>
            <strong>Catatan:</strong> File akan diunggah dan diproses di background oleh queue worker. Untuk data besar (lebih dari 500 baris), proses membutuhkan waktu beberapa menit. Hasil bisa dicek di log aplikasi.
        </div>
    </div>

    {{-- Download Template --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.siswa.import.template') }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template Excel
        </a>
    </div>

    {{-- Upload Form --}}
    <div class="card-tartil import-card">
        <form method="POST" action="{{ route('admin.siswa.import.proses') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 12px;">Pilih File Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-input" style="padding: 8px; font-size: 13px;" required>
                @error('file')
                <div style="color: #C62828; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-tartil" style="font-size: 13px; padding: 10px 24px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Data
            </button>
        </form>
    </div>

    {{-- Error List --}}
    @if(session('import_errors'))
    <div class="card-tartil" style="padding: 0; margin-top: 20px; overflow: hidden;">
        <div style="padding: 12px 16px; background: #FFEBEE; border-bottom: 1px solid #E8A0A0;">
            <strong style="font-size: 13px; color: #A85A52;">Detail Error ({{ count(session('import_errors')) }} baris):</strong>
        </div>
        <div class="error-list">
            @foreach(session('import_errors') as $err)
            <div class="error-item">{{ $err }}</div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
