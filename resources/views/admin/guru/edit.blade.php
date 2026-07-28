@extends('layouts.admin')
@section('title', 'Edit Guru Tartil')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Edit Guru Tartil</h1>
        <p class="page-subtitle">{{ $guru->nama }}</p>
    </div>

    {{-- Error Validasi --}}
    @if($errors->any())
    <div class="alert-tartil alert-error" style="margin-bottom: 16px;">
        <strong>Terjadi kesalahan:</strong>
        <ul style="margin: 6px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-tartil" style="max-width: 600px; padding: 24px;">
        <form method="POST" action="{{ route('admin.guru.update', $guru->id) }}">
            @csrf
            @method('PUT')
            <div style="display: grid; gap: 16px;">
                {{-- Nama --}}
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-input @error('nama') is-invalid @enderror" required value="{{ old('nama', $guru->nama) }}" style="width: 100%;">
                    @error('nama')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- NIP --}}
                <div>
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-input @error('nip') is-invalid @enderror" value="{{ old('nip', $guru->nip) }}" style="width: 100%;">
                    @error('nip')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" required value="{{ old('email', $guru->email) }}" style="width: 100%;">
                    @error('email')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- No HP --}}
                <div>
                    <label class="form-label">No HP *</label>
                    <input type="text" name="no_hp" class="form-input @error('no_hp') is-invalid @enderror" required value="{{ old('no_hp', $guru->no_hp) }}" style="width: 100%;">
                    @error('no_hp')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-input @error('jenis_kelamin') is-invalid @enderror" required style="width: 100%;">
                        <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input @error('alamat') is-invalid @enderror" rows="2" style="width: 100%;">{{ old('alamat', $guru->alamat) }}</textarea>
                    @error('alamat')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="form-label">Status</label>
                    <select name="is_aktif" class="form-input" style="width: 100%;">
                        <option value="1" {{ old('is_aktif', $guru->is_aktif) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_aktif', $guru->is_aktif) ? '' : 'selected' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-tartil">Simpan Perubahan</button>
                <a href="{{ route('admin.guru.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
