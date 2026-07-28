@extends('layouts.admin')

@section('title', "Audit - {$siswa->nama}")

@section('content')
<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 24px; margin: 0;">
            Audit: {{ $siswa->nama }}
        </h1>
        <p class="page-subtitle" style="color: var(--text-muted); font-size: 14px; margin: 4px 0 0;">
            NIS: {{ $siswa->nis }} | Semester: {{ $semester->nama }} | Status: 
            @if($semester->status === 'ditutup')
                <span style="color: var(--success); font-weight: 600;">TERKUNCI</span>
            @else
                <span style="color: var(--warning);">BELUM TERKUNCI</span>
            @endif
        </p>
    </div>
    <a href="{{ route('admin.audit-semester.index', ['ta' => $semester->tahun_ajaran, 'semester_id' => $semester->id]) }}" class="btn-tartil" style="font-size: 12px; padding: 8px 16px;">Kembali</a>
</div>

{{-- R2 Summary --}}
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;">
    <div class="card-tartil" style="text-align: center;">
        <div class="stat-label">R2 Harian</div>
        <div class="stat-value" style="font-size: 32px;">{{ $snapR2?->r2_harian ?? 0 }}</div>
    </div>
    <div class="card-tartil" style="text-align: center;">
        <div class="stat-label">R2 Penilaian</div>
        <div class="stat-value" style="font-size: 32px;">{{ $snapR2?->r2_penilaian ?? 0 }}</div>
    </div>
    <div class="card-tartil" style="text-align: center;">
        <div class="stat-label">R2 Akhir</div>
        <div class="stat-value" style="font-size: 32px; color: var(--accent);">{{ $snapR2?->r2_akhir ?? 0 }}</div>
    </div>
</div>

