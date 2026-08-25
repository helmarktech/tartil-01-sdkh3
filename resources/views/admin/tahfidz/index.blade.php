@extends('layouts.admin')

@section('title', 'Tahfidz & Hafalan')

@section('content')
<style>
.tahfidz-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.2s;
}
.tahfidz-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.tahfidz-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e8f5e9;
    flex-wrap: wrap;
    gap: 12px;
}
.tahfidz-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
}
.tahfidz-meta {
    font-size: 12px;
    color: #888;
}
.siswa-row {
    display: grid;
    grid-template-columns: 40px 1fr 80px 90px 110px 110px 90px;
    gap: 8px;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-size: 13px;
}
.siswa-row:nth-child(odd) {
    background: #f8faf8;
}
.siswa-row:hover {
    background: #e8f5e9;
}
.juz-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #0c8a5f;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}
.kualitas-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.kualitas-mumtaz { background: #d4edda; color: #155724; }
.kualitas-jayyid_jiddan { background: #e3f2fd; color: #1565c0; }
.kualitas-jayyid { background: #fff3cd; color: #856404; }
.kualitas-naqis { background: #fce4ec; color: #880e4f; }
.status-badge-tf {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-lanjutan { background: #e8f5e9; color: #0c8a5f; }
.status-baru { background: #fff3cd; color: #856404; }
.empty-tahfidz {
    text-align: center;
    padding: 48px;
    color: #888;
}
.summary-tahfidz {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.summary-box-tf {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.summary-box-tf .val {
    font-size: 24px;
    font-weight: 700;
    color: #0c8a5f;
}
.summary-box-tf .lbl {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}
.semester-panel {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.semester-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.semester-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}
.semester-sub {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}
.btn-outline-tf {
    padding: 8px 16px;
    border: 1px solid #0c8a5f;
    color: #0c8a5f;
    background: #fff;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}
.btn-outline-tf:hover {
    background: #f4fbf7;
}
.juz-panel {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.juz-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    min-width: 160px;
}
.juz-surat-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.juz-surat-chip {
    background: #f4fbf7;
    border: 1px solid #d1e7dd;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 12px;
    color: #155724;
}
.juz-progress-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-top: 12px;
}
.juz-progress-table th {
    text-align: left;
    padding: 10px 12px;
    background: #f8faf8;
    font-size: 11px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
}
.juz-progress-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.progress-bar-bg {
    background: #e9ecef;
    border-radius: 6px;
    height: 8px;
    width: 100%;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: #0c8a5f;
    border-radius: 6px;
    transition: width 0.3s;
}
/* Step indicator */
.step-indicator {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
}
.step-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #888;
}
.step-item.active {
    color: #0c8a5f;
}
.step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}
.step-item.active .step-number {
    background: #0c8a5f;
    color: #fff;
}
.step-connector {
    width: 40px;
    height: 2px;
    background: #e0e0e0;
}
.step-panel {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
}
.step-label {
    font-size: 14px;
    font-weight: 600;
    color: #555;
    margin-bottom: 12px;
    display: block;
}
.step-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
}
.step-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.btn-tf {
    padding: 8px 16px;
    background: #0c8a5f;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.btn-tf:disabled {
    background: #ccc;
    cursor: not-allowed;
}
.btn-tf-outline {
    padding: 8px 16px;
    background: #fff;
    color: #555;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}
@media (max-width: 768px) {
    .siswa-row {
        grid-template-columns: 30px 1fr 70px 80px;
    }
    .siswa-row > div:nth-child(5),
    .siswa-row > div:nth-child(6),
    .siswa-row > div:nth-child(7) {
        display: none;
    }
    .summary-tahfidz {
        grid-template-columns: repeat(2, 1fr);
    }
    .step-connector {
        width: 20px;
    }
}
@media (max-width: 640px) {
    .siswa-row { grid-template-columns: 1fr; gap: 6px; padding: 12px; }
    .siswa-row > div:nth-child(n) { display: block; width: 100%; text-align: left !important; }
    .summary-tahfidz { grid-template-columns: 1fr; }
    .semester-info { flex-direction: column; align-items: flex-start; }
    .juz-panel form { flex-direction: column; align-items: stretch; }
    .juz-select { width: 100%; }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">&#128218; Tahfidz & Hafalan</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Tracking hafalan Al-Quran siswa kelas Tartil</p>
    </div>
</div>

{{-- Step Indicator --}}
<div class="step-indicator">
    <div class="step-item {{ $semester ? 'active' : '' }}">
        <div class="step-number">1</div>
        <span>Pilih Semester</span>
    </div>
    <div class="step-connector"></div>
    <div class="step-item {{ $semester && ! $kelasTerpilih ? 'active' : ($kelasTerpilih ? 'active' : '') }}">
        <div class="step-number">2</div>
        <span>Pilih Kelas</span>
    </div>
    <div class="step-connector"></div>
    <div class="step-item {{ $kelasTerpilih ? 'active' : '' }}">
        <div class="step-number">3</div>
        <span>Lihat Rekap</span>
    </div>
</div>

{{-- Step 1: Pilih Semester --}}
@if(!$semester)
<div class="step-panel">
    <label class="step-label">Langkah 1: Pilih Tahun Ajaran</label>
    <select id="tahunAjaranSelect" class="step-select" onchange="loadSemesters()">
        <option value="">-- Pilih Tahun Ajaran --</option>
        @foreach($tahunAjaranList as $ta)
            <option value="{{ $ta->nama }}">{{ $ta->nama }} {{ $ta->status === 'aktif' ? '(Aktif)' : '' }}</option>
        @endforeach
    </select>

    <form method="GET" action="{{ route('admin.tahfidz.index') }}" id="semesterForm">
        <label class="step-label">Langkah 2: Pilih Semester</label>
        <select name="semester_id" id="semesterSelect" class="step-select" required>
            <option value="">-- Pilih Semester --</option>
        </select>
        <div class="step-actions">
            <button type="submit" class="btn-tf" id="btnStep1" disabled>Lanjut Pilih Kelas</button>
        </div>
    </form>
</div>
@endif

{{-- Step 2: Pilih Kelas --}}
@if($semester && !$kelasTerpilih)
<div class="step-panel">
    <div style="margin-bottom: 16px;">
        <div style="font-size: 12px; color: #888;">Semester terpilih</div>
        <div style="font-size: 16px; font-weight: 700; color: #1a1a2e;">{{ $semester->nama }}</div>
        <div style="font-size: 12px; color: #888;">{{ $semester->tanggal_mulai->format('d M Y') }} s/d {{ $semester->tanggal_selesai->format('d M Y') }}</div>
    </div>

    <form method="GET" action="{{ route('admin.tahfidz.index') }}">
        <input type="hidden" name="semester_id" value="{{ $semester->id }}">
        <label class="step-label">Langkah 3: Pilih Kelas</label>
        <select name="kelas_id" class="step-select" required>
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelasListOptions as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
        <div class="step-actions">
            <a href="{{ route('admin.tahfidz.index') }}" class="btn-tf-outline">Kembali</a>
            <button type="submit" class="btn-tf">Lihat Rekap</button>
        </div>
    </form>
</div>
@endif

{{-- Step 3: Rekap Kelas --}}
@if($kelasTerpilih)
{{-- Panel Semester & Kelas --}}
<div class="semester-panel">
    <div class="semester-info">
        <div>
            <div class="semester-title">
                {{ $kelasTerpilih->nama }}
            </div>
            <div class="semester-sub">
                {{ $semester->nama }} &middot;
                Guru: {{ $kelasTerpilih->guru?->nama ?? '-' }} &middot;
                {{ count($kelasTerpilih->rekap['perSiswa'] ?? []) }} siswa &middot;
                Rata-rata {{ $kelasTerpilih->avgJuz }} juz
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.tahfidz.rekap-semester', ['semester_id' => $semester->id]) }}" class="btn-outline-tf">&#128202; Rekap per Semester</a>
            <a href="{{ route('admin.tahfidz.index', ['semester_id' => $semester->id]) }}" class="btn-outline-tf">&#128260; Pilih Kelas Lain</a>
            <a href="{{ route('admin.tahfidz.index') }}" class="btn-outline-tf">&#127968; Reset</a>
        </div>
    </div>
</div>

{{-- Panel Pilih Juz --}}
<div class="juz-panel">
    <form method="GET" action="{{ route('admin.tahfidz.index') }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <input type="hidden" name="semester_id" value="{{ $semester->id }}">
        <input type="hidden" name="kelas_id" value="{{ $kelasTerpilih->id }}">
        <label style="font-size: 13px; font-weight: 600; color: #555;">Pilih Juz:</label>
        <select name="juz" class="juz-select" onchange="this.form.submit()">
            <option value="">-- Semua Juz --</option>
            @for($j = 1; $j <= 30; $j++)
                <option value="{{ $j }}" {{ $juzSelected == $j ? 'selected' : '' }}>Juz {{ $j }}</option>
            @endfor
        </select>
        @if($juzSelected)
            <span style="font-size: 13px; color: #888;">
                Total {{ $juzSurat->sum('total_ayat') }} ayat &middot; {{ $juzSurat->count() }} surat
            </span>
        @endif
    </form>

    @if($juzSelected && $juzSurat->isNotEmpty())
        <div class="juz-surat-list">
            @foreach($juzSurat as $js)
                <div class="juz-surat-chip" title="Ayat {{ $js->ayat_mulai }}-{{ $js->ayat_selesai }}">
                    {{ $js->surat?->nama_latin ?? '-' }} ({{ $js->total_ayat }} ayat)
                </div>
            @endforeach
        </div>
    @endif

    @if($juzSelected && !empty($persentaseJuz))
        <div style="font-size: 13px; font-weight: 700; color: #555; margin-top: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
            Persentase Hafalan Juz {{ $juzSelected }} &mdash; {{ $semester?->nama ?? '-' }}
        </div>
        <table class="juz-progress-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Ayat Hafal</th>
                    <th style="width: 200px;">Progress</th>
                    <th style="text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($persentaseJuz as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 600;">{{ $p['siswa']['nama'] }}</td>
                        <td>{{ $p['kelas'] }}</td>
                        <td>{{ $p['persentase']['ayatHafal'] }} / {{ $p['persentase']['totalAyat'] }}</td>
                        <td>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: {{ min($p['persentase']['persentase'], 100) }}%;"></div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0c8a5f;">
                            {{ $p['persentase']['persentase'] }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($juzSelected)
        <div style="text-align: center; padding: 24px; color: #888; font-size: 13px; margin-top: 12px;">
            Tidak ada data siswa untuk dihitung persentasenya.
        </div>
    @endif
</div>

{{-- Ringkasan --}}
<div class="summary-tahfidz">
    <div class="summary-box-tf">
        <div class="val">{{ count($kelasTerpilih->rekap['perSiswa'] ?? []) }}</div>
        <div class="lbl">Siswa</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTerpilih->rekap['totalHafal'] ?? 0 }}</div>
        <div class="lbl">Total Hafalan Kumulatif</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasTerpilih->avgJuz }}</div>
        <div class="lbl">Rata-rata Juz</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ count($kelasTerpilih->rekap['perSiswa']) > 0 ? collect($kelasTerpilih->rekap['perSiswa'])->max('juzHafal') : 0 }}</div>
        <div class="lbl">Juz Tertinggi</div>
    </div>
</div>

{{-- Daftar Siswa --}}
<div class="tahfidz-card">
    <div class="tahfidz-header">
        <div>
            <div class="tahfidz-title">Daftar Siswa {{ $kelasTerpilih->nama }}</div>
            <div class="tahfidz-meta">
                Klik nama siswa untuk melihat detail hafalan
            </div>
        </div>
    </div>

    @if(empty($kelasTerpilih->rekap['perSiswa']))
        <div style="text-align: center; padding: 24px; color: #888; font-size: 13px;">
            Tidak ada siswa di kelas ini untuk semester yang dipilih.
        </div>
    @else
        <div style="font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding: 0 12px;">
            <div class="siswa-row" style="background: none; font-weight: 700; color: #555;">
                <div>#</div>
                <div>Nama Siswa</div>
                <div style="text-align: center;">Juz Hafal</div>
                <div>Status</div>
                <div>Setoran Semester Ini</div>
                <div>Hafalan Terakhir</div>
                <div style="text-align: center;">Kualitas</div>
            </div>
        </div>
        @foreach($kelasTerpilih->rekap['perSiswa'] as $i => $s)
            <a href="{{ route('admin.tahfidz.detail-siswa', $s['siswa']['id']) }}" class="siswa-row" style="text-decoration: none; color: inherit;">
                <div style="font-weight: 600; color: #888;">{{ $i + 1 }}</div>
                <div style="font-weight: 600;">
                    {{ $s['siswa']['nama'] }}
                    @if($s['punyaHafalanLama'])
                        <span class="status-badge-tf status-lanjutan">Lanjutan</span>
                    @else
                        <span class="status-badge-tf status-baru">Baru</span>
                    @endif
                </div>
                <div style="text-align: center;">
                    <span class="juz-badge" style="{{ $s['juzHafal'] > 0 ? '' : 'background: #ccc;' }}">{{ $s['juzHafal'] }}</span>
                </div>
                <div style="font-size: 12px;">
                    @if($s['juzHafal'] > 0)
                        <span style="color: #0c8a5f; font-weight: 600;">{{ $s['juzHafal'] }} Juz</span>
                    @else
                        <span style="color: #aaa;">-</span>
                    @endif
                </div>
                <div style="font-size: 12px; color: #666;">
                    {{ $s['setoranSemester'] !== '-' ? $s['setoranSemester'] : '-' }}
                    @if($s['belumKonfirmasi'] > 0)
                        <span style="display: inline-block; margin-top: 4px; color: #c62828; background: #ffebee; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;">
                            {{ $s['belumKonfirmasi'] }} belum dikonfirmasi ortu
                        </span>
                    @endif
                </div>
                <div style="font-size: 12px;">
                    @if($s['lastJuz'] !== '-')
                        Juz {{ $s['lastJuz'] }} &middot; {{ $s['lastSurat'] }}
                        <span style="color: #888;">&middot; {{ $s['lastTanggal'] }}</span>
                    @else
                        <span style="color: #aaa;">-</span>
                    @endif
                </div>
                <div style="text-align: center;">
                    @if($s['kualitas'] !== '-')
                        <span class="kualitas-badge kualitas-{{ $s['kualitas'] }}">{{ \App\Models\HafalanTahfidz::labelKualitas($s['kualitas']) }}</span>
                    @else
                        <span style="color: #aaa;">-</span>
                    @endif
                </div>
            </a>
        @endforeach
    @endif
</div>
@endif

<script>
    const semesterMap = @json($semesterMap);

    function loadSemesters() {
        const ta = document.getElementById('tahunAjaranSelect').value;
        const semesterSelect = document.getElementById('semesterSelect');
        const btnStep1 = document.getElementById('btnStep1');

        semesterSelect.innerHTML = '<option value="">-- Pilih Semester --</option>';
        btnStep1.disabled = true;

        if (ta && semesterMap[ta]) {
            semesterMap[ta].forEach(function (s) {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nama;
                semesterSelect.appendChild(opt);
            });
        }
    }

    document.getElementById('semesterSelect').addEventListener('change', function () {
        document.getElementById('btnStep1').disabled = this.value === '';
    });
</script>
@endsection
