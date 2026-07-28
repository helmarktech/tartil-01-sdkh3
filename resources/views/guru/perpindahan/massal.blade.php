@extends('layouts.admin')
@section('title', 'Pindah Kelas Massal')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Pindah Kelas Massal</h1>
            <p class="page-subtitle">Ajukan perpindahan siswa ke kelas lain (3 langkah)</p>
        </div>
        <a href="{{ route('guru.dashboard') }}" class="btn-tartil-outline">Kembali</a>
    </div>

    {{-- Step Progress --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px;">
        <div class="card-tartil" style="flex: 1; text-align: center; padding: 12px; {{ $step >= 1 ? 'border-color: var(--accent);' : '' }}">
            <div style="font-size: 20px; font-weight: 700; color: {{ $step >= 1 ? 'var(--accent)' : 'var(--text-muted)' }};">1</div>
            <div style="font-size: 12px; color: var(--text-muted);">Pilih Kelas Asal</div>
        </div>
        <div style="display: flex; align-items: center; color: var(--text-muted);">→</div>
        <div class="card-tartil" style="flex: 1; text-align: center; padding: 12px; {{ $step >= 2 ? 'border-color: var(--accent);' : '' }}">
            <div style="font-size: 20px; font-weight: 700; color: {{ $step >= 2 ? 'var(--accent)' : 'var(--text-muted)' }};">2</div>
            <div style="font-size: 12px; color: var(--text-muted);">Pilih Kelas Tujuan</div>
        </div>
        <div style="display: flex; align-items: center; color: var(--text-muted);">→</div>
        <div class="card-tartil" style="flex: 1; text-align: center; padding: 12px; {{ $step >= 3 ? 'border-color: var(--accent);' : '' }}">
            <div style="font-size: 20px; font-weight: 700; color: {{ $step >= 3 ? 'var(--accent)' : 'var(--text-muted)' }};">3</div>
            <div style="font-size: 12px; color: var(--text-muted);">Pilih Siswa</div>
        </div>
    </div>

    {{-- Step 1: Pilih Kelas Asal (hanya kelas yang diajar guru) --}}
    @if($step == 1)
    <div class="card-tartil" style="padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Langkah 1: Pilih Kelas Asal</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            Hanya kelas yang Anda ajar yang ditampilkan di bawah.
        </p>

        @if($kelasAsalList->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
            @foreach($kelasAsalList as $k)
            <a href="{{ route('guru.perpindahan.massal') }}?kelas_asal_id={{ $k->id }}"
               class="card-tartil" style="text-decoration: none; padding: 16px;">
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">{{ $k->nama }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $k->mata_pelajaran ?? '-' }} | {{ $k->jenis ?? '-' }}</div>
                <div style="font-size: 12px; color: var(--accent); margin-top: 8px;">
                    {{ $k->siswas_count }} siswa aktif
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            Anda belum memiliki kelas yang diajar. Hubungi admin.
        </div>
        @endif
    </div>
    @endif

    {{-- Step 2: Pilih Kelas Tujuan --}}
    @if($step == 2 && $kelasAsal)
    <div class="card-tartil" style="padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 8px; color: var(--text-primary); font-weight: 600;">Langkah 2: Pilih Kelas Tujuan</h3>
        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            Kelas Asal: <strong>{{ $kelasAsal->nama }}</strong> ({{ $kelasAsal->siswas_count }} siswa)
            <a href="{{ route('guru.perpindahan.massal') }}" style="color: var(--accent); margin-left: 12px;">Ganti</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
            @foreach($kelasTujuanList as $k)
            @if($k->id != $kelasAsal->id)
            <a href="{{ route('guru.perpindahan.massal') }}?kelas_asal_id={{ $kelasAsal->id }}&kelas_tujuan_id={{ $k->id }}"
               class="card-tartil" style="text-decoration: none; padding: 16px;">
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">{{ $k->nama }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $k->mata_pelajaran ?? '-' }} | {{ $k->jenis ?? '-' }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                    Wali: {{ $k->guru->nama ?? '-' }}
                </div>
            </a>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Step 3: Pilih Siswa + Submit --}}
    @if($step == 3 && $kelasAsal && $kelasTujuan)
    <div class="card-tartil" style="padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 8px; color: var(--text-primary); font-weight: 600;">Langkah 3: Pilih Siswa & Kirim</h3>
        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            <strong>{{ $kelasAsal->nama }}</strong> → <strong>{{ $kelasTujuan->nama }}</strong>
            <a href="{{ route('guru.perpindahan.massal') }}?kelas_asal_id={{ $kelasAsal->id }}" style="color: var(--accent); margin-left: 12px;">Ganti Tujuan</a>
            | <a href="{{ route('guru.perpindahan.massal') }}" style="color: var(--accent);">Ulangi</a>
        </div>

        @if($siswaList->count() > 0)
        <form method="POST" action="{{ route('guru.perpindahan.massal.store') }}">
            @csrf
            <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsal->id }}">
            <input type="hidden" name="kelas_tujuan_id" value="{{ $kelasTujuan->id }}">
            <input type="hidden" name="semester_id" value="{{ $semester->id }}">

            {{-- Alasan --}}
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Alasan Perpindahan <span style="color: #c62828">*</span></label>
                <textarea name="alasan" class="form-input" rows="2" required placeholder="Contoh: Penyesuaian kemampuan, perubahan jadwal, dll"></textarea>
            </div>

            {{-- Checkbox: Pilih Semua --}}
            <div style="margin-bottom: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 500;">
                    <input type="checkbox" id="checkAll" onchange="toggleAll(this)" style="width: 16px; height: 16px;">
                    Pilih Semua Siswa ({{ $siswaList->count() }})
                </label>
            </div>

            {{-- Tabel Siswa --}}
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table-tartil" style="font-size: 13px;">
                    <thead style="position: sticky; top: 0; background: var(--bg-body); z-index: 1;">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas Reguler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswaList as $s)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="siswa-check" style="width: 16px; height: 16px;">
                            </td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td style="color: var(--text-muted);">{{ $s->kelasReguler->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-tartil" onclick="return confirmSubmit()">Ajukan Perpindahan</button>
                <a href="{{ route('guru.perpindahan.massal') }}?kelas_asal_id={{ $kelasAsal->id }}" class="btn-tartil-outline">Batal</a>
            </div>
        </form>
        @else
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            Tidak ada siswa aktif di kelas ini.
        </div>
        @endif
    </div>
    @endif

    {{-- Riwayat Pengajuan --}}
    @if($perpindahans->count() > 0)
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Riwayat Pengajuan Anda</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas Lama</th>
                    <th>Kelas Tujuan</th>
                    <th>Pengaju</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perpindahans as $p)
                <tr>
                    <td style="font-size: 12px;">{{ $p->created_at->format('d/m/Y') }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>{{ $p->kelasLama->nama ?? '-' }}</td>
                    <td>{{ $p->kelasBaru->nama ?? '-' }}</td>
                    <td>
                        <span class="badge-subject" style="font-size: 10px;">Guru</span>
                    </td>
                    <td>
                        @if($p->status == 'pending')
                            <span class="badge-warning">Menunggu</span>
                        @elseif($p->status == 'disetujui')
                            <span class="badge-success">Disetujui</span>
                        @else
                            <span class="badge-error">Ditolak</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $perpindahans->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleAll(cb) {
    document.querySelectorAll('.siswa-check').forEach(c => c.checked = cb.checked);
}
function confirmSubmit() {
    const checked = document.querySelectorAll('.siswa-check:checked').length;
    if (checked === 0) { alert('Pilih minimal 1 siswa.'); return false; }
    return confirm('Ajukan perpindahan ' + checked + ' siswa? Data akan masuk approval.');
}
</script>
@endpush
