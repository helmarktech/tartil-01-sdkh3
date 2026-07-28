@extends('layouts.admin')
@section('title', 'Pengajuan Pindah Kelas')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Pengajuan Pindah Kelas</h1>
        <p class="page-subtitle">Ajukan perpindahan kelas untuk siswa</p>
    </div>

    @if(!$semester)
    <div class="alert-tartil alert-error">Tidak ada semester aktif. Hubungi admin.</div>
    @else
    <div class="card-tartil" style="max-width: 600px; padding: 24px;">
        <form method="POST" action="{{ route('guru.perpindahan.store') }}" id="formPindah">
            @csrf
            <input type="hidden" name="semester_id" value="{{ $semester->id }}">

            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Pilih Kelas (Asal) *</label>
                    <select name="kelas_lama_id" id="kelasLama" class="form-input" required style="width: 100%;" onchange="loadSiswa(this.value)">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }} - {{ $k->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Pilih Siswa *</label>
                    <select name="siswa_id" id="siswaSelect" class="form-input" required style="width: 100%;" disabled>
                        <option value="">-- Pilih Kelas dulu --</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Kelas Tujuan *</label>
                    <select name="kelas_baru_id" class="form-input" required style="width: 100%;">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }} - {{ $k->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Alasan Perpindahan *</label>
                    <textarea name="alasan" class="form-input" rows="3" required style="width: 100%;" placeholder="Jelaskan alasan perpindahan..."></textarea>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-tartil">Ajukan</button>
                <a href="{{ route('guru.dashboard') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
    @endif
</div>

@push('scripts')
<script>
function loadSiswa(kelasId) {
    const select = document.getElementById('siswaSelect');
    if (!kelasId) {
        select.innerHTML = '<option value="">-- Pilih Kelas dulu --</option>';
        select.disabled = true;
        return;
    }
    
    select.innerHTML = '<option value="">Memuat...</option>';
    select.disabled = true;

    fetch('{{ route('guru.api.siswa') }}?kelas_id=' + kelasId)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            data.forEach(s => {
                select.innerHTML += '<option value="' + s.id + '">' + s.nama + ' (NIS: ' + s.nis + ')</option>';
            });
            select.disabled = false;
        })
        .catch(() => {
            select.innerHTML = '<option value="">Gagal memuat</option>';
        });
}
</script>
@endpush
@endsection
