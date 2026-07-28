@extends('layouts.admin')
@section('title', 'Progress Absensi')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Progress Absensi</h1>
            <p class="page-subtitle">Monitoring pengisian absensi (B/C/K) oleh guru — {{ $semester->nama ?? 'Pilih TA dan Semester' }}</p>
        </div>
    </div>

    {{-- Step Breadcrumb --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('admin.progress.absensi') }}" class="btn-tartil-outline {{ $step == 'ta' ? 'active' : '' }}" style="font-size: 12px; padding: 6px 12px;">1. Pilih TA</a>
        @if($ta ?? false)
        <a href="{{ route('admin.progress.absensi', ['step' => 'semester', 'ta' => $ta]) }}" class="btn-tartil-outline {{ $step == 'semester' ? 'active' : '' }}" style="font-size: 12px; padding: 6px 12px;">2. Pilih Semester</a>
        @endif
        @if(($semesterId ?? false) && $ta)
        <a href="{{ route('admin.progress.absensi', ['step' => 'guru', 'ta' => $ta, 'semester_id' => $semesterId]) }}" class="btn-tartil-outline {{ $step == 'guru' ? 'active' : '' }}" style="font-size: 12px; padding: 6px 12px;">3. Pilih Guru</a>
        @endif
    </div>

    {{-- Step 1: Pilih Tahun Ajaran --}}
    @if($step === 'ta')
    <div class="card-tartil" style="padding: 24px;">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0 0 20px;">Pilih Tahun Ajaran</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
            @forelse($tahunAjarans as $taItem)
            <a href="{{ route('admin.progress.absensi', ['step' => 'semester', 'ta' => $taItem->nama]) }}" class="card-tartil" style="text-decoration: none; display: flex; justify-content: space-between; align-items: center; padding: 16px;">
                <div>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $taItem->nama }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">{{ $taItem->semesters_count ?? 0 }} semester</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @empty
            <div style="color: var(--text-muted); text-align: center; padding: 40px;">Belum ada tahun ajaran.</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Step 2: Pilih Semester --}}
    @if($step === 'semester')
    <div class="card-tartil" style="padding: 24px;">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0 0 20px;">TA {{ $ta }} — Pilih Semester</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
            @forelse($semesters as $s)
            <a href="{{ route('admin.progress.absensi', ['step' => 'guru', 'ta' => $ta, 'semester_id' => $s->id]) }}" class="card-tartil" style="text-decoration: none; display: flex; justify-content: space-between; align-items: center; padding: 16px;">
                <div>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ ucfirst($s->jenis) }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $s->tanggal_mulai->format('d/m/Y') }} - {{ $s->tanggal_selesai->format('d/m/Y') }}</div>
                </div>
                @if($s->is_aktif)
                <span class="badge-success" style="font-size: 10px;">Aktif</span>
                @elseif($s->status == 'ditutup')
                <span class="badge-error" style="font-size: 10px;">Ditutup</span>
                @else
                <span class="badge-warning" style="font-size: 10px;">Nonaktif</span>
                @endif
            </a>
            @empty
            <div style="color: var(--text-muted); text-align: center; padding: 40px;">Belum ada semester untuk TA ini.</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Step 3: Pilih Guru --}}
    @if($step === 'guru')
    <div style="margin-bottom: 16px;">
        <div style="font-size: 14px; color: var(--text-muted);">Semester: <strong style="color: var(--text-primary);">{{ $semester->nama ?? '-' }}</strong></div>
    </div>

    @forelse($gurus as $guru)
    <div class="card-tartil" style="padding: 20px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #f5f0eb; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: #6B5E51;">
                {{ substr($guru->nama, 0, 1) }}
            </div>
            <div>
                <div style="font-weight: 600;">{{ $guru->nama }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $guru->kelas->count() }} kelas</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
            @foreach($guru->kelas as $kelas)
            @php $p = $kelas->progressAbsensi; @endphp
            <a href="{{ route('admin.progress.absensi', ['step' => 'kelas', 'ta' => $ta, 'semester_id' => $semesterId, 'guru_id' => $guru->id, 'kelas_id' => $kelas->id]) }}" class="card-tartil" style="text-decoration: none; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">{{ $kelas->nama }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $p['terisi'] }}/{{ $p['total_slot'] }} slot ({{ $p['jumlah_siswa'] }} siswa × {{ $p['jumlah_hari'] }} hari)</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 20px; font-weight: 700; color: {{ $p['persen'] >= 90 ? '#5A7D5A' : ($p['persen'] >= 75 ? '#8B9A4A' : '#A85A52') }};">
                            {{ $p['persen'] }}%
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">absensi terisi</div>
                    </div>
                </div>
                <div style="width: 100%; height: 6px; background: #f0ece4; border-radius: 3px; margin-top: 10px;">
                    <div style="width: {{ $p['persen'] }}%; height: 100%; background: {{ $p['persen'] >= 90 ? '#5A7D5A' : ($p['persen'] >= 75 ? '#B8860B' : '#C62828') }}; border-radius: 3px; transition: width 0.3s;"></div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @empty
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Tidak ada guru dengan kelas di semester ini.</div>
    </div>
    @endforelse
    @endif

    {{-- Step 4: Detail per Kelas --}}
    @if($step === 'kelas')
    @php $p = $progressKelas; @endphp
    <div style="margin-bottom: 16px;">
        <a href="{{ route('admin.progress.absensi', ['step' => 'guru', 'ta' => $ta, 'semester_id' => $semesterId]) }}" class="btn-tartil-outline" style="font-size: 12px;">← Kembali ke daftar guru</a>
    </div>

    <div class="card-tartil" style="padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 600; margin: 0;">
                    {{ $kelas->nama }}
                    @if($kelas->is_kelas_baru)
                    <span style="font-size: 11px; color: #1565c0; background: #e3f2fd; padding: 2px 8px; border-radius: 10px; margin-left: 6px; font-weight: 500;">Kelas Baru {{ $kelas->tanggal_dibuat->format('d/m/Y') }}</span>
                    @endif
                </h2>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Guru: {{ $kelas->guru->nama ?? '-' }} | Semester: {{ $semester->nama ?? '-' }} | {{ count($detailSiswa) }} siswa</div>
                @php $mutasiCount = collect($detailSiswa)->where('is_mutasi', true)->count(); @endphp
                @if($mutasiCount > 0)
                <div style="font-size: 11px; color: #856404; margin-top: 2px;">
                    {{ $mutasiCount }} siswa mutasi — total entry otomatis disesuaikan
                </div>
                @endif
            </div>
            <div style="text-align: right;">
                <div style="font-size: 28px; font-weight: 700; color: {{ $p['persen'] >= 90 ? '#5A7D5A' : ($p['persen'] >= 75 ? '#B8860B' : '#A85A52') }};">{{ $p['persen'] }}%</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $p['terisi'] }}/{{ $p['total_slot'] }} slot terisi</div>
            </div>
        </div>
        <div style="width: 100%; height: 8px; background: #f0ece4; border-radius: 4px; margin-top: 12px;">
            <div style="width: {{ $p['persen'] }}%; height: 100%; background: {{ $p['persen'] >= 90 ? '#5A7D5A' : ($p['persen'] >= 75 ? '#B8860B' : '#C62828') }}; border-radius: 4px;"></div>
        </div>
        @if($p['hari_libur'] > 0)
        <div style="font-size: 10px; color: #B8860B; margin-top: 4px;">{{ $p['hari_libur'] }} hari libur ditandai untuk kelas ini</div>
        @endif
    </div>

    {{-- Form Tandai Hari Libur --}}
    @php
        $liburList = $kelas->liburs()
            ->whereBetween('tanggal', [$semester->tanggal_mulai, min($semester->tanggal_selesai, now())])
            ->orderBy('tanggal', 'desc')
            ->get();
    @endphp
    <div class="card-tartil" style="padding: 16px; margin-bottom: 16px; background: #faf8f5;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h4 style="font-size: 13px; font-weight: 600; margin: 0; color: var(--text-primary);">Hari Libur Kelas {{ $kelas->nama }}</h4>
            <span style="font-size: 11px; color: var(--text-muted);">{{ $liburList->count() }} hari ditandai</span>
        </div>

        <form method="POST" action="{{ route('admin.kelas-libur.store') }}" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 10px;">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <div>
                <label style="font-size: 10px; color: var(--text-muted); display: block; margin-bottom: 2px;">Tanggal</label>
                <input type="date" name="tanggal" class="form-input" required style="font-size: 12px; padding: 5px 8px; width: 140px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 10px; color: var(--text-muted); display: block; margin-bottom: 2px;">Keterangan</label>
                <input type="text" name="keterangan" class="form-input" required placeholder="Contoh: Kegiatan OSIS" style="font-size: 12px; padding: 5px 8px; width: 100%;">
            </div>
            <button type="submit" class="btn-tartil" style="font-size: 11px; padding: 6px 12px; white-space: nowrap;">+ Tandai Libur</button>
        </form>

        @if($liburList->count() > 0)
        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
            @foreach($liburList as $libur)
            <div style="display: inline-flex; align-items: center; gap: 4px; background: #FFF8E1; border: 1px solid #FFE082; border-radius: 4px; padding: 3px 8px; font-size: 11px; color: #856404;">
                <span>{{ $libur->tanggal->format('d/m/Y') }} — {{ $libur->keterangan }}</span>
                <form method="POST" action="{{ route('admin.kelas-libur.destroy', $libur) }}" style="display: inline;" onsubmit="return confirm('Hapus tanda libur ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #B8860B; cursor: pointer; font-size: 13px; padding: 0 2px;">&times;</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th style="min-width: 150px;">NAMA SISWA</th>
                    <th style="text-align: center; width: 80px;">KET</th>
                    <th style="text-align: center;">TOTAL ENTRY</th>
                    <th style="text-align: center; color: #5A7D5A;">TERISI (B/C/K)</th>
                    <th style="text-align: center; color: #A85A52;">BELUM</th>
                    <th style="text-align: center;">% TERISI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailSiswa as $d)
                <tr>
                    <td style="font-weight: 500;">
                        {{ $d['siswa']->nama }}
                        @if($d['siswa']->status != 'aktif')
                        <span style="font-size: 9px; color: #999; background: #f0f0f0; padding: 1px 4px; border-radius: 3px; margin-left: 4px;">{{ $d['siswa']->status }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($d['is_mutasi'])
                        <span style="font-size: 9px; color: #856404; background: #FFF8E1; padding: 2px 5px; border-radius: 3px; white-space: nowrap;" title="Siswa mutasi masuk {{ $d['tanggal_masuk']?->format('d/m/Y') ?? '' }}">
                            Mutasi
                        </span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $d['total'] }}</td>
                    <td style="text-align: center; color: #5A7D5A; font-weight: 600;">{{ $d['dinilai'] }}</td>
                    <td style="text-align: center; color: #A85A52;">{{ $d['belum'] }} <span style="font-size: 9px; color: #999;">belum diisi</span></td>
                    <td style="text-align: center;">
                        <span class="{{ $d['persen'] >= 90 ? 'badge-success' : ($d['persen'] >= 75 ? 'badge-warning' : 'badge-error') }}" style="font-size: 11px;">{{ $d['persen'] }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
