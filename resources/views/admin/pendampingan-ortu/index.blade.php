@extends('layouts.admin')

@section('title', 'Monitoring Pendampingan Ortu - Admin')

@section('content')
<style>
.po-card {
    background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px;
    padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.po-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; font-family: 'DM Serif Display', serif; }
.po-sub { font-size: 13px; color: #666; margin: 4px 0 0; }
.po-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.po-tab {
    padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;
    text-decoration: none; background: #f5f5f4; color: #78716c;
}
.po-tab.active { background: #0c8a5f; color: #fff; }
.po-table-wrap { overflow-x: auto; }
.po-table {
    width: 100%; border-collapse: collapse; font-size: 13px;
}
.po-table th {
    text-align: left; padding: 10px 12px; background: #f8faf8;
    font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0; white-space: nowrap;
}
.po-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
.po-table tr:hover td { background: #f8faf8; }
.po-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600;
}
.po-badge-pengajuan { background: #fff3cd; color: #856404; }
.po-badge-dikonfirmasi { background: #d4edda; color: #155724; }
.po-ayat { font-size: 12px; color: #78716c; }
.po-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 18px; background: #0c8a5f; color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
}
.po-btn:hover { background: #0a6b4a; }
.po-btn:disabled { background: #ccc; cursor: not-allowed; }
.po-checkbox { width: 18px; height: 18px; cursor: pointer; }
.po-empty { text-align: center; padding: 48px; color: #888; }
.po-cards { display: none; }
@media (max-width: 768px) {
    .po-table-wrap { display: none; }
    .po-cards { display: flex; flex-direction: column; gap: 12px; }
    .po-card-item {
        background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; padding: 14px;
        display: flex; flex-direction: column; gap: 8px;
    }
    .po-card-row { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; }
    .po-card-label { color: #78716c; font-size: 12px; }
    .po-card-value { font-weight: 600; color: #1c1917; text-align: right; }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="po-title">&#128106; Monitoring Pendampingan Ortu</h1>
        <p class="po-sub">Semua laporan tadarus & murajaah dari orang tua siswa</p>
    </div>
</div>

<div class="po-tabs">
    <a href="{{ route('admin.pendampingan-ortu.index') }}" class="po-tab {{ $status === 'semua' ? 'active' : '' }}">Semua</a>
    <a href="{{ route('admin.pendampingan-ortu.index', ['status' => 'pengajuan']) }}" class="po-tab {{ $status === 'pengajuan' ? 'active' : '' }}">Pengajuan Konfirmasi</a>
    <a href="{{ route('admin.pendampingan-ortu.index', ['status' => 'dikonfirmasi']) }}" class="po-tab {{ $status === 'dikonfirmasi' ? 'active' : '' }}">Telah Dikonfirmasi</a>
</div>

@if($laporan->isEmpty())
    <div class="po-card po-empty">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128106;</div>
        <h3>Tidak ada laporan</h3>
    </div>
@else
<form method="POST" action="{{ route('admin.pendampingan-ortu.konfirmasi-bulk') }}" id="formKonfirmasiBulk">
    @csrf
    <div class="po-card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; cursor: pointer;">
            <input type="checkbox" id="pilihSemua" class="po-checkbox">
            Pilih semua
        </label>
        <button type="submit" class="po-btn" id="btnKonfirmasi" disabled onclick="return confirm('Konfirmasi laporan terpilih?')">
            Konfirmasi Terpilih
        </button>
    </div>

    <div class="po-table-wrap">
        <table class="po-table">
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis</th>
                    <th>Surat / Ayat</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $l)
                <tr>
                    <td>
                        @if($l->status === 'pengajuan_konfirmasi')
                            <input type="checkbox" name="laporan_ids[]" value="{{ $l->id }}" class="po-checkbox checkbox-item">
                        @endif
                    </td>
                    <td>{{ $l->tanggal?->format('d/m/Y') }}</td>
                    <td><strong>{{ $l->siswa?->nama ?? '-' }}</strong></td>
                    <td>{{ $l->kelas?->nama ?? '-' }}</td>
                    <td>{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</td>
                    <td>
                        <strong>{{ $l->surat?->nama_latin ?? '-' }}</strong>
                        <div class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</div>
                    </td>
                    <td style="max-width: 180px; word-break: break-word;">{{ $l->catatan ?? '-' }}</td>
                    <td>
                        <span class="po-badge {{ $l->status === 'telah_dikonfirmasi' ? 'po-badge-dikonfirmasi' : 'po-badge-pengajuan' }}">
                            {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                        </span>
                    </td>
                    <td>
                        @if($l->status === 'pengajuan_konfirmasi')
                            <form method="POST" action="{{ route('admin.pendampingan-ortu.konfirmasi', $l) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="po-btn" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Konfirmasi laporan ini?')">Konfirmasi</button>
                            </form>
                        @else
                            <span class="po-ayat">{{ $l->guruKonfirmasi?->nama ?? '-' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="po-cards">
        @foreach($laporan as $l)
        <div class="po-card-item">
            <div class="po-card-row">
                <span class="po-card-label">Siswa</span>
                <span class="po-card-value">{{ $l->siswa?->nama ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Kelas</span>
                <span class="po-card-value">{{ $l->kelas?->nama ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Tanggal</span>
                <span class="po-card-value">{{ $l->tanggal?->format('d/m/Y') }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Jenis</span>
                <span class="po-card-value">{{ \App\Models\LaporanPendampinganOrtu::labelJenis($l->jenis) }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Surat / Ayat</span>
                <span class="po-card-value" style="text-align: right;">
                    {{ $l->surat?->nama_latin ?? '-' }}<br>
                    <span class="po-ayat">Ayat {{ $l->ayat_mulai }}{{ $l->ayat_selesai ? '-'.$l->ayat_selesai : '' }}</span>
                </span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Catatan</span>
                <span class="po-card-value" style="max-width: 60%; word-break: break-word; font-weight: 400;">{{ $l->catatan ?? '-' }}</span>
            </div>
            <div class="po-card-row">
                <span class="po-card-label">Status</span>
                <span class="po-card-value">
                    <span class="po-badge {{ $l->status === 'telah_dikonfirmasi' ? 'po-badge-dikonfirmasi' : 'po-badge-pengajuan' }}">
                        {{ \App\Models\LaporanPendampinganOrtu::labelStatus($l->status) }}
                    </span>
                </span>
            </div>
            @if($l->status === 'telah_dikonfirmasi')
            <div class="po-card-row">
                <span class="po-card-label">Guru Konfirmasi</span>
                <span class="po-card-value">{{ $l->guruKonfirmasi?->nama ?? '-' }}</span>
            </div>
            @endif
            <div class="po-card-row" style="margin-top: 4px;">
                @if($l->status === 'pengajuan_konfirmasi')
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="laporan_ids[]" value="{{ $l->id }}" class="po-checkbox checkbox-item">
                        Pilih untuk konfirmasi
                    </label>
                @else
                    <span class="po-ayat">Sudah dikonfirmasi</span>
                @endif
            </div>
            @if($l->status === 'pengajuan_konfirmasi')
            <div style="margin-top: 4px;">
                <form method="POST" action="{{ route('admin.pendampingan-ortu.konfirmasi', $l) }}">
                    @csrf
                    <button type="submit" class="po-btn" style="width: 100%;" onclick="return confirm('Konfirmasi laporan ini?')">Konfirmasi Laporan</button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</form>

<script>
(function() {
    const pilihSemua = document.getElementById('pilihSemua');
    const checkboxes = document.querySelectorAll('.checkbox-item');
    const btn = document.getElementById('btnKonfirmasi');

    function updateBtn() {
        const any = Array.from(checkboxes).some(cb => cb.checked);
        btn.disabled = !any;
    }

    pilihSemua.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBtn();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBtn));
})();
</script>
@endif
@endsection
