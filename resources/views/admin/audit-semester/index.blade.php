@extends('layouts.admin')

@section('title', 'Audit Semester - ' . $selectedTa)

@section('content')
<style>
/* ===== SEMESTER CARD ===== */
.semester-card {
    background: #ffffff;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.semester-card:hover {
    border-color: #0c8a5f;
    box-shadow: 0 6px 24px rgba(12,138,95,0.18);
    transform: translateY(-2px);
}
.semester-card.active {
    border-color: #0c8a5f;
    background: #f1f8f4;
    box-shadow: 0 4px 16px rgba(12,138,95,0.15);
}
.semester-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.semester-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
}
.semester-card-status {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: 0.5px;
}
.status-ditutup { background: #f8d7da; color: #721c24; }
.status-aktif { background: #d4edda; color: #155724; }
.semester-card-stats {
    display: flex;
    gap: 14px;
    font-size: 12px;
    color: #555;
    margin-bottom: 6px;
}
.semester-card-stats span strong {
    color: #0c8a5f;
}
.semester-card-periode {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}

/* ===== AUDIT DETAIL CARD ===== */
.audit-detail-card {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.audit-detail-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: background 0.2s;
    background: #f8faf8;
    border-bottom: 1px solid #e0e0e0;
}
.audit-detail-header:hover {
    background: #e8f5e9;
}
.audit-detail-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 10px;
}
.audit-detail-badge {
    background: #0c8a5f;
    color: #fff;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
}
.audit-detail-body {
    display: none;
    padding: 20px;
    background: #fafbfa;
}
.audit-detail-body.active {
    display: block;
}
.toggle-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    background: #fff;
    border: 1px solid #ddd;
    transition: transform 0.2s;
    font-size: 12px;
    color: #0c8a5f;
}
.toggle-icon.open {
    transform: rotate(180deg);
}

/* ===== MUNAQOSYAH CARD ===== */
.mq-detail-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.mq-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}
.mq-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}
.mq-stat-item {
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    background: #f8faf8;
    border: 1px solid #e8f5e9;
}
.mq-stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #0c8a5f;
}
.mq-stat-label {
    font-size: 10px;
    color: #888;
    margin-top: 4px;
}

/* ===== KELAS CARD ===== */
.kelas-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.kelas-header {
    padding: 12px 16px;
    background: #e3f2fd;
    color: #1565c0;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kelas-header:hover {
    background: #bbdefb;
}
.kelas-body {
    display: none;
    padding: 16px;
    background: #fafbfa;
}
.kelas-body.active {
    display: block;
}

