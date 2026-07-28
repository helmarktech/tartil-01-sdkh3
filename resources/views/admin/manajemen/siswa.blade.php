@extends('layouts.admin')
@section('title', 'Daftar Siswa')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Daftar Siswa</h1>
            <p class="page-subtitle">Kelola data siswa aktif dan non-aktif</p>
        </div>
        @if($tab === 'aktif' && $semesterAktif)
            <button onclick="document.getElementById('formTambah').style.display='block'" class="btn-tartil">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Siswa
            </button>
        @endif
    </div>

    {{-- Info: Semester tidak aktif --}}
    @if($tab === 'aktif' && !$semesterAktif)
    <div class="alert-tartil alert-error" style="margin-bottom: 20px;">
        <strong>Tidak ada semester aktif.</strong> Penambahan siswa tidak tersedia. Silakan buat Tahun Ajaran dan aktifkan semester terlebih dahulu melalui menu <a href="{{ route('admin.tahun-ajaran.index') }}" style="color: #fff; text-decoration: underline;">Tahun Ajaran</a>.
    </div>
    @endif

    {{-- Form Tambah --}}
    @if($tab === 'aktif' && $semesterAktif)
    <div id="formTambah" class="card-tartil" style="display: none; margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 20px; color: var(--text-primary); font-weight: 600;">Tambah Siswa Baru</h3>
        <form method="POST" action="{{ route('admin.manajemen.siswa.store') }}">
            @csrf
            <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">NIS <span style="color:#c62828">*</span></label>
                    <input type="text" name="nis" class="form-input" placeholder="Nomor Induk Siswa" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Nama <span style="color:#c62828">*</span></label>
                    <input type="text" name="nama" class="form-input" placeholder="Nama lengkap" required>
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
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Kelas Reguler <span style="color:#c62828">*</span></label>
                    <select name="kelas_reguler_id" class="form-input" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasRegulars as $kr)
                        <option value="{{ $kr->id }}">{{ $kr->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tanggal Masuk <span style="color:#c62828">*</span></label>
                    <input type="date" name="tanggal_masuk" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota lahir">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-input" placeholder="Nama lengkap ayah">
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn-tartil">Simpan</button>
                <button type="button" onclick="document.getElementById('formTambah').style.display='none'" class="btn-tartil-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="tab-nav" style="display: flex; gap: 4px; margin-bottom: 16px; border-bottom: 2px solid var(--border); padding-bottom: 0;">
        <a href="{{ route('admin.manajemen.siswa', ['tab' => 'aktif'] + request()->except('tab', 'page')) }}" class="tab-item {{ $tab === 'aktif' ? 'active' : '' }}" style="padding: 10px 20px; font-size: 13px; font-weight: 500; color: {{ $tab === 'aktif' ? 'var(--accent)' : 'var(--text-muted)' }}; border-bottom: 2px solid {{ $tab === 'aktif' ? 'var(--accent)' : 'transparent' }}; margin-bottom: -2px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
            Siswa Aktif
            <span style="background: {{ $tab === 'aktif' ? 'var(--accent)' : '#e9ecef' }}; color: {{ $tab === 'aktif' ? '#fff' : 'var(--text-muted)' }}; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;">{{ $countAktif }}</span>
        </a>
        <a href="{{ route('admin.manajemen.siswa', ['tab' => 'nonaktif'] + request()->except('tab', 'page')) }}" class="tab-item {{ $tab === 'nonaktif' ? 'active' : '' }}" style="padding: 10px 20px; font-size: 13px; font-weight: 500; color: {{ $tab === 'nonaktif' ? 'var(--accent)' : 'var(--text-muted)' }}; border-bottom: 2px solid {{ $tab === 'nonaktif' ? 'var(--accent)' : 'transparent' }}; margin-bottom: -2px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
            Siswa Non-Aktif
            <span style="background: {{ $tab === 'nonaktif' ? 'var(--accent)' : '#e9ecef' }}; color: {{ $tab === 'nonaktif' ? '#fff' : 'var(--text-muted)' }}; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;">{{ $countNonaktif }}</span>
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="form-inline" style="margin-bottom: 16px; gap: 8px;">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Cari nama/NIS..." style="max-width: 200px;">
        @if($tab === 'aktif')
        <select name="kelas_reguler" class="form-input" style="max-width: 180px;">
            <option value="">Semua Kelas</option>
            @foreach($kelasRegulars as $kr)
            <option value="{{ $kr->id }}" {{ request('kelas_reguler') == $kr->id ? 'selected' : '' }}>{{ $kr->nama }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="btn-tartil-outline" style="padding: 8px 16px;">Filter</button>
        @if(request('search') || request('kelas_reguler'))
            <a href="{{ route('admin.manajemen.siswa', ['tab' => $tab]) }}" class="btn-tartil-outline" style="padding: 8px 16px;">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Tempat, Tgl Lahir</th>
                    <th>Nama Ayah</th>
                    @if($tab === 'aktif')
                        <th>Kelas Reguler</th>
                        <th>Kelas Tartil</th>
                    @else
                        <th>Status</th>
                        <th>Keterangan & Waktu</th>
                    @endif
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $i => $s)
                <tr class="{{ $s->trashed() ? 'row-muted' : '' }}">
                    <td>{{ $siswas->firstItem() + $i }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">
                        {{ $s->nama }}
                        @if($s->trashed())<span class="badge-error" style="font-size: 10px;">TERHAPUS</span>@endif
                    </td>
                    <td>{{ $s->tempat_lahir ?? '' }}, {{ $s->tanggal_lahir ? $s->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                    <td>{{ $s->nama_ayah ?? '-' }}</td>

                    @if($tab === 'aktif')
                        <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                        <td>{{ $s->kelasTartil->nama ?? '-' }}</td>
                    @else
                        {{-- Tab Non-Aktif --}}
                        <td>
                            @if($s->status == 'mutasi_keluar')
                                <span class="badge-warning">Mutasi Keluar</span>
                            @elseif($s->status == 'lulus')
                                <span class="badge-info">Lulus</span>
                            @else
                                <span class="badge-muted">{{ $s->status }}</span>
                            @endif
                        </td>
                        <td style="font-size: 12px;">
                            @php
                                $riwayat = $s->riwayatMutasi->first();
                                $semInfo = '';
                                if ($riwayat) {
                                    // Cocokkan tanggal mutasi dengan semester dari collection
                                    $tgl = $riwayat->tanggal_mutasi;
                                    $match = $semesters->first(fn($sm) => $tgl >= $sm->tanggal_mulai && $tgl <= $sm->tanggal_selesai);
                                    $semInfo = $match ? $match->nama : '';
                                }
                            @endphp
                            @if($riwayat)
                                <div style="color: var(--text-muted);">{{ $riwayat->keterangan }}</div>
                                <div style="color: #888; margin-top: 2px;">
                                    {{ $riwayat->tanggal_mutasi->format('d/m/Y') }}
                                    @if($semInfo) &middot; {{ $semInfo }} @endif
                                </div>
                            @else
                                <span style="color: #bbb;">-</span>
                            @endif
                        </td>
                    @endif

                    <td>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="{{ route('admin.manajemen.siswa.edit', $s) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center;">Edit</a>

                            @if(!$s->trashed())
                                <button onclick="toggleResetPw({{ $s->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Reset PW</button>

                                @if($s->status == 'aktif')
                                    <button onclick="toggleMutasiKeluar({{ $s->id }})" class="btn-tartil-warning" style="padding: 6px 12px; font-size: 12px;">Mutasi Keluar</button>
                                    <button onclick="toggleHapus({{ $s->id }})" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                                @else
                                    <form method="POST" action="{{ route('admin.manajemen.siswa.aktif', $s->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;">Aktifkan</button>
                                    </form>
                                @endif
                            @endif
                        </div>

                        {{-- Inline Reset Password Form --}}
                        <div id="resetpw-{{ $s->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.siswa.resetpw', $s->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Password Baru</label>
                                    <input type="text" name="password" class="form-input" required minlength="6">
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil" style="padding: 6px 12px; font-size: 12px;">Reset</button>
                                    <button type="button" onclick="toggleResetPw({{ $s->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>

                        {{-- Inline Mutasi Keluar Form --}}
                        @if($s->status == 'aktif')
                        <div id="mutasi-{{ $s->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #fff3e0; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.siswa.nonaktif', $s->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Keterangan Mutasi Keluar</label>
                                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Pindah sekolah, Keluar, dll" required>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil-warning" style="padding: 6px 12px; font-size: 12px;">Simpan Mutasi</button>
                                    <button type="button" onclick="toggleMutasiKeluar({{ $s->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                        @endif

                        {{-- Inline Hapus Form --}}
                        <div id="hapus-{{ $s->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.manajemen.siswa.hapus', $s->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Keterangan Hapus</label>
                                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Keluar sekolah, Lulus, dll" required>
                                </div>
                                <p style="font-size: 12px; color: #c62828; margin: 8px 0;">Data akan dihapus (soft delete) dan bisa dipulihkan.</p>
                                <div style="display: flex; gap: 8px;">
                                    <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                                    <button type="button" onclick="toggleHapus({{ $s->id }})" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $siswas->links() }}
</div>
@endsection

@push('scripts')
<script>
function toggleResetPw(id) {
    const el = document.getElementById('resetpw-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleMutasiKeluar(id) {
    const el = document.getElementById('mutasi-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleHapus(id) {
    const el = document.getElementById('hapus-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
