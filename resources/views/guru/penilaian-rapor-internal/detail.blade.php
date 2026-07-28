@extends('layouts.admin')
@section('title', 'Detail: ' . $ujian->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $ujian->nama }}</h1>
            <p class="page-subtitle">{{ ucfirst($ujian->tingkat) }} — Semester {{ $ujian->semester->nama ?? '-' }}</p>
        </div>
        <a href="{{ route('guru.penilaian-rapor-internal.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    {{-- Info & Link ke Input Nilai --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="font-size: 14px; color: var(--text-primary); font-weight: 600;">{{ $ujian->pesertas->count() }} siswa terdaftar</div>
            <div style="font-size: 12px; color: var(--text-muted);">Nilai diisi melalui menu <strong>Rapor Indikator</strong></div>
        </div>
        @if($semesterPenilaian)
        <a href="{{ route('guru.penilaian-rapor.index') }}" class="btn-tartil" style="text-decoration: none; font-size: 13px;">
            Input Nilai Indikator →
        </a>
        @else
        <span class="badge-warning" style="font-size: 12px;">Penilaian indikator belum aktif</span>
        @endif
    </div>

    {{-- Daftarkan Siswa Baru --}}
    @if($siswas->count() > 0)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">Daftarkan Siswa ({{ $siswas->count() }} belum terdaftar)</h3>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: var(--text-muted);">
                <input type="checkbox" id="check-all" onclick="toggleCheckAll()">
                <span>Pilih Semua</span>
            </label>
        </div>

        <form method="POST" action="{{ route('guru.penilaian-rapor-internal.daftarkan', $ujian->id) }}">
            @csrf
            <div class="table-responsive" style="margin-bottom: 16px;">
                <table class="table-tartil" style="font-size: 13px; min-width: 500px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas Reguler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $i => $s)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="siswa_ids[]" class="siswa-check" value="{{ $s->id }}">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn-tartil" onclick="return validateDaftar()">Daftarkan Siswa Terpilih</button>
        </form>
    </div>
    @else
    <div class="card-tartil" style="padding: 24px; text-align: center; margin-bottom: 20px;">
        <p style="color: var(--text-muted); font-size: 14px;">Semua siswa dari kelas Anda sudah terdaftar.</p>
    </div>
    @endif

    {{-- Daftar Peserta + Progress Nilai --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Peserta & Progress Nilai</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas Tartil</th>
                    <th>Progress Nilai</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ujian->pesertas as $i => $ps)
                @php $prog = $progressMap[$ps->siswa_id] ?? ['total' => 0, 'diisi' => 0, 'persen' => 0]; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ps->siswa->nis ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $ps->siswa->nama ?? '-' }}</td>
                    <td>{{ $ps->siswa->kelasTartil->nama ?? '-' }} <span class="badge-subject" style="font-size: 10px;">{{ $ps->siswa->kelasTartil->jenis ?? '' }}</span></td>
                    <td style="min-width: 160px;">
                        @if($prog['total'] > 0)
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 3px;">
                                <span>{{ $prog['diisi'] }}/{{ $prog['total'] }} indikator</span>
                                <span>{{ $prog['persen'] }}%</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: var(--surface); border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $prog['persen'] }}%; height: 100%; background: {{ $prog['persen'] == 100 ? '#5A7D5A' : 'var(--accent)' }}; border-radius: 3px;"></div>
                            </div>
                        @else
                            <span style="font-size: 11px; color: var(--text-muted);">Belum ada indikator</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px;">
                            @if($semesterPenilaian)
                            <a href="{{ route('guru.penilaian-rapor.isi-nilai', [$semesterPenilaian->id, $ps->siswa->kelas_tartil_id ?? 0]) }}" class="btn-tartil" style="padding: 4px 8px; font-size: 11px; text-decoration: none;">Nilai</a>
                            @endif
                            <form method="POST" action="{{ route('guru.penilaian-rapor-internal.hapus-peserta', $ps->id) }}" style="display:inline;" onsubmit="return confirm('Hapus {{ $ps->siswa->nama ?? 'siswa' }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-tartil-danger" style="padding: 4px 8px; font-size: 11px;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada peserta terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCheckAll() {
    const checkAll = document.getElementById('check-all');
    document.querySelectorAll('.siswa-check').forEach(c => c.checked = checkAll.checked);
}
function validateDaftar() {
    const checks = document.querySelectorAll('.siswa-check:checked');
    if (checks.length === 0) { alert('Pilih minimal 1 siswa.'); return false; }
    return confirm('Daftarkan ' + checks.length + ' siswa?');
}
</script>
@endpush
