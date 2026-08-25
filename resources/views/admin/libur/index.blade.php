@extends('layouts.admin')
@section('title', 'Manajemen Hari Libur')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Manajemen Hari Libur</h1>
            <p class="page-subtitle">
                @if($semesterAktif)
                    Tandai hari libur untuk seluruh kelas tartil — Semester: <strong>{{ $semesterAktif->nama }}</strong>
                @else
                    <span style="color: #c62828;">Tidak ada semester aktif</span>
                @endif
            </p>
        </div>
    </div>

    @if(!$semesterAktif)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted);">Tidak dapat mengatur hari libur. Tidak ada semester yang aktif saat ini.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buka Tahun Ajaran</a>
    </div>
    @else

    {{-- Form Tandai Libur Massal --}}
    <div class="card-tartil" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--text-primary);">Tandai Hari Libur (Massal)</h3>
        <form method="POST" action="{{ route('admin.kelas-libur.store') }}">
            @csrf
            <input type="hidden" name="semua_kelas" value="1">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 12px;">Tanggal Libur</label>
                    <input type="date" name="tanggal" class="form-input" required style="width: 100%;">
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Kegiatan OSIS" required style="width: 100%;">
                </div>
                <div>
                    <button type="submit" class="btn-tartil" style="width: 100%;">+ Tandai Libur Massal</button>
                </div>
            </div>
        </form>
        <div style="margin-top: 12px; font-size: 11px; color: var(--text-muted);">
            Tanggal yang ditandai akan otomatis diterapkan ke <strong>seluruh kelas tartil aktif</strong>.
        </div>
    </div>

    {{-- Daftar Hari Libur --}}
    <div class="card-tartil" style="padding: 20px;">
        <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--text-primary);">Daftar Hari Libur</h3>

        @if($liburList->count() > 0)
        <div style="overflow-x: auto;">
            <table class="table-tartil" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">NO</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Keterangan</th>
                        <th style="text-align: center;">Jumlah Kelas</th>
                        <th style="text-align: center; width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liburList as $i => $l)
                    <tr>
                        <td style="text-align: center;">{{ $i + 1 }}</td>
                        <td>{{ $l['tanggal']->format('d/m/Y') }}</td>
                        <td>{{ $l['tanggal']->locale('id')->isoFormat('dddd') }}</td>
                        <td>{{ $l['keterangan'] }}</td>
                        <td style="text-align: center;">{{ $l['jumlah_kelas'] }} kelas</td>
                        <td style="text-align: center;">
                            @foreach($l['items'] as $item)
                            <form method="POST" action="{{ route('admin.kelas-libur.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('Hapus tanda libur {{ $l['tanggal']->format('d/m/Y') }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger-sm" style="padding: 4px 8px; font-size: 11px;">Hapus</button>
                            </form>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align: center; padding: 32px; color: var(--text-muted);">
            Belum ada hari libur yang ditandai di semester ini.
        </div>
        @endif
    </div>

    @endif
</div>
@endsection
