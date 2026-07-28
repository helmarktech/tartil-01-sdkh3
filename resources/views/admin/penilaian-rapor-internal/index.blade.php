@extends('layouts.admin')
@section('title', 'Penilaian Rapor Internal')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Penilaian Rapor Internal</h1>
            <p class="page-subtitle">Buat penilaian untuk guru mengisi nilai siswa</p>
        </div>
    </div>

    {{-- Form Buat --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Buat Penilaian Baru</h3>
        <form method="POST" action="{{ route('admin.penilaian-rapor-internal.store') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            @csrf
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Nama Penilaian</label>
                <input type="text" name="nama" class="form-input" placeholder="Contoh: Penilaian Ganjil 2025/2026" required>
            </div>
            <div style="min-width: 200px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Semester</label>
                <select name="semester_id" class="form-input" required>
                    @foreach(\App\Models\Semester::orderBy('tanggal_mulai', 'desc')->get() as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->tahunAjaran->nama ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-tartil">Buat</button>
        </form>
    </div>

    {{-- List --}}
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Nama Penilaian</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penilaians as $p)
                <tr>
                    <td style="font-weight: 500;">{{ $p->nama }}</td>
                    <td>{{ $p->semester->nama ?? '-' }}</td>
                    <td>
                        @if($p->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-subject">Selesai</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.penilaian-rapor-internal.destroy', $p->id) }}" style="display:inline;" onsubmit="return confirm('Hapus penilaian ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada penilaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $penilaians->links() }}
</div>
@endsection
