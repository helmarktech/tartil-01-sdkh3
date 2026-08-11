@extends('layouts.admin')
@section('title', 'Keterangan Kelas')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Keterangan Kelas</h1>
            <p class="page-subtitle">Lihat siswa per kelas reguler dan daftarkan siswa baru</p>
        </div>
        <a href="{{ route('admin.kelas-reguler.keterangan.export') }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Excel
        </a>
    </div>

    @forelse($kelasRegulers as $kr)
    <div class="card-tartil" style="margin-bottom: 16px; padding: 24px;">
        {{-- Header --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <h3 style="font-size: 16px; margin: 0; color: var(--text-primary);">{{ $kr->nama }}</h3>
                <span class="badge-subject" style="background: #E9E6E1; color: #6B5E51;">Jenjang {{ $kr->jenjang }} | Rombel {{ $kr->tingkat }}</span>
                <span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">{{ $kr->total_siswa }} siswa</span>
                @if($kr->guruPengampu)
                <span class="badge-subject" style="background: #E8D5B5; color: #6B5E51;">Guru: {{ $kr->guruPengampu->nama }}</span>
                @endif
            </div>
            <a href="{{ route('admin.kelas-reguler.detail', $kr->id) }}" class="btn-tartil-outline" style="padding: 6px 14px; font-size: 12px;">Lihat Detail</a>
        </div>

        {{-- Siswa Table --}}
        @if($kr->siswas->count() > 0)
        <div class="table-responsive" style="margin-bottom: 16px;">
            <table class="table-tartil" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>L/P</th>
                        <th>Kelas Tartil</th>
                        <th>Guru Tartil</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kr->siswas->take(10) as $i => $s)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $s->nis }}</td>
                        <td style="font-weight: 500;">{{ $s->nama }}</td>
                        <td>{{ $s->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                        <td>
                            @if($s->kelasTartil)
                            <span class="badge-subject" style="background: #E8D5B5;">{{ $s->kelasTartil->nama }}</span>
                            @else
                            <span class="badge-warning" style="font-size: 10px;">Belum masuk</span>
                            @endif
                        </td>
                        <td>
                            @if($s->kelasTartil && $s->kelasTartil->guru)
                                <span style="color: var(--text-secondary); font-size: 12px;">{{ $s->kelasTartil->guru->nama }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($kr->siswas->count() > 10)
        <p style="text-align: center; color: var(--text-muted); font-size: 12px; margin: 8px 0 16px;">
            +{{ $kr->siswas->count() - 10 }} siswa lagi. <a href="{{ route('admin.kelas-reguler.detail', $kr->id) }}" style="color: var(--accent);">Lihat semua</a>
        </p>
        @endif
        @else
        <p style="color: var(--text-muted); text-align: center; padding: 16px; font-size: 13px; margin-bottom: 16px;">Tidak ada siswa aktif di kelas ini.</p>
        @endif

        {{-- Daftarkan Siswa --}}
        @if($siswaBelumPunyaKelas->count() > 0)
        <div style="padding: 20px; background: #f8f9fa; border-radius: 12px; border: 1px solid var(--border);">
            <h4 style="font-size: 13px; margin-bottom: 12px; color: var(--text-primary); font-weight: 600;">
                Daftarkan Siswa ({{ $siswaBelumPunyaKelas->count() }} belum punya kelas)
            </h4>
            <form method="POST" action="{{ route('admin.kelas-reguler.daftarkan-siswa', $kr->id) }}">
                @csrf
                <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <select name="siswa_ids[]" class="form-input" multiple style="min-height: 80px; font-size: 12px; width: 100%;">
                            @foreach($siswaBelumPunyaKelas as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Tahan Ctrl untuk pilih multiple</p>
                    </div>
                    <button type="submit" class="btn-tartil" style="padding: 10px 20px; font-size: 13px; white-space: nowrap;" onclick="return confirm({{ json_encode('Daftarkan siswa terpilih ke kelas '.$kr->nama.'?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})">
                        Daftarkan
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted); font-size: 14px;">Belum ada kelas reguler aktif.</p>
    </div>
    @endforelse
</div>
@endsection
