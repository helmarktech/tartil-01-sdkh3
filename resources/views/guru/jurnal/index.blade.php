@extends('layouts.admin')
@section('title', 'Jurnal & Absensi')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Jurnal & Absensi</h1>
            <p class="page-subtitle">Input jurnal pembelajaran dan absensi siswa per tanggal</p>
        </div>
    </div>

    @if(isset($noSemester) && $noSemester)
    <div class="alert-tartil alert-warning">
        Tidak ada semester aktif. Silakan hubungi admin untuk mengaktifkan semester.
    </div>
    @else

    {{-- Filter: Kelas + Tanggal --}}
    <div class="card-tartil" style="margin-bottom: 16px; padding: 20px;">
        <form method="GET" class="form-inline" style="gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="min-width: 200px;">
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Tanggal</label>
                <input type="date" name="tanggal" class="form-input" value="{{ $tanggal }}" onchange="this.form.submit()" style="min-width: 160px;">
            </div>
        </form>
    </div>

    @if($kelasAktif)
    {{-- Info Card --}}
    <div class="card-tartil" style="margin-bottom: 16px; padding: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
            <div>
                <label class="form-label" style="font-size: 11px; margin-bottom: 4px;">Kelas</label>
                <div style="padding: 10px 14px; background: var(--bg-body); border-radius: 8px; font-size: 14px; color: var(--text-primary);">
                    {{ $kelasAktif->nama }}
                </div>
            </div>
            <div>
                <label class="form-label" style="font-size: 11px; margin-bottom: 4px;">Tanggal</label>
                <div style="padding: 10px 14px; background: var(--bg-body); border-radius: 8px; font-size: 14px; color: var(--text-primary);">
                    {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </div>
            </div>
            <div>
                <label class="form-label" style="font-size: 11px; margin-bottom: 4px;">Mata Pelajaran</label>
                <div style="padding: 10px 14px; background: var(--bg-body); border-radius: 8px; font-size: 14px; color: var(--text-primary);">
                    {{ $kelasAktif->mata_pelajaran ?? 'Tahfidz' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Jurnal Pembelajaran --}}
    <div class="card-tartil" style="margin-bottom: 16px; padding: 24px;">
        <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--text-primary);">Jurnal Pembelajaran</h3>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            <div>
                <label class="form-label" style="font-size: 12px;">TM (Pertemuan Ke-)</label>
                <input type="number" id="pertemuan_ke" class="form-input" value="{{ $jurnalKelas->pertemuan_ke ?? '' }}" placeholder="Auto" min="1" title="Kosongkan untuk otomatis (hitung pertemuan di bulan ini)" style="width: 100%;">
                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">Kosongkan = auto (pertemuan ke-<span id="previewPertemuan">?</span>)</div>
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Surat</label>
                <select id="surat_id" class="form-input" style="width: 100%;">
                    <option value="">-- Pilih Surat --</option>
                    @foreach($surats as $surat)
                    <option value="{{ $surat->id }}" {{ ($jurnalKelas->surat_id ?? '') == $surat->id ? 'selected' : '' }}>{{ $surat->nama }} ({{ $surat->jumlah_ayat }} ayat)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Ayat</label>
                <input type="text" id="ayat" class="form-input" value="{{ $jurnalKelas->ayat ?? '' }}" placeholder="Contoh: 1-5" style="width: 100%;">
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Halaman / Juz</label>
                <input type="text" id="halaman_juz" class="form-input" value="{{ $jurnalKelas->halaman_juz ?? '' }}" placeholder="Contoh: Juz 1 hal 23-25" style="width: 100%;">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 12px;">
            <div>
                <label class="form-label" style="font-size: 12px;">Materi Pembelajaran</label>
                <input type="text" id="materi_pembelajaran" class="form-input" value="{{ $jurnalKelas->materi_pembelajaran ?? '' }}" placeholder="Contoh: Mad Wajib Muttashil" style="width: 100%;">
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Topik</label>
                <input type="text" id="topik" class="form-input" value="{{ $jurnalKelas->topik ?? '' }}" placeholder="Topik tambahan..." style="width: 100%;">
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Rencana</label>
                <input type="text" id="rencana" class="form-input" value="{{ $jurnalKelas->rencana ?? '' }}" placeholder="Rencana pertemuan berikutnya..." style="width: 100%;">
            </div>
            <div>
                <label class="form-label" style="font-size: 12px;">Catatan</label>
                <input type="text" id="catatan_kelas" class="form-input" value="{{ $jurnalKelas->catatan_kelas ?? '' }}" placeholder="Catatan untuk pertemuan ini..." style="width: 100%;">
            </div>
        </div>
    </div>

    @if($siswaList->count() > 0)
    {{-- Absensi & Penilaian Siswa --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        {{-- Header Absensi --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 600; margin: 0; color: var(--text-primary);">Absensi & Penilaian Siswa</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0;">B = Baik | C = Cukup | K = Kurang</p>
            </div>
            <div style="display: flex; gap: 6px;">
                <button type="button" onclick="setSemuaB()" class="btn-toggle-b">Semua B</button>
                <button type="button" onclick="setSemuaC()" class="btn-toggle-c">Semua C</button>
                <button type="button" onclick="setSemuaK()" class="btn-toggle-k">Semua K</button>
                <button type="button" onclick="resetSemua()" class="btn-toggle-reset" style="margin-left: 4px;">Reset</button>
            </div>
        </div>

        {{-- Tabel Siswa --}}
        <div class="table-responsive">
            <table class="table-tartil" id="tabelPenilaian" style="font-size: 13px;">
                <thead>
                    <tr style="background: var(--bg-body);">
                        <th style="width: 40px; text-align: center;">NO</th>
                        <th style="width: 80px;">NIS</th>
                        <th style="min-width: 160px;">NAMA SISWA</th>
                        <th style="text-align: center;">NILAI (B/C/K)</th>
                        <th style="min-width: 180px;">CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswaList as $i => $s)
                    @php
                        $p = $penilaianMap->get($s->id);
                        $currentNilai = $p->penilaian ?? '';
                    @endphp
                    <tr class="row-siswa" data-siswa-id="{{ $s->id }}">
                        <td style="text-align: center; color: var(--text-muted);">{{ $i + 1 }}</td>
                        <td style="color: var(--text-muted);">{{ $s->nis ?? '-' }}</td>
                        <td style="font-weight: 500;">{{ $s->nama }}</td>
                        <td style="text-align: center;">
                            <div class="toggle-group" data-siswa="{{ $s->id }}">
                                <button type="button" class="toggle-btn btn-b {{ $currentNilai == 'B' ? 'active' : '' }}" onclick="setNilai({{ $s->id }}, 'B')">B</button>
                                <button type="button" class="toggle-btn btn-c {{ $currentNilai == 'C' ? 'active' : '' }}" onclick="setNilai({{ $s->id }}, 'C')">C</button>
                                <button type="button" class="toggle-btn btn-k {{ $currentNilai == 'K' ? 'active' : '' }}" onclick="setNilai({{ $s->id }}, 'K')">K</button>
                                <input type="hidden" class="inp-nilai" data-siswa="{{ $s->id }}" value="{{ $currentNilai }}">
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-input inp-catatan" data-siswa="{{ $s->id }}" value="{{ $p->catatan ?? '' }}" placeholder="Catatan..." style="padding: 6px 10px; font-size: 12px; min-height: 32px;">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Save Button --}}
    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="simpanJurnal()" class="btn-tartil" style="padding: 12px 32px; font-size: 14px;" id="btnSimpan">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Jurnal
        </button>
    </div>
    <div id="saveStatus" style="text-align: right; margin-top: 8px; font-size: 13px; min-height: 20px;"></div>

    @else
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <div style="color: var(--text-muted);">Tidak ada siswa aktif di kelas ini.</div>
    </div>
    @endif

    @else
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <div style="color: var(--text-muted);">Pilih kelas untuk mulai mengisi jurnal.</div>
    </div>
    @endif

    @endif
</div>
@endsection

@push('scripts')
<script>
const KELAS_ID = {{ $kelasId ?? 'null' }};
const TANGGAL = '{{ $tanggal }}';
const CSRF = '{{ csrf_token() }}';

// Show auto pertemuan preview
const savedPertemuan = {{ $jurnalKelas->pertemuan_ke ?? 'null' }};
if (savedPertemuan) {
    document.getElementById('previewPertemuan').textContent = savedPertemuan + ' (tersimpan)';
}

// ================= TOGGLE NILAI B/C/K =================
function setNilai(siswaId, nilai) {
    const group = document.querySelector('.toggle-group[data-siswa="' + siswaId + '"]');
    const input = group.querySelector('.inp-nilai');

    // Toggle: klik lagi = unselect
    if (input.value === nilai) {
        input.value = '';
        group.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    } else {
        input.value = nilai;
        group.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
        group.querySelector('.btn-' + nilai.toLowerCase()).classList.add('active');
    }
}

// ================= BULK ACTIONS =================
function setSemuaB() { document.querySelectorAll('.toggle-group').forEach(g => { const id = g.dataset.siswa; setNilai(id, 'B'); }); }
function setSemuaC() { document.querySelectorAll('.toggle-group').forEach(g => { const id = g.dataset.siswa; setNilai(id, 'C'); }); }
function setSemuaK() { document.querySelectorAll('.toggle-group').forEach(g => { const id = g.dataset.siswa; setNilai(id, 'K'); }); }
function resetSemua() { document.querySelectorAll('.toggle-group').forEach(g => { const id = g.dataset.siswa; const input = g.querySelector('.inp-nilai'); input.value = ''; g.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active')); }); }

// ================= SIMPAN JURNAL =================
function simpanJurnal() {
    if (!KELAS_ID) { alert('Pilih kelas terlebih dahulu.'); return; }

    const btn = document.getElementById('btnSimpan');
    const status = document.getElementById('saveStatus');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    // Collect jurnal kelas info
    const suratId = document.getElementById('surat_id').value || null;

    const jurnalKelas = {
        pertemuan_ke: document.getElementById('pertemuan_ke').value || null,
        halaman_juz: document.getElementById('halaman_juz').value || null,
        surat_id: suratId,
        ayat: document.getElementById('ayat').value || null,
        materi_pembelajaran: document.getElementById('materi_pembelajaran').value || null,
        topik: document.getElementById('topik').value || null,
        rencana: document.getElementById('rencana').value || null,
        catatan_kelas: document.getElementById('catatan_kelas').value || null,
    };

    // Collect penilaian per siswa
    const entries = [];
    document.querySelectorAll('.toggle-group').forEach(g => {
        const siswaId = parseInt(g.dataset.siswa);
        const penilaian = g.querySelector('.inp-nilai').value || null;
        const catatan = document.querySelector('.inp-catatan[data-siswa="' + siswaId + '"]')?.value || null;

        if (penilaian || catatan) {
            entries.push({ siswa_id: siswaId, penilaian: penilaian, catatan: catatan });
        }
    });

    fetch('/guru/jurnal/batch-store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
            tanggal: TANGGAL,
            kelas_id: KELAS_ID,
            ...jurnalKelas,
            entries: entries
        })
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok) throw new Error(data.error || 'Gagal menyimpan');
        return data;
    })
    .then(data => {
        status.textContent = data.message;
        status.style.color = '#5A7D5A';
        setTimeout(() => { status.textContent = ''; }, 4000);
    })
    .catch(err => {
        status.textContent = err.message;
        status.style.color = '#c62828';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><polyline points="20 6 9 17 4 12"/></svg>Simpan Jurnal';
    });
}

// Keyboard shortcut: Ctrl+S = simpan
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        simpanJurnal();
    }
});
</script>
@endpush

