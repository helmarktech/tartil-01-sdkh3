@extends('layouts.admin')
@section('title', 'Rekap Kelas Tartil')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Rekap Kelas Tartil</h1>
            <p class="page-subtitle">
                @if($semesterAktif)
                    Semester aktif: <strong>{{ $semesterAktif->nama }}</strong> ({{ $semesterAktif->tahun_ajaran }})
                @else
                    <span style="color: #c62828;">Tidak ada semester aktif</span>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.kelas.index') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
            Kelola Kelas
        </a>
    </div>

    @if(!$semesterAktif)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted); font-size: 14px;">Tidak dapat menampilkan rekap. Tidak ada semester yang aktif saat ini.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buka Tahun Ajaran</a>
    </div>
    @else
        {{-- Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px;">
            {{-- Total siswa --}}
            <div class="card-tartil" style="text-align: center;">
                <div style="font-size: 28px; font-weight: 600; color: var(--accent);">{{ $totalSiswa }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Siswa Aktif</div>
            </div>
            {{-- Total kelas --}}
            <div class="card-tartil" style="text-align: center;">
                <div style="font-size: 28px; font-weight: 600; color: #5A7D5A;">{{ $kelasList->count() }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Kelas Aktif</div>
            </div>
            {{-- Per jenis --}}
            @foreach(['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'] as $jenis)
                @if($jumlahPerJenis->has($jenis))
                <div class="card-tartil" style="text-align: center;">
                    <div style="font-size: 28px; font-weight: 600; color: #6B5E51;">{{ $jumlahPerJenis[$jenis] }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $jenis }}</div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- Detail Table --}}
        <div class="card-tartil table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Jenis</th>
                        <th>Guru</th>
                        <th>Jumlah Siswa</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasList as $i => $k)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $k->nama }}</td>
                        <td><span class="badge-subject">{{ $k->jenis }}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="student-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                    {{ substr($k->guru->nama ?? '-', 0, 2) }}
                                </div>
                                <span>{{ $k->guru->nama ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">
                                {{ $k->siswas_count }} siswa
                            </span>
                        </td>
                        <td>
                            @if($totalSiswa > 0)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: var(--border); border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="width: {{ round(($k->siswas_count / $totalSiswa) * 100) }}%; background: var(--accent); height: 100%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                    {{ round(($k->siswas_count / $totalSiswa) * 100) }}%
                                </span>
                            </div>
                            @else
                            <span style="color: var(--text-muted); font-size: 12px;">0%</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada kelas tartil aktif.</td></tr>
                    @endforelse
                </tbody>
                @if($kelasList->count() > 0)
                <tfoot>
                    <tr style="font-weight: 600; background: #f8f9fa;">
                        <td colspan="4" style="text-align: right;">Total</td>
                        <td><span class="badge-subject" style="background: #E8D5B5; color: #6B5E51;">{{ $totalSiswa }} siswa</span></td>
                        <td>100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>
@endsection
