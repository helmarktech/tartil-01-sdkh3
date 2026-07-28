@extends('layouts.admin')
@section('title', 'Rekap Absensi')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <h1 class="page-title-display">Rekap Absensi</h1>
            <p class="page-subtitle">Ringkasan absensi seluruh kelas tartil</p>
        </div>
    </div>

    @php
        // ── LONG TERM: Hitung summary dari data rekap (snapshot) — bukan dari siswa aktif ──
        $totalPertemuan = 0;
        $totalB = 0;
        $totalC = 0;
        $totalK = 0;
        $totalSiswaRekap = 0;

        foreach ($rekap as $siswaId => $bulanRekaps) {
            $totalSiswaRekap++;
            foreach ($bulanRekaps as $r) {
                $totalPertemuan += $r->total_hadir;
                $totalB += $r->count_b;
                $totalC += $r->count_c;
                $totalK += $r->count_k;
            }
        }

        $totalKelas = $kelasList->count();

        // Hitung total siswa dari data rekap (historis), bukan hanya siswa aktif
        $totalSiswa = $totalSiswaRekap > 0 ? $totalSiswaRekap : 0;
        if ($totalSiswa == 0 && $kelasId) {
            // Fallback: hitung dari siswa aktif jika belum ada data rekap
            $totalSiswa = \App\Models\Siswa::where('kelas_tartil_id', $kelasId)->count();
        }

        $totalNilai = $totalB + $totalC + $totalK;
        $rataRata = $totalNilai > 0 ? round((($totalB * 1.0 + $totalC * 0.67 + $totalK * 0.33) / $totalNilai) * 100) : 0;

    @endphp

    {{-- 4 Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px;">
        {{-- Jumlah Siswa --}}
        <div class="card-tartil" style="text-align: center; padding: 28px 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #E8EDF3; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#5A7D8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-primary); line-height: 1;">{{ $totalSiswa }}</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">Jumlah Siswa</div>
        </div>

        {{-- Jumlah Pertemuan --}}
        <div class="card-tartil" style="text-align: center; padding: 28px 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF8E1; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#B8860B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-primary); line-height: 1;">{{ $totalPertemuan }}</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">Jumlah Pertemuan</div>
        </div>

        {{-- Jumlah Kelas --}}
        <div class="card-tartil" style="text-align: center; padding: 28px 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #E9F0E9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#5A7D5A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-primary); line-height: 1;">{{ $totalKelas }}</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">Jumlah Kelas</div>
        </div>

        {{-- Rata-rata Kelas --}}
        <div class="card-tartil" style="text-align: center; padding: 28px 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #f5f0eb; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8B7355" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
            </div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-primary); line-height: 1;">{{ $rataRata }}%</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">Rata-rata Kelas</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 20px;">
        <form method="GET" class="form-inline" style="gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Semester</label>
                <select name="semester_id" class="form-input" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">-- Pilih Semester --</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Kelas</label>
                <select name="kelas_id" class="form-input" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Bulan (opsional)</label>
                <select name="bulan" class="form-input" onchange="this.form.submit()" style="min-width: 160px;">
                    <option value="">-- Semua Bulan --</option>
                    @php
                        $bulanList = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                      '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                    @endphp
                    @foreach($bulanList as $bln => $nama)
                    <option value="{{ $bln }}" {{ $bulan == $bln ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Tabel Daftar Rekap per Kelas --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 15px; font-weight: 600; margin: 0; color: var(--text-primary);">Daftar Rekap Absensi per Kelas</h3>
        </div>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama Kelas</th>
                        <th>Guru</th>
                        <th style="text-align: center;">Jumlah Siswa</th>
                        <th style="text-align: center;">Jumlah Pertemuan</th>
                        <th style="text-align: center;">B (Baik)</th>
                        <th style="text-align: center;">C (Cukup)</th>
                        <th style="text-align: center;">K (Kurang)</th>
                        <th style="text-align: center;">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @php
                        // LONG TERM: Hitung total dari data rekap (sudah difilter per kelas)
                        $siswaCount = $rekap->count();
                        $pertemuanCount = 0;
                        $kelasB = 0;
                        $kelasC = 0;
                        $kelasK = 0;
                        foreach ($rekap as $siswaId => $rekapSiswa) {
                            $r = $rekapSiswa->first();
                            $pertemuanCount += $r->total_hadir;
                            $kelasB += $r->count_b;
                            $kelasC += $r->count_c;
                            $kelasK += $r->count_k;
                        }
                        $kelasTotal = $kelasB + $kelasC + $kelasK;
                        $kelasRata = $kelasTotal > 0 ? round((($kelasB * 1.0 + $kelasC * 0.67 + $kelasK * 0.33) / $kelasTotal) * 100) : 0;
                    @endphp
                    @if($kelasId && $kelasList->firstWhere('id', $kelasId))
                    @php $k = $kelasList->firstWhere('id', $kelasId); @endphp
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td style="font-weight: 600;">{{ $k->nama }}</td>
                        <td style="color: var(--text-muted);">{{ $k->guru->nama ?? '-' }}</td>
                        <td style="text-align: center;">{{ $siswaCount }}</td>
                        <td style="text-align: center;">{{ $pertemuanCount }}</td>
                        <td style="text-align: center; color: #5A7D5A; font-weight: 600;">{{ $kelasB }}</td>
                        <td style="text-align: center; color: #B8860B; font-weight: 600;">{{ $kelasC }}</td>
                        <td style="text-align: center; color: #C62828; font-weight: 600;">{{ $kelasK }}</td>
                        <td style="text-align: center;">
                            <span class="{{ $kelasRata >= 80 ? 'badge-success' : ($kelasRata >= 60 ? 'badge-warning' : 'badge-error') }}" style="font-size: 11px;">{{ $kelasRata }}%</span>
                        </td>
                    </tr>
                    @endif
                    @if($kelasList->count() == 0)
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">Tidak ada kelas.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- LONG TERM: Tabel Detail per Siswa — termasuk siswa yang sudah lulus/nonaktif --}}
    @if($warningBulan ?? false)
    <div class="card-tartil" style="padding: 12px 16px; margin-bottom: 16px; background: #FFF8E1; border-left: 4px solid #B8860B;">
        <div style="font-size: 13px; color: #6B5E51;">
            <strong>Perhatian:</strong> {{ $warningBulan }}
        </div>
    </div>
    @endif

    @if($rekap->isNotEmpty())
    <div class="card-tartil" style="padding: 0; overflow: hidden; margin-top: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 15px; font-weight: 600; margin: 0; color: var(--text-primary);">
                Detail Absensi per Siswa
                <span style="font-size: 12px; font-weight: 400; color: var(--text-muted);">— data historis, tetap terlihat walaupun siswa sudah lulus</span>
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table-tartil">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Pertemuan</th>
                        <th style="text-align: center; color: #5A7D5A;">B</th>
                        <th style="text-align: center; color: #B8860B;">C</th>
                        <th style="text-align: center; color: #C62828;">K</th>
                        <th style="text-align: center;">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noSiswa = 1; @endphp
                    @foreach($rekap as $siswaId => $rekapSiswa)
                    @php
                        $s = $rekapSiswa->first()->siswa ?? $siswaDariRekap->get($siswaId);
                        $r = $rekapSiswa->first();
                        $sTotal = $r->count_b + $r->count_c + $r->count_k;
                        $sRata = $sTotal > 0 ? round((($r->count_b * 1.0 + $r->count_c * 0.67 + $r->count_k * 0.33) / $sTotal) * 100) : 0;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $noSiswa++ }}</td>
                        <td style="color: var(--text-muted);">{{ $s->nis ?? '-' }}</td>
                        <td style="font-weight: 600;">
                            {{ $s->nama ?? 'Siswa #' . $siswaId }}
                            @if($s && $s->status != 'aktif')
                            <span style="font-size: 9px; font-weight: 400; color: #999; background: #f0f0f0; padding: 1px 4px; border-radius: 3px; margin-left: 4px;">{{ $s->status }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($s)
                                @if($s->status == 'aktif')
                                    <span class="badge-success" style="font-size: 10px;">Aktif</span>
                                @elseif($s->status == 'lulus')
                                    <span class="badge-primary" style="font-size: 10px;">Lulus</span>
                                @else
                                    <span class="badge-warning" style="font-size: 10px;">{{ $s->status }}</span>
                                @endif
                            @else
                                <span class="badge-muted" style="font-size: 10px;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $r->total_hadir }}</td>
                        <td style="text-align: center; color: #5A7D5A; font-weight: 600;">{{ $r->count_b }}</td>
                        <td style="text-align: center; color: #B8860B; font-weight: 600;">{{ $r->count_c }}</td>
                        <td style="text-align: center; color: #C62828; font-weight: 600;">{{ $r->count_k }}</td>
                        <td style="text-align: center;">
                            <span class="{{ $sRata >= 80 ? 'badge-success' : ($sRata >= 60 ? 'badge-warning' : 'badge-error') }}" style="font-size: 11px;">{{ $sRata }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
