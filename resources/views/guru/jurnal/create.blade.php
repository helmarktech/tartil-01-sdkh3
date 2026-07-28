@extends('layouts.admin')
@section('title', 'Input Jurnal')

@section('content')
<div class="jurnal-mobile">
    {{-- Step 1: Pilih Kelas & Info Jurnal --}}
    @if(!$selectedKelas)
    <div class="page-header">
        <h1 class="page-title-display">Input Jurnal</h1>
        <p class="page-subtitle">Pilih kelas untuk memulai penilaian</p>
    </div>

    <form method="GET" action="{{ route('guru.jurnal.create') }}" class="card-tartil" style="padding: 20px;">
        <div class="form-group">
            <label class="form-label">Kelas *</label>
            <select name="kelas_id" class="form-input" required onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}">{{ $k->nama }} - {{ $k->mata_pelajaran }}</option>
                @endforeach
            </select>
        </div>
        <p style="font-size: 12px; color: var(--text-muted);">Pilih kelas untuk melihat daftar siswa</p>
    </form>
    @endif

    {{-- Step 2: Form Penilaian --}}
    @if($selectedKelas && $siswas->count() > 0)
    <div class="jurnal-header-sticky">
        <h1 class="page-title-display" style="font-size: 20px;">{{ $selectedKelas->nama }}</h1>
        <p class="page-subtitle" style="font-size: 12px;">{{ $siswas->count() }} siswa | Semester {{ $semester->tahun_ajaran }} {{ $semester->jenis }}</p>
    </div>

    <form method="POST" action="{{ route('guru.jurnal.store') }}" id="formJurnal">
        @csrf
        <input type="hidden" name="semester_id" value="{{ $semester->id }}">

        {{-- Info Jurnal --}}
        <div class="card-tartil" style="padding: 16px; margin-bottom: 12px;">
            <div style="display: grid; gap: 12px;">
                <div>
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="tanggal" class="form-input" required value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label">Surat *</label>
                    <input type="text" name="surat" class="form-input" required placeholder="Contoh: Al-Fatihah">
                </div>
                <div>
                    <label class="form-label">Ayat *</label>
                    <input type="text" name="ayat" class="form-input" required placeholder="Contoh: 1-7">
                </div>
                <div>
                    <label class="form-label">Materi</label>
                    <textarea name="materi" class="form-input" rows="2" placeholder="Materi yang dipelajari..."></textarea>
                </div>
                <div>
                    <label class="form-label">Jenis Penilaian *</label>
                    <select name="jenis_penilaian" class="form-input" required>
                        <option value="harian">Harian</option>
                        <option value="tengah_semester">Tengah Semester</option>
                        <option value="akhir_semester">Akhir Semester</option>
                    </select>
                </div>
                <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
            </div>
        </div>

        {{-- Batch Actions --}}
        <div class="batch-actions">
            <span style="font-size: 12px; color: var(--text-muted);">Set semua:</span>
            <button type="button" class="batch-btn" onclick="setAllNilai(85)">B=85</button>
            <button type="button" class="batch-btn" onclick="setAllNilai(80)">B=80</button>
            <button type="button" class="batch-btn" onclick="setAllAbsensi('Hadir')">Hadir</button>
            <button type="button" class="batch-btn" onclick="setAllAbsensi('Alpha')">Alpha</button>
        </div>

        {{-- Siswa Cards (Mobile Optimized) --}}
        <div id="siswaCards">
            @foreach($siswas as $i => $s)
            <div class="siswa-card" data-index="{{ $i }}">
                <div class="siswa-card-header">
                    <div class="student-avatar">{{ $s->initials }}</div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $s->nama }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">NIS: {{ $s->nis }}</div>
                    </div>
                    <div>
                        <select name="absensi[{{ $i }}][status]" class="attendance-select absensi-select" data-index="{{ $i }}" style="font-size: 11px; padding: 4px 8px;">
                            <option value="Hadir" style="color: #5A7D5A;">Hadir</option>
                            <option value="Sakit" style="color: #C4953A;">Sakit</option>
                            <option value="Izin" style="color: #5A7A8A;">Izin</option>
                            <option value="Alpha" style="color: #A85A52;">Alpha</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="penilaian[{{ $i }}][siswa_id]" value="{{ $s->id }}">
                <input type="hidden" name="absensi[{{ $i }}][siswa_id]" value="{{ $s->id }}">

                {{-- Penilaian B, C, K --}}
                <div class="nilai-grid">
                    <div>
                        <label class="nilai-label">Bacaan (B)</label>
                        <input type="number" name="penilaian[{{ $i }}][nilai_b]" class="nilai-input nilai-b" data-index="{{ $i }}" min="0" max="100" value="0" required>
                    </div>
                    <div>
                        <label class="nilai-label">Catatan (C)</label>
                        <input type="number" name="penilaian[{{ $i }}][nilai_c]" class="nilai-input nilai-c" data-index="{{ $i }}" min="0" max="100" value="0" required>
                    </div>
                    <div>
                        <label class="nilai-label">Keterampilan (K)</label>
                        <input type="number" name="penilaian[{{ $i }}][nilai_k]" class="nilai-input nilai-k" data-index="{{ $i }}" min="0" max="100" value="0" required>
                    </div>
                    <div>
                        <label class="nilai-label">Rata-rata</label>
                        <div class="nilai-rata" id="rata-{{ $i }}">0</div>
                    </div>
                </div>

                <div style="margin-top: 8px;">
                    <input type="text" name="penilaian[{{ $i }}][catatan]" class="form-input" style="font-size: 13px;" placeholder="Catatan (opsional)">
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sticky Submit Button --}}
        <div class="sticky-submit">
            <button type="submit" class="btn-tartil" style="width: 100%; justify-content: center; padding: 14px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Jurnal & Absensi
            </button>
        </div>
    </form>
    @endif
