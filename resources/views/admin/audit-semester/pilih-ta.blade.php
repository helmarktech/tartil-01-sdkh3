@extends('layouts.admin')

@section('title', 'Audit Semester - Pilih Tahun Ajaran')

@section('content')
<style>
.ta-card {
    background: #ffffff;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.ta-card:hover {
    border-color: #0c8a5f;
    box-shadow: 0 6px 24px rgba(12,138,95,0.18);
    transform: translateY(-3px);
}
.ta-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.ta-card-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a2e;
}
.ta-card-status {
    font-size: 10px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: 0.5px;
}
.status-aktif { background: #d4edda; color: #155724; }
.status-ditutup { background: #f8d7da; color: #721c24; }
.status-nonaktif { background: #fff3cd; color: #856404; }
.ta-card-periode {
    font-size: 12px;
    color: #666;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e0e0e0;
}
.ta-card-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.ta-stat {
    text-align: center;
    padding: 14px 8px;
    background: #f8faf8;
    border-radius: 10px;
    border: 1px solid #e8f5e9;
}
.ta-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #0c8a5f;
}
.ta-stat-label {
    font-size: 10px;
    color: #888;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.ta-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
    font-size: 12px;
    color: #666;
}
.ta-card-arrow {
    color: #0c8a5f;
    font-weight: 600;
}
.page-intro {
    background: #e8f5e9;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
}
.page-intro h2 {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 6px;
}
.page-intro p {
    font-size: 13px;
    color: #555;
    margin: 0;
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0;">Audit Semester</h1>
        <p class="page-subtitle" style="color: #666; font-size: 14px; margin: 4px 0 0;">Track record terkunci per Tahun Ajaran dan Semester</p>
    </div>
</div>

<div class="page-intro">
    <h2>&#128203; Pilih Tahun Ajaran</h2>
    <p>Setiap card menampilkan ringkasan statistik lengkap. Klik card untuk melihat detail per semester.</p>
</div>

@if($tahunAjaranList->isEmpty())
    <div style="background: #fff; border: 1px solid #ddd; border-radius: 12px; text-align: center; padding: 48px;">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
        <h3 style="margin-bottom: 8px;">Belum ada Tahun Ajaran</h3>
        <p style="color: #888;">Buat Tahun Ajaran terlebih dahulu di menu Tahun Ajaran.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 16px;">Ke Menu Tahun Ajaran</a>
    </div>
@else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px;">
        @foreach($tahunAjaranList as $ta)
            <a href="{{ route('admin.audit-semester.index', ['ta' => $ta->nama]) }}" class="ta-card">
                <div class="ta-card-header">
                    <span class="ta-card-title">TA {{ $ta->nama }}</span>
                    <span class="ta-card-status status-{{ $ta->status ?? 'nonaktif' }}">
                        {{ $ta->status === 'aktif' ? 'AKTIF' : ($ta->status === 'ditutup' ? 'DITUTUP' : 'NONAKTIF') }}
                    </span>
                </div>
                <div class="ta-card-periode">&#128197; {{ $ta->periode ?? '-' }}</div>
                <div class="ta-card-stats">
                    <div class="ta-stat">
                        <div class="ta-stat-value">{{ $ta->total_siswa ?? 0 }}</div>
                        <div class="ta-stat-label">Siswa</div>
                    </div>
                    <div class="ta-stat">
                        <div class="ta-stat-value">{{ $ta->total_semester ?? 0 }}</div>
                        <div class="ta-stat-label">Semester</div>
                    </div>
                    <div class="ta-stat">
                        <div class="ta-stat-value">{{ $ta->total_munaqosyah ?? 0 }}</div>
                        <div class="ta-stat-label">Munaqosyah</div>
                    </div>
                    <div class="ta-stat">
                        <div class="ta-stat-value">{{ $ta->total_penilaian ?? 0 }}</div>
                        <div class="ta-stat-label">Penilaian</div>
                    </div>
                    <div class="ta-stat">
                        <div class="ta-stat-value" style="color: #1565c0;">{{ $ta->rata_r2_akhir ?? 0 }}</div>
                        <div class="ta-stat-label">R2 Rata-rata</div>
                    </div>
                    <div class="ta-stat">
                        <div class="ta-stat-value" style="color: #e65100;">{{ $ta->semester_ditutup ?? 0 }}</div>
                        <div class="ta-stat-label">Terkunci</div>
                    </div>
                </div>
                <div class="ta-card-footer">
                    <span>{{ $ta->semester_aktif ?? 0 }} aktif &middot; {{ $ta->semester_ditutup ?? 0 }} terkunci</span>
                    <span class="ta-card-arrow">Lihat Detail &rarr;</span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