{{-- Jurnal Detail --}}
<div class="card-tartil" style="margin-bottom: 20px;">
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 12px;">Jurnal Harian</h3>
    @if($snapJurnal)
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 12px;">
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Total Hari</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapJurnal->total_hari }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--success);">Baik (B)</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapJurnal->count_b }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--warning);">Cukup (C)</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapJurnal->count_c }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--danger);">Kurang (K)</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapJurnal->count_k }}</div>
            </div>
        </div>

        @if(!empty($snapJurnal->detail_bulanan))
            <h4 style="font-size: 13px; font-weight: 600; margin: 12px 0 8px;">Rekap Bulanan</h4>
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 12px;">
                    <thead>
                        <tr><th>Bulan</th><th>Total</th><th>B</th><th>C</th><th>K</th><th>%B</th></tr>
                    </thead>
                    <tbody>
                        @foreach($snapJurnal->detail_bulanan as $b)
                            <tr>
                                <td>{{ $b['bulan'] ?? '-' }}</td>
                                <td>{{ $b['total'] ?? 0 }}</td>
                                <td>{{ $b['b'] ?? 0 }}</td>
                                <td>{{ $b['c'] ?? 0 }}</td>
                                <td>{{ $b['k'] ?? 0 }}</td>
                                <td>{{ $b['persentase_b'] ?? 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <p style="color: var(--text-muted); font-size: 13px;">Tidak ada data jurnal untuk semester ini.</p>
    @endif
</div>

{{-- Munaqosyah Detail --}}
<div class="card-tartil" style="margin-bottom: 20px;">
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 12px;">Munaqosyah</h3>
    @if($snapMunaqosyah && $snapMunaqosyah->total_ujian > 0)
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 12px;">
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Total Ujian</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapMunaqosyah->total_ujian }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--success);">Lulus</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapMunaqosyah->total_lulus }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--danger);">Tidak Lulus</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapMunaqosyah->total_tidak_lulus }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Rata-rata Nilai</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $snapMunaqosyah->rata_rata_nilai ?? '-' }}</div>
            </div>
        </div>

        @if(!empty($snapMunaqosyah->detail_ujian))
            <h4 style="font-size: 13px; font-weight: 600; margin: 12px 0 8px;">Detail Ujian</h4>
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 12px;">
                    <thead>
                        <tr><th>Ujian</th><th>Tingkat</th><th>Status</th><th>Nilai</th><th>Catatan</th></tr>
                    </thead>
                    <tbody>
                        @foreach($snapMunaqosyah->detail_ujian as $u)
                            <tr>
                                <td>{{ $u['nama_ujian'] ?? '-' }}</td>
                                <td>{{ $u['tingkat'] ?? '-' }}</td>
                                <td>
                                    @if(($u['status'] ?? '') === 'L')
                                        <span class="badge badge-success">Lulus</span>
                                    @elseif(($u['status'] ?? '') === 'TL')
                                        <span class="badge badge-error">Tidak Lulus</span>
                                    @else
                                        <span class="badge badge-warning">Terdaftar</span>
                                    @endif
                                </td>
                                <td>{{ $u['nilai'] ?? '-' }}</td>
                                <td>{{ $u['catatan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <p style="color: var(--text-muted); font-size: 13px;">Tidak mengikuti ujian munaqosyah pada semester ini.</p>
    @endif
</div>

{{-- Riwayat Kelas --}}
<div class="card-tartil" style="margin-bottom: 20px;">
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 12px;">Riwayat Kelas</h3>
    @if($snapRiwayat)
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px;">
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Kelas Tartil Akhir</div>
                <div style="font-size: 14px; font-weight: 600;">{{ $snapRiwayat->kelasTartil?->nama ?? '-' }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Kelas Reguler Akhir</div>
                <div style="font-size: 14px; font-weight: 600;">{{ $snapRiwayat->kelasReguler?->nama ?? '-' }}</div>
            </div>
            <div style="padding: 8px; background: var(--bg-elevated); border-radius: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">Pindah Kelas</div>
                <div style="font-size: 14px; font-weight: 600;">{{ $snapRiwayat->jumlah_pindah_tartil + $snapRiwayat->jumlah_pindah_reguler }}x</div>
            </div>
        </div>

        @if(!empty($snapRiwayat->detail_perpindahan))
            <h4 style="font-size: 13px; font-weight: 600; margin: 12px 0 8px;">Detail Perpindahan</h4>
            <div class="table-responsive">
                <table class="table-tartil" style="font-size: 12px;">
                    <thead>
                        <tr><th>Jenis</th><th>Dari</th><th>Ke</th><th>Tanggal</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($snapRiwayat->detail_perpindahan as $p)
                            <tr>
                                <td>{{ $p['jenis'] ?? '-' }}</td>
                                <td>{{ $p['dari_kelas'] ?? '-' }}</td>
                                <td>{{ $p['ke_kelas'] ?? '-' }}</td>
                                <td>{{ $p['tanggal'] ?? '-' }}</td>
                                <td>{{ $p['status'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($snapRiwayat->detail_kenaikan)
            <div style="margin-top: 12px; padding: 10px; background: var(--accent-soft); border-radius: 8px; font-size: 13px;">
                <strong>Kenaikan Kelas Reguler:</strong> 
                {{ $snapRiwayat->detail_kenaikan['dari_reguler'] ?? '-' }} → 
                {{ $snapRiwayat->detail_kenaikan['ke_reguler'] ?? '-' }}
                @if(!empty($snapRiwayat->detail_kenaikan['tanggal']))
                    ({{ $snapRiwayat->detail_kenaikan['tanggal'] }})
                @endif
            </div>
        @endif
    @else
        <p style="color: var(--text-muted); font-size: 13px;">Tidak ada data riwayat kelas untuk semester ini.</p>
    @endif
</div>

{{-- Audit Log --}}
<div class="card-tartil">
    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 12px;">Log Audit</h3>
    @if($auditLogs->count() > 0)
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 12px;">
                <thead>
                    <tr><th>Waktu</th><th>Tipe</th><th>Aksi</th><th>Jumlah Record</th><th>Oleh</th></tr>
                </thead>
                <tbody>
                    @foreach($auditLogs as $log)
                        <tr>
                            <td>{{ $log->locked_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ ucfirst($log->tipe) }}</td>
                            <td>{{ ucfirst($log->aksi) }}</td>
                            <td>{{ $log->jumlah_record }}</td>
                            <td>{{ $log->user?->nama ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 13px;">Belum ada log audit untuk semester ini.</p>
    @endif
</div>
@endsection
