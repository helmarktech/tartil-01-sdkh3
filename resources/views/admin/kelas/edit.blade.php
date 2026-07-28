@extends('layouts.admin')
@section('title', 'Edit Kelas Tartil')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Edit Kelas Tartil</h1>
            <p class="page-subtitle">{{ $kelas->nama }}</p>
        </div>
    </div>

    <div class="card-tartil" style="max-width: 600px; padding: 24px;">
        <form method="POST" action="{{ route('admin.kelas.update', $kelas->id) }}">
            @csrf @method('PUT')
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Nama Kelas *</label>
                    <input type="text" name="nama" class="form-input" value="{{ $kelas->nama }}" required>
                </div>
                <div>
                    <label class="form-label">Jenis Kelas *</label>
                    <select name="jenis" class="form-input" required>
                        <option value="BQ 1" {{ $kelas->jenis == 'BQ 1' ? 'selected' : '' }}>BQ 1</option>
                        <option value="BQ 2" {{ $kelas->jenis == 'BQ 2' ? 'selected' : '' }}>BQ 2</option>
                        <option value="BQ 3" {{ $kelas->jenis == 'BQ 3' ? 'selected' : '' }}>BQ 3</option>
                        <option value="BQ 4" {{ $kelas->jenis == 'BQ 4' ? 'selected' : '' }}>BQ 4</option>
                        <option value="Tartil" {{ $kelas->jenis == 'Tartil' ? 'selected' : '' }}>Tartil</option>
                        <option value="Tahfidz" {{ $kelas->jenis == 'Tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Guru Pengajar</label>
                    <select name="guru_id" class="form-input">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                        <option value="{{ $g->id }}" {{ $kelas->guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="background: {{ $kelas->is_kelas_baru ? '#e3f2fd' : 'transparent' }}; border: 1px solid {{ $kelas->is_kelas_baru ? '#90caf9' : 'var(--border)' }}; border-radius: 6px; padding: 12px;">
                    <label class="form-label" style="color: {{ $kelas->is_kelas_baru ? '#1565c0' : 'var(--text-secondary)' }};">
                        Tanggal Dibuat {{ $kelas->is_kelas_baru ? '(Kelas Baru)' : '(khusus kelas baru)' }}
                    </label>
                    <input type="date" name="tanggal_dibuat" class="form-input" value="{{ old('tanggal_dibuat', $kelas->tanggal_dibuat?->format('Y-m-d')) }}" style="width: 100%;">
                    <p style="font-size: 11px; color: {{ $kelas->is_kelas_baru ? '#1565c0' : 'var(--text-muted)' }}; margin-top: 4px;">
                        {{ $kelas->is_kelas_baru ? 'Kelas ini dibuat di pertengahan semester. Target hari dihitung dari ' . $kelas->tanggal_dibuat->format('d/m/Y') . '.' : 'Isi tanggal pertama kali kelas ini aktif. Kosongkan jika kelas dibuat di awal semester.' }}
                    </p>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <textarea name="deskripsi" class="form-input" rows="3">{{ $kelas->deskripsi }}</textarea>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="aktif" {{ $kelas->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ $kelas->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-tartil">Update</button>
                <a href="{{ route('admin.kelas.index') }}" class="btn-tartil-outline" style="margin-left: 8px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
