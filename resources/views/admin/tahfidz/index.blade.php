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
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    width: 90%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.modal-step {
    display: none;
}
.modal-step.active {
    display: block;
}
.modal-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin-top: 8px;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 20px;
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
}
@media (max-width: 640px) {
    .siswa-row { grid-template-columns: 1fr; gap: 6px; padding: 12px; }
    .siswa-row > div:nth-child(n) { display: block; width: 100%; text-align: left !important; }
    .summary-tahfidz { grid-template-columns: 1fr; }
    .semester-info { flex-direction: column; align-items: flex-start; }
    .juz-panel form { flex-direction: column; align-items: stretch; }
    .juz-select { width: 100%; }
    .modal-box { width: 95%; padding: 16px; }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">&#128218; Tahfidz & Hafalan</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Tracking hafalan Al-Quran siswa kelas Tartil</p>
    </div>
</div>

{{-- Panel Semester --}}
<div class="semester-panel">
    <div class="semester-info">
        <div>
            <div class="semester-title">
                @if($semester && $semesterAktif && $semester->id === $semesterAktif->id)
                    Semester Aktif: {{ $semester->nama }}
                @elseif($semester)
                    Rekap Semester: {{ $semester->nama }}
                @else
                    Tidak ada semester aktif
                @endif
            </div>
            <div class="semester-sub">
                Periode {{ $semester?->tanggal_mulai?->format('d M Y') ?? '-' }} s/d {{ $semester?->tanggal_selesai?->format('d M Y') ?? '-' }}
                @if($semester && $semesterAktif && $semester->id !== $semesterAktif->id)
                    &middot; <span style="color: #0c8a5f;">Kumulatif hafalan dihitung sampai semester ini</span>
                @endif
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.tahfidz.rekap-semester') }}" class="btn-outline-tf">&#128202; Rekap per Semester</a>
            <button type="button" class="btn-outline-tf" onclick="openSemesterModal()">
                &#128197; Pilih Semester Lain
            </button>
        </div>
    </div>
</div>

{{-- Panel Pilih Juz --}}
<div class="juz-panel">
    <form method="GET" action="{{ route('admin.tahfidz.index') }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        @if($semester)
            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
        @endif
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
                        <td style="font-weight: 600;">{{ $p['siswa']->nama }}</td>
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
        <div class="val">{{ $kelasList->count() }}</div>
        <div class="lbl">Kelas</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasList->sum(fn($k) => count($k->rekap['perSiswa'] ?? [])) }}</div>
        <div class="lbl">Total Siswa</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasList->sum(fn($k) => $k->rekap['totalHafal'] ?? 0) }}</div>
        <div class="lbl">Total Hafalan Kumulatif</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $kelasList->avg('avgJuz') ? round($kelasList->avg('avgJuz'), 1) : 0 }}</div>
        <div class="lbl">Rata-rata Juz</div>
    </div>
</div>

@if($kelasList->isEmpty())
    <div class="empty-tahfidz">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
        <h3>Belum ada kelas tartil</h3>
        <p>Buat kelas di menu Kelas Tartil.</p>
    </div>
@else
    @foreach($kelasList as $kelas)
    <div class="tahfidz-card">
        <div class="tahfidz-header">
            <div>
                <div class="tahfidz-title">{{ $kelas->nama }}</div>
                <div class="tahfidz-meta">
                    Guru: {{ $kelas->guru?->nama ?? '-' }} &middot;
                    {{ count($kelas->rekap['perSiswa'] ?? []) }} siswa &middot;
                    Rata-rata {{ $kelas->avgJuz }} juz &middot;
                    Semester: {{ $semester?->nama ?? '-' }}
                </div>
            </div>
        </div>

        @if(empty($kelas->rekap['perSiswa']))
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
            @foreach($kelas->rekap['perSiswa'] as $i => $s)
                <a href="{{ route('admin.tahfidz.detail-siswa', $s['siswa']->id) }}" class="siswa-row" style="text-decoration: none; color: inherit;">
                    <div style="font-weight: 600; color: #888;">{{ $i + 1 }}</div>
                    <div style="font-weight: 600;">
                        {{ $s['siswa']->nama }}
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
    @endforeach
@endif

{{-- Modal Pilih Semester (3 Step) --}}
<div class="modal-overlay" id="semesterModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px;">Pilih Semester Lain</h3>
            <button onclick="closeSemesterModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form method="GET" action="{{ route('admin.tahfidz.index') }}" id="semesterForm">
            {{-- Step 1: Pilih Tahun Ajaran --}}
            <div class="modal-step active" id="step1">
                <label style="font-size: 13px; font-weight: 600; color: #555;">Langkah 1: Pilih Tahun Ajaran</label>
                <select id="tahunAjaranSelect" class="modal-select" onchange="goToStep2()">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    @foreach($tahunAjaranList as $ta)
                        <option value="{{ $ta->nama }}">{{ $ta->nama }} {{ $ta->status === 'aktif' ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
                <div class="modal-actions">
                    <button type="button" class="btn-tf-outline" onclick="closeSemesterModal()">Batal</button>
                </div>
            </div>

            {{-- Step 2: Pilih Semester --}}
            <div class="modal-step" id="step2">
                <label style="font-size: 13px; font-weight: 600; color: #555;">Langkah 2: Pilih Semester</label>
                <select name="semester_id" id="semesterSelect" class="modal-select" onchange="enableSubmit()">
                    <option value="">-- Pilih Semester --</option>
                </select>
                <div class="modal-actions">
                    <button type="button" class="btn-tf-outline" onclick="backToStep1()">Kembali</button>
                    <button type="submit" class="btn-tf" id="btnSubmitSemester" disabled>Lihat Rekap</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const semesterMap = @json($semesterMap);

    function openSemesterModal() {
        document.getElementById('semesterModal').classList.add('show');
        backToStep1();
    }

    function closeSemesterModal() {
        document.getElementById('semesterModal').classList.remove('show');
    }

    function goToStep2() {
        const ta = document.getElementById('tahunAjaranSelect').value;
        const semesterSelect = document.getElementById('semesterSelect');
        semesterSelect.innerHTML = '<option value="">-- Pilih Semester --</option>';

        if (ta && semesterMap[ta]) {
            semesterMap[ta].forEach(function (s) {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nama;
                semesterSelect.appendChild(opt);
            });
        }

        document.getElementById('step1').classList.remove('active');
        document.getElementById('step2').classList.add('active');
        enableSubmit();
    }

    function backToStep1() {
        document.getElementById('step1').classList.add('active');
        document.getElementById('step2').classList.remove('active');
        document.getElementById('tahunAjaranSelect').value = '';
        document.getElementById('semesterSelect').value = '';
        enableSubmit();
    }

    function enableSubmit() {
        const hasSemester = document.getElementById('semesterSelect').value !== '';
        document.getElementById('btnSubmitSemester').disabled = !hasSemester;
    }

    document.getElementById('semesterModal').addEventListener('click', function(e) {
        if (e.target === this) closeSemesterModal();
    });
</script>
@endsection
