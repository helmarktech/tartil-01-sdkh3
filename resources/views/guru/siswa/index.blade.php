@extends('layouts.admin')

@section('title', 'Data Siswa Kelas Tartil - Guru')

@section('content')
<style>
.siswa-card {
    background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px;
    padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.siswa-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; font-family: 'DM Serif Display', serif; }
.siswa-sub { font-size: 13px; color: #666; margin: 4px 0 0; }
.siswa-filter {
    display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px;
}
.siswa-select {
    padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px;
    font-size: 14px; min-width: 180px;
}
.siswa-table-wrap { overflow-x: auto; }
.siswa-table {
    width: 100%; border-collapse: collapse; font-size: 13px;
}
.siswa-table th {
    text-align: left; padding: 10px 12px; background: #f8faf8;
    font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0; white-space: nowrap;
}
.siswa-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.siswa-table tr:hover td { background: #f8faf8; }
.siswa-input {
    padding: 8px 10px; border: 1px solid #ddd; border-radius: 8px;
    font-size: 13px; min-width: 140px; width: 100%;
}
.siswa-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    padding: 8px 14px; background: #0c8a5f; color: #fff;
    border: none; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
}
.siswa-btn:hover { background: #0a6b4a; }
.siswa-cards { display: none; }
@media (max-width: 768px) {
    .siswa-table-wrap { display: none; }
    .siswa-cards { display: flex; flex-direction: column; gap: 12px; }
    .siswa-card-item {
        background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; padding: 14px;
        display: flex; flex-direction: column; gap: 8px;
    }
    .siswa-card-row { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; }
    .siswa-card-label { color: #78716c; font-size: 12px; }
    .siswa-card-value { font-weight: 600; color: #1c1917; text-align: right; }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="siswa-title">&#128101; Data Siswa Kelas Tartil</h1>
        <p class="siswa-sub">Kelola nomor HP siswa di kelas yang Anda ampu</p>
    </div>
</div>

<div class="siswa-card">
    <form method="GET" action="{{ route('guru.siswa.index') }}" class="siswa-filter">
        <label style="font-size: 13px; color: #555; font-weight: 600;">Filter Kelas:</label>
        <select name="kelas_id" class="siswa-select" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ $kelasFilter == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
        </select>
    </form>

    @if($siswaList->isEmpty())
        <div style="text-align: center; padding: 40px; color: #888;">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128101;</div>
            <p>Tidak ada siswa aktif di kelas Anda.</p>
        </div>
    @else
    <div class="siswa-table-wrap">
        <table class="siswa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Nomor HP</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaList as $i => $s)
                <tr>
                    <form method="POST" action="{{ route('guru.siswa.update-no-hp', $s) }}">
                        @csrf
                        @method('PUT')
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $s->nis }}</td>
                        <td><strong>{{ $s->nama }}</strong></td>
                        <td>{{ $s->kelasTartil?->nama ?? '-' }}</td>
                        <td>
                            <input type="tel" name="no_hp" class="siswa-input" value="{{ $s->no_hp }}" placeholder="Nomor HP" maxlength="15">
                        </td>
                        <td>
                            <button type="submit" class="siswa-btn">Simpan</button>
                        </td>
                    </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="siswa-cards">
        @foreach($siswaList as $s)
        <form method="POST" action="{{ route('guru.siswa.update-no-hp', $s) }}" class="siswa-card-item">
            @csrf
            @method('PUT')
            <div class="siswa-card-row">
                <span class="siswa-card-label">NIS</span>
                <span class="siswa-card-value">{{ $s->nis }}</span>
            </div>
            <div class="siswa-card-row">
                <span class="siswa-card-label">Nama</span>
                <span class="siswa-card-value">{{ $s->nama }}</span>
            </div>
            <div class="siswa-card-row">
                <span class="siswa-card-label">Kelas</span>
                <span class="siswa-card-value">{{ $s->kelasTartil?->nama ?? '-' }}</span>
            </div>
            <div class="siswa-card-row" style="flex-direction: column; gap: 6px;">
                <span class="siswa-card-label" style="text-align: left;">Nomor HP</span>
                <input type="tel" name="no_hp" class="siswa-input" value="{{ $s->no_hp }}" placeholder="Nomor HP" maxlength="15">
            </div>
            <button type="submit" class="siswa-btn" style="width: 100%; margin-top: 6px;">Simpan Nomor HP</button>
        </form>
        @endforeach
    </div>
    @endif
</div>
@endsection
