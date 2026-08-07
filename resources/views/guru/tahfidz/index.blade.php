@extends('layouts.admin')

@section('title', 'Tahfidz & Hafalan - Guru')

@section('content')
<style>
.tf-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.tf-header {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
}
.tf-meta {
    font-size: 12px;
    color: #888;
    margin-bottom: 16px;
}
.siswa-row {
    display: grid;
    grid-template-columns: 40px 1fr 70px 120px 100px;
    gap: 8px;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-size: 13px;
}
.siswa-row:nth-child(odd) { background: #f8faf8; }
.siswa-row:hover { background: #e8f5e9; }
.juz-pill {
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
.empty-tf {
    text-align: center;
    padding: 48px;
    color: #888;
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
</style>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title-display" style="font-family: 'DM Serif Display', serif; font-size: 26px; margin: 0;">&#128218; Tahfidz & Hafalan</h1>
        <p style="color: #666; font-size: 14px; margin: 4px 0 0;">Input setoran hafalan siswa kelas Anda</p>
    </div>
</div>

{{-- Panel Pilih Juz --}}
<div class="juz-panel">
    <form method="GET" action="{{ route('guru.tahfidz.index') }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
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
            Persentase Hafalan Juz {{ $juzSelected }}
        </div>
        <table class="juz-progress-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Siswa</th>
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

@if(!$kelas)
    <div class="empty-tf">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
        <h3>Anda belum ditugaskan ke kelas tartil</h3>
        <p>Hubungi admin untuk penugasan kelas.</p>
    </div>
@else
    {{-- Info Kelas --}}
    <div class="tf-card">
        <div class="tf-header">{{ $kelas->nama }}</div>
        <div class="tf-meta">{{ count($rekap['perSiswa'] ?? []) }} siswa &middot; Semester: {{ $semester?->nama ?? '-' }}</div>

        @if(empty($rekap['perSiswa']))
            <div style="text-align: center; padding: 24px; color: #888;">Belum ada siswa di kelas ini.</div>
        @else
            <div style="font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding: 0 12px;">
                <div class="siswa-row" style="background: none; font-weight: 700;">
                    <div>#</div>
                    <div>Nama Siswa</div>
                    <div style="text-align: center;">Juz Hafal</div>
                    <div>Setoran Terakhir</div>
                    <div></div>
                </div>
            </div>
            @foreach($rekap['perSiswa'] as $i => $s)
            <div class="siswa-row">
                <div style="font-weight: 600; color: #888;">{{ $i + 1 }}</div>
                <div style="font-weight: 600;">{{ $s['siswa']['nama'] }}</div>
                <div style="text-align: center;">
                    <span class="juz-pill" style="{{ $s['juzHafal'] > 0 ? '' : 'background: #ccc;' }}">{{ $s['juzHafal'] }}</span>
                </div>
                <div style="font-size: 12px;">
                    @if($s['lastJuz'] !== '-')
                        <span style="color: #0c8a5f; font-weight: 600;">Juz {{ $s['lastJuz'] }}</span>
                        @if($s['lastStatus'])
                            <span style="color: #888;">({{ \App\Models\HafalanTahfidz::labelStatus($s['lastStatus']) }})</span>
                        @endif
                        <div style="font-size: 11px; color: #aaa;">{{ $s['lastTanggal'] }}</div>
                        @if($s['belumKonfirmasi'] > 0)
                            <div style="margin-top: 4px;">
                                <span style="color: #c62828; background: #ffebee; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;">
                                    {{ $s['belumKonfirmasi'] }} belum dikonfirmasi ortu
                                </span>
                            </div>
                        @endif
                    @else
                        <span style="color: #aaa;">-</span>
                    @endif
                </div>
                <div>
                    <button onclick="openForm({{ $s['siswa']['id'] }}, '{{ $s['siswa']['nama'] }}')" class="btn-tartil" style="padding: 6px 14px; font-size: 12px;">+ Setoran</button>
                </div>
            </div>
            @endforeach
        @endif
    </div>
@endif

{{-- Modal Form Setoran --}}
<div class="modal-overlay" id="formModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px;">Input Setoran Hafalan</h3>
            <button onclick="closeForm()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div style="font-size: 13px; color: #666; margin-bottom: 16px;" id="modalSiswaName">-</div>

        <form method="POST" action="{{ route('guru.tahfidz.hafalan.store') }}">
            @csrf
            <input type="hidden" name="siswa_id" id="modalSiswaId">
            @if($semester)
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Juz *</label>
                    <select name="juz" class="form-input" required>
                        @for($j = 1; $j <= 30; $j++)
                            <option value="{{ $j }}">Juz {{ $j }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Surat</label>
                    <select name="surat_id" class="form-input" id="modalSuratSelect">
                        <option value="">- Pilih -</option>
                        @foreach(\App\Models\Surat::orderBy('urutan')->get() as $surat)
                            <option value="{{ $surat->id }}">{{ $surat->urutan }}. {{ $surat->nama_latin }} ({{ $surat->jumlah_ayat }} ayat)</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Ayat Mulai *</label>
                    <input type="number" name="ayat_mulai" class="form-input" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Ayat Selesai</label>
                    <input type="number" name="ayat_selesai" class="form-input" min="1" placeholder="Opsional">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Status *</label>
                    <select name="status" class="form-input" required>
                        <option value="baru">Baru</option>
                        <option value="setengah_hafal">Setengah Hafal</option>
                        <option value="hafal" selected>Hafal</option>
                        <option value="murajaah">Murojaah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size: 12px;">Kualitas *</label>
                    <select name="kualitas" class="form-input" required>
                        <option value="mumtaz">Mumtaz</option>
                        <option value="jayyid_jiddan" selected>Jayyid Jiddan</option>
                        <option value="jayyid">Jayyid</option>
                        <option value="naqis">Naqis</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label" style="font-size: 12px;">Tanggal *</label>
                <input type="date" name="tanggal_hafalan" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-size: 12px;">Catatan</label>
                <textarea name="catatan" class="form-input" rows="2" placeholder="Catatan setoran..."></textarea>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 16px;">
                <button type="submit" class="btn-tartil" style="flex: 1;">Simpan Setoran</button>
                <button type="button" onclick="closeForm()" class="btn-tartil-outline">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
window.juzSuratMap = @json($juzSuratMap ?? []);

function openForm(siswaId, siswaName) {
    document.getElementById('modalSiswaId').value = siswaId;
    document.getElementById('modalSiswaName').textContent = siswaName;
    document.getElementById('formModal').classList.add('show');
}
function closeForm() {
    document.getElementById('formModal').classList.remove('show');
}
document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) closeForm();
});

// Filter surat berdasarkan juz di modal
(function () {
    const juzSelect = document.querySelector('#formModal select[name="juz"]');
    const suratSelect = document.getElementById('modalSuratSelect');
    if (!juzSelect || !suratSelect) return;
    const allOptions = Array.from(suratSelect.options).slice(1);

    function filterSurat() {
        const juz = parseInt(juzSelect.value) || 0;
        const allowedIds = window.juzSuratMap[juz] || [];
        suratSelect.innerHTML = '<option value="">- Pilih -</option>';
        allOptions.forEach(function (opt) {
            const suratId = parseInt(opt.value);
            if (juz === 0 || allowedIds.includes(suratId)) {
                suratSelect.appendChild(opt);
            }
        });
    }

    juzSelect.addEventListener('change', filterSurat);
    filterSurat();
})();
</script>
@endsection
