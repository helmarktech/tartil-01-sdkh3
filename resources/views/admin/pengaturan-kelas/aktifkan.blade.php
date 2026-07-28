@extends('layouts.admin')
@section('title', 'Aktifkan Penilaian Rapor')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title-display">Aktifkan Penilaian Rapor</h1>
            <p class="page-subtitle">Buat penilaian rapor semesteran. Berlaku untuk semua kelas aktif. Semua siswa akan otomatis terdaftar dan guru wajib mengisi nilai.</p>
        </div>
    </div>

    {{-- Form Aktivasi --}}
    <div class="card-tartil" style="padding: 20px; margin-bottom: 24px;">
        <h3 style="font-size: 14px; color: var(--text-primary); margin-bottom: 16px;">Buat Penilaian Rapor Baru</h3>
        <form method="POST" action="{{ route('admin.pengaturan-kelas.aktifkan.proses') }}">
            @csrf
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 220px; margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px;">Semester</label>
                    <select name="semester_id" class="form-input" required style="font-size: 13px;">
                        <option value="">-- Pilih Semester --</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 220px; margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px;">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Penilaian Akhir Semester Ganjil" style="font-size: 13px;">
                </div>
                <button type="submit" class="btn-tartil" style="font-size: 12px; padding: 10px 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Aktifkan
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar Penilaian Rapor --}}
    <h3 style="font-size: 14px; color: var(--text-primary); margin-bottom: 12px;">Riwayat Penilaian Rapor</h3>
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">NO</th>
                        <th>SEMESTER</th>
                        <th style="width: 100px; text-align: center;">STATUS</th>
                        <th style="width: 80px; text-align: center;">SISWA</th>
                        <th style="width: 80px; text-align: center;">KELAS</th>
                        <th style="width: 100px; text-align: center;">PROGRESS</th>
                        <th style="width: 120px; text-align: center;">TGL AKTIF</th>
                        <th style="width: 180px; text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesterPenilaians as $i => $sp)
                    <tr>
                        <td style="text-align: center; color: var(--text-muted);">{{ $semesterPenilaians->firstItem() + $i }}</td>
                        <td style="font-weight: 500;">
                            {{ $sp->semester->nama ?? '-' }}
                            @if($sp->keterangan)
                                <span style="color: var(--text-muted); font-size: 11px; display: block;">{{ $sp->keterangan }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="{{ $sp->statusBadgeClass() }}">{{ $sp->statusLabel() }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-subject">{{ $sp->jumlahSiswa() }} siswa</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-subject">{{ $sp->jumlahKelas() }} kelas</span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; align-items: center; gap: 6px; justify-content: center;">
                                <div style="width: 50px; height: 6px; background: #f0ece4; border-radius: 3px; overflow: hidden;">
                                    <div style="width: {{ $sp->progressPersen() }}%; height: 100%; background: {{ $sp->progressPersen() >= 80 ? '#5A7D5A' : ($sp->progressPersen() >= 50 ? '#B8860B' : '#C62828') }}; border-radius: 3px; transition: width 0.3s;"></div>
                                </div>
                                <span style="font-size: 11px; font-weight: 600;">{{ $sp->progressPersen() }}%</span>
                            </div>
                        </td>
                        <td style="text-align: center; color: var(--text-muted);">
                            {{ $sp->tanggal_aktif ? $sp->tanggal_aktif->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                <a href="{{ route('admin.pengaturan-kelas.monitoring', $sp->id) }}" class="btn-tartil-outline" style="padding: 4px 8px; font-size: 11px;">Detail</a>
                                @if($sp->status === 'aktif')
                                <form method="POST" action="{{ route('admin.pengaturan-kelas.ubah-status', $sp->id) }}" style="display: inline;" onsubmit="return confirm('Tandai penilaian ini selesai? Guru tidak akan bisa mengisi lagi.');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" class="btn-tartil-success" style="padding: 4px 8px; font-size: 11px;">Selesai</button>
                                </form>
                                @elseif($sp->status === 'selesai')
                                <form method="POST" action="{{ route('admin.pengaturan-kelas.ubah-status', $sp->id) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="aktif">
                                    <button type="submit" class="btn-tartil-warning" style="padding: 4px 8px; font-size: 11px;">Buka</button>
                                </form>
                                @endif
                                @if($sp->status === 'draft')
                                <form method="POST" action="{{ route('admin.pengaturan-kelas.semester.destroy', $sp->id) }}" style="display: inline;" onsubmit="return confirm('Hapus penilaian ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-tartil-danger" style="padding: 4px 8px; font-size: 11px;">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                            Belum ada penilaian rapor yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 12px;">
        {{ $semesterPenilaians->links() }}
    </div>
</div>
@endsection
