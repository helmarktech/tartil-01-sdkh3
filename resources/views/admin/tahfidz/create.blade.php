@extends('layouts.admin')

@section('title', 'Tambah Hafalan - ' . $siswa->nama)

@section('content')
<div style="max-width: 640px; margin: 0 auto;">
    <div style="font-size: 13px; color: #666; margin-bottom: 20px;">
        <a href="{{ route('admin.tahfidz.detail-siswa', $siswa->id) }}" style="color: #0c8a5f; text-decoration: none; font-weight: 500;">&larr; Kembali</a>
    </div>

    <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 24px; margin-bottom: 4px;">Tambah Hafalan</h1>
    <p style="color: #666; font-size: 14px; margin-bottom: 24px;">{{ $siswa->nama }} &middot; {{ $siswa->kelasTartil?->nama ?? '-' }}</p>

    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 24px;">
        <form method="POST" action="{{ route('admin.tahfidz.hafalan.store') }}">
            @csrf
            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
            <input type="hidden" name="kelas_id" value="{{ $siswa->kelas_tartil_id }}">
            @if($semester)
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Juz <span style="color:#c62828">*</span></label>
                    <select name="juz" class="form-input" required>
                        @for($j = 1; $j <= 30; $j++)
                            <option value="{{ $j }}">Juz {{ $j }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Surat</label>
                    <select name="surat_id" class="form-input">
                        <option value="">- Pilih Surat -</option>
                        @foreach($suratList as $surat)
                            <option value="{{ $surat->id }}">{{ $surat->urutan }}. {{ $surat->nama_latin }} ({{ $surat->jenis }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Ayat Mulai <span style="color:#c62828">*</span></label>
                    <input type="number" name="ayat_mulai" class="form-input" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Ayat Selesai</label>
                    <input type="number" name="ayat_selesai" class="form-input" min="1" placeholder="Opsional">
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Status <span style="color:#c62828">*</span></label>
                    <select name="status" class="form-input" required>
                        <option value="baru">Baru</option>
                        <option value="setengah_hafal">Setengah Hafal</option>
                        <option value="hafal" selected>Hafal</option>
                        <option value="murajaah">Murojaah</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Kualitas <span style="color:#c62828">*</span></label>
                    <select name="kualitas" class="form-input" required>
                        <option value="mumtaz">Mumtaz (Sempurna)</option>
                        <option value="jayyid_jiddan" selected>Jayyid Jiddan (Sangat Baik)</option>
                        <option value="jayyid">Jayyid (Baik)</option>
                        <option value="naqis">Naqis (Kurang)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 13px;">Tanggal Hafalan <span style="color:#c62828">*</span></label>
                    <input type="date" name="tanggal_hafalan" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" style="font-size: 13px;">Catatan</label>
                <textarea name="catatan" class="form-input" rows="2" placeholder="Catatan opsional..."></textarea>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 20px;">
                <button type="submit" class="btn-tartil">Simpan Hafalan</button>
                <a href="{{ route('admin.tahfidz.detail-siswa', $siswa->id) }}" class="btn-tartil-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
