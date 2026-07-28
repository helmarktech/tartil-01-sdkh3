@extends('layouts.admin')
@section('title', 'Rekap Nilai Rapor')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Rekap Nilai Rapor</h1>
            <p class="page-subtitle">R2 Penilaian + R2 Harian → R2 Akhir</p>
        </div>
    </div>

    {{-- Filter Kelas --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 20px;">
        <form method="GET" action="{{ route('guru.penilaian-rapor.rekap') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-size: 13px;">Pilih Kelas</label>
                <select name="kelas_id" class="form-input" required onchange="this.form.submit()">
                    <option value="" disabled {{ !$kelasTerpilih ? 'selected' : '' }}>-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasTerpilih && $kelasTerpilih->id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->jenis }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($penilaian && $kelasTerpilih)
        <div style="margin-top: 12px; padding: 10px 14px; background: #E9F0E9; border-radius: 8px; font-size: 13px; color: #5A7D5A;">
            <strong>{{ $penilaian->nama }}</strong> — Semester {{ $penilaian->semester->nama ?? '-' }}
            <span style="margin-left: 12px;">Rumus: <strong>(R2 Penilaian + R2 Harian) / 2 = R2 Akhir</strong></span>
        </div>
        @elseif(!$penilaian && $kelasTerpilih)
        <div style="margin-top: 12px; padding: 10px 14px; background: #fff3e0; border-radius: 8px; font-size: 13px; color: #e65100;">
            Belum ada penilaian rapor untuk semester ini. Hubungi admin.
        </div>
        @endif
    </div>

    @if($kelasTerpilih && $siswaList->isNotEmpty() && $penilaian)
    <div class="card-tartil table-responsive" style="padding: 0;">
        <table class="table-tartil" style="font-size: 12px;">
            <thead>
                <tr style="background: var(--surface);">
                    <th style="width: 30px; text-align: center;">No</th>
                    <th style="min-width: 120px;">Nama</th>
                    {{-- Nilai per indikator --}}
                    @foreach($indikators as $ind)
                    <th style="text-align: center; min-width: 55px; font-size: 10px;">
                        <div style="max-width: 70px; word-wrap: break-word; line-height: 1.2;">{{ $ind->nama_indikator }}</div>
                    </th>
                    @endforeach
                    {{-- 3 R2 terpisah --}}
                    <th style="text-align: center; min-width: 55px;">R2<br>Penilaian</th>
                    <th style="text-align: center; min-width: 55px;">R2<br>Harian</th>
                    <th style="text-align: center; min-width: 55px; background: #E9F0E9;">R2<br>Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaList as $i => $s)
                @php $rd = $rekapData[$s->id] ?? null; @endphp
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td style="font-weight: 500; white-space: nowrap;">
                        {{ $s->nama }}
                        <span style="font-size: 9px; color: var(--text-muted); display: block;">{{ $s->nis }}</span>
                    </td>
                    {{-- Nilai per indikator --}}
                    @foreach($indikators as $ind)
                    @php $n = $rd['nilai_per_indikator'][$ind->id] ?? null; @endphp
                    <td style="text-align: center;">
                        @if($n !== null)
                            <span style="font-weight: 600; color: {{ $n >= 80 ? '#5A7D5A' : ($n >= 60 ? '#D4A373' : '#A85A52') }}">{{ $n }}</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    @endforeach
                    {{-- R2 Penilaian --}}
                    <td style="text-align: center;">
                        @if($rd)
                            <span class="badge-subject" style="font-size: 11px; background: #E9F0E9; color: #5A7D5A;">{{ $rd['r2_penilaian'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    {{-- R2 Harian --}}
                    <td style="text-align: center;">
                        @if($rd)
                            <span class="badge-subject" style="font-size: 11px;">{{ $rd['r2_harian'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    {{-- R2 Akhir --}}
                    <td style="text-align: center; background: #f8faf8;">
                        @if($rd)
                            <span style="font-size: 14px; font-weight: 700; color: {{ $rd['r2_akhir'] >= 80 ? '#5A7D5A' : ($rd['r2_akhir'] >= 60 ? '#D4A373' : '#A85A52') }}">{{ $rd['r2_akhir'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif($kelasTerpilih && $siswaList->isEmpty())
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">Tidak ada siswa aktif di kelas ini.</p>
    </div>
    @elseif($kelasTerpilih && !$penilaian)
    <div class="card-tartil" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">Belum ada penilaian rapor untuk semester ini. Hubungi admin untuk membuat penilaian.</p>
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 60px 40px;">
        <p style="color: var(--text-muted);">Pilih kelas untuk melihat rekap nilai.</p>
    </div>
    @endif
</div>
@endsection
