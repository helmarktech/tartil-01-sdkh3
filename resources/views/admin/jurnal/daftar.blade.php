@extends('layouts.admin')
@section('title', 'Daftar Jurnal')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title-display">Daftar Jurnal</h1>
        <p class="page-subtitle">Rekap pengisian jurnal per bulan — tema dan topik pembelajaran</p>
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
                <label class="form-label" style="font-size: 12px;">Bulan</label>
                <input type="month" name="bulan" class="form-input" value="{{ $bulan }}" onchange="this.form.submit()" style="min-width: 160px;">
            </div>
        </form>
    </div>

    @if($semesterId && $kelasId)
        @php
            $kelasAktif = $kelasList->firstWhere('id', $kelasId);
            $bulanLabel = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->locale('id')->isoFormat('MMMM YYYY');
        @endphp

        {{-- Info --}}
        <div style="margin-bottom: 16px; font-size: 14px; color: var(--text-muted);">
            <strong style="color: var(--text-primary);">{{ $kelasAktif?->nama ?? '-' }}</strong> — {{ $bulanLabel }}
            <span style="margin-left: 12px;">({{ $jurnals->count() }} pertemuan)</span>
        </div>

        @if($jurnals->count() > 0)
        <div class="card-tartil" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="table-tartil">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th style="width: 80px; text-align: center;">Tanggal</th>
                            <th style="width: 60px; text-align: center;">TM Ke-</th>
                            <th>Surat / Ayat</th>
                            <th>Tema Pembelajaran</th>
                            <th>Topik</th>
                            <th>Halaman / Juz</th>
                            <th>Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jurnals as $i => $j)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">{{ $i + 1 }}</td>
                            <td style="text-align: center; font-weight: 500;">
                                {{ $j->tanggal->format('d/m') }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-subject" style="font-size: 10px;">{{ $j->pertemuan_ke ?? '-' }}</span>
                            </td>
                            <td>
                                @if($j->surat)
                                    <span style="font-weight: 500;">{{ $j->surat->nama }}</span>
                                    @if($j->ayat)
                                        <span style="color: var(--text-muted); font-size: 12px;"> ({{ $j->ayat }})</span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td style="font-weight: 500;">{{ $j->materi_pembelajaran ?? '-' }}</td>
                            <td>{{ $j->topik ?? '-' }}</td>
                            <td style="color: var(--text-muted);">{{ $j->halaman_juz ?? '-' }}</td>
                            <td style="color: var(--text-muted); font-size: 12px;">{{ $j->guru->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card-tartil" style="text-align: center; padding: 48px;">
            <div style="color: var(--text-muted);">Belum ada jurnal untuk kelas dan bulan ini.</div>
        </div>
        @endif
    @else
    <div class="card-tartil" style="text-align: center; padding: 48px;">
        <div style="color: var(--text-muted);">Pilih semester dan kelas untuk melihat daftar jurnal.</div>
    </div>
    @endif
</div>
@endsection