/* ===== BADGES ===== */
.status-badge-L { background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.status-badge-TL { background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.status-badge-T { background: #fff3cd; color: #856404; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }

/* ===== UTILITIES ===== */
.empty-state {
    text-align: center;
    padding: 32px;
    color: #888;
    font-size: 13px;
    background: #f8faf8;
    border-radius: 8px;
}
.btn-sm {
    font-size: 11px;
    padding: 6px 14px;
    border-radius: 6px;
}
.breadcrumb {
    font-size: 13px;
    color: #666;
    margin-bottom: 20px;
    padding: 10px 16px;
    background: #f5f5f5;
    border-radius: 8px;
}
.breadcrumb a {
    color: #0c8a5f;
    text-decoration: none;
    font-weight: 500;
}
.breadcrumb a:hover {
    text-decoration: underline;
}
.ringkasan-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.stat-box {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.stat-box .val {
    font-size: 24px;
    font-weight: 700;
    color: #0c8a5f;
}
.stat-box .lbl {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}
</style>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ route('admin.audit-semester.pilih-ta') }}">Pilih Tahun Ajaran</a>
    <span style="margin: 0 8px;">&rsaquo;</span>
    <strong>TA {{ $selectedTa }}</strong>
    @if($selectedSemester)
        <span style="margin: 0 8px;">&rsaquo;</span>
        <strong>{{ $selectedSemester->nama }}</strong>
    @endif
</div>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">TA {{ $selectedTa }}</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Pilih semester untuk melihat track record detail</p>
    </div>
</div>

{{-- ====== STEP 2: DAFTAR SEMESTER ====== --}}
<div style="margin-bottom: 24px;">
    <h3 style="font-size: 14px; font-weight: 700; color: #555; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">&#128197; Pilih Semester</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        @foreach($semesterList as $sem)
            <a href="{{ route('admin.audit-semester.index', ['ta' => $selectedTa, 'semester_id' => $sem->id]) }}"
               class="semester-card {{ $selectedSemester && $selectedSemester->id == $sem->id ? 'active' : '' }}">
                <div class="semester-card-header">
                    <span class="semester-card-title">{{ $sem->nama }}</span>
                    <span class="semester-card-status status-{{ $sem->status }}">
                        {{ $sem->status === 'ditutup' ? 'TERKUNCI' : 'AKTIF' }}
                    </span>
                </div>
                <div class="semester-card-stats">
                    <span><strong>{{ $sem->total_siswa }}</strong> siswa</span>
                    <span><strong>{{ $sem->total_munaqosyah }}</strong> munaqosyah</span>
                    <span><strong>{{ $sem->total_penilaian }}</strong> penilaian</span>
                </div>
                @php
                    $semRataR2 = \App\Models\RekapR2Akhir::where('semester_id', $sem->id)->avg('r2_akhir');
                    $semTotalMengaji = \App\Models\RekapJurnalSemester::where('semester_id', $sem->id)->sum('total_hari');
                    $semAvgMengaji = $sem->total_siswa > 0 ? round($semTotalMengaji / $sem->total_siswa, 0) : 0;
                @endphp
                <div style="display: flex; gap: 16px; margin-top: 4px; font-size: 11px; color: #888;">
                    <span>R2: <strong style="color: #0c8a5f;">{{ $semRataR2 ? round($semRataR2, 1) : '-' }}</strong></span>
                    <span>Mengaji: <strong>{{ $semAvgMengaji }} hari</strong></span>
                </div>
                <div class="semester-card-periode">{{ $sem->tanggal_mulai?->format('d/m/Y') ?? '-' }} &ndash; {{ $sem->tanggal_selesai?->format('d/m/Y') ?? '-' }}</div>
            </a>
        @endforeach
    </div>
</div>

{{-- ====== STEP 3: DETAIL SEMESTER ====== --}}
@if($selectedSemester && $rekapData)
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 14px; font-weight: 700; color: #555; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">&#128203; Detail {{ $selectedSemester->nama }}</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.audit-semester.export-pdf', $selectedSemester) }}" class="btn-tartil btn-sm">&#128196; Export PDF</a>
            <a href="{{ route('admin.audit-semester.export-excel', $selectedSemester) }}" class="btn-tartil btn-sm">&#128202; Export Excel</a>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="ringkasan-grid" style="margin-bottom: 20px;">
        <div class="stat-box">
            <div class="val">{{ $rekapData['totalSiswa'] }}</div>
            <div class="lbl">Total Siswa</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $rekapData['rataR2Akhir'] }}</div>
            <div class="lbl">R2 Akhir Rata-rata</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $rekapData['rataMengaji'] }}</div>
            <div class="lbl">Mengaji Rata-rata</div>
        </div>
        <div class="stat-box">
            <div class="val" style="color: {{ $selectedSemester->status === 'ditutup' ? '#2e7d32' : '#e65100' }};">
                {{ $selectedSemester->status === 'ditutup' ? 'TERKUNCI' : 'AKTIF' }}
            </div>
            <div class="lbl">Status Data</div>
        </div>
    </div>

    {{-- CARD: MUNAQOSYAH --}}
    <div class="audit-detail-card">
        <div class="audit-detail-header" onclick="toggleDetail('mq')">
            <div class="audit-detail-title">&#127942; Munaqosyah <span class="audit-detail-badge">{{ count($rekapData['munaqosyahList'] ?? []) }} ujian</span></div>
            <span class="toggle-icon" id="icon-mq">&#9660;</span>
        </div>
        <div class="audit-detail-body" id="body-mq">
            @if(!empty($rekapData['munaqosyahList']))
                @foreach($rekapData['munaqosyahList'] as $mq)
                    <div class="mq-detail-card">
                        <div class="mq-detail-header">
                            <strong style="font-size: 14px;">{{ $mq['ujian']->nama }}</strong>
                            <span style="font-size: 12px; color: #888;">{{ $mq['ujian']->tanggal_ujian?->format('d/m/Y') ?? '-' }} &middot; {{ $mq['ujian']->tingkat }}</span>
                        </div>
                        <div class="mq-stat-grid">
                            <div class="mq-stat-item">
                                <div class="mq-stat-value">{{ $mq['total'] }}</div>
                                <div class="mq-stat-label">Peserta</div>
                            </div>
                            <div class="mq-stat-item" style="background: #d4edda; border-color: #a5d6a7;">
                                <div class="mq-stat-value" style="color: #155724;">{{ $mq['lulus'] }}</div>
                                <div class="mq-stat-label" style="color: #2e7d32;">Lulus</div>
                            </div>
                            <div class="mq-stat-item" style="background: #f8d7da; border-color: #ef9a9a;">
                                <div class="mq-stat-value" style="color: #721c24;">{{ $mq['tidakLulus'] }}</div>
                                <div class="mq-stat-label" style="color: #c62828;">Tidak Lulus</div>
                            </div>
                            <div class="mq-stat-item">
                                <div class="mq-stat-value" style="color: #1565c0;">{{ $mq['rataNilai'] }}</div>
                                <div class="mq-stat-label">Rata-rata</div>
                            </div>
                        </div>
                        @if(!empty($mq['peserta']))
                            <div class="table-responsive">
                                <table class="table-tartil" style="font-size: 12px;">
                                    <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Nilai</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach($mq['peserta'] as $i => $p)
                                            <tr><td>{{ $i + 1 }}</td><td>{{ $p['siswa']->nis ?? '-' }}</td><td>{{ $p['siswa']->nama ?? '-' }}</td><td>{{ $p['nilai'] ?? '-' }}</td><td><span class="status-badge-{{ $p['status'] }}">{{ $p['status'] === 'L' ? 'Lulus' : ($p['status'] === 'TL' ? 'Tidak Lulus' : 'Terdaftar') }}</span></td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-state">Tidak ada data munaqosyah</div>
            @endif
        </div>
    </div>

    {{-- CARD: PENILAIAN RAPOR --}}
    <div class="audit-detail-card">
        <div class="audit-detail-header" onclick="toggleDetail('nilai')">
            <div class="audit-detail-title">&#128221; Penilaian Rapor <span class="audit-detail-badge">{{ count($rekapData['penilaianList'] ?? []) }} penilaian</span></div>
            <span class="toggle-icon" id="icon-nilai">&#9660;</span>
        </div>
        <div class="audit-detail-body" id="body-nilai">
            @if(!empty($rekapData['penilaianList']))
                @foreach($rekapData['penilaianList'] as $pn)
                    <div class="mq-detail-card">
                        {{-- Header penilaian --}}
                        <div class="mq-detail-header">
                            <strong style="font-size: 14px;">{{ $pn['penilaian']->nama }}</strong>
                            <span style="font-size: 12px; color: #888;">{{ $pn['totalSiswa'] }} siswa total</span>
                        </div>

                        {{-- Loop per kelas tartil --}}
                        @if(!empty($pn['perKelasTartil']))
                            @foreach($pn['perKelasTartil'] as $pkt)
                                {{-- Header kelas tartil --}}
                                <div style="background: #e8f5e9; border-left: 4px solid #0c8a5f; border-radius: 6px; padding: 10px 14px; margin: 12px 0 8px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 13px; font-weight: 700; color: #0c8a5f;">&#127968; Kelas {{ $pkt['jenisKelas'] }}</span>
                                    <span style="font-size: 11px; color: #555;">{{ $pkt['totalSiswa'] }} siswa</span>
                                </div>

                                {{-- Indikator khusus kelas ini --}}
                                @if(!empty($pkt['indikatorNames']))
                                    <div style="font-size: 11px; color: #666; margin-bottom: 8px; padding-left: 8px;">
                                        Indikator:
                                        @foreach($pkt['indikatorNames'] as $ind)
                                            <span style="display: inline-block; background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-right: 4px; font-weight: 600;">{{ $ind }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Tabel nilai per siswa di kelas ini --}}
                                @if(!empty($pkt['nilaiPerSiswa']))
                                    <div class="table-responsive">
                                        <table class="table-tartil" style="font-size: 12px;">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>NIS</th>
                                                    <th>Nama</th>
                                                    <th>Rata-rata</th>
                                                    @if(!empty($pkt['nilaiPerSiswa'][0]['detail']))
                                                        @foreach($pkt['nilaiPerSiswa'][0]['detail'] as $d)
                                                            <th>{{ $d['indikator'] }}</th>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pkt['nilaiPerSiswa'] as $i => $ns)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $ns['siswa']->nis ?? '-' }}</td>
                                                        <td>{{ $ns['siswa']->nama ?? '-' }}</td>
                                                        <td><strong>{{ $ns['nilaiRata'] }}</strong></td>
                                                        @foreach($ns['detail'] as $d)
                                                            <td>{{ $d['nilai'] }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="empty-state" style="padding: 16px;">Tidak ada data nilai</div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-state">Tidak ada data penilaian</div>
            @endif
        </div>
    </div>

    {{-- CARD: SISWA PER KELAS REGULER --}}
    <div class="audit-detail-card">
        <div class="audit-detail-header" onclick="toggleDetail('siswa')">
            <div class="audit-detail-title">&#128101; Siswa per Kelas Reguler <span class="audit-detail-badge">{{ count($rekapData['siswaPerKelasReguler'] ?? []) }} kelas</span></div>
            <span class="toggle-icon" id="icon-siswa">&#9660;</span>
        </div>
        <div class="audit-detail-body" id="body-siswa">
            @if(!empty($rekapData['siswaPerKelasReguler']))
                @foreach($rekapData['siswaPerKelasReguler'] as $kelasRegulerNama => $siswaKelas)
                    <div class="kelas-card">
                        <div class="kelas-header" onclick="toggleKelas('{{ md5($kelasRegulerNama) }}')">
                            <span>{{ $kelasRegulerNama }}</span>
                            <span style="font-size: 11px; background: rgba(255,255,255,0.5); padding: 2px 10px; border-radius: 10px;">{{ count($siswaKelas) }} siswa</span>
                        </div>
                        <div class="kelas-body" id="kelas-{{ md5($kelasRegulerNama) }}">
                            <div class="table-responsive">
                                <table class="table-tartil" style="font-size: 12px;">
                                    <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas Tartil</th><th>R2 Harian</th><th>R2 Penilaian</th><th>R2 Akhir</th><th>Mengaji</th><th>B/C/K</th><th>Munaqosyah</th></tr></thead>
                                    <tbody>
                                        @foreach($siswaKelas as $i => $d)
                                            <tr><td>{{ $i + 1 }}</td><td>{{ $d['siswa']->nis }}</td><td>{{ $d['siswa']->nama }}</td><td>{{ $d['kelasTartil'] }}</td><td>{{ $d['r2Harian'] }}</td><td>{{ $d['r2Penilaian'] }}</td><td><strong>{{ $d['r2Akhir'] }}</strong></td><td>{{ $d['totalHari'] }} hari</td><td>{{ $d['countB'] }}/{{ $d['countC'] }}/{{ $d['countK'] }}</td><td>{{ $d['munaqosyahStatus'] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">Tidak ada data siswa</div>
            @endif
        </div>
    </div>

    @if($selectedSemester->status !== 'ditutup')
        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; padding: 14px 20px; margin-bottom: 32px; color: #856404; font-size: 13px;">
            <strong>&#9888; Perhatian:</strong> Semester ini belum ditutup. Data di atas adalah real-time, bukan snapshot terkunci.
        </div>
    @endif
@endif

<script>
function toggleDetail(id) {
    const body = document.getElementById('body-' + id);
    const icon = document.getElementById('icon-' + id);
    body.classList.toggle('active');
    icon.classList.toggle('open');
}
function toggleKelas(hash) {
    const body = document.getElementById('kelas-' + hash);
    body.classList.toggle('active');
}
</script>
@endsection
