@extends('layouts.admin')
@section('title', 'Pindah Kelas Reguler')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Pindah Kelas</h1>
            <p class="page-subtitle">
                @if($step == 1) Pilih kelas asal dan kelas tujuan
                @elseif($step == 2) Pilih kelas tujuan
                @else Pilih siswa yang akan dipindahkan
                @endif
            </p>
        </div>
        @if($step > 1)
        <a href="{{ route('admin.kelas-reguler.pindah-index') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">Reset</a>
        @endif
    </div>

    {{-- Progress Indicator --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px;">
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step >= 1 ? 'background: var(--accent); color: #fff;' : 'background: var(--border); color: var(--text-muted);' }}">
            1. Pilih Kelas Asal & Tujuan
        </div>
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step >= 3 ? 'background: var(--accent); color: #fff;' : 'background: var(--border); color: var(--text-muted);' }}">
            2. Pilih Siswa
        </div>
        <div style="flex: 1; padding: 10px; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 500; {{ $step >= 3 ? 'background: #E9F0E9; color: #5A7D5A;' : 'background: var(--border); color: var(--text-muted);' }}">
            3. Selesai
        </div>
    </div>

    {{-- STEP 1 & 2: Pilih Kelas Asal dan Kelas Tujuan --}}
    @if($step <= 2)
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.kelas-reguler.pindah-index') }}">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                {{-- Kelas Asal --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">
                        Kelas Asal <span style="color:#c62828">*</span>
                    </label>
                    <select name="kelas_asal_id" class="form-input" required onchange="if(this.value) this.form.submit()">
                        <option value="">-- Pilih Kelas Asal --</option>
                        @foreach($kelasRegulers as $kr)
                        <option value="{{ $kr->id }}" {{ request('kelas_asal_id') == $kr->id ? 'selected' : '' }}>
                            {{ $kr->nama }} (Jenjang {{ $kr->jenjang }} {{ $kr->tingkat }}) - {{ $kr->total_siswa }} siswa
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kelas Tujuan --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">
                        Kelas Tujuan <span style="color:#c62828">*</span>
                    </label>
                    <select name="kelas_tujuan_id" class="form-input" required {{ $step == 1 ? 'disabled' : '' }} onchange="if(this.value && document.querySelector('select[name=kelas_asal_id]').value) this.form.submit()">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        @if($step == 2 && $kelasAsal)
                            @foreach($kelasRegulers as $kr)
                                @if($kr->id != $kelasAsal->id)
                                <option value="{{ $kr->id }}">
                                    {{ $kr->nama }} (Jenjang {{ $kr->jenjang }} {{ $kr->tingkat }})
                                </option>
                                @endif
                            @endforeach
                        @elseif($step == 3 && $kelasAsal && $kelasTujuan)
                            @foreach($kelasRegulers as $kr)
                                @if($kr->id != $kelasAsal->id)
                                <option value="{{ $kr->id }}" {{ $kelasTujuan->id == $kr->id ? 'selected' : '' }}>
                                    {{ $kr->nama }} (Jenjang {{ $kr->jenjang }} {{ $kr->tingkat }})
                                </option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                    @if($step == 1)
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pilih kelas asal terlebih dahulu</p>
                    @endif
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- Step 3: Tampilkan info kelas yang dipilih + form siswa --}}
    @if($step == 3 && $kelasAsal && $kelasTujuan)
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="background: #f8f9fa; text-align: center;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas Asal</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $kelasAsal->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Jenjang {{ $kelasAsal->jenjang }} {{ $kelasAsal->tingkat }}</div>
        </div>
        <div class="card-tartil" style="background: #fff3e0; text-align: center;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas Tujuan</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $kelasTujuan->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Jenjang {{ $kelasTujuan->jenjang }} {{ $kelasTujuan->tingkat }}</div>
        </div>
    </div>
    @endif

    {{-- STEP 3: Pilih Siswa dengan Checkbox --}}
    @if($step == 3 && $siswaList->count() > 0)
    <div class="card-tartil" style="padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 14px; margin: 0; color: var(--text-primary); font-weight: 600;">
                Pilih Siswa dari {{ $kelasAsal->nama }}
            </h3>
            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: var(--text-muted);">
                    <input type="checkbox" id="check-all" onclick="toggleCheckAll()">
                    <span>Pilih Semua ({{ $siswaList->count() }} siswa)</span>
                </label>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.kelas-reguler.pindah') }}" id="form-pindah">
            @csrf
            <input type="hidden" name="kelas_reguler_baru_id" value="{{ $kelasTujuan->id }}">

            <div class="table-responsive" style="margin-bottom: 20px;">
                <table class="table-tartil" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>L/P</th>
                            <th>Kelas Tartil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswaList as $i => $s)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="siswa_ids[]" class="siswa-check" value="{{ $s->id }}">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td>{{ $s->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                            <td>
                                @if($s->kelasTartil)
                                <span class="badge-subject" style="background: #E8D5B5;">{{ $s->kelasTartil->nama }}</span>
                                @else
                                <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="submit" class="btn-tartil-warning" style="padding: 10px 24px; font-size: 13px;" onclick="return validatePindah()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 6px;"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    Proses Pindah ke {{ $kelasTujuan->nama }}
                </button>
            </div>
        </form>
    </div>
    @elseif($step == 3)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted); font-size: 14px;">Tidak ada siswa aktif di kelas {{ $kelasAsal->nama ?? 'asal' }}.</p>
    </div>
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
function validatePindah() {
    const checks = document.querySelectorAll('.siswa-check:checked');
    if (checks.length === 0) {
        alert('Pilih minimal 1 siswa yang akan dipindah.');
        return false;
    }
    return confirm('Yakin pindahkan ' + checks.length + ' siswa ke kelas tujuan?');
}
</script>
@endpush
