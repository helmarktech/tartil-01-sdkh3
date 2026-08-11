@extends('layouts.admin')
@section('title', 'Detail Ujian Munaqosyah')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">{{ $munaqosyah->nama }}</h1>
            <p class="page-subtitle">{{ ucfirst($munaqosyah->tingkat) }} - {{ $munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($munaqosyah->tanggal_ujian)) : '-' }}</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.munaqosyah.daftar', ['ujian_id' => $munaqosyah->id]) }}" class="btn-tartil">Daftar Siswa Ujian</a>
            @if($munaqosyah->total_pendaftar > 0)
            <a href="{{ route('admin.munaqosyah.export-excel', $munaqosyah) }}" class="btn-tartil-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Excel
            </a>
            @endif
            <a href="{{ route('admin.munaqosyah.index') }}" class="btn-tartil-outline">Kembali</a>
        </div>
    </div>

    {{-- Info Ujian --}}
    <div class="card-tartil" style="margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Nama Ujian</div>
                <div style="font-weight: 600; color: var(--text-primary);">{{ $munaqosyah->nama }}</div>
            </div>
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Tingkat</div>
                <span class="badge-subject">{{ ucfirst($munaqosyah->tingkat) }}</span>
            </div>
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Tanggal Ujian</div>
                <div style="color: var(--text-primary);">{{ $munaqosyah->tanggal_ujian ? date('d/m/Y', strtotime($munaqosyah->tanggal_ujian)) : '-' }}</div>
            </div>
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Semester</div>
                <div style="color: var(--text-primary);">{{ $munaqosyah->semester->nama ?? '-' }}</div>
            </div>
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Status</div>
                @if($munaqosyah->status == 'pengajuan')
                    <span class="badge-warning">Pengajuan</span>
                @elseif($munaqosyah->status == 'disetujui')
                    <span class="badge-success">Disetujui</span>
                @elseif($munaqosyah->status == 'berlangsung')
                    <span class="badge-info">Berlangsung</span>
                @elseif($munaqosyah->status == 'selesai')
                    <span class="badge-subject">Selesai</span>
                @else
                    <span class="badge-error">Ditolak</span>
                @endif
            </div>
            <div>
                <div class="form-label" style="margin-bottom: 4px;">Dibuat Oleh</div>
                <div style="color: var(--text-primary);">{{ $munaqosyah->pengaju->nama ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkasan --}}
    <h3 style="font-size: 16px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Statistik Pendaftar</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 24px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--accent);">{{ $munaqosyah->total_pendaftar }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Peserta</div>
        </div>
        @if($munaqosyah->total_pending > 0)
        <div class="card-tartil" style="text-align: center; cursor: pointer;" onclick="window.location='{{ route('admin.munaqosyah.approval.index') }}'">
            <div style="font-size: 28px; font-weight: 700; color: #D4A373;">{{ $munaqosyah->total_pending }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Menunggu Approval</div>
            <div style="font-size: 10px; color: var(--accent); margin-top: 4px;">Klik untuk lihat</div>
        </div>
        @endif
        @php
            $totalTerdaftar = $munaqosyah->pendaftarans->where('status', 'T')->count();
        @endphp
        @if($totalTerdaftar > 0)
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: #8B9DAF;">{{ $totalTerdaftar }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Terdaftar (T)</div>
        </div>
        @endif
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: #5A7D5A;">{{ $munaqosyah->total_lulus }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Lulus</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: #A85A52;">{{ $munaqosyah->total_tidak_lulus }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Tidak Lulus</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--text-secondary);">{{ $munaqosyah->total_menunggu }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Menunggu Penilaian</div>
        </div>
    </div>

    {{-- Peserta Overview (hanya nama & status, tanpa aksi) --}}
    {{-- Hanya tampilkan siswa yang sudah terdaftar, bukan pending --}}
    @php
        $pesertaAktif = $munaqosyah->pendaftarans()->with('siswa.kelasReguler', 'siswa.kelasTartil')
            ->orderByRaw("FIELD(status, 'T', 'L', 'TL')")
            ->orderBy('created_at')
            ->get();
    @endphp
    @if($pesertaAktif->count() > 0)
    {{-- Toggle Form Nilai --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3 style="font-size: 16px; margin: 0; color: var(--text-primary); font-weight: 600;">Daftar Peserta</h3>
        <button type="button" class="btn-tartil" style="font-size: 12px; padding: 6px 14px;" onclick="document.getElementById('formNilaiAdmin').classList.toggle('d-none');">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Input Nilai
        </button>
    </div>

    {{-- Form Input Nilai (Admin) --}}
    <div id="formNilaiAdmin" class="d-none" style="margin-bottom: 20px;">
        <form method="POST" action="{{ route('admin.munaqosyah.nilai.admin', $munaqosyah) }}" class="card-tartil" style="padding: 20px;">
            @csrf
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="font-size: 14px; margin: 0;">Input Nilai Munaqosyah</h4>
                <button type="button" class="btn-tartil-outline" style="font-size: 11px; padding: 4px 10px;" onclick="lulusSemua()">Lulus Semua</button>
            </div>
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th style="width: 70px;">Status</th>
                            <th style="width: 60px;">Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaAktif as $i => $p)
                        @php $isPending = $p->approval && $p->approval->status === 'pending'; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td>
                                {{ $p->siswa->nama ?? '-' }}
                                @if($isPending)
                                <span class="badge-warning" style="font-size: 9px;">Belum Approve</span>
                                @endif
                            </td>
                            <td>
                                <select name="nilai[{{ $i }}][status]" class="form-input status-select" style="font-size: 12px; padding: 4px 6px;" {{ $isPending ? 'disabled' : '' }}>
                                    <option value="T" {{ $p->status == 'T' ? 'selected' : '' }}>Terdaftar</option>
                                    <option value="L" {{ $p->status == 'L' ? 'selected' : '' }}>Lulus</option>
                                    <option value="TL" {{ $p->status == 'TL' ? 'selected' : '' }}>Tidak Lulus</option>
                                </select>
                                <input type="hidden" name="nilai[{{ $i }}][pendaftaran_id]" value="{{ $p->id }}">
                            </td>
                            <td>
                                <input type="number" name="nilai[{{ $i }}][nilai]" value="{{ $p->nilai }}" min="0" max="100" class="form-input" style="font-size: 12px; padding: 4px 6px; width: 60px; text-align: center;" {{ $isPending ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" name="nilai[{{ $i }}][catatan]" value="{{ $p->catatan }}" class="form-input" style="font-size: 12px; padding: 4px 6px;" placeholder="Catatan..." {{ $isPending ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 12px;">
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 20px;">Simpan Nilai</button>
                <span style="font-size: 11px; color: var(--text-muted); margin-left: 10px;">Siswa yang belum di-approve tidak dapat dinilai.</span>
            </div>
        </form>
    </div>

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas Reguler</th>
                    <th>Kelas Tartil</th>
                    <th>Status</th>
                    <th>Nilai</th>
                    <th style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pesertaAktif as $i => $p)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $p->siswa->nis ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>{{ $p->siswa->kelasReguler->nama ?? '-' }}</td>
                    <td>{{ $p->siswa->kelasTartil->nama ?? '-' }}</td>
                    <td>
                        <span class="{{ $p->status_badge_class }}">{{ $p->status_label }}</span>
                    </td>
                    <td>{{ $p->nilai ?? '-' }}</td>
                    <td style="text-align: center;">
                        <form method="POST" action="{{ route('admin.munaqosyah.peserta.batal', [$munaqosyah, $p]) }}" style="display:inline;" onsubmit="return confirm({{ json_encode('Yakin membatalkan pendaftaran '.($p->siswa->nama ?? 'siswa').'?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }});">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:11px;font-weight:600;padding:2px 6px;border-radius:4px;" title="Batalkan Pendaftaran">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Batal
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        @if($munaqosyah->total_menunggu > 0)
            <div style="font-size: 14px; color: #D4A373; font-weight: 500;">{{ $munaqosyah->total_menunggu }} siswa menunggu penilaian (T).</div>
        @else
            <div style="font-size: 14px; color: var(--text-muted);">Belum ada peserta terdaftar.</div>
            <a href="{{ route('admin.munaqosyah.daftar', ['ujian_id' => $munaqosyah->id]) }}" class="btn-tartil" style="margin-top: 12px;">Daftarkan Siswa</a>
        @endif
    </div>
    @endif
</div>

<script>
function lulusSemua() {
    document.querySelectorAll('.status-select').forEach(function(sel) {
        if (!sel.disabled) sel.value = 'L';
    });
}
</script>

<style>
.d-none { display: none !important; }
</style>
@endsection
