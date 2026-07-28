@extends('layouts.siswa')
@section('title', 'Absensi')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display" style="font-size: 22px;">Absensi</h1>
        <p class="page-subtitle">Riwayat kehadiran</p>
    </div>

    <div class="card-tartil" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="GET" action="{{ route('siswa.absensi') }}" style="margin: 0;">
            <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Pilih Semester</label>
            <select name="semester" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; font-family: 'Inter', sans-serif;">
                @foreach($semesters as $s)
                <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>
                    {{ $s->tahun_ajaran }} {{ ucfirst($s->jenis) }} 
                    {{ $s->is_aktif ? '[AKTIF]' : ($s->status == 'ditutup' ? '(DITUTUP)' : '') }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card-tartil">
        @forelse($absensis as $a)
        <div style="padding: 14px 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-weight: 500; font-size: 14px;">{{ $a->tanggal->format('d F Y') }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $a->kelas->nama }}</div>
            </div>
            <span class="badge-subject" style="background: {{ $a->status == 'Hadir' ? '#E9F0E9' : ($a->status == 'Sakit' ? '#F0ECE9' : ($a->status == 'Izin' ? '#E9EEF0' : '#F0E9E9')) }}; color: {{ $a->status == 'Hadir' ? '#5A7D5A' : ($a->status == 'Sakit' ? '#C4953A' : ($a->status == 'Izin' ? '#5A7A8A' : '#A85A52')) }};">
                {{ $a->status }}
            </span>
        </div>
        @empty
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">Belum ada data absensi untuk semester ini.</div>
        @endforelse
    </div>
    {{ $absensis->links() }}
</div>
@endsection
