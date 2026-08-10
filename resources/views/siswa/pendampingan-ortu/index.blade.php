@extends('layouts.siswa')

@section('title', 'Pendampingan Orang Tua')

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

.po-table-wrap { overflow-x: auto; }
.po-table {
    width: 100%; border-collapse: collapse; font-size: 13px;
}
.po-table th {
    text-align: left; padding: 10px 12px; background: #f8faf8;
    font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0; white-space: nowrap;
}
.po-table td {
    padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;
}
.po-table tr:hover td { background: #f8faf8; }
.po-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600;
}
.po-badge-pengajuan { background: #fff3cd; color: #856404; }
.po-badge-dikonfirmasi { background: #d4edda; color: #155724; }
.po-empty { text-align: center; padding: 40px; color: #888; }
.po-ayat { font-size: 12px; color: #78716c; }
</style>

<div class="siswa-page-header">
    <div class="siswa-page-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
        <h1 class="siswa-page-title">Pendampingan Orang Tua</h1>
        <p class="siswa-page-subtitle">Laporkan kegiatan tadarus dan murajaah bersama anak</p>
    </div>
</div>

{{-- Form Input --}}
<div class="po-section">
    <h2 class="po-title">Form Laporan Pendampingan</h2>
    <form method="POST" action="{{ route('siswa.pendampingan-ortu.store') }}">
        @csrf
        <div class="po-form-grid">
            <div class="po-form-group">
                <label class="po-label">Jenis Kegiatan *</label>
                <select name="jenis" class="po-select" required>
                    <option value="tadarus" {{ old('jenis') == 'tadarus' ? 'selected' : '' }}>Tadarus</option>
                    <option value="murajaah" {{ old('jenis') == 'murajaah' ? 'selected' : '' }}>Murajaah</option>
                </select>
            </div>
            <div class="po-form-group">
                <label class="po-label">Surat *</label>
                <select name="surat_id" class="po-select" required>
                    <option value="">-- Pilih Surat --</option>
                    @foreach($suratList as $s)
                        <option value="{{ $s->id }}" {{ old('surat_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->urutan }}. {{ $s->nama_latin }} ({{ $s->jumlah_ayat }} ayat)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="po-form-group">
                <label class="po-label">Ayat Mulai *</label>
                <input type="number" name="ayat_mulai" class="po-input" min="1" value="{{ old('ayat_mulai', 1) }}" required>
            </div>
            <div class="po-form-group">
                <label class="po-label">Ayat Selesai</label>
                <input type="number" name="ayat_selesai" class="po-input" min="1" placeholder="Opsional, jika hanya 1 ayat kosongkan">
            </div>
            <div class="po-form-group">
                <label class="po-label">Tanggal Kegiatan *</label>
                <input type="date" name="tanggal" class="po-input" value="{{ old('tanggal', date('Y-m-d')) }}" required>
            </div>
            <div class="po-form-group" style="grid-column: 1 / -1;">
                <label class="po-label">Catatan</label>
                <textarea name="catatan" class="po-textarea" placeholder="Catatan pendampingan (misal: kualitas bacaan, kendala, dll)">{{ old('catatan') }}</textarea>
            </div>
        </div>
        <button type="submit" class="po-btn" style="margin-top: 16px;">Kirim Laporan</button>
    </form>
</div>

{{-- Riwayat Laporan --}}
<div class="po-section">
    <h2 class="po-title">Riwayat Laporan ({{ $riwayat->count() }})</h2>

    @if($riwayat->isEmpty())
        <div class="po-empty">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128106;</div>
            <p>Belum ada laporan pendampingan.</p>
        </div>
    @else
        <div class="po-table-wrap">
            <table class="po-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Surat / Ayat</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Guru Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $l)
                    <tr>
                        <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                        <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                        <td>
                            <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                            <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                        </td>
                        <td style="max-width: 220px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                        <td>
                            <span class="po-badge {{ $l->status === 'telah_dikonfirmasi' ? 'po-badge-dikonfirmasi' : 'po-badge-pengajuan' }}">
                                {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                            </span>
                        </td>
                        <td>
                            @if($l->guruKonfirmasi)
                                <strong>{{ $l->guruKonfirmasi->nama }}</strong>
                                <div class="po-ayat">{{ $l->tanggal_konfirmasi?->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="po-ayat">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