</div>

@push('styles')
<style>
    .jurnal-mobile { max-width: 800px; }
    .jurnal-header-sticky {
        position: sticky;
        top: 56px;
        background: var(--bg-page);
        padding: 12px 0;
        z-index: 40;
        border-bottom: 1px solid var(--border);
        margin-bottom: 12px;
    }
    .form-group { margin-bottom: 12px; }
    .form-label { display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 4px; }
    .form-input {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid var(--border);
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        background: var(--bg-card);
        color: var(--text-primary);
    }
    .form-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(107,94,81,0.1); }

    .batch-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .batch-btn {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-card);
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        color: var(--text-secondary);
    }
    .batch-btn:hover { background: var(--bg-hover); border-color: var(--accent); }

    .siswa-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(37,33,29,0.04);
    }
    .siswa-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .nilai-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    @media (max-width: 480px) {
        .nilai-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .nilai-label {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
        display: block;
    }
    .nilai-input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid var(--border);
        font-size: 16px; /* 16px prevents iOS zoom */
        font-weight: 600;
        text-align: center;
        font-family: 'Inter', sans-serif;
        background: var(--bg-page);
        color: var(--text-primary);
    }
    .nilai-input:focus { outline: none; border-color: var(--accent); }
    .nilai-rata {
        padding: 10px;
        border-radius: 8px;
        background: var(--bg-elevated);
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        color: var(--accent);
        font-family: 'DM Serif Display', serif;
    }

    .sticky-submit {
        position: sticky;
        bottom: 0;
        background: var(--bg-page);
        padding: 12px 0;
        z-index: 50;
        border-top: 1px solid var(--border);
        margin-top: 8px;
    }

    /* Auto color for absensi */
    .absensi-select option[value="Hadir"] { color: #5A7D5A; }
    .absensi-select option[value="Sakit"] { color: #C4953A; }
    .absensi-select option[value="Izin"] { color: #5A7A8A; }
    .absensi-select option[value="Alpha"] { color: #A85A52; }
</style>
@endpush

@push('scripts')
<script>
    // Hitung rata-rata otomatis
    document.querySelectorAll('.nilai-b, .nilai-c, .nilai-k').forEach(input => {
        input.addEventListener('input', function() {
            const idx = this.dataset.index;
            const b = parseInt(document.querySelector(`.nilai-b[data-index="${idx}"]`).value) || 0;
            const c = parseInt(document.querySelector(`.nilai-c[data-index="${idx}"]`).value) || 0;
            const k = parseInt(document.querySelector(`.nilai-k[data-index="${idx}"]`).value) || 0;
            const rata = Math.round((b + c + k) / 3);
            document.getElementById(`rata-${idx}`).textContent = rata;

            // Color code
            const rataEl = document.getElementById(`rata-${idx}`);
            if (rata >= 85) rataEl.style.color = '#5A7D5A';
            else if (rata >= 75) rataEl.style.color = '#5A7A8A';
            else if (rata >= 65) rataEl.style.color = '#8A7A6B';
            else rataEl.style.color = '#A85A52';
        });
    });

    // Batch set nilai
    function setAllNilai(val) {
        document.querySelectorAll('.nilai-b').forEach(el => { el.value = val; el.dispatchEvent(new Event('input')); });
        document.querySelectorAll('.nilai-c').forEach(el => { el.value = val; el.dispatchEvent(new Event('input')); });
        document.querySelectorAll('.nilai-k').forEach(el => { el.value = val; el.dispatchEvent(new Event('input')); });
    }

    function setAllAbsensi(status) {
        document.querySelectorAll('.absensi-select').forEach(el => el.value = status);
    }

    // Form validation
    document.getElementById('formJurnal').addEventListener('submit', function(e) {
        const surat = document.querySelector('input[name="surat"]').value;
        const ayat = document.querySelector('input[name="ayat"]').value;
        if (!surat || !ayat) {
            e.preventDefault();
            alert('Harap isi Surat dan Ayat terlebih dahulu.');
            return false;
        }
    });
</script>
@endpush
@endsection
