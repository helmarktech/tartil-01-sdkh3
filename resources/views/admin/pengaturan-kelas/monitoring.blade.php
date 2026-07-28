@extends('layouts.admin')
@section('title', 'Monitoring Penilaian Rapor')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Monitoring Penilaian</h1>
            <p class="page-subtitle">Progress pengisian nilai per kelas oleh guru</p>
        </div>
        <a href="{{ route('admin.pengaturan-kelas.aktifkan') }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px;">
            ← Kembali
        </a>
    </div>

    {{-- Info Card --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="color: var(--text-primary);">{{ $semesterPenilaian->semester->nama ?? '-' }}</strong>
                <span style="margin-left: 16px; font-size: 13px; color: var(--text-muted);">{{ $semesterPenilaian->keterangan ?? '' }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="{{ $semesterPenilaian->statusBadgeClass() }}">{{ $semesterPenilaian->statusLabel() }}</span>
            </div>
        </div>
    </div>

    {{-- Progress Summary --}}
    @php
        $totalKelas = count($progressByKelas);
        $totalSiswa = array_sum(array_column($progressByKelas, 'jumlah_siswa'));
        $totalDiisi = array_sum(array_column($progressByKelas, 'diisi'));
        $totalEntry = array_sum(array_column($progressByKelas, 'total'));
        $avgProgress = $totalEntry > 0 ? round(($totalDiisi / $totalEntry) * 100) : 0;
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--text-primary);">{{ $totalKelas }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Kelas</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--text-primary);">{{ $totalSiswa }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Siswa</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: {{ $avgProgress >= 80 ? '#5A7D5A' : ($avgProgress >= 50 ? '#B8860B' : '#C62828') }};">{{ $avgProgress }}%</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Rata-rata Progress</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: #5A7D5A;">{{ $totalDiisi }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Nilai Terisi</div>
        </div>
    </div>

    {{-- Detail Progress per Kelas --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">NO</th>
                        <th>NAMA KELAS</th>
                        <th style="width: 80px; text-align: center;">JENIS</th>
                        <th style="width: 80px; text-align: center;">SISWA</th>
                        <th style="width: 80px; text-align: center;">TERISI</th>
                        <th style="width: 80px; text-align: center;">TOTAL</th>
                        <th style="width: 120px; text-align: center;">PROGRESS</th>
                        <th style="width: 80px; text-align: center;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progressByKelas as $i => $p)
                    <tr>
                        <td style="text-align: center; color: var(--text-muted);">{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">
                            {{ $p['kelas']->nama }}
                            <span style="color: var(--text-muted); font-size: 11px; display: block;">{{ $p['kelas']->guru->nama ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-subject">{{ $p['kelas']->jenis ?? '-' }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $p['jumlah_siswa'] }}</td>
                        <td style="text-align: center; font-weight: 600; color: #5A7D5A;">{{ $p['diisi'] }}</td>
                        <td style="text-align: center;">{{ $p['total'] }}</td>
                        <td style="text-align: center;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="flex: 1; height: 6px; background: #f0ece4; border-radius: 3px; overflow: hidden;">
                                    <div style="width: {{ $p['persen'] }}%; height: 100%; background: {{ $p['persen'] >= 80 ? '#5A7D5A' : ($p['persen'] >= 50 ? '#B8860B' : '#C62828') }}; border-radius: 3px;"></div>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; width: 32px;">{{ $p['persen'] }}%</span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if($p['persen'] === 100)
                                <span class="badge-success" style="font-size: 10px;">Selesai</span>
                            @elseif($p['persen'] > 0)
                                <span class="badge-warning" style="font-size: 10px;">Proses</span>
                            @else
                                <span class="badge-error" style="font-size: 10px;">Belum</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
