@extends('layouts.admin')
@section('title', 'Tambah Siswa')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Tambah Siswa</h1>
        <p class="page-subtitle">Tambah data siswa baru</p>
    </div>

    <div class="card-tartil" style="max-width: 600px; padding: 24px;">
        <form method="POST" action="{{ route('admin.siswa.store') }}">
            @csrf
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">NIS *</label>
                    <input type="text" name="nis" class="form-input" required value="{{ old('nis') }}" style="width: 100%;" placeholder="Contoh: 2024001">
                </div>
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-input" required value="{{ old('nama') }}" style="width: 100%;">
                </div>
                <div>
                    <label class="form-label">No HP *</label>
                    <input type="text" name="no_hp" class="form-input" required value="{{ old('no_hp') }}" style="width: 100%;" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-input" required style="width: 100%;">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Kelas Reguler *</label>
                    <select name="kelas_reguler_id" class="form-input" required style="width: 100%;">
                        <option value="">-- Pilih --</option>
                        @foreach($kelasRegulars as $kr)
                        <option value="{{ $kr->id }}">{{ $kr->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Kelas Tartil</label>
                    <select name="kelas_tartil_id" class="form-input" style="width: 100%;">
                        <option value="">-- Pilih --</option>
                        @foreach($kelasTartils as $kt)
                        <option value="{{ $kt->id }}">{{ $kt->nama }} - {{ $kt->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 6px; padding: 12px;">
                    <label class="form-label" style="color: #856404;">Tanggal Masuk Kelas Tartil (khusus siswa mutasi)</label>
                    <input type="date" name="tanggal_masuk_kelas_tartil" class="form-input" value="{{ old('tanggal_masuk_kelas_tartil') }}" style="width: 100%;" placeholder="Kosongkan untuk siswa reguler">
                    <label class="form-label" style="color: #856404; margin-top: 8px;">Keterangan Mutasi</label>
                    <input type="text" name="keterangan_mutasi" class="form-input" value="{{ old('keterangan_mutasi') }}" style="width: 100%;" placeholder="Contoh: Mutasi dari MI Al-Hidayah Surabaya">
                    <p style="font-size: 11px; color: #856404; margin-top: 4px;">Isi hanya untuk siswa mutasi masuk di pertengahan semester. Kosongkan untuk siswa reguler.</p>
                </div>
                <div>
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir') }}" style="width: 100%;">
                </div>
                <div>
                    <label class="form-label">Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" class="form-input" required value="{{ old('tanggal_masuk', date('Y-m-d')) }}" style="width: 100%;">
                </div>
                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input" rows="2" style="width: 100%;">{{ old('alamat') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Nama Orang Tua</label>
                    <input type="text" name="nama_ayah" class="form-input" value="{{ old('nama_ortu') }}" style="width: 100%;">
                </div>
                <div>
                    <label class="form-label">No HP Orang Tua</label>
                    <input type="text" name="no_hp_ortu" class="form-input" value="{{ old('no_hp_ortu') }}" style="width: 100%;">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-tartil">Simpan</button>
                <a href="{{ route('admin.siswa.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
