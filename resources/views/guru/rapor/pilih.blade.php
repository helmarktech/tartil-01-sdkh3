@extends('layouts.admin')
@section('title', 'Cetak Rapor')

@section('content')
<div>
    <div class="page-header">
        <h1 class="page-title-display">Cetak Rapor</h1>
        <p class="page-subtitle">Pilih kelas dan semester untuk generate rapor</p>
    </div>

    <div class="card-tartil" style="max-width: 500px; padding: 24px;">
        <form method="POST" action="{{ route('guru.rapor.preview') }}" style="margin: 0;">
            @csrf
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Kelas *</label>
                    <select name="kelas_id" class="form-input" required style="width: 100%;">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }} - {{ $k->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Semester *</label>
                    <select name="semester_id" class="form-input" required style="width: 100%;">
                        <option value="">-- Pilih Semester --</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->tahun_ajaran }} {{ ucfirst($s->jenis) }} {{ $s->is_aktif ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Jenis Penilaian *</label>
                    <select name="jenis" class="form-input" required style="width: 100%;">
                        <option value="tengah">Tengah Semester</option>
                        <option value="akhir">Akhir Semester</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-tartil" style="margin-top: 20px; width: 100%; justify-content: center;">Preview Rapor</button>
        </form>
    </div>
</div>
@endsection
