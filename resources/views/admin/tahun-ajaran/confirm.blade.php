@extends('layouts.admin')
@section('title', 'Konfirmasi Tanggal Mulai Kelas')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Konfirmasi Tahun Ajaran Baru</h1>
            <p class="page-subtitle">Atur tanggal mulai efektif setiap kelas tartil untuk TA <strong>{{ $validated['nama'] }}</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}">
        @csrf
        <input type="hidden" name="nama" value="{{ $validated['nama'] }}">
        <input type="hidden" name="tanggal_mulai" value="{{ $validated['tanggal_mulai'] }}">

        <div class="card-tartil" style="padding: 20px; margin-bottom: 20px; background: #f8f9fa;">
            <div style="font-size: 12px; color: var(--text-muted); line-height: 1.7;">
                <strong style="color: var(--text-primary);">Petunjuk:</strong><br>
                • <strong>Tanggal dibuat</strong> = tanggal record kelas pertama kali dibuat.<br>
                • <strong>Tanggal dimulai</strong> = tanggal efektif kelas mulai pembelajaran di TA ini.<br>
                • Sistem akan menggunakan <strong>tanggal dimulai</strong> sebagai awal perhitungan hari aktif.<br>
                • Jika tanggal dimulai dikosongkan, sistem menggunakan tanggal mulai TA.
            </div>
        </div>

        <div class="card-tartil" style="padding: 0; overflow: hidden; margin-bottom: 20px;">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">NO</th>
                        <th>KELAS</th>
                        <th style="text-align: center; width: 120px;">JENIS</th>
                        <th style="text-align: center; width: 140px;">TANGGAL DIBUAT</th>
                        <th style="text-align: center; width: 180px;">TANGGAL DIMULAI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasTartil as $i => $k)
                    <tr>
                        <td style="text-align: center;">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight: 500;">{{ $k->nama }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $k->guru?->nama ?? 'Belum ada guru' }}</div>
                        </td>
                        <td style="text-align: center;">{{ ucfirst($k->jenis) }}</td>
                        <td style="text-align: center;">{{ $k->tanggal_dibuat ? $k->tanggal_dibuat->format('d/m/Y') : '-' }}</td>
                        <td style="text-align: center;">
                            <input type="date" name="tanggal_dimulai[{{ $k->id }}]" class="form-input" value="{{ $tglMulaiDefault }}" style="padding: 6px 10px; font-size: 12px; min-width: 140px;">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                            Tidak ada kelas tartil aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Batal</a>
            <button type="submit" class="btn-tartil" onclick="return confirm('Yakin buat Tahun Ajaran {{ $validated['nama'] }} dengan pengaturan tanggal mulai kelas ini? Proses ini tidak bisa dibatalkan.')">
                Konfirmasi & Buat Tahun Ajaran
            </button>
        </div>
    </form>
</div>
@endsection