@push('styles')
<style>
/* Toggle B/C/K Buttons */
.toggle-group {
    display: inline-flex;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--border);
}
.toggle-btn {
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    background: var(--bg-card);
    color: var(--text-muted);
}
.toggle-btn:hover {
    background: var(--bg-body);
}
.toggle-btn.btn-b.active {
    background: #E9F0E9;
    color: #5A7D5A;
}
.toggle-btn.btn-c.active {
    background: #FFF8E1;
    color: #B8860B;
}
.toggle-btn.btn-k.active {
    background: #FBE9E7;
    color: #C62828;
}
.toggle-btn + .toggle-btn {
    border-left: 1px solid var(--border);
}

/* Bulk toggle buttons */
.btn-toggle-b, .btn-toggle-c, .btn-toggle-k, .btn-toggle-reset {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.15s;
    background: var(--bg-card);
    color: var(--text-secondary);
}
.btn-toggle-b:hover { background: #E9F0E9; color: #5A7D5A; border-color: #5A7D5A; }
.btn-toggle-c:hover { background: #FFF8E1; color: #B8860B; border-color: #B8860B; }
.btn-toggle-k:hover { background: #FBE9E7; color: #C62828; border-color: #C62828; }
.btn-toggle-reset:hover { background: var(--bg-body); }
</style>
@endpush
