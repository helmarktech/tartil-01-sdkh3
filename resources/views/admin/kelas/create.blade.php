@extends('layouts.admin')
@section('title', 'Tambah Kelas Tartil')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Tambah Kelas Tartil</h1>
            <p class="page-subtitle">Buat kelas tartil baru</p>
        </div>
    </div>

    <div class="card-tartil" style="max-width: 600px; padding: 24px;">
        <form method="POST" action="{{ route('admin.kelas.store') }}">
            @csrf
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Nama Kelas *</label>
                    <input type="text" name="nama" class="form-input" required placeholder="Contoh: Kelas Tahfidz A">
                </div>
                <div>
                    <label class="form-label">Jenis Kelas *</label>
                    <select name="jenis" class="form-input" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="BQ 1">BQ 1</option>
                        <option value="BQ 2">BQ 2</option>
                        <option value="BQ 3">BQ 3</option>
                        <option value="BQ 4">BQ 4</option>
                        <option value="Tartil">Tartil</option>
                        <option value="Tahfidz">Tahfidz</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Guru Pengajar</label>
                    <select name="guru_id" class="form-input">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 6px; padding: 12px;">
                    <label class="form-label" style="color: #1565c0;">Tanggal Dibuat (khusus kelas baru di pertengahan semester)</label>
                    <input type="date" name="tanggal_dibuat" class="form-input" value="{{ old('tanggal_dibuat') }}" style="width: 100%;">
                    <p style="font-size: 11px; color: #1565c0; margin-top: 4px;">Isi tanggal pertama kali kelas ini aktif. Kosongkan jika kelas dibuat di awal semester — target hari dihitung dari awal semester.</p>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <textarea name="deskripsi" class="form-input" rows="3" placeholder="Deskripsi atau keterangan kelas..."></textarea>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-tartil">Simpan</button>
                <a href="{{ route('admin.kelas.index') }}" class="btn-tartil-outline" style="margin-left: 8px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
