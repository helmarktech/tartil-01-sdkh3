@extends('layouts.admin')
@section('title', 'Guru Reguler')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Guru Reguler</h1>
            <p class="page-subtitle">Manajemen guru pengampu kelas reguler (info, tidak terhubung ke sistem tartil)</p>
        </div>
        <button onclick="document.getElementById('formTambah').style.display='block'" class="btn-tartil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Guru Reguler
        </button>
    </div>

    {{-- Form Tambah --}}
    <div id="formTambah" class="card-tartil" style="display: none; margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Tambah Guru Reguler</h3>
        <form method="POST" action="{{ route('admin.guru-reguler.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama <span style="color:#c62828">*</span></label>
                    <input type="text" name="nama" class="form-input @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                    @error('nama')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-input @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                    @error('nip')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span style="color:#c62828">*</span></label>
                    <select name="jenis_kelamin" class="form-input @error('jenis_kelamin') is-invalid @enderror" required>
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="color:#c62828">*</span></label>
                    <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">No HP <span style="color:#c62828">*</span></label>
                    <input type="text" name="no_hp" class="form-input @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required>
                    @error('no_hp')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-input @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat') }}</textarea>
                @error('alamat')<div style="color: #c62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>
            <div style="display: flex; gap: 8px; margin-top: 16px;">
                <button type="submit" class="btn-tartil">Simpan</button>
                <button type="button" onclick="document.getElementById('formTambah').style.display='none'" class="btn-tartil-outline">Batal</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>L/P</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gurus as $i => $g)
                <tr class="{{ !$g->is_aktif ? 'row-muted' : '' }}">
                    <td>{{ $gurus->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $g->nama }}</td>
                    <td>{{ $g->nip ?? '-' }}</td>
                    <td>{{ $g->email }}</td>
                    <td>{{ $g->no_hp }}</td>
                    <td>{{ $g->jenis_kelamin }}</td>
                    <td>
                        @if($g->is_aktif)
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-error">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.guru-reguler.edit', $g->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada data guru reguler.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $gurus->links() }}
</div>
@endsection

@push('scripts')
<script>
// Auto-buka form tambah jika ada error validasi
@if($errors->any() && old('_token') && !old('_method'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formTambah').style.display = 'block';
});
@endif
</script>
@endpush
