@extends('layouts.admin')
@section('title', 'Ujian Munaqosyah')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Ujian Munaqosyah</h1>
            <p class="page-subtitle">Buat dan kelola ujian munaqosyah</p>
        </div>
        <button onclick="document.getElementById('formBuatUjian').style.display='block'" class="btn-tartil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Ujian
        </button>
    </div>

    {{-- Form Buat Ujian --}}
    <div id="formBuatUjian" class="card-tartil" style="display: none; margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 20px; color: var(--text-primary); font-weight: 600;">Buat Ujian Baru</h3>
        <form method="POST" action="{{ route('admin.munaqosyah.store') }}">
            @csrf
            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Nama Ujian <span style="color:#c62828">*</span></label>
                    <input type="text" name="nama" class="form-input" placeholder="Contoh: Munaqosyah Akhir Semester" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tingkat <span style="color:#c62828">*</span></label>
                    <select name="tingkat" class="form-input" required>
                        <option value="unit">Unit</option>
                        <option value="yayasan">Yayasan</option>
                        <option value="pesantren">Pesantren</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Tanggal Ujian <span style="color:#c62828">*</span></label>
                    <input type="date" name="tanggal_ujian" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Semester</label>
                    <input type="hidden" name="semester_id" value="{{ $semester->id ?? '' }}">
                    <input type="text" class="form-input" value="{{ $semester->nama ?? 'Belum ada semester aktif' }}" readonly style="background: #f5f5f5;">
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 20px;">
                <button type="submit" class="btn-tartil">Buat Ujian</button>
                <button type="button" onclick="document.getElementById('formBuatUjian').style.display='none'" class="btn-tartil-outline">Batal</button>
            </div>
        </form>
    </div>

    {{-- List Ujian --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Nama Ujian</th>
                    <th>Tingkat</th>
                    <th>Tanggal</th>
                    <th>Semester</th>
                    <th>Peserta</th>
                    <th>Approval</th>
                    <th>Pendaftaran</th>
                    <th style="min-width: 200px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ujians as $u)
                <tr>
                    <td style="font-weight: 500;">{{ $u->nama }}</td>
                    <td><span class="badge-subject">{{ ucfirst($u->tingkat) }}</span></td>
                    <td>{{ $u->tanggal_ujian ? date('d/m/Y', strtotime($u->tanggal_ujian)) : '-' }}</td>
                    <td>{{ $u->semester->nama ?? '-' }}</td>
                    <td><span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">{{ $u->pendaftarans_count ?? 0 }} siswa</span></td>
                    <td>
                        @if($u->status == 'pengajuan')
                            <span class="badge-warning">Pengajuan</span>
                        @elseif($u->status == 'disetujui')
                            <span class="badge-success">Disetujui</span>
                        @elseif($u->status == 'sedang_berlangsung')
                            <span class="badge-info">Berlangsung</span>
                        @elseif($u->status == 'selesai')
                            <span class="badge-success">Selesai</span>
                        @else
                            <span class="badge-error">Ditolak</span>
                        @endif
                    </td>
                    <td>
                        @if($u->status_pendaftaran === 'buka')
                            <span class="badge-success" style="background: #d1fae5; color: #065f43;">&#128308; Buka</span>
                        @else
                            <span class="badge-error" style="background: #fee2e2; color: #991b1b;">&#128308; Tutup</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('admin.munaqosyah.detail', $u->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Detail</a>

                            {{-- Buka / Tutup Pendaftaran --}}
                            @if($u->status_pendaftaran === 'tutup')
                                <form method="POST" action="{{ route('admin.munaqosyah.buka-pendaftaran', $u->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="return confirm('Buka pendaftaran ujian ini? Siswa akan bisa mendaftar.')">&#128308; Buka</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.munaqosyah.tutup-pendaftaran', $u->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-tartil-warning" style="padding: 6px 12px; font-size: 12px; white-space: nowrap; background: #fff3cd; color: #856404; border: 1px solid #ffc107;" onclick="return confirm('Tutup pendaftaran ujian ini? Data akan di-rekap dan siswa tidak bisa mendaftar lagi.')">&#128308; Tutup</button>
                                </form>
                            @endif

                            @if($u->status == 'pengajuan')
                            <form method="POST" action="{{ route('admin.munaqosyah.approve', $u->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="return confirm('Setujui ujian ini?')">Setuju</button>
                            </form>
                            <button onclick="document.getElementById('tolak-{{ $u->id }}').style.display='block'" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;">Tolak</button>
                            @endif
                        </div>
                        @if($u->status == 'pengajuan')
                        <div id="tolak-{{ $u->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <form method="POST" action="{{ route('admin.munaqosyah.tolak', $u->id) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 12px;">Alasan Penolakan</label>
                                    <textarea name="catatan" class="form-input" rows="2" required></textarea>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Tolak</button>
                                    <button type="button" onclick="document.getElementById('tolak-{{ $u->id }}').style.display='none'" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada ujian munaqosyah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ujians->links() }}
</div>
@endsection
