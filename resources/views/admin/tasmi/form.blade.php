@extends('layouts.admin')
@section('title', $tasmi ? 'Edit Video Tasmi\'' : 'Tambah Video Tasmi\'')

@section('content')
<div style="max-width: 720px;">
    <div class="page-header">
        <h1 class="page-title-display">{{ $tasmi ? 'Edit' : 'Tambah' }} Video Tasmi'</h1>
        <p class="page-subtitle">Gunakan link YouTube (format watch, youtu.be, atau shorts)</p>
    </div>

    <div class="card-tartil" style="padding: 24px;">
        <form method="POST" action="{{ $tasmi ? route('admin.tasmi.update', $tasmi) : route('admin.tasmi.store') }}">
            @csrf
            @if($tasmi) @method('PUT') @endif

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">
                    Siswa * <span style="font-weight: 400; color: var(--text-muted); font-size: 12px;">(bisa pilih lebih dari satu — satu video dapat memuat beberapa siswa)</span>
                </label>
                <input type="text" id="cariSiswa" class="form-input" placeholder="Ketik untuk mencari nama/NIS..." style="margin-bottom: 6px;" autocomplete="off">
                <div id="daftarSiswa" style="max-height: 220px; overflow-y: auto; border: 1px solid var(--border, #e5e5e5); border-radius: 8px; padding: 6px;">
                    @foreach($siswas as $s)
                    <label class="siswa-item" style="display: flex; align-items: center; gap: 8px; padding: 7px 8px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="cb-siswa" style="width: 16px; height: 16px; accent-color: #0c8a5f;"
                            {{ in_array($s->id, old('siswa_ids', $terpilih ?? [])) ? 'checked' : '' }}>
                        <span>{{ $s->nama }} <small style="color: var(--text-muted);">({{ $s->nis }})</small></span>
                    </label>
                    @endforeach
                </div>
                <div id="jumlahTerpilih" style="font-size: 12px; color: #0c8a5f; font-weight: 600; margin-top: 4px;"></div>
                @error('siswa_ids')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Juz *</label>
                    <select name="juz" class="form-input" required>
                        <option value="">— Pilih Juz —</option>
                        @for($j = 1; $j <= 30; $j++)
                        <option value="{{ $j }}" {{ old('juz', $tasmi?->juz) == $j ? 'selected' : '' }}>Juz {{ $j }}</option>
                        @endfor
                    </select>
                    @error('juz')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tanggal Tasmi'</label>
                    <input type="date" name="tanggal_tasmi" class="form-input" value="{{ old('tanggal_tasmi', $tasmi?->tanggal_tasmi?->format('Y-m-d')) }}">
                    @error('tanggal_tasmi')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Judul *</label>
                <input type="text" name="judul" class="form-input" maxlength="150" placeholder="Contoh: Tasmi' Juz 30 — Ahmad Fauzi" value="{{ old('judul', $tasmi?->judul) }}" required>
                @error('judul')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Link YouTube *</label>
                <input type="text" name="youtube_url" id="youtubeUrl" class="form-input" maxlength="255" placeholder="https://www.youtube.com/watch?v=... atau https://youtu.be/..." value="{{ old('youtube_url', $tasmi?->youtube_url) }}" required>
                @error('youtube_url')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                <div id="previewWrap" style="margin-top: 10px; display: none;">
                    <img id="previewThumb" src="" alt="Preview" style="width: 100%; max-width: 320px; border-radius: 8px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Deskripsi</label>
                <textarea name="deskripsi" class="form-input" rows="3" maxlength="1000" placeholder="Catatan tambahan (opsional)">{{ old('deskripsi', $tasmi?->deskripsi) }}</textarea>
                @error('deskripsi')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-tartil">{{ $tasmi ? 'Simpan Perubahan' : 'Simpan' }}</button>
                <a href="{{ route('admin.tasmi.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
// Pencarian siswa + penghitung pilihan (multi-siswa per video)
(function () {
    const items = document.querySelectorAll('.siswa-item');
    const counter = document.getElementById('jumlahTerpilih');

    function updateCounter() {
        const n = document.querySelectorAll('.cb-siswa:checked').length;
        counter.textContent = n > 0 ? n + ' siswa dipilih' : 'Belum ada siswa dipilih';
        counter.style.color = n > 0 ? '#0c8a5f' : '#c62828';
    }

    document.getElementById('cariSiswa').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        items.forEach(function (item) {
            item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.cb-siswa').forEach(function (cb) {
        cb.addEventListener('change', updateCounter);
    });
    updateCounter();
})();

// Preview thumbnail YouTube saat link diisi
function ekstrakId(url) {
    url = url.trim();
    if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
    const m = url.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|embed\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : null;
}
const inputUrl = document.getElementById('youtubeUrl');
function updatePreview() {
    const id = ekstrakId(inputUrl.value || '');
    const wrap = document.getElementById('previewWrap');
    if (id) {
        document.getElementById('previewThumb').src = 'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg';
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
}
inputUrl.addEventListener('input', updatePreview);
updatePreview();
</script>
@endsection
