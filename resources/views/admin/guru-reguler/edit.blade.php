@extends('layouts.admin')
@section('title', 'Edit Guru Reguler')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Edit Guru Reguler</h1>
            <p class="page-subtitle">{{ $guruReguler->nama }}</p>
        </div>
        <a href="{{ route('admin.guru-reguler.index') }}" class="btn-tartil-outline">Kembali</a>
    </div>

    <div class="card-tartil" style="max-width: 700px; padding: 28px;">
        <form method="POST" action="{{ route('admin.guru-reguler.update', $guruReguler->id) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                {{-- Nama --}}
                <div style="grid-column: span 2;">
                    <label class="form-label">Nama Lengkap <span style="color:#c62828">*</span></label>
                    <input type="text" name="nama" class="form-input @error('nama') is-invalid @enderror" value="{{ old('nama', $guruReguler->nama) }}" required style="width: 100%;">
                    @error('nama')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- NIP --}}
                <div>
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-input @error('nip') is-invalid @enderror" value="{{ old('nip', $guruReguler->nip) }}" style="width: 100%;">
                    @error('nip')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="form-label">Jenis Kelamin <span style="color:#c62828">*</span></label>
                    <select name="jenis_kelamin" class="form-input @error('jenis_kelamin') is-invalid @enderror" required style="width: 100%;">
                        <option value="L" {{ old('jenis_kelamin', $guruReguler->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $guruReguler->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label">Email <span style="color:#c62828">*</span></label>
                    <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email', $guruReguler->email) }}" required style="width: 100%;">
                    @error('email')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- No HP --}}
                <div>
                    <label class="form-label">No HP <span style="color:#c62828">*</span></label>
                    <input type="text" name="no_hp" class="form-input @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $guruReguler->no_hp) }}" required style="width: 100%;">
                    @error('no_hp')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="form-label">Status</label>
                    <select name="is_aktif" class="form-input" style="width: 100%;">
                        <option value="1" {{ old('is_aktif', $guruReguler->is_aktif) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_aktif', $guruReguler->is_aktif) ? '' : 'selected' }}>Nonaktif</option>
                    </select>
                </div>

                {{-- Alamat --}}
                <div style="grid-column: span 2;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input @error('alamat') is-invalid @enderror" rows="2" style="width: 100%;">{{ old('alamat', $guruReguler->alamat) }}</textarea>
                    @error('alamat')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Info readonly --}}
            <div style="margin-top: 20px; padding: 12px; background: var(--bg-body); border-radius: 8px; font-size: 12px; color: var(--text-muted);">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>Dibuat: {{ $guruReguler->created_at->format('d/m/Y H:i') }}</div>
                    <div>Terakhir diubah: {{ $guruReguler->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn-tartil">Simpan Perubahan</button>
                <a href="{{ route('admin.guru-reguler.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
