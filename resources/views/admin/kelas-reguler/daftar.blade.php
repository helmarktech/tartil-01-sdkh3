@extends('layouts.admin')
@section('title', 'Daftar Kelas Reguler')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Daftar Kelas Reguler</h1>
            <p class="page-subtitle">Input guru pengampu dan kelola kelas reguler</p>
        </div>
        <button onclick="document.getElementById('formTambah').style.display='block'" class="btn-tartil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Kelas
        </button>
    </div>

    {{-- Form Tambah --}}
    <div id="formTambah" class="card-tartil" style="display: none; margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Tambah Kelas Reguler</h3>
        <form method="POST" action="{{ route('admin.kelas-reguler.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Kelas</label>
                    <input type="text" name="nama" class="form-input" placeholder="Contoh: 1A" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenjang (1 - 6)</label>
                    <input type="number" name="jenjang" class="form-input" min="1" max="6" placeholder="1 - 6" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rombel</label>
                    <input type="text" name="tingkat" class="form-input" placeholder="Contoh: A, B, Putra, Putri" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Guru Pengampu (opsional)</label>
                    <select name="guru_pengampu_id" class="form-input">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label">Keterangan (opsional)</label>
                <textarea name="keterangan" class="form-input" rows="2" placeholder="Deskripsi atau keterangan kelas..."></textarea>
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
                    <th>Nama Kelas</th>
                    <th>Jenjang</th>
                    <th>Rombel</th>
                    <th>Guru Pengampu</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelasRegulers as $i => $kr)
                <tr class="{{ !$kr->is_aktif ? 'row-muted' : '' }}">
                    <td>{{ $kelasRegulers->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $kr->nama }}</td>
                    <td>{{ $kr->jenjang }}</td>
                    <td>{{ $kr->tingkat }}</td>
                    <td>
                        @if($kr->guruPengampu)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div class="student-avatar" style="width: 24px; height: 24px; font-size: 10px;">{{ $kr->guruPengampu->initials ?? substr($kr->guruPengampu->nama, 0, 2) }}</div>
                                {{ $kr->guruPengampu->nama }}
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                        @endif
                    </td>
                    <td>{{ $kr->keterangan ?? '-' }}</td>
                    <td>
                        @if($kr->is_aktif)
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-error">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <button onclick="toggleEdit({{ $kr->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Edit</button>

                        {{-- Inline Edit Form --}}
                        <div id="edit-{{ $kr->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.kelas-reguler.update', $kr->id) }}">
                                @csrf @method('PUT')
                                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                    <div class="form-group">
                                        <label class="form-label">Nama Kelas</label>
                                        <input type="text" name="nama" value="{{ $kr->nama }}" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Jenjang (1 - 6)</label>
                                        <input type="number" name="jenjang" value="{{ $kr->jenjang }}" class="form-input" min="1" max="6" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Rombel</label>
                                        <input type="text" name="tingkat" value="{{ $kr->tingkat }}" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Guru Pengampu</label>
                                        <select name="guru_pengampu_id" class="form-input">
                                            <option value="">-- Pilih Guru --</option>
                                            @foreach($gurus as $g)
                                            <option value="{{ $g->id }}" {{ $kr->guru_pengampu_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 8px;">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-input" rows="2">{{ $kr->keterangan }}</textarea>
                                </div>
                                <div class="form-group" style="margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="is_aktif" value="1" {{ $kr->is_aktif ? 'checked' : '' }}>
                                        <span style="font-size: 13px;">Kelas Aktif</span>
                                    </label>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil" style="padding: 6px 12px; font-size: 12px;">Update</button>
                                    <button type="button" onclick="toggleEdit({{ $kr->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada kelas reguler.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $kelasRegulers->links() }}
</div>
@endsection

@push('scripts')
<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
