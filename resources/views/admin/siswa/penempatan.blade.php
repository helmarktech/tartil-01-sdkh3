@extends('layouts.admin')
@section('title', 'Penempatan Siswa Tartil')

@section('content')
<style>
.siswa-row { display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border); }
.siswa-row:hover { background: var(--bg-hover); }
.siswa-row:last-child { border-bottom: none; }
.siswa-checkbox { margin-right: 12px; }
.siswa-info { flex: 1; }
.siswa-nama { font-weight: 500; font-size: 13px; }
.siswa-meta { font-size: 11px; color: var(--text-muted); }
.sticky-action {
    position: sticky;
    bottom: 0;
    background: var(--bg-card);
    padding: 12px 16px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.empty-state { text-align: center; padding: 48px; color: var(--text-muted); }
.empty-state strong { color: var(--text-primary); }
</style>

<div>
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Penempatan Tartil</h1>
            <p class="page-subtitle">Pilih siswa yang belum memiliki kelas tartil untuk ditempatkan secara massal</p>
        </div>
        <a href="{{ route('admin.siswa.import') }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px;">← Import Siswa</a>
    </div>

    {{-- Info --}}
    <div class="card-tartil" style="padding: 14px 18px; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div style="font-size: 13px; color: var(--text-primary);">
                <strong>{{ $siswas->total() }}</strong> siswa belum memiliki kelas tartil
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                Pilih siswa → pilih kelas tartil → klik Tempatkan
            </div>
        </div>
    </div>

    @if($siswas->isNotEmpty())
    <form method="POST" action="{{ route('admin.siswa.penempatan.proses') }}">
        @csrf

        {{-- Daftar Siswa --}}
        <div class="card-tartil" style="padding: 0; overflow: hidden; margin-bottom: 0;">
            <div style="padding: 12px 16px; background: var(--bg-elevated); border-bottom: 1px solid var(--border);">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; cursor: pointer; color: var(--text-secondary);">
                    <input type="checkbox" id="checkAll" style="cursor: pointer;">
                    <strong>Pilih Semua</strong>
                </label>
            </div>
            @foreach($siswas as $s)
            <div class="siswa-row">
                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="siswa-checkbox siswa-check" style="width: 18px; height: 18px;">
                <div class="siswa-info">
                    <div class="siswa-nama">{{ $s->nama }}</div>
                    <div class="siswa-meta">NIS: {{ $s->nis }} | Kelas Reguler: {{ $s->kelasReguler?->nama ?? '-' }} | JK: {{ $s->jenis_kelamin }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 12px;">
            {{ $siswas->links() }}
        </div>

        {{-- Sticky Action --}}
        <div class="sticky-action">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Kelas Tartil Tujuan</label>
                <select name="kelas_tartil_id" class="form-input" required style="font-size: 13px;">
                    <option value="">-- Pilih Kelas Tartil --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->jenis }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 10px 24px;" onclick="return confirm('Tempatkan siswa terpilih ke kelas tartil?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"/></svg>
                Tempatkan
            </button>
        </div>
    </form>
    @else
    <div class="card-tartil empty-state">
        <div style="margin-bottom: 8px;">✅ <strong>Semua siswa sudah memiliki kelas tartil!</strong></div>
        <div style="font-size: 12px;">Import siswa baru melalui menu <a href="{{ route('admin.siswa.import') }}" style="color: var(--accent);">Import Siswa</a>.</div>
    </div>
    @endif
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.siswa-check').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
