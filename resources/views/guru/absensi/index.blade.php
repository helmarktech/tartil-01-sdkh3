@extends('layouts.admin')
@section('title', 'Absensi Bulanan')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Absensi Bulanan</h1>
            <p class="page-subtitle">Rekap kehadiran siswa per bulan</p>
        </div>
    </div>

    {{-- Filter: Semester → Kelas → Bulan (konsisten dengan Jurnal Bulanan) --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('guru.absensi.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; margin: 0; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Semester</label>
                <select name="semester_id" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; font-family: 'Inter', sans-serif;">
                    <option value="">-- Pilih Semester --</option>
                    @foreach($semesterList as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>
                        {{ $s->tahun_ajaran }} {{ ucfirst($s->jenis) }} {{ $s->is_aktif ? '[AKTIF]' : ($s->status == 'ditutup' ? '(DITUTUP)' : '') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas</label>
                <select name="kelas" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; font-family: 'Inter', sans-serif;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; font-family: 'Inter', sans-serif;">
            </div>
        </form>
    </div>

    {{-- Info Card --}}
    @if($semesterAktif && $kelasId)
    @php
        $kelasAktif = $kelasList->firstWhere('id', $kelasId);
        try {
            $bulanLabel = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY');
        } catch(\Exception $e) {
            $bulanLabel = $bulan;
        }
    @endphp
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <strong style="color: var(--text-primary); font-size: 15px;">{{ $kelasAktif?->nama ?? '-' }}</strong>
            <span class="badge-subject" style="margin-left: 8px;">{{ $kelasAktif?->jenis ?? '-' }}</span>
        </div>
        <div style="font-size: 12px; color: var(--text-muted);">
            {{ $semesterAktif->nama }} | {{ $bulanLabel }}
        </div>
    </div>
    @endif

    {{-- Tabel Absensi --}}
    @if($semesterId && $kelasId)
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensis as $a)
                <tr>
                    <td>{{ $a->tanggal->format('d/m/Y') }}</td>
                    <td style="font-weight: 500;">{{ $a->siswa->nama }}</td>
                    <td>{{ $a->kelas->nama }}</td>
                    <td>
                        <span class="badge-subject" style="background: {{ $a->status == 'Hadir' ? '#E9F0E9' : ($a->status == 'Sakit' ? '#F0ECE9' : ($a->status == 'Izin' ? '#E9EEF0' : '#F0E9E9')) }}; color: {{ $a->status == 'Hadir' ? '#5A7D5A' : ($a->status == 'Sakit' ? '#C4953A' : ($a->status == 'Izin' ? '#5A7A8A' : '#A85A52')) }};">
                            {{ $a->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data absensi untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $absensis->links() }}
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Pilih semester, kelas, dan bulan untuk melihat absensi.</div>
    </div>
    @endif
</div>
@endsection
