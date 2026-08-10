@extends('layouts.admin')

@section('title', 'Rekap Hafalan per Semester')

@section('content')
<style>
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
.btn-outline-tf:hover { background: #f4fbf7; }
.summary-tf {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
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
.rekap-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.rekap-table th {
    text-align: left;
    padding: 10px 12px;
    background: #f8faf8;
    font-size: 11px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
}
.rekap-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.rekap-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.rekap-card-header {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
}
.rekap-card-meta {
    font-size: 12px;
    color: #888;
    margin-bottom: 16px;
}
.tuntas-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}
.tuntas-chip {
    background: #e8f5e9;
    color: #155724;
    border-radius: 16px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
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
.modal-step { display: none; }
.modal-step.active { display: block; }
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
.btn-tf:disabled { background: #ccc; cursor: not-allowed; }
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
    .summary-tf { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 28px; margin: 0; color: #1a1a2e;">&#128218; Rekap Hafalan per Semester</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Perbandingan jumlah siswa, siswa hafal juz, dan siswa tuntas per juz</p>
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
            <a href="{{ route('admin.tahfidz.index') }}" class="btn-outline-tf">&#8592; Kembali</a>
            <button type="button" class="btn-outline-tf" onclick="openSemesterModal()">&#128197; Pilih Semester</button>
        </div>
    </div>
</div>

{{-- Panel Pilih Juz --}}
<div class="juz-panel">
    <form method="GET" action="{{ route('admin.tahfidz.rekap-semester') }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        @if($semester)
            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
        @endif
        <label style="font-size: 13px; font-weight: 600; color: #555;">Pilih Juz:</label>
        <select name="juz" class="juz-select" onchange="this.form.submit()">
            @for($j = 1; $j <= 30; $j++)
                <option value="{{ $j }}" {{ $juzSelected == $j ? 'selected' : '' }}>Juz {{ $j }}</option>
            @endfor
        </select>
        <span style="font-size: 13px; color: #888;">
            Menampilkan rekap untuk <strong>Juz {{ $juzSelected }}</strong>
        </span>
    </form>
</div>

{{-- Summary Kumulatif --}}
<div class="summary-tf">
    <div class="summary-box-tf">
        <div class="val">{{ $totalSummary['totalSiswa'] }}</div>
        <div class="lbl">Total Siswa</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $totalSummary['sudahHafal'] }}</div>
        <div class="lbl">Sudah Hafal Juz {{ $juzSelected }}</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">{{ $totalSummary['tuntas'] }}</div>
        <div class="lbl">Tuntas Juz {{ $juzSelected }}</div>
    </div>
    <div class="summary-box-tf">
        <div class="val">
            {{ $totalSummary['totalSiswa'] > 0 ? round(($totalSummary['tuntas'] / $totalSummary['totalSiswa']) * 100, 1) : 0 }}%
        </div>
        <div class="lbl">Persentase Tuntas</div>
    </div>
</div>

{{-- Rekap Per Kelas --}}
@if(empty($rekapPerKelas))
    <div style="text-align: center; padding: 48px; color: #888;">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
        <h3>Belum ada kelas tartil</h3>
    </div>
@else
    @foreach($rekapPerKelas as $r)
    <div class="rekap-card">
        <div class="rekap-card-header">{{ $r['kelas']->nama }}</div>
        <div class="rekap-card-meta">
            Guru: {{ $r['kelas']->guru?->nama ?? '-' }} &middot;
            Total siswa: {{ $r['totalSiswa'] }} &middot;
            Juz {{ $juzSelected }}: {{ $r['juzSelected']['sudahHafal'] }} hafal, {{ $r['juzSelected']['tuntas'] }} tuntas
        </div>

        <div style="overflow-x: auto;">
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th>Juz</th>
                        <th>Total Siswa</th>
                        <th>Sudah Hafal</th>
                        <th>Tuntas</th>
                        <th>Persentase Tuntas</th>
                        <th>Siswa Tuntas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($r['juzData'] as $jd)
                    <tr style="{{ $jd['juz'] == $juzSelected ? 'background: #f4fbf7;' : '' }}">
                        <td><strong>Juz {{ $jd['juz'] }}</strong></td>
                        <td>{{ $jd['totalSiswa'] }}</td>
                        <td>{{ $jd['sudahHafal'] }}</td>
                        <td>{{ $jd['tuntas'] }}</td>
                        <td>
                            {{ $jd['totalSiswa'] > 0 ? round(($jd['tuntas'] / $jd['totalSiswa']) * 100, 1) : 0 }}%
                        </td>
                        <td>
                            @if(count($jd['siswaTuntas']) > 0)
                                <div class="tuntas-list">
                                    @foreach($jd['siswaTuntas'] as $st)
                                        <span class="tuntas-chip">{{ $st['nama'] }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color: #aaa; font-size: 12px;">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif

{{-- Modal Pilih Semester (3 Step) --}}
<div class="modal-overlay" id="semesterModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px;">Pilih Semester</h3>
            <button onclick="closeSemesterModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form method="GET" action="{{ route('admin.tahfidz.rekap-semester') }}" id="semesterForm">
            <input type="hidden" name="juz" value="{{ $juzSelected }}">
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
