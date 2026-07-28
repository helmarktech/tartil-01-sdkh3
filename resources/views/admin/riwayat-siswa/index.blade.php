@extends('layouts.admin')
@section('title', 'Riwayat Siswa - By Kelas Reguler')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Riwayat Siswa</h1>
            <p class="page-subtitle">By Kelas Reguler — Lihat riwayat semester, kelas reguler, dan kelas tartil setiap siswa</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="form-inline" style="margin-bottom: 16px; gap: 8px;">
        <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Cari nama / NIS..." style="max-width: 250px;">
        <select name="kelas_reguler" class="form-input" style="max-width: 180px;">
            <option value="">Semua Kelas Reguler</option>
            @foreach($kelasRegulars as $kr)
            <option value="{{ $kr->id }}" {{ request('kelas_reguler') == $kr->id ? 'selected' : '' }}>{{ $kr->nama }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-tartil-outline" style="padding: 8px 16px;">Filter</button>
        @if(request('search') || request('kelas_reguler'))
            <a href="{{ route('admin.riwayat-siswa.index') }}" class="btn-tartil-outline" style="padding: 8px 16px;">Reset</a>
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
                    <th>Kelas Reguler (Saat Ini)</th>
                    <th>Kelas Tartil (Saat Ini)</th>
                    <th>Jumlah Semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $i => $s)
                <tr>
                    <td>{{ $siswas->firstItem() + $i }}</td>
                    <td>{{ $s->nis }}</td>
                    <td style="font-weight: 500;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="student-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                {{ $s->initials }}
                            </div>
                            {{ $s->nama }}
                        </div>
                    </td>
                    <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                    <td>
                        @if($s->kelasTartil)
                            <span class="badge-subject" style="background: #E8D5B5;">{{ $s->kelasTartil->nama }}</span>
                        @else
                            <span class="badge-warning" style="font-size: 10px;">Belum masuk</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-subject" style="background: #E9EEF0; color: #5A7A8A;">{{ $s->semesters_count }} semester</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.riwayat-siswa.detail', $s->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Lihat Riwayat</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Tidak ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $siswas->links() }}
</div>
@endsection
