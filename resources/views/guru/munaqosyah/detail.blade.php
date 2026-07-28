@extends('layouts.admin')
@section('title', 'Detail Munaqosyah')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $munaqosyah->nama }}</h1>
            <p class="page-subtitle">{{ ucfirst($munaqosyah->tingkat) }} - {{ $munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($munaqosyah->tanggal_ujian)) : '-' }}</p>
        </div>
        <a href="{{ route('guru.munaqosyah.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    {{-- Statistik --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $munaqosyah->pendaftarans->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total Pendaftar</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #5A7D5A;">{{ $munaqosyah->pendaftarans->where('status', 'L')->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Lulus (L)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #A85A52;">{{ $munaqosyah->pendaftarans->where('status', 'TL')->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Tidak Lulus (TL)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--text-secondary);">{{ $munaqosyah->pendaftarans->where('status', 'T')->count() }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Terdaftar (T)</div>
        </div>
    </div>

    {{-- Guru: Daftarkan Siswa (hanya dari kelas sendiri, checkbox massal) --}}
    @if($siswas->count() > 0)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">Daftarkan Siswa ({{ $siswas->count() }} dari kelas Anda)</h3>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: var(--text-muted);">
                <input type="checkbox" id="check-all" onclick="toggleCheckAll()">
                <span>Pilih Semua</span>
            </label>
        </div>

        <form method="POST" action="{{ route('guru.munaqosyah.daftarkan', $munaqosyah->id) }}">
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
        <p style="color: var(--text-muted); font-size: 14px;">Tidak ada siswa yang tersedia dari kelas Anda, atau semua siswa sudah terdaftar.</p>
    </div>
    @endif

    {{-- Input Nilai Toggle L / TL / T --}}
    @if($munaqosyah->pendaftarans->count() > 0)
    @php
        $approvedPendaftarans = $munaqosyah->pendaftarans->filter(fn($p) => !($p->approval && $p->approval->status === 'pending'));
        $pendingPendaftarans = $munaqosyah->pendaftarans->filter(fn($p) => $p->approval && $p->approval->status === 'pending');
    @endphp

    {{-- Info approval pending --}}
    @if($pendingPendaftarans->count() > 0)
    <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #856404;">
        <strong>Catatan:</strong> {{ $pendingPendaftarans->count() }} siswa menunggu approval admin. 
        Input nilai hanya tersedia setelah admin menyetujui pendaftaran.
    </div>
    @endif

    {{-- Yang sudah approved — bisa input nilai --}}
    @if($approvedPendaftarans->count() > 0)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Input Nilai & Status</h3>
        <form method="POST" action="{{ route('guru.munaqosyah.nilai', $munaqosyah->id) }}" id="form-nilai">
            @csrf
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th style="text-align: center;">Status Toggle</th>
                            <th>Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedPendaftarans->values() as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                            <td style="text-align: center;">
                                <input type="hidden" name="nilai[{{ $i }}][pendaftaran_id]" value="{{ $p->id }}">
                                <input type="hidden" name="nilai[{{ $i }}][status]" id="status-{{ $i }}" value="{{ $p->status }}">
                                
                                <div class="toggle-group" style="display: inline-flex; gap: 2px; background: var(--surface); border-radius: 8px; padding: 2px;">
                                    <button type="button" 
                                        class="toggle-btn {{ $p->status == 'T' ? 'active' : '' }}" 
                                        data-status="T" 
                                        data-index="{{ $i }}"
                                        onclick="setStatus({{ $i }}, 'T')"
                                        title="Terdaftar"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $p->status == 'T' ? '#D4A373' : 'transparent' }}; color: {{ $p->status == 'T' ? '#fff' : 'var(--text-muted)' }};">
                                        T
                                    </button>
                                    <button type="button" 
                                        class="toggle-btn {{ $p->status == 'L' ? 'active' : '' }}" 
                                        data-status="L" 
                                        data-index="{{ $i }}"
                                        onclick="setStatus({{ $i }}, 'L')"
                                        title="Lulus"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $p->status == 'L' ? '#5A7D5A' : 'transparent' }}; color: {{ $p->status == 'L' ? '#fff' : 'var(--text-muted)' }};">
                                        L
                                    </button>
                                    <button type="button" 
                                        class="toggle-btn {{ $p->status == 'TL' ? 'active' : '' }}" 
                                        data-status="TL" 
                                        data-index="{{ $i }}"
                                        onclick="setStatus({{ $i }}, 'TL')"
                                        title="Tidak Lulus"
                                        style="padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: {{ $p->status == 'TL' ? '#A85A52' : 'transparent' }}; color: {{ $p->status == 'TL' ? '#fff' : 'var(--text-muted)' }};">
                                        TL
                                    </button>
                                </div>
                            </td>
                            <td><input type="number" name="nilai[{{ $i }}][nilai]" value="{{ $p->nilai }}" class="form-input" min="0" max="100" style="max-width: 80px;"></td>
                            <td><input type="text" name="nilai[{{ $i }}][catatan]" value="{{ $p->catatan }}" class="form-input" placeholder="Catatan..."></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;">
                <button type="submit" class="btn-tartil">Simpan Nilai</button>
                <button type="button" class="btn-tartil-success" onclick="lulusSemua()">Lulus Semua</button>
                <button type="button" class="btn-tartil-danger" onclick="tidakLulusSemua()">Tidak Lulus Semua</button>
            </div>
        </form>

        {{-- Hidden forms for batch actions --}}
        <form method="POST" action="{{ route('guru.munaqosyah.lulussemua', $munaqosyah->id) }}" id="form-lulus-semua" style="display:none;">
            @csrf
        </form>
        <form method="POST" action="{{ route('guru.munaqosyah.tidaklulussemua', $munaqosyah->id) }}" id="form-tidak-lulus-semua" style="display:none;">
            @csrf
        </form>
    </div>
    @endif

    {{-- Yang masih pending approval — tidak bisa input nilai --}}
    @if($pendingPendaftarans->count() > 0)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px; background: #fafafa;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Menunggu Approval Admin</h3>
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Status Pendaftaran</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingPendaftarans->values() as $i => $p)
                    <tr style="opacity: 0.7;">
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                        <td>
                            <span class="badge-warning" style="font-size: 11px;">{{ $p->status_label }}</span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px;">
                            Belum di-approve admin. Hubungi admin untuk menyetujui pendaftaran.
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif

    {{-- Daftar Pendaftar --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Pendaftar</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($munaqosyah->pendaftarans as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->siswa->nis ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>
                        <span class="{{ $p->status_badge_class }}">{{ $p->status_label }}</span>
                    </td>
                    <td>
                        @if($p->approval)
                            @if($p->approval->status == 'disetujui')
                                <span class="badge-success" style="font-size: 11px;">Disetujui</span>
                            @elseif($p->approval->status == 'ditolak')
                                <span class="badge-error" style="font-size: 11px;">Ditolak</span>
                            @else
                                <span class="badge-warning" style="font-size: 11px;">Menunggu</span>
                            @endif
                        @else
                            <span class="badge-subject" style="font-size: 11px;">Langsung</span>
                        @endif
                    </td>
                    <td>{{ $p->nilai ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada pendaftar.</td></tr>
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
    const checks = document.querySelectorAll('.siswa-check');
    checks.forEach(c => c.checked = checkAll.checked);
}
function validateDaftar() {
    const checks = document.querySelectorAll('.siswa-check:checked');
    if (checks.length === 0) {
        alert('Pilih minimal 1 siswa yang akan didaftarkan.');
        return false;
    }
    return confirm('Daftarkan ' + checks.length + ' siswa terpilih ke ujian ini?');
}

// Toggle Status: T (Terdaftar) | L (Lulus) | TL (Tidak Lulus)
function setStatus(index, status) {
    const input = document.getElementById('status-' + index);
    if (input) input.value = status;
    
    const buttons = document.querySelectorAll('.toggle-btn[data-index="' + index + '"]');
    buttons.forEach(btn => {
        const btnStatus = btn.getAttribute('data-status');
        if (btnStatus === status) {
            if (status === 'T') {
                btn.style.background = '#D4A373';
                btn.style.color = '#fff';
            } else if (status === 'L') {
                btn.style.background = '#5A7D5A';
                btn.style.color = '#fff';
            } else if (status === 'TL') {
                btn.style.background = '#A85A52';
                btn.style.color = '#fff';
            }
            btn.classList.add('active');
        } else {
            btn.style.background = 'transparent';
            btn.style.color = 'var(--text-muted)';
            btn.classList.remove('active');
        }
    });
}

function lulusSemua() {
    if (confirm('Luluskan semua siswa yang masih Terdaftar (T)?')) {
        document.getElementById('form-lulus-semua').submit();
    }
}

function tidakLulusSemua() {
    if (confirm('Tidak luluskan semua siswa yang masih Terdaftar (T)?')) {
        document.getElementById('form-tidak-lulus-semua').submit();
    }
}
</script>
@endpush
