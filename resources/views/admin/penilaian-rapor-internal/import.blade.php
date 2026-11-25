@extends('layouts.admin')
@section('title', 'Import Nilai Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Import Nilai Rapor</h1>
            <p class="page-subtitle">Import nilai penilaian rapor internal massal via Excel — template otomatis terisi data siswa per kelas tartil</p>
        </div>
    </div>

    {{-- STEP 1: Pilih Penilaian --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Step 1: Pilih Penilaian</h3>
        <form method="GET" action="{{ route('admin.penilaian-rapor-internal.import') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 250px;">
                <select name="penilaian_id" class="form-input" required onchange="this.form.submit()">
                    <option value="" disabled {{ !$penilaianTerpilih ? 'selected' : '' }}>-- Pilih Penilaian --</option>
                    @foreach($penilaians as $p)
                    <option value="{{ $p->id }}" {{ $penilaianTerpilih && $penilaianTerpilih->id == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->semester->nama ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
            @if($penilaianTerpilih)
            <a href="{{ route('admin.penilaian-rapor-internal.import') }}" class="btn-tartil-outline" style="text-decoration: none;">Pilih Ulang</a>
            @endif
        </form>

        @if($penilaianTerpilih)
        <div style="margin-top: 12px; padding: 10px 14px; background: #E9F0E9; border-radius: 8px; font-size: 13px; color: #5A7D5A;">
            <strong>Penilaian terpilih:</strong> {{ $penilaianTerpilih->nama }} — Semester {{ $penilaianTerpilih->semester->nama ?? '-' }}
        </div>
        @endif
    </div>

    {{-- STEP 2: Pilih Kelas Tartil --}}
    @if($penilaianTerpilih)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Step 2: Pilih Kelas Tartil</h3>
        <form method="GET" action="{{ route('admin.penilaian-rapor-internal.import') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="penilaian_id" value="{{ $penilaianTerpilih->id }}">
            <div style="flex: 1; min-width: 250px;">
                <select name="kelas_id" class="form-input" required onchange="this.form.submit()">
                    <option value="" disabled {{ !$kelasTerpilih ? 'selected' : '' }}>-- Pilih Kelas Tartil --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasTerpilih && $kelasTerpilih->id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->jenis }}) — {{ $k->guru->nama ?? '-' }} — {{ $k->siswas_count }} siswa
                    </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($kelasTerpilih)
        <div style="margin-top: 12px; padding: 10px 14px; background: #E9F0E9; border-radius: 8px; font-size: 13px; color: #5A7D5A;">
            <strong>Kelas terpilih:</strong> {{ $kelasTerpilih->nama }} ({{ $kelasTerpilih->jenis }}) — {{ $kelasTerpilih->siswas_count }} siswa aktif — {{ $indikators->count() }} indikator:
            {{-- Daftar indikator bernomor, maksimal 7 baris per kolom — sisanya lanjut ke kolom samping --}}
            <div style="display: flex; flex-wrap: wrap; gap: 8px 32px; margin-top: 8px;">
                @foreach($indikators->chunk(7) as $chunk)
                <ol start="{{ ($loop->index * 7) + 1 }}" style="margin: 0; padding-left: 20px; min-width: 220px; flex: 1;">
                    @foreach($chunk as $ind)
                    <li style="line-height: 1.7;">{{ $ind->nama_indikator }}</li>
                    @endforeach
                </ol>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- STEP 3: Download Template & Upload --}}
    @if($penilaianTerpilih && $kelasTerpilih)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Step 3: Download Template & Upload Nilai</h3>

        @if($indikators->isEmpty())
        <div style="padding: 10px 14px; background: #FDF2F0; border-radius: 8px; font-size: 13px; color: #A85A52;">
            Belum ada indikator untuk jenis kelas "{{ $kelasTerpilih->jenis }}". Atur dulu di menu Pengaturan Kelas.
        </div>
        @else
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 16px;">
            <a href="{{ route('admin.penilaian-rapor-internal.import.template', [$penilaianTerpilih, $kelasTerpilih]) }}" class="btn-tartil-outline" style="text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Template ({{ $kelasTerpilih->siswas_count }} siswa)
            </a>
            <span style="font-size: 12px; color: var(--text-muted);">Template sudah berisi seluruh siswa aktif kelas ini beserta nilai yang sudah ada.</span>
        </div>

        <form method="POST" action="{{ route('admin.penilaian-rapor-internal.import.proses', [$penilaianTerpilih, $kelasTerpilih]) }}" enctype="multipart/form-data">
            @csrf
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <input type="file" name="file" accept=".xlsx,.xls" class="form-input" style="flex: 1; min-width: 220px; font-size: 12px;" required>
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 20px;">Proses Import</button>
            </div>
            <p style="font-size: 11px; color: var(--text-muted); margin: 8px 0 0;">
                Isi nilai angka bulat 1-100 pada kolom indikator; kosongkan sel yang belum dinilai. Jangan mengubah kolom ID Siswa/NIS/Nama maupun header kolom.
            </p>
        </form>

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
        @endif
    </div>
    @elseif($penilaianTerpilih)
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Pilih kelas tartil pada Step 2 untuk melanjutkan.</p>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128229;</div>
        <h3 style="font-size: 16px; color: var(--text-primary); margin-bottom: 8px;">Silakan Pilih Penilaian</h3>
        <p style="color: var(--text-muted); font-size: 14px; max-width: 420px; margin: 0 auto;">
            Pilih penilaian rapor dari dropdown di atas, lalu pilih kelas tartil. Sistem akan membuat template Excel yang sudah terisi data siswa sesuai jumlah siswa terdaftar di kelas tersebut.
        </p>
    </div>
    @endif
</div>
@endsection
