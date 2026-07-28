@extends($role == 'siswa' ? 'layouts.siswa' : 'layouts.admin')
@section('title', 'Detail Track Record - ' . $siswa->nama)

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Track Record Siswa</h1>
            <p class="page-subtitle">Detail riwayat kelas dan performa</p>
        </div>
        @if($role != 'siswa')
        <a href="{{ url()->previous() }}" class="btn-tartil-outline">Kembali</a>
        @endif
    </div>

    {{-- Profile Card --}}
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; flex-shrink: 0;">
                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
            </div>
            <div style="flex: 1;">
                <h2 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-primary);">{{ $siswa->nama }}</h2>
                <div style="display: flex; gap: 20px; margin-top: 8px; flex-wrap: wrap; font-size: 13px; color: var(--text-muted);">
                    <span><strong style="color: var(--text-primary);">NIS:</strong> {{ $siswa->nis ?? '-' }}</span>
                    <span><strong style="color: var(--text-primary);">Kelas Reguler:</strong> {{ $siswa->kelasReguler->nama ?? '-' }}</span>
                    <span><strong style="color: var(--text-primary);">Kelas Tartil:</strong> {{ $siswa->kelasTartil->nama ?? '-' }}</span>
                    <span><strong style="color: var(--text-primary);">Status:</strong>
                        @if($siswa->status == 'aktif')
                            <span class="badge-success" style="font-size: 10px;">Aktif</span>
                        @elseif($siswa->status == 'lulus')
                            <span class="badge-primary" style="font-size: 10px;">Lulus</span>
                        @else
                            <span class="badge-warning" style="font-size: 10px;">{{ ucfirst($siswa->status) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelas History Timeline --}}
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px; color: var(--text-primary);">Riwayat Perpindahan Kelas</h3>

    @if(count($kelasHistory) > 0)
    <div style="position: relative; padding-left: 24px; margin-bottom: 32px;">
        {{-- Timeline line --}}
        <div style="position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>

        @foreach($kelasHistory as $h)
        <div style="position: relative; margin-bottom: 20px;">
            {{-- Timeline dot --}}
            <div style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--accent); border: 2px solid white; box-shadow: 0 0 0 2px var(--accent);"></div>

            <div class="card-tartil" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">
                            {{ $h['tanggal']->locale('id')->isoFormat('dddd, D MMMM YYYY') }} | Semester {{ $h['semester'] }}
                        </div>
                        <div style="font-weight: 600; color: var(--text-primary);">
                            <span style="color: var(--text-muted);">{{ $h['kelas_lama'] }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent); margin: 0 4px; vertical-align: middle;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            <span style="color: var(--accent);">{{ $h['kelas_baru'] }}</span>
                        </div>
                        @if($h['alasan'] && $h['alasan'] !== '-')
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            Alasan: {{ $h['alasan'] }}
                        </div>
                        @endif
                    </div>
                    <span class="badge-subject" style="font-size: 10px;">Naik Kelas</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 32px; margin-bottom: 32px;">
        <div style="color: var(--text-muted);">Belum ada riwayat perpindahan kelas.</div>
    </div>
    @endif

    {{-- Rekap Per Semester --}}
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px; color: var(--text-primary);">Rekap Performa Per Semester</h3>

    @if(count($rekapPerSemester) > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        @foreach($rekapPerSemester as $r)
        <div class="card-tartil" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="font-size: 14px; font-weight: 600; margin: 0; color: var(--text-primary);">{{ $r['semester']->nama }}</h4>
                <span class="badge-subject" style="font-size: 10px;">{{ $r['bulan_count'] }} bulan</span>
            </div>

            {{-- Progress bar rata-rata --}}
            <div style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                    <span style="color: var(--text-muted);">Rata-rata</span>
                    <span style="font-weight: 600;">{{ $r['rata_rata'] }}%</span>
                </div>
                <div style="height: 8px; background: var(--bg-body); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $r['rata_rata'] }}%; background: {{ $r['rata_rata'] >= 80 ? '#5A7D5A' : ($r['rata_rata'] >= 60 ? '#B8860B' : '#C62828') }}; border-radius: 4px; transition: width 0.3s;"></div>
                </div>
            </div>

            {{-- Stats grid --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; text-align: center;">
                <div style="padding: 8px; background: #E9F0E9; border-radius: 8px;">
                    <div style="font-size: 16px; font-weight: 700; color: #5A7D5A;">{{ $r['count_b'] }}</div>
                    <div style="font-size: 10px; color: #5A7D5A;">Baik</div>
                </div>
                <div style="padding: 8px; background: #FFF8E1; border-radius: 8px;">
                    <div style="font-size: 16px; font-weight: 700; color: #B8860B;">{{ $r['count_c'] }}</div>
                    <div style="font-size: 10px; color: #B8860B;">Cukup</div>
                </div>
                <div style="padding: 8px; background: #FBE9E7; border-radius: 8px;">
                    <div style="font-size: 16px; font-weight: 700; color: #C62828;">{{ $r['count_k'] }}</div>
                    <div style="font-size: 10px; color: #C62828;">Kurang</div>
                </div>
            </div>

            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); font-size: 11px; color: var(--text-muted); text-align: center;">
                Total pertemuan: {{ $r['total_hadir'] }} hari
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 32px;">
        <div style="color: var(--text-muted);">Belum ada data rekap performa.</div>
    </div>
    @endif
</div>
@endsection
