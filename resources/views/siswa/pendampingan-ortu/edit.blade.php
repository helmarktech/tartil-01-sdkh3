@extends('layouts.siswa')

@section('title', 'Edit Laporan Pendampingan')

@section('content')
<style>
.po-section {
    background: #fff; border: 1px solid #e7e5e4; border-radius: 12px;
    padding: 20px; margin-bottom: 16px;
}
.po-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #78716c; margin: 0 0 16px; }
.po-form-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
}
@media (max-width: 640px) {
    .po-form-grid { grid-template-columns: 1fr; }
}
.po-form-group { display: flex; flex-direction: column; gap: 6px; }
.po-label { font-size: 12px; color: #78716c; font-weight: 500; }
.po-input, .po-select, .po-textarea {
    padding: 10px 12px; border: 1px solid #e7e5e4; border-radius: 8px;
    font-size: 14px; font-family: inherit; background: #fff; color: #1c1917;
}
.po-textarea { min-height: 80px; resize: vertical; }
.po-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 12px 20px; background: #0c8a5f; color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; width: 100%;
}
.po-btn:hover { background: #0a6b4a; }
.po-btn-cancel {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 12px 20px; background: #f5f5f4; color: #44403c;
    border: 1px solid #e7e5e4; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; width: 100%; box-sizing: border-box;
}
.po-btn-cancel:hover { background: #e7e5e4; }
.po-warning {
    background: #fff3cd; border: 1px solid #ffeeba; color: #856404;
    border-radius: 10px; padding: 12px 14px; font-size: 13px; margin-bottom: 16px;
}
.po-error { font-size: 12px; color: #b91c1c; }
</style>

<div class="siswa-page-header">
    <div class="siswa-page-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </div>
    <div>
        <h1 class="siswa-page-title">Edit Laporan Pendampingan</h1>
        <p class="siswa-page-subtitle">Perbarui laporan kegiatan tadarus / murajaah</p>
    </div>
</div>

<div class="po-section">
    <h2 class="po-title">Form Edit Laporan</h2>

    @if($laporan->isDikonfirmasi())
        <div class="po-warning">
            <strong>Laporan ini sudah dikonfirmasi.</strong><br>
            Menyimpan perubahan akan mengembalikan status ke <em>Pengajuan Konfirmasi</em> dan guru akan mengonfirmasi ulang laporan ini.
        </div>
    @endif

    <form method="POST" action="{{ route('siswa.pendampingan-ortu.update', $laporan) }}">
        @csrf
        @method('PUT')
        <div style="margin-bottom: 14px;">
            <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #44403c; cursor: pointer; background: #f0faf5; border: 1px solid #d1eadd; border-radius: 10px; padding: 12px 14px;">
                <input type="checkbox" name="is_jilid" id="cbJilid" value="1" {{ old('is_jilid', $laporan->is_jilid) ? 'checked' : '' }} style="width: 17px; height: 17px; margin-top: 1px; accent-color: #0c8a5f; flex-shrink: 0;">
                <span><strong>Kelas Jilid / Bilqolam</strong><br>
                <span style="font-size: 12px; color: #78716c;">Centang jika pendampingan membaca jilid/bilqolam — tidak perlu mengisi surat dan ayat, cukup jenis kegiatan dan catatan.</span></span>
            </label>
        </div>
        <div class="po-form-grid">
            <div class="po-form-group">
                <label class="po-label">Jenis Kegiatan *</label>
                <select name="jenis" class="po-select" required>
                    <option value="tadarus" {{ old('jenis', $laporan->jenis) == 'tadarus' ? 'selected' : '' }}>Tadarus</option>
                    <option value="murajaah" {{ old('jenis', $laporan->jenis) == 'murajaah' ? 'selected' : '' }}>Murajaah</option>
                </select>
                @error('jenis') <span class="po-error">{{ $message }}</span> @enderror
            </div>
            <div class="po-form-group" id="fieldSurat">
                <label class="po-label">Surat *</label>
                <select name="surat_id" class="po-select" required>
                    <option value="">-- Pilih Surat --</option>
                    @foreach($suratList as $s)
                        <option value="{{ $s->id }}" {{ old('surat_id', $laporan->surat_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->urutan }}. {{ $s->nama_latin }} ({{ $s->jumlah_ayat }} ayat)
                        </option>
                    @endforeach
                </select>
                @error('surat_id') <span class="po-error">{{ $message }}</span> @enderror
            </div>
            <div class="po-form-group" id="fieldAyatMulai">
                <label class="po-label">Ayat Mulai *</label>
                <input type="number" name="ayat_mulai" class="po-input" min="1" value="{{ old('ayat_mulai', $laporan->ayat_mulai ?? 1) }}" required>
                @error('ayat_mulai') <span class="po-error">{{ $message }}</span> @enderror
            </div>
            <div class="po-form-group" id="fieldAyatSelesai">
                <label class="po-label">Ayat Selesai</label>
                <input type="number" name="ayat_selesai" class="po-input" min="1" value="{{ old('ayat_selesai', $laporan->ayat_selesai) }}" placeholder="Opsional, jika hanya 1 ayat kosongkan">
                @error('ayat_selesai') <span class="po-error">{{ $message }}</span> @enderror
            </div>
            <div class="po-form-group">
                <label class="po-label">Tanggal Kegiatan *</label>
                <input type="date" name="tanggal" class="po-input" value="{{ old('tanggal', $laporan->tanggal?->format('Y-m-d')) }}" required>
                @error('tanggal') <span class="po-error">{{ $message }}</span> @enderror
            </div>
            <div class="po-form-group" style="grid-column: 1 / -1;">
                <label class="po-label">Catatan</label>
                <textarea name="catatan" class="po-textarea" placeholder="Catatan pendampingan (misal: kualitas bacaan, kendala, dll)">{{ old('catatan', $laporan->catatan) }}</textarea>
                @error('catatan') <span class="po-error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="po-form-grid" style="margin-top: 16px;">
            <button type="submit" class="po-btn">Simpan Perubahan</button>
            <a href="{{ route('siswa.pendampingan-ortu.index') }}" class="po-btn-cancel">Batal</a>
        </div>
    </form>
    <script>
    (function() {
        const cb = document.getElementById('cbJilid');
        const groups = ['fieldSurat', 'fieldAyatMulai', 'fieldAyatSelesai'].map(id => document.getElementById(id));

        function applyJilid() {
            groups.forEach(g => {
                g.style.opacity = cb.checked ? '0.45' : '';
                g.querySelector('select, input').disabled = cb.checked;
            });
        }

        cb.addEventListener('change', applyJilid);
        applyJilid();
    })();
    </script>
</div>
@endsection
