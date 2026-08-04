@extends('layouts.admin')
@section('title', 'Manajemen Guru Tartil')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Manajemen Guru Tartil</h1>
            <p class="page-subtitle">CRUD guru tartil, reset password, nonaktifkan &amp; hapus data</p>
        </div>
        <button onclick="document.getElementById('formTambah').style.display='block'" class="btn-tartil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Guru
        </button>
    </div>

    {{-- Form Tambah --}}
    <div id="formTambah" class="card-tartil" style="display: none; margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 20px; color: var(--text-primary); font-weight: 600;">Tambah Guru Baru</h3>
        <form method="POST" action="{{ route('admin.manajemen.guru.store') }}">
            @csrf
            <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Nama <span style="color:#c62828">*</span></label>
                    <input type="text" name="nama" class="form-input" placeholder="Nama lengkap guru" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">NIP</label>
                    <input type="text" name="nip" class="form-input" placeholder="Nomor Induk Pegawai">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Email <span style="color:#c62828">*</span></label>
                    <input type="email" name="email" class="form-input" placeholder="email@domain.com" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">No HP <span style="color:#c62828">*</span></label>
                    <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Jenis Kelamin <span style="color:#c62828">*</span></label>
                    <select name="jenis_kelamin" class="form-input" required>
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Password <span style="color: #888; font-weight: 400;">(default: 123456)</span></label>
                    <input type="text" name="password" class="form-input" placeholder="Kosongkan untuk default 123456">
                </div>
            </div>
            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Alamat</label>
                <textarea name="alamat" class="form-input" rows="2" placeholder="Alamat lengkap..."></textarea>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn-tartil">Simpan</button>
                <button type="button" onclick="document.getElementById('formTambah').style.display='none'" class="btn-tartil-outline">Batal</button>
            </div>
        </form>
    </div>

    {{-- Search --}}
    <form method="GET" class="form-inline" style="margin-bottom: 16px; gap: 8px;">
        <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Cari nama guru..." style="max-width: 300px;">
        <button type="submit" class="btn-tartil-outline" style="padding: 8px 16px;">Cari</button>
        @if(request('search'))
            <a href="{{ route('admin.manajemen.guru') }}" class="btn-tartil-outline" style="padding: 8px 16px;">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>JK</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gurus as $i => $g)
                <tr class="{{ $g->trashed() ? 'row-muted' : '' }}">
                    <td>{{ $gurus->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">
                        {{ $g->nama }}
                        @if($g->trashed())<span class="badge-error" style="font-size: 10px;">TERHAPUS</span>@endif
                    </td>
                    <td>{{ $g->nip ?? '-' }}</td>
                    <td>{{ $g->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                    <td>{{ $g->email }}</td>
                    <td>{{ $g->no_hp }}</td>
                    <td>
                        @if($g->trashed())
                            <span class="badge-error">Dihapus</span>
                        @elseif(!$g->is_aktif)
                            <span class="badge-warning">Nonaktif</span>
                        @else
                            <span class="badge-success">Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="{{ route('admin.manajemen.guru.edit', $g->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Edit</a>

                            @if(!$g->trashed())
                                <button onclick="toggleResetPw({{ $g->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Reset PW</button>

                                @if($g->is_aktif)
                                    <button onclick="toggleNonaktif({{ $g->id }})" class="btn-tartil-warning" style="padding: 6px 12px; font-size: 12px;">Nonaktif</button>
                                    <button onclick="toggleHapus({{ $g->id }})" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                                @else
                                    <form method="POST" action="{{ route('admin.manajemen.guru.aktif', $g->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;">Aktifkan</button>
                                    </form>
                                @endif
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">Terhapus</span>
                            @endif
                        </div>

                        {{-- Inline Reset Password Form --}}
                        <div id="resetpw-{{ $g->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.guru.resetpw', $g->id) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 12px;">Password Baru</label>
                                    <input type="text" name="password" class="form-input" required minlength="6">
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil" style="padding: 6px 12px; font-size: 12px;">Reset</button>
                                    <button type="button" onclick="toggleResetPw({{ $g->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>

                        {{-- Inline Nonaktif Form --}}
                        <div id="nonaktif-{{ $g->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #fff3e0; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.guru.nonaktif', $g->id) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 12px;">Keterangan Nonaktif</label>
                                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Mutasi, Cuti, dll" required>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil-warning" style="padding: 6px 12px; font-size: 12px;">Nonaktifkan</button>
                                    <button type="button" onclick="toggleNonaktif({{ $g->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>

                        {{-- Inline Hapus Form --}}
                        <div id="hapus-{{ $g->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.guru.hapus', $g->id) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 12px;">Keterangan Hapus</label>
                                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Keluar, Pensiun, dll" required>
                                </div>
                                <p style="font-size: 12px; color: #c62828; margin: 8px 0;">Data akan dihapus (soft delete) dan bisa dipulihkan.</p>
                                <div style="display: flex; gap: 8px;">
                                    <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                                    <button type="button" onclick="toggleHapus({{ $g->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada data guru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $gurus->links() }}
</div>
@endsection

@push('scripts')
<script>
// Auto-buka form tambah jika ada error validasi saat menambah
@if($errors->any() && old('_token') && !old('_method'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formTambah').style.display = 'block';
});
@endif

function toggleResetPw(id) {
    const el = document.getElementById('resetpw-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleNonaktif(id) {
    const el = document.getElementById('nonaktif-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleHapus(id) {
    const el = document.getElementById('hapus-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush