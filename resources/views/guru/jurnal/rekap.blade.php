@extends('layouts.admin')
@section('title', 'Rekap Absensi')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">REKAP ABSENSI</h1>
            <p class="page-subtitle">
                {{ $kelasAktif ? $kelasAktif->nama . ' — ' . \Carbon\Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM YYYY') : 'Pilih semester, kelas, dan bulan' }}
                @if($semesterFilter)
                    <span style="font-size: 11px; color: #999; margin-left: 6px;">({{ $semesterFilter->nama }})</span>
                @elseif($semesterId)
                    <span style="font-size: 11px; color: #999; margin-left: 6px;">(Semester terpilih)</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Filter — LONG TERM: Semester → Kelas → Bulan (konsisten dengan Jurnal Bulanan & Absensi Bulanan) --}}
    <div class="card-tartil" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('guru.jurnal.rekap') }}" style="display: flex; gap: 12px; flex-wrap: wrap; margin: 0; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">Semester</label>
                <select name="semester_id" class="form-input" onchange="this.form.submit()" style="font-size: 13px; min-width: 200px;">
                    <option value="">-- Semua Semester --</option>
                    @foreach($semesterList as $sem)
                    <option value="{{ $sem->id }}" {{ ($semesterId ?? '') == $sem->id ? 'selected' : '' }}>
                        {{ $sem->nama }} {{ $sem->status == 'ditutup' ? '(ditutup)' : ($sem->is_aktif ? '[AKTIF]' : '') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="font-size: 14px; min-width: 200px;">
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 160px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">Bulan</label>
                <input type="month" name="bulan" class="form-input" value="{{ $bulan }}"
                    onchange="this.form.submit()" style="font-size: 14px; min-width: 160px;">
            </div>
            @if($kelasAktif && count($tanggalList) > 0)
            <a href="{{ route('guru.jurnal.rekap', ['kelas_id' => $kelasId, 'bulan' => $bulan, 'semester_id' => $semesterId, 'export' => 'excel']) }}" class="btn-tartil-outline" style="font-size: 13px; text-decoration: none; margin-bottom: 1px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Excel
            </a>
            @endif
        </form>
    </div>

    @if($kelasAktif && count($tanggalList) > 0)
    {{-- Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
        <div class="card-tartil" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f5f0eb; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8B7355" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted);">SISWA</div>
                <div style="font-size: 28px; font-weight: 700; color: var(--text-primary);">{{ $siswaList->count() }}</div>
            </div>
        </div>
        <div class="card-tartil" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f5f0eb; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8B7355" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted);">PERTEMUAN</div>
                <div style="font-size: 28px; font-weight: 700; color: var(--text-primary);">{{ count($tanggalList) }}</div>
            </div>
        </div>
        <div class="card-tartil" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f5f0eb; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8B7355" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted);">RATA-RATA</div>
                <div style="font-size: 28px; font-weight: 700; color: var(--text-primary);">{{ $rataRataKelas }}%</div>
            </div>
        </div>
    </div>

    {{-- Tabel Rekap --}}
    <div class="card-tartil" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
            <thead>
                <tr style="background: #f5f0eb;">
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; width: 30px;">NO</th>
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; width: 70px;">NIS</th>
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; width: 60px;">KELAS</th>
                    <th style="padding: 12px 10px; text-align: left; font-size: 11px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; min-width: 150px;">NAMA SISWA</th>
                    @foreach($tanggalList as $t)
                    <th style="padding: 8px 4px; text-align: center; font-size: 10px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; min-width: 36px;">
                        {{ $t['tanggal']->format('d') }}<br>
                        <span style="font-size: 9px; color: #999;">{{ strtoupper($t['tanggal']->format('M')) }}</span>
                    </th>
                    @endforeach
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #5A7D5A; border-bottom: 2px solid #ddd; width: 50px;">B+C</th>
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #C62828; border-bottom: 2px solid #ddd; width: 50px;">K</th>
                    <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: 600; color: #6B5E51; border-bottom: 2px solid #ddd; width: 60px;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaList as $i => $s)
                @php
                    $sid = (int) $s->id;
                    $dataSiswa = $rekapData[$sid] ?? [];
                    $countBaikCukup = $dataSiswa['summary']['b_c'] ?? 0;
                    $countKurang = $dataSiswa['summary']['k'] ?? 0;
                    $totalPertemuan = count($tanggalList);
                    $persen = $totalPertemuan > 0 ? round(($countBaikCukup / $totalPertemuan) * 100) : 0;
                    $persen = min(100, $persen);
                @endphp
                <tr style="border-bottom: 1px solid #f0ece6;">
                    <td style="text-align: center; padding: 8px 6px; font-size: 12px; color: #999;">{{ $i + 1 }}</td>
                    <td style="text-align: center; padding: 8px 6px; font-size: 12px; color: #999;">{{ $s->nis ?? '-' }}</td>
                    <td style="text-align: center; padding: 8px 6px; font-size: 12px; color: #999;">{{ $s->kelas_reguler_snapshot ?? $s->kelasReguler?->nama ?? '-' }}</td>
                    <td style="padding: 8px 10px; font-weight: 600; font-size: 13px;">
                        {{ $s->nama }}
                        @if(($s->status_snapshot ?? $s->status) != 'aktif')
                        <span style="font-size: 9px; font-weight: 400; color: #999; background: #f0f0f0; padding: 1px 4px; border-radius: 3px; margin-left: 4px;">{{ $s->status_snapshot ?? $s->status }}</span>
                        @endif
                    </td>
                    @foreach($tanggalList as $t)
                    @php
                        $nilai = $dataSiswa['nilai'][$t['tanggal_str']] ?? null;
                        $bgClass = '';
                        if ($nilai == 'B') $bgClass = 'style="background: #E9F0E9; color: #5A7D5A; font-weight: 600;"';
                        elseif ($nilai == 'C') $bgClass = 'style="background: #FFF8E1; color: #B8860B; font-weight: 600;"';
                        elseif ($nilai == 'K') $bgClass = 'style="background: #FBE9E7; color: #C62828; font-weight: 600;"';
                    @endphp
                    <td style="text-align: center; padding: 6px 4px; font-size: 12px;" {!! $bgClass !!}>{{ $nilai ?? '-' }}</td>
                    @endforeach
                    <td style="text-align: center; padding: 8px 6px; font-weight: 600; font-size: 13px;">{{ $countBaikCukup }}</td>
                    <td style="text-align: center; padding: 8px 6px; color: #C62828; font-weight: 600; font-size: 13px;">{{ $countKurang }}</td>
                    <td style="text-align: center; padding: 8px 6px; font-weight: 600; font-size: 13px; color: {{ $persen >= 75 ? '#5A7D5A' : ($persen >= 50 ? '#C4953A' : '#A85A52') }}">{{ $persen }}%</td>
                </tr>
                @endforeach

                {{-- Baris B+C --}}
                <tr style="background: #f9f7f4; border-top: 2px solid #ddd; font-weight: 600;">
                    <td colspan="4" style="text-align: right; padding: 10px 12px; font-size: 12px; color: #6B5E51;">B+C</td>
                    @foreach($tanggalList as $t)
                    <td style="text-align: center; padding: 8px 4px; font-size: 12px;">{{ $summaryPerTanggal[$t['tanggal_str']]['b_c'] ?? 0 }}</td>
                    @endforeach
                    <td colspan="3"></td>
                </tr>

                {{-- Baris K --}}
                <tr style="background: #f9f7f4; font-weight: 600;">
                    <td colspan="4" style="text-align: right; padding: 10px 12px; font-size: 12px; color: #C62828;">K</td>
                    @foreach($tanggalList as $t)
                    <td style="text-align: center; padding: 8px 4px; font-size: 12px; color: #C62828;">{{ $summaryPerTanggal[$t['tanggal_str']]['k'] ?? 0 }}</td>
                    @endforeach
                    <td colspan="3"></td>
                </tr>

                {{-- Baris Prosentase Hasil (%) --}}
                {{-- Prosentase = (B+C) / totalSiswaYangDitampilkan * 100 --}}
                <tr style="background: #f5f0eb; font-weight: 700;">
                    <td colspan="4" style="text-align: right; padding: 10px 12px; font-size: 11px; color: #6B5E51;">PROSENTASE HASIL (%)</td>
                    @php $totalSiswa = $siswaList->count(); @endphp
                    @foreach($tanggalList as $t)
                    @php
                        $bc = $summaryPerTanggal[$t['tanggal_str']]['b_c'] ?? 0;
                        $pct = $totalSiswa > 0 ? round(($bc / $totalSiswa) * 100) : 0;
                        $pct = min(100, $pct);
                    @endphp
                    <td style="text-align: center; padding: 8px 4px; font-size: 12px; color: {{ $pct >= 75 ? '#5A7D5A' : ($pct >= 50 ? '#C4953A' : '#A85A52') }}">{{ $pct }}%</td>
                    @endforeach
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Keterangan --}}
    <div style="margin-top: 16px; padding: 16px; background: #faf8f5; border-radius: 8px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
        <strong>Keterangan:</strong> B = Baik, C = Cukup, K = Kurang. Prosentase = (B+C) / total siswa × 100.
        @if($semesterFilter)
        <br>Data difilter untuk semester: <strong>{{ $semesterFilter->nama }}</strong>.
        @endif
    </div>
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Pilih semester, kelas, dan bulan untuk melihat rekap absensi.</div>
    </div>
    @endif
</div>
@endsection
