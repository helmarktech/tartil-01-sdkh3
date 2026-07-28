@extends('layouts.admin')
@section('title', 'Track Record Nilai Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Track Record</h1>
            <p class="page-subtitle">Riwayat nilai rapor per siswa</p>
        </div>
    </div>

    {{-- Filter Siswa --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 20px;">
        <form method="GET" action="{{ route('guru.penilaian-rapor.track-record') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 250px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Pilih Siswa</label>
                <select name="siswa_id" class="form-input" required onchange="this.form.submit()">
                    <option value="" disabled {{ !$siswa ? 'selected' : '' }}>-- Pilih Siswa --</option>
                    @foreach($siswaList as $s)
                    <option value="{{ $s->id }}" {{ $siswa && $siswa->id == $s->id ? 'selected' : '' }}>
                        {{ $s->nama }} ({{ $s->nis }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($siswa && !empty($nilaiDetail))
        {{-- Info Siswa --}}
        <div class="card-tartil" style="margin-bottom: 20px; padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700;">
                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 16px; font-weight: 600; color: var(--text-primary);">{{ $siswa->nama }}</div>
                <div style="font-size: 13px; color: var(--text-muted);">{{ $siswa->nis }} — Kelas Tartil: {{ $siswa->kelasTartil->nama ?? '-' }} — Kelas Reguler: {{ $siswa->kelasReguler->nama ?? '-' }}</div>
            </div>
        </div>

        {{-- Jurnal --}}
        @if(isset($nilaiDetail['jurnal']))
        <div class="card-tartil" style="margin-bottom: 16px; padding: 20px;">
            <h3 style="font-size: 14px; margin: 0 0 12px; color: var(--text-primary); font-weight: 600;">Jurnal Harian</h3>
            <div style="font-size: 24px; font-weight: 700; color: var(--accent);">
                {{ $nilaiDetail['jurnal']['persen_b'] }}%
                <span style="font-size: 13px; color: var(--text-muted); font-weight: 400;">persentase B (Baik)</span>
            </div>
        </div>
        @endif

        {{-- Penilaian Internal --}}
        @foreach($nilaiDetail as $key => $nd)
            @if($key !== 'jurnal' && is_array($nd))
            <div class="card-tartil" style="margin-bottom: 16px; padding: 0; overflow: hidden;">
                <div style="background: var(--surface); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 600; font-size: 14px; color: var(--text-primary);">{{ $nd['penilaian']->nama }}</div>
                    <div>
                        <span class="badge-success" style="font-size: 14px;">Rata-rata: {{ $nd['rata'] }}</span>
                    </div>
                </div>
                <div style="padding: 12px 20px;">
                    <div class="table-responsive">
                        <table class="table-tartil" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>Indikator</th>
                                    <th style="text-align: center;">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nd['detail'] as $n)
                                <tr>
                                    <td>{{ $n->indikator->nama_indikator ?? '-' }}</td>
                                    <td style="text-align: center; font-weight: 600;">{{ $n->nilai }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @elseif($siswa)
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">Belum ada data nilai untuk siswa ini.</p>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 60px 40px;">
        <p style="color: var(--text-muted);">Pilih siswa untuk melihat track record nilai.</p>
    </div>
    @endif
</div>
@endsection
