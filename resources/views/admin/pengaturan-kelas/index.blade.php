@extends('layouts.admin')
@section('title', 'Pengaturan Kelas - Indikator Penilaian')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title-display">Pengaturan Kelas</h1>
            <p class="page-subtitle">Kelola indikator penilaian rapor untuk setiap jenis kelas tartil</p>
        </div>
    </div>

    {{-- Tab Jenis Kelas --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        @foreach($jenisList as $jenis)
            <a href="{{ route('admin.pengaturan-kelas.index', ['jenis' => $jenis]) }}"
               class="btn-tartil{{ $jenisAktif === $jenis ? '' : '-outline' }}"
               style="text-decoration: none; font-size: 13px; padding: 8px 18px;">
                {{ $jenis }}
            </a>
        @endforeach
    </div>

    {{-- Card Info Jenis --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="color: var(--text-primary); font-size: 16px;">Jenis Kelas: {{ $jenisAktif }}</strong>
                <span style="color: var(--text-muted); margin-left: 12px; font-size: 13px;">{{ $indikators->count() }} indikator penilaian</span>
            </div>
            <button onclick="toggleForm()" class="btn-tartil" style="font-size: 12px; padding: 8px 14px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Indikator
            </button>
        </div>
    </div>

    {{-- Form Tambah Indikator (hidden by default) --}}
    <div id="formTambah" class="card-tartil" style="padding: 20px; margin-bottom: 20px; display: none;">
        <h3 style="font-size: 14px; color: var(--text-primary); margin-bottom: 16px;">Tambah Indikator {{ $jenisAktif }}</h3>
        <form method="POST" action="{{ route('admin.pengaturan-kelas.indikator.store') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            @csrf
            <input type="hidden" name="jenis_kelas" value="{{ $jenisAktif }}">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Nama Indikator</label>
                <input type="text" name="nama_indikator" class="form-input" placeholder="Contoh: TAJWID MUTSAQOL" required style="font-size: 13px;">
            </div>
            <div class="form-group" style="width: 100px; margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Urutan</label>
                <input type="number" name="urutan" class="form-input" value="{{ $indikators->count() + 1 }}" min="1" required style="font-size: 13px;">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 16px;">Simpan</button>
                <button type="button" onclick="toggleForm()" class="btn-tartil-outline" style="font-size: 12px; padding: 8px 16px;">Batal</button>
            </div>
        </form>
    </div>

    {{-- Tabel Indikator --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">NO</th>
                        <th>NAMA INDIKATOR</th>
                        <th style="width: 80px; text-align: center;">URUTAN</th>
                        <th style="width: 120px; text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indikators as $i => $ind)
                    <tr>
                        <td style="text-align: center; color: var(--text-muted);">{{ $i + 1 }}</td>
                        <td style="font-weight: 500;">{{ $ind->nama_indikator }}</td>
                        <td style="text-align: center;">
                            <span class="badge-subject">{{ $ind->urutan }}</span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                <button onclick="editIndikator({{ $ind->id }}, '{{ $ind->nama_indikator }}', {{ $ind->urutan }})" class="btn-tartil-outline" style="padding: 4px 10px; font-size: 11px;">Edit</button>
                                <form method="POST" action="{{ route('admin.pengaturan-kelas.indikator.destroy', $ind->id) }}" style="display: inline;" onsubmit="return confirm('Hapus indikator ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-tartil-danger" style="padding: 4px 10px; font-size: 11px;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px;">
                            Belum ada indikator untuk jenis kelas {{ $jenisAktif }}.
                            <br><button onclick="toggleForm()" class="btn-tartil" style="margin-top: 12px; font-size: 12px;">Tambah Indikator Pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</div>

{{-- Modal Edit Indikator --}}
<div id="modalEdit" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card-tartil" style="width: 90%; max-width: 480px; padding: 24px;">
        <h3 style="font-size: 15px; color: var(--text-primary); margin-bottom: 16px;">Edit Indikator</h3>
        <form id="formEdit" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 12px;">Nama Indikator</label>
                <input type="text" id="editNama" name="nama_indikator" class="form-input" required style="font-size: 13px;">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 12px;">Urutan</label>
                <input type="number" id="editUrutan" name="urutan" class="form-input" min="1" required style="font-size: 13px;">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="closeModal()" class="btn-tartil-outline" style="font-size: 12px; padding: 8px 16px;">Batal</button>
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 8px 16px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('formTambah');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function editIndikator(id, nama, urutan) {
    document.getElementById('formEdit').action = '/admin/pengaturan-kelas/indikator/' + id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editUrutan').value = urutan;
    document.getElementById('modalEdit').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('modalEdit').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection
