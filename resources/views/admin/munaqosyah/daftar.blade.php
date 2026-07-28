@extends('layouts.admin')
@section('title', 'Daftar Siswa Ujian')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Daftar Siswa Ujian</h1>
            <p class="page-subtitle">Daftarkan siswa ke ujian munaqosyah yang sudah disetujui</p>
        </div>
        @if($step > 1)
        <a href="{{ route('admin.munaqosyah.daftar') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">Reset</a>
        @endif
    </div>

    {{-- Progress Indicator --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px;">
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step >= 1 ? 'background: var(--accent); color: #fff;' : 'background: var(--border); color: var(--text-muted);' }}">
            1. Pilih Ujian & Kelas
        </div>
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step == 2 ? 'background: var(--accent); color: #fff;' : 'background: var(--border); color: var(--text-muted);' }}">
            2. Pilih Siswa
        </div>
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step == 2 ? 'background: #E9F0E9; color: #5A7D5A;' : 'background: var(--border); color: var(--text-muted);' }}">
            3. Selesai
        </div>
    </div>

    {{-- STEP 1: Pilih Ujian & Kelas Tartil --}}
    @if($step <= 1)
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <h3 style="font-size: 16px; margin-bottom: 20px; color: var(--text-primary); font-weight: 600;">Langkah 1: Pilih Ujian & Kelas Tartil</h3>
        <form method="GET" action="{{ route('admin.munaqosyah.daftar') }}">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Ujian <span style="color:#c62828">*</span></label>
                    <select name="ujian_id" class="form-input" required onchange="if(this.value) this.form.submit()">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach($ujianList as $u)
                        <option value="{{ $u->id }}" {{ request('ujian_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama }} ({{ ucfirst($u->tingkat) }}) - {{ $u->tanggal_ujian ? date('d/m/Y', strtotime($u->tanggal_ujian)) : '-' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Kelas Tartil <span style="color:#c62828">*</span></label>
                    <select name="kelas_tartil_id" class="form-input" required {{ $step == 0 ? 'disabled' : '' }} onchange="if(this.value && document.querySelector('select[name=ujian_id]').value) this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        @if($ujianTerpilih)
                            @foreach($kelasTartilList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_tartil_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }} ({{ $k->jenis }}) - {{ $k->siswas_count }} siswa - {{ $k->guru->nama ?? 'Tanpa guru' }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                    @if(!$ujianTerpilih)
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pilih ujian terlebih dahulu</p>
                    @endif
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- STEP 2: Info + Pilih Siswa --}}
    @if($step == 2 && $ujianTerpilih && $kelasTerpilih)
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="background: #f8f9fa; text-align: center; padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Ujian</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $ujianTerpilih->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">{{ ucfirst($ujianTerpilih->tingkat) }}</div>
        </div>
        <div class="card-tartil" style="background: #E9F0E9; text-align: center; padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $kelasTerpilih->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);"><span class="badge-subject">{{ $kelasTerpilih->jenis }}</span></div>
        </div>
    </div>

    @if($siswaList->count() > 0)
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 14px; margin: 0; color: var(--text-primary); font-weight: 600;">Langkah 2: Pilih Siswa ({{ $siswaList->where('sudah_terdaftar', false)->count() }} belum terdaftar, {{ $siswaList->where('sudah_terdaftar', true)->count() }} sudah terdaftar)</h3>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: var(--text-muted);">
                <input type="checkbox" id="check-all" onclick="toggleCheckAll()">
                <span>Pilih Semua</span>
            </label>
        </div>

        <form method="POST" action="{{ route('admin.munaqosyah.daftar.simpan') }}">
            @csrf
            <input type="hidden" name="ujian_id" value="{{ $ujianTerpilih->id }}">

            <div class="table-responsive" style="margin-bottom: 20px;">
                <table class="table-tartil" style="font-size: 13px; min-width: 500px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>L/P</th>
                            <th>Kelas Reguler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswaList as $i => $s)
                        <tr style="{{ $s->sudah_terdaftar ? 'background: #f5f5f5;' : '' }}">
                            <td style="text-align: center;">
                                @if($s->sudah_terdaftar)
                                    <span class="badge-success" style="font-size: 10px;">Terdaftar</span>
                                @else
                                    <input type="checkbox" name="siswa_ids[]" class="siswa-check" value="{{ $s->id }}">
                                @endif
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500; {{ $s->sudah_terdaftar ? 'color: var(--text-muted);' : '' }}">{{ $s->nama }}</td>
                            <td>{{ $s->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                            <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-tartil" onclick="return validateDaftar()">Daftarkan ke Ujian</button>
                <a href="{{ route('admin.munaqosyah.daftar') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
    @else
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted); font-size: 14px;">Tidak ada siswa yang tersedia, atau semua siswa sudah terdaftar di ujian ini.</p>
    </div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleCheckAll() {
    const checkAll = document.getElementById('check-all');
    const checks = document.querySelectorAll('.siswa-check');
    checks.forEach(c => c.checked = checkAll.checked);
}
function validateDaftar() {
    const checks = document.querySelectorAll('.siswa-check:checked');
    if (checks.length === 0) {
        alert('Pilih minimal 1 siswa yang akan didaftarkan.');
        return false;
    }
    return confirm('Daftarkan ' + checks.length + ' siswa ke ujian ini?');
}
</script>
@endpush
