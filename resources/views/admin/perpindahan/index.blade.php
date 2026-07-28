@extends('layouts.admin')
@section('title', 'Perpindahan Kelas Tartil')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Perpindahan Kelas Tartil</h1>
            <p class="page-subtitle">
                @if($step == 1) Pilih kelas asal dan kelas tujuan
                @elseif($step == 2) Pilih kelas tujuan
                @else Pilih siswa yang akan dipindahkan
                @endif
            </p>
        </div>
        @if($step > 1)
        <a href="{{ route('admin.perpindahan-tartil.admin') }}" class="btn-tartil-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">Reset</a>
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
        <form method="GET" action="{{ route('admin.perpindahan-tartil.admin') }}">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                {{-- Kelas Asal --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">
                        Kelas Asal <span style="color:#c62828">*</span>
                    </label>
                    <select name="kelas_asal_id" class="form-input" required onchange="if(this.value) this.form.submit()">
                        <option value="">-- Pilih Kelas Asal --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_asal_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} ({{ $k->jenis }}) - {{ $k->siswas_count }} siswa - {{ $k->guru->nama ?? 'Tanpa guru' }}
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
                            @foreach($kelasList as $k)
                                @if($k->id != $kelasAsal->id)
                                <option value="{{ $k->id }}">
                                    {{ $k->nama }} ({{ $k->jenis }}) - {{ $k->guru->nama ?? 'Tanpa guru' }}
                                </option>
                                @endif
                            @endforeach
                        @elseif($step == 3 && $kelasAsal && $kelasTujuan)
                            @foreach($kelasList as $k)
                                @if($k->id != $kelasAsal->id)
                                <option value="{{ $k->id }}" {{ $kelasTujuan->id == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} ({{ $k->jenis }}) - {{ $k->guru->nama ?? 'Tanpa guru' }}
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

    {{-- Step 3: Info kelas yang dipilih + form siswa --}}
    @if($step == 3 && $kelasAsal && $kelasTujuan)
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="background: #f8f9fa; text-align: center;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas Asal</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $kelasAsal->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);"><span class="badge-subject">{{ $kelasAsal->jenis }}</span></div>
        </div>
        <div class="card-tartil" style="background: #fff3e0; text-align: center;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kelas Tujuan</div>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $kelasTujuan->nama }}</div>
            <div style="font-size: 12px; color: var(--text-muted);"><span class="badge-subject">{{ $kelasTujuan->jenis }}</span></div>
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

        <form method="POST" action="{{ route('admin.perpindahan-tartil.ajukan-massal') }}" id="form-pindah">
            @csrf
            <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsal->id }}">
            <input type="hidden" name="kelas_tujuan_id" value="{{ $kelasTujuan->id }}">
            @if($semester)
            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
            @endif

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
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="siswa_ids[]" class="siswa-check" value="{{ $s->id }}">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td style="font-weight: 500;">{{ $s->nama }}</td>
                            <td>{{ $s->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                            <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Alasan <span style="color:#c62828">*</span></label>
                <textarea name="alasan" class="form-input" rows="2" placeholder="Alasan perpindahan..." required></textarea>
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

    {{-- Riwayat Perpindahan --}}
    <div class="card-tartil" style="margin-top: 20px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 16px; margin: 0; color: var(--text-primary);">Riwayat Perpindahan</h3>
            <div style="display: flex; gap: 8px;">
                <button type="button" onclick="submitApproveAll()" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;">Setuju Semua Terpilih</button>
                <button type="button" onclick="submitTolakAll()" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Tolak Semua Terpilih</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="check-all-history" onclick="toggleCheckAllHistory()"></th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas Lama</th>
                        <th>Kelas Baru</th>
                        <th>Alasan</th>
                        <th>Pengaju</th>
                        <th>Guru Tujuan</th>
                        <th>Status</th>
                        <th style="min-width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perpindahans as $p)
                    <tr>
                        <td style="text-align: center;">
                            @if($p->status == 'pending')
                            <input type="checkbox" name="ids[]" class="history-check" value="{{ $p->id }}" form="form-approve-all">
                            @endif
                        </td>
                        <td>{{ $p->created_at->format('d/m/Y') }}</td>
                        <td style="font-weight: 500;">{{ $p->siswa->nama }}</td>
                        <td>{{ $p->kelasLama->nama ?? '-' }}</td>
                        <td>{{ $p->kelasBaru->nama ?? '-' }}</td>
                        <td style="max-width: 150px; font-size: 12px; color: var(--text-secondary);">{{ $p->alasan ?? '-' }}</td>
                        <td>
                            <div>{{ $p->pengaju->nama ?? '-' }}</div>
                            @if($p->pengaju_type == 'admin' || ($p->pengaju && $p->pengaju->role == 'admin'))
                                <span class="badge-subject" style="background: #E8D5B5; font-size: 10px;">Admin</span>
                            @else
                                <span class="badge-subject" style="background: #E9F0E9; font-size: 10px;">Guru</span>
                            @endif
                        </td>
                        <td>{{ $p->guruTujuan->nama ?? '-' }}</td>
                        <td>
                            @if($p->status == 'pending')
                                <span class="badge-warning">Menunggu</span>
                            @elseif($p->status == 'disetujui')
                                <span class="badge-success">Disetujui</span>
                            @else
                                <span class="badge-error">Ditolak</span>
                            @endif
                        </td>
                        <td>
                            @if($p->status == 'pending')
                                <div style="display: flex; gap: 6px;">
                                    <form method="POST" action="{{ route('admin.perpindahan-tartil.approve', $p->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="return confirm('Setujui perpindahan {{ $p->siswa->nama }} ke {{ $p->kelasBaru->nama ?? '?' }}?')">Setuju</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.perpindahan-tartil.tolak', $p->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="return confirm('Tolak perpindahan {{ $p->siswa->nama }}?')">Tolak</button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size: 12px; color: var(--text-muted);">{{ $p->approved_at?->format('d/m/Y') ?? '-' }}</span>
                                @if($p->catatan)
                                <div style="font-size: 11px; color: #c62828; margin-top: 2px;">{{ $p->catatan }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">Belum ada pengajuan perpindahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Hidden forms for mass actions --}}
        <form id="form-approve-all" method="POST" action="{{ route('admin.perpindahan-tartil.approve-all') }}" style="display: none;">
            @csrf
        </form>
        <form id="form-tolak-all" method="POST" action="{{ route('admin.perpindahan-tartil.tolak-all') }}" style="display: none;">
            @csrf
        </form>
    </div>
    {{ $perpindahans->links() }}
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
function toggleCheckAllHistory() {
    const checkAll = document.getElementById('check-all-history');
    const checks = document.querySelectorAll('.history-check');
    checks.forEach(c => c.checked = checkAll.checked);
}
function submitApproveAll() {
    const checks = document.querySelectorAll('.history-check:checked');
    if (checks.length === 0) {
        alert('Pilih minimal 1 perpindahan yang akan disetujui.');
        return;
    }
    if (!confirm('Setujui ' + checks.length + ' perpindahan terpilih? Siswa akan pindah kelas.')) return;

    // Move checked checkboxes to the approve form
    const form = document.getElementById('form-approve-all');
    checks.forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = c.value;
        form.appendChild(input);
    });
    form.submit();
}
function submitTolakAll() {
    const checks = document.querySelectorAll('.history-check:checked');
    if (checks.length === 0) {
        alert('Pilih minimal 1 perpindahan yang akan ditolak.');
        return;
    }
    if (!confirm('Tolak ' + checks.length + ' perpindahan terpilih?')) return;

    // Move checked checkboxes to the tolak form
    const form = document.getElementById('form-tolak-all');
    checks.forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = c.value;
        form.appendChild(input);
    });
    form.submit();
}
</script>
@endpush
