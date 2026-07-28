@extends('layouts.admin')
@section('title', 'Semester')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Semester</h1>
            <p class="page-subtitle">Daftar semester yang dibuat otomatis saat menambah Tahun Ajaran baru</p>
        </div>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil-outline">Ke Tahun Ajaran</a>
    </div>

    <div class="alert-tartil alert-info" style="margin-bottom: 20px;">
        <strong>Info:</strong> Semester hanya bisa dibuat otomatis saat menambah <a href="{{ route('admin.tahun-ajaran.index') }}" style="color: var(--accent);">Tahun Ajaran baru</a>. Satu TA terdiri dari semester Ganjil dan Genap.
    </div>

    {{-- Daftar Semester --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>TA</th>
                    <th>Periode</th>
                    <th>Kelas</th>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $i => $s)
                <tr style="{{ $s->is_aktif ? 'background: rgba(90,125,90,0.04);' : ($s->status == 'ditutup' ? 'background: #f5f5f5;' : '') }}">
                    <td>{{ $semesters->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>{{ $s->tahunAjaran->nama ?? $s->tahun_ajaran }}</td>
                    <td style="font-size: 13px; color: var(--text-secondary);">
                        {{ $s->tanggal_mulai->format('d M Y') }} - {{ $s->tanggal_selesai->format('d M Y') }}
                    </td>
                    <td style="text-align: center;">{{ $s->kelas_tartils_count ?? 0 }}</td>
                    <td style="text-align: center;">{{ $s->siswas_count ?? 0 }}</td>
                    <td>
                        @if($s->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @elseif($s->status == 'ditutup')
                            <span class="badge-error">Ditutup</span>
                        @else
                            <span class="badge-warning">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="{{ route('admin.semester.detail', $s->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Detail</a>
                            @if($s->status != 'ditutup' && !$s->is_aktif)
                            <form method="POST" action="{{ route('admin.semester.aktifkan', $s->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;">Aktifkan</button>
                            </form>
                            @endif
                            @if($s->status != 'ditutup')
                            <form method="POST" action="{{ route('admin.semester.tutup', $s->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Tutup semester ini?')">Tutup</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada semester. Tambah Tahun Ajaran baru untuk membuat semester.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $semesters->links() }}
    </div>
</div>
@endsection
