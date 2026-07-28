@extends('layouts.admin')
@section('title', 'Edit Siswa - ' . $siswa->nama)

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Edit Siswa</h1>
            <p class="page-subtitle">{{ $siswa->nama }} - NIS: {{ $siswa->nis }}</p>
        </div>
        <a href="{{ route('admin.siswa.show', $siswa) }}" class="btn-tartil-outline" style="text-decoration: none;">Kembali</a>
    </div>

    <div class="card-tartil" style="max-width: 700px; padding: 24px;">
        <form method="POST" action="{{ route('admin.siswa.update', $siswa) }}">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div style="background: #FBE9E7; border: 1px solid #EF9A9A; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #C62828;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display: grid; gap: 16px;">
                {{-- Baris 1: NIS + Nama --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">NIS <span style="color: #C62828;">*</span></label>
                        <input type="text" name="nis" class="form-input" required value="{{ old('nis', $siswa->nis) }}" style="width: 100%;">
                    </div>
                    <div>
                        <label class="form-label">Nama Lengkap <span style="color: #C62828;">*</span></label>
                        <input type="text" name="nama" class="form-input" required value="{{ old('nama', $siswa->nama) }}" style="width: 100%;">
                    </div>
                </div>

                {{-- Baris 2: No HP + Jenis Kelamin --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">No HP <span style="color: #C62828;">*</span></label>
                        <input type="text" name="no_hp" class="form-input" required value="{{ old('no_hp', $siswa->no_hp) }}" style="width: 100%;" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin <span style="color: #C62828;">*</span></label>
                        <select name="jenis_kelamin" class="form-input" required style="width: 100%;">
                            <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 3: Kelas Reguler + Kelas Tartil --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">Kelas Reguler <span style="color: #C62828;">*</span></label>
                        <select name="kelas_reguler_id" class="form-input" required style="width: 100%;">
                            <option value="">-- Pilih --</option>
                            @foreach($kelasRegulars as $kr)
                            <option value="{{ $kr->id }}" {{ old('kelas_reguler_id', $siswa->kelas_reguler_id) == $kr->id ? 'selected' : '' }}>{{ $kr->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Kelas Tartil</label>
                        <select name="kelas_tartil_id" class="form-input" style="width: 100%;">
                            <option value="">-- Pilih --</option>
                            @foreach($kelasTartils as $kt)
                            <option value="{{ $kt->id }}" {{ old('kelas_tartil_id', $siswa->kelas_tartil_id) == $kt->id ? 'selected' : '' }}>{{ $kt->nama }} ({{ $kt->jenis ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tanggal masuk kelas tartil (khusus mutasi) --}}
                <div style="background: {{ $siswa->isMutasi ? '#FFF8E1' : 'transparent' }}; border: 1px solid {{ $siswa->isMutasi ? '#FFE082' : 'var(--border)' }}; border-radius: 6px; padding: 12px;">
                    <label class="form-label" style="color: {{ $siswa->isMutasi ? '#856404' : 'var(--text-secondary)' }};">
                        Tanggal Masuk Kelas Tartil {{ $siswa->isMutasi ? '(Siswa Mutasi)' : '(khusus siswa mutasi)' }}
                    </label>
                    <input type="date" name="tanggal_masuk_kelas_tartil" class="form-input" value="{{ old('tanggal_masuk_kelas_tartil', $siswa->tanggal_masuk_kelas_tartil?->format('Y-m-d')) }}" style="width: 100%;" placeholder="Kosongkan untuk siswa reguler">
                    <label class="form-label" style="color: {{ $siswa->isMutasi ? '#856404' : 'var(--text-secondary)' }}; margin-top: 8px;">Keterangan Mutasi</label>
                    <input type="text" name="keterangan_mutasi" class="form-input" value="{{ old('keterangan_mutasi', $siswa->keterangan_mutasi) }}" style="width: 100%;" placeholder="Contoh: Mutasi dari MI Al-Hidayah Surabaya">
                    <p style="font-size: 11px; color: {{ $siswa->isMutasi ? '#856404' : 'var(--text-muted)' }}; margin-top: 4px;">
                        {{ $siswa->isMutasi ? 'Siswa ini terdaftar sebagai mutasi masuk sejak ' . $siswa->tanggal_masuk_kelas_tartil->format('d/m/Y') . '. Penilaian jurnal dimulai dari tanggal ini.' : 'Isi hanya untuk siswa mutasi masuk di pertengahan semester. Kosongkan untuk siswa reguler.' }}
                    </p>
                </div>

                {{-- Baris 4: Tanggal Lahir + Tanggal Masuk --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir?->format('Y-m-d')) }}" style="width: 100%;">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Masuk <span style="color: #C62828;">*</span></label>
                        <input type="date" name="tanggal_masuk" class="form-input" required value="{{ old('tanggal_masuk', $siswa->tanggal_masuk?->format('Y-m-d')) }}" style="width: 100%;">
                    </div>
                </div>

                {{-- Baris 5: Status --}}
                <div>
                    <label class="form-label">Status <span style="color: #C62828;">*</span></label>
                    <select name="status" class="form-input" required style="width: 100%;">
                        <option value="aktif" {{ old('status', $siswa->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="mutasi_keluar" {{ old('status', $siswa->status) == 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
                        <option value="lulus" {{ old('status', $siswa->status) == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="nonaktif" {{ old('status', $siswa->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                {{-- Baris 6: Alamat --}}
                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input" rows="2" style="width: 100%;">{{ old('alamat', $siswa->alamat) }}</textarea>
                </div>

                {{-- Baris 7: Nama Ortu + No HP Ortu --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">Nama Orang Tua</label>
                        <input type="text" name="nama_ayah" class="form-input" value="{{ old('nama_ayah', $siswa->nama_ayah) }}" style="width: 100%;">
                    </div>
                    <div>
                        <label class="form-label">No HP Orang Tua</label>
                        <input type="text" name="no_hp_ortu" class="form-input" value="{{ old('no_hp_ortu', $siswa->no_hp_ortu) }}" style="width: 100%;" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                {{-- Baris 8: Reset Password (opsional) --}}
                <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 4px;">
                    <label class="form-label">Reset Password (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="form-input" style="width: 100%;" placeholder="Minimal 4 karakter" minlength="4">
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Isi hanya jika ingin mengganti password siswa.</p>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 10px;">
                <button type="submit" class="btn-tartil">Simpan Perubahan</button>
                <a href="{{ route('admin.siswa.show', $siswa) }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
