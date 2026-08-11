@extends('layouts.admin')
@section('title', 'Approval Pendaftaran Munaqosyah')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Approval Pendaftaran Munaqosyah</h1>
            <p class="page-subtitle">Setujui atau tolak pengajuan siswa ke ujian munaqosyah</p>
        </div>
    </div>

    @php
    $pendingList = $pendaftarans->filter(fn($p) => $p->statusApproval === 'pending');
    $pendingCount = $pendingList->count();
    @endphp

    @if($pendingCount > 0)
    <div style="margin-bottom: 12px;">
        <span class="badge-warning" style="font-size: 12px;">{{ $pendingCount }} menunggu persetujuan</span>
    </div>
    @endif

    {{-- Tombol Massal --}}
    @if($pendingCount > 0)
    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
        <button type="button" onclick="submitSetujuMassal()" class="btn-tartil-success" style="padding: 8px 16px; font-size: 13px;">Setuju Semua Terpilih</button>
        <button type="button" onclick="submitTolakMassal()" class="btn-tartil-danger" style="padding: 8px 16px; font-size: 13px;">Tolak Semua Terpilih</button>
    </div>
    @endif

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="check-all" onclick="toggleCheckAll()"></th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas Tartil</th>
                    <th>Ujian</th>
                    <th>Pengaju</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th style="min-width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftarans as $p)
                <tr>
                    <td style="text-align: center;">
                        @if($p->statusApproval == 'pending' && $p->approval)
                        <input type="checkbox" class="approval-check" value="{{ $p->approval->id }}">
                        @endif
                    </td>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                    <td>{{ $p->siswa->nis ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>{{ $p->siswa->kelasTartil->nama ?? '-' }}</td>
                    <td>
                        <div>{{ $p->munaqosyah->nama ?? '-' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ ucfirst($p->munaqosyah->tingkat ?? '-') }}</div>
                    </td>
                    <td>
                        <div>{{ $p->pengaju->nama ?? '-' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            @if($p->pengaju_type == 'admin')
                                <span class="badge-subject" style="background: #E8D5B5; font-size: 10px;">Admin</span>
                            @elseif($p->pengaju_type == 'guru')
                                <span class="badge-subject" style="background: #E9F0E9; font-size: 10px;">Guru</span>
                            @else
                                <span class="badge-muted" style="font-size: 10px;">-</span>
                            @endif
                        </div>
                    </td>
                    <td style="max-width: 150px; font-size: 12px; color: var(--text-secondary);">{{ $p->alasan ?? '-' }}</td>
                    <td>
                        @if($p->statusApproval == 'pending')
                            <span class="badge-warning">Menunggu</span>
                        @elseif($p->statusApproval == 'disetujui')
                            <span class="badge-success">Disetujui</span>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $p->approval->approved_at?->format('d/m/Y') ?? '' }}</div>
                        @else
                            <span class="badge-error">Ditolak</span>
                            @if($p->approval->catatan)
                            <div style="font-size: 11px; color: #c62828;">{{ $p->approval->catatan }}</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($p->statusApproval == 'pending')
                        <div style="display: flex; gap: 6px;">
                            <form method="POST" action="{{ route('admin.munaqosyah.approval.setuju', $p->approval->id ?? 0) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="return confirm({{ json_encode('Setujui pendaftaran '.($p->siswa->nama ?? '').' ke '.($p->munaqosyah->nama ?? '').'?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})">Setuju</button>
                            </form>
                            <button onclick="document.getElementById('tolak-{{ $p->id }}').style.display='block'" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;">Tolak</button>
                        </div>
                        <div id="tolak-{{ $p->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.munaqosyah.approval.tolak', $p->approval->id ?? 0) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 12px;">Alasan Penolakan</label>
                                    <textarea name="catatan" class="form-input" rows="2" required></textarea>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Tolak</button>
                                    <button type="button" onclick="document.getElementById('tolak-{{ $p->id }}').style.display='none'" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">{{ $p->approval->approved_at ? $p->approval->approved_at->format('d/m/Y H:i') : '-' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align: center; color: var(--text-muted); padding: 40px;">Tidak ada pengajuan pendaftaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pendaftarans->links() }}

    {{-- Hidden forms for mass actions --}}
    <form id="form-setuju-massal" method="POST" action="{{ route('admin.munaqosyah.approval.setuju-massal') }}" style="display: none;">
        @csrf
    </form>
    <form id="form-tolak-massal" method="POST" action="{{ route('admin.munaqosyah.approval.tolak-massal') }}" style="display: none;">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleCheckAll() {
    const checkAll = document.getElementById('check-all');
    const checks = document.querySelectorAll('.approval-check');
    checks.forEach(c => c.checked = checkAll.checked);
}
function submitSetujuMassal() {
    const checks = document.querySelectorAll('.approval-check:checked');
    if (checks.length === 0) { alert('Pilih minimal 1 pendaftaran.'); return; }
    if (!confirm('Setujui ' + checks.length + ' pendaftaran terpilih?')) return;
    const form = document.getElementById('form-setuju-massal');
    checks.forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'ids[]'; input.value = c.value;
        form.appendChild(input);
    });
    form.submit();
}
function submitTolakMassal() {
    const checks = document.querySelectorAll('.approval-check:checked');
    if (checks.length === 0) { alert('Pilih minimal 1 pendaftaran.'); return; }
    if (!confirm('Tolak ' + checks.length + ' pendaftaran terpilih?')) return;
    const form = document.getElementById('form-tolak-massal');
    checks.forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'ids[]'; input.value = c.value;
        form.appendChild(input);
    });
    form.submit();
}
</script>
@endpush
