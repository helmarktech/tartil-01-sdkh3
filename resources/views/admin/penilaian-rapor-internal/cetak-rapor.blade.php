@extends('layouts.admin')
@section('title', 'Cetak Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Cetak Rapor</h1>
            <p class="page-subtitle">
                @if($penilaian)
                    Penilaian: <strong>{{ $penilaian->nama }}</strong> | Semester: {{ $penilaian->semester->nama ?? '-' }}
                @else
                    <span style="color: #c62828;">Belum ada penilaian aktif</span>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.kop-surat-rapor.index') }}" class="btn-tartil-outline" style="text-decoration: none;">Pengaturan Kop Surat</a>
    </div>

    @if(session('error'))
    <div style="background: #FBE9E7; border: 1px solid #EF9A9A; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #C62828;">
        {{ session('error') }}
    </div>
    @endif

    @if(!$penilaian)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted);">Tidak ada penilaian rapor aktif. Buat penilaian terlebih dahulu.</p>
        <a href="{{ route('admin.penilaian-rapor-internal.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buat Penilaian</a>
    </div>
    @else

    {{-- Step 1: Pilih Mode --}}
    <div class="card-tartil" style="padding: 20px; margin-bottom: 16px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Langkah 1: Pilih Berdasarkan Kelas</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.cetak-rapor.pilih', ['mode' => 'tartil']) }}"
               class="btn-tartil{{ $mode == 'tartil' ? '' : '-outline' }}"
               style="text-decoration: none; font-size: 13px; padding: 8px 20px;">
                Kelas Tartil
            </a>
            <a href="{{ route('admin.cetak-rapor.pilih', ['mode' => 'reguler']) }}"
               class="btn-tartil{{ $mode == 'reguler' ? '' : '-outline' }}"
               style="text-decoration: none; font-size: 13px; padding: 8px 20px;">
                Kelas Reguler
            </a>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
            Mode aktif: <strong>{{ $mode == 'tartil' ? 'Kelas Tartil' : 'Kelas Reguler' }}</strong>
            — Cetak rapor {{ $mode == 'tartil' ? 'per kelas tartil (BQ, Tartil, Tahfidz)' : 'per kelas reguler (MI/MTs)' }}
        </p>
    </div>

    {{-- Step 2: Pilih Kelas --}}
    <div class="card-tartil" style="padding: 20px; margin-bottom: 16px;">
        <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Langkah 2: Pilih Kelas</h3>
        <form method="GET" action="{{ route('admin.cetak-rapor.pilih') }}">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <select name="kelas_id" class="form-input" required onchange="this.form.submit()" style="width: 100%;">
                        <option value="">-- Pilih {{ $mode == 'tartil' ? 'Kelas Tartil' : 'Kelas Reguler' }} --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            @if($mode == 'tartil')
                                {{ $k->nama }} ({{ $k->jenis }}) - {{ $k->guru->nama ?? 'Tanpa Guru' }} - {{ $k->siswas_count ?? 0 }} siswa
                            @else
                                {{ $k->nama }} {{ $k->jenjang ? '(' . $k->jenjang . ')' : '' }} - {{ $k->siswas->count() ?? 0 }} siswa
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                @if($kelasTerpilih)
                <a href="{{ $mode == 'tartil' ? route('admin.cetak-rapor.kelas.pdf', ['kelas_id' => $kelasTerpilih->id]) : route('admin.cetak-rapor.kelas-reguler.pdf', ['kelas_id' => $kelasTerpilih->id]) }}"
                   class="btn-tartil"
                   style="text-decoration: none; white-space: nowrap;"
                   onclick="return confirm('Cetak rapor PDF untuk SEMUA {{ $kelasTerpilih->siswas->count() }} siswa?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M10 18c0 0 2-3 4-3s4 3 4 3"/><line x1="12" y1="14" x2="12" y2="22"/><line x1="4" y1="22" x2="20" y2="22"/></svg>
                    Cetak Semua
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Step 3: Preview Siswa --}}
    @if($kelasTerpilih)
    <div class="card-tartil" style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 14px; margin: 0; color: var(--text-primary); font-weight: 600;">
                {{ $kelasTerpilih->nama }} — {{ $kelasTerpilih->siswas->count() }} siswa
            </h3>
            @if($mode == 'tartil')
            <span class="badge-subject">{{ $kelasTerpilih->jenis }} — Guru: {{ $kelasTerpilih->guru->nama ?? '-' }}</span>
            @endif
        </div>

        @if($kelasTerpilih->siswas->count() > 0)
        <div class="table-responsive">
            <table class="table-tartil" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        @if($mode == 'reguler')<th>Kelas Tartil</th>@endif
                        @if($mode == 'tartil')<th>Kelas Reguler</th>@endif
                        <th style="text-align: center;">R2 Harian</th>
                        <th style="text-align: center;">R2 Penilaian</th>
                        <th style="text-align: center;">R2 Akhir</th>
                        <th style="text-align: center;">Terisi</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelasTerpilih->siswas as $i => $s)
                    @php $r = $rekapSiswa[$s->id] ?? null; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $s->nis }}</td>
                        <td style="font-weight: 500;">{{ $s->nama }}</td>
                        @if($mode == 'reguler')
                        <td>{{ $s->kelasTartil->nama ?? '-' }} {{ $s->kelasTartil ? '(' . $s->kelasTartil->jenis . ')' : '' }}</td>
                        @endif
                        @if($mode == 'tartil')
                        <td>{{ $s->kelasReguler->nama ?? '-' }}</td>
                        @endif
                        <td style="text-align: center;">
                            @if($r)
                            <span class="badge-subject" style="background: {{ $r['r2_harian'] >= 80 ? '#E9F0E9' : ($r['r2_harian'] >= 60 ? '#FFF8E1' : '#FBE9E7') }}; color: {{ $r['r2_harian'] >= 80 ? '#5A7D5A' : ($r['r2_harian'] >= 60 ? '#B8860B' : '#C62828') }};">
                                {{ $r['r2_harian'] }}%
                            </span>
                            @else - @endif
                        </td>
                        <td style="text-align: center;">
                            @if($r)
                            <span class="badge-subject" style="background: {{ $r['r2_penilaian'] >= 80 ? '#E9F0E9' : ($r['r2_penilaian'] >= 60 ? '#FFF8E1' : '#FBE9E7') }}; color: {{ $r['r2_penilaian'] >= 80 ? '#5A7D5A' : ($r['r2_penilaian'] >= 60 ? '#B8860B' : '#C62828') }};">
                                {{ $r['r2_penilaian'] }}
                            </span>
                            @else - @endif
                        </td>
                        <td style="text-align: center;">
                            @if($r)
                            <span class="badge-subject" style="background: {{ $r['r2_akhir'] >= 80 ? '#E9F0E9' : ($r['r2_akhir'] >= 60 ? '#FFF8E1' : '#FBE9E7') }}; color: {{ $r['r2_akhir'] >= 80 ? '#5A7D5A' : ($r['r2_akhir'] >= 60 ? '#B8860B' : '#C62828') }};">
                                {{ $r['r2_akhir'] }}
                            </span>
                            @else - @endif
                        </td>
                        <td style="text-align: center;">
                            @if($r)
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $r['jumlah_terisi'] }}/{{ $r['jumlah_indikator'] }}</span>
                            @else - @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.cetak-rapor.pdf', $s->id) }}" class="btn-tartil" style="padding: 5px 10px; font-size: 11px; text-decoration: none;">Cetak PDF</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="color: var(--text-muted); text-align: center; padding: 24px;">Tidak ada siswa aktif di kelas ini.</p>
        @endif
    </div>
    @endif

    @endif
</div>
@endsection
