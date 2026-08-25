@extends('layouts.admin')
@section('title', 'Monitoring Guru')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Monitoring Guru</h1>
            <p class="page-subtitle">
                @if($semesterAktif)
                    Deteksi guru yang belum mengisi jurnal — Semester: <strong>{{ $semesterAktif->nama }}</strong>
                @else
                    <span style="color: #c62828;">Tidak ada semester aktif</span>
                @endif
            </p>
        </div>
    </div>

    @if(!$semesterAktif)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted);">Tidak dapat memonitoring. Tidak ada semester yang aktif saat ini.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buka Tahun Ajaran</a>
    </div>
    @else

    {{-- Ringkasan --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $ringkasan['hari_kerja'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Hari Kerja<br>(Senin-Kamis)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $ringkasan['total_hari_libur'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Hari Libur<br>(Ditandai)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $ringkasan['total_kelas'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Total Kelas Aktif</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: {{ $ringkasan['total_kelas_kurang'] > 0 ? '#A85A52' : '#5A7D5A' }};">{{ $ringkasan['total_kelas_kurang'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Kelas Masih Kurang</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: {{ $ringkasan['total_hari_kurang'] > 0 ? '#A85A52' : '#5A7D5A' }};">{{ number_format($ringkasan['total_hari_kurang']) }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Hari Kurang</div>
        </div>
    </div>

    {{-- Penjelasan Logika --}}
    <div class="card-tartil" style="padding: 16px; margin-bottom: 20px; background: #f8f9fa;">
        <div style="font-size: 12px; color: var(--text-muted); line-height: 1.7;">
            <strong style="color: var(--text-primary);">Logika (dengan hari libur per kelas):</strong><br>
            1. <strong>Hari kerja</strong> = Senin-Kamis dari awal semester sampai hari ini.<br>
            2. <strong>Hari libur</strong> = tanggal yang ditandai libur untuk kelas tersebut (kegiatan sekolah, dll).<br>
            3. <strong>Target per kelas</strong> = hari kerja − hari libur kelas itu.<br>
            4. Hari libur dikelola admin via menu <strong>Pengaturan → Hari Libur</strong>.<br>
            5. <strong>Kurang</strong> = target − distinct tanggal jurnal yang sudah terisi.
        </div>
    </div>

    {{-- Daftar Guru --}}
    @forelse($dataGuru as $guruId => $g)
    @php
        $kelasKurang = collect($g['kelas'])->where('kurang', '>', 0)->count();
        $persenRata = collect($g['kelas'])->avg('persen');
        $warna = $persenRata >= 90 ? '#5A7D5A' : ($persenRata >= 70 ? '#B8860B' : '#A85A52');
    @endphp
    <div class="card-tartil" style="padding: 20px; margin-bottom: 16px;">
        {{-- Header Guru --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #f5f0eb; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: #6B5E51;">
                    {{ $g['inisial'] }}
                </div>
                <div>
                    <div style="font-weight: 600;">{{ $g['nama'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ count($g['kelas']) }} kelas</div>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 18px; font-weight: 700; color: {{ $warna }};">{{ round($persenRata) }}%</div>
                <div style="font-size: 10px; color: var(--text-muted);">
                    @if($kelasKurang > 0)
                    <span style="color: #A85A52;">{{ $kelasKurang }} kelas kurang</span>
                    @else
                    <span style="color: #5A7D5A;">Lengkap</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Kelas --}}
        @foreach($g['kelas'] as $k)
        <div style="display: grid; grid-template-columns: {{ $k['kurang'] > 0 ? 'minmax(280px, 1fr) minmax(220px, 1fr)' : 'minmax(280px, 1fr)' }}; gap: 10px; margin-bottom: 10px;">
            <div style="border: 1px solid {{ $k['kurang'] > 0 ? '#EF9A9A' : '#C3D9C3' }}; border-radius: 6px; padding: 12px; background: {{ $k['kurang'] > 0 ? '#FFF8F8' : '#F8FBF8' }};">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <div style="font-weight: 600; font-size: 13px;">{{ $k['kelas']->nama }}</div>
                    <div style="font-size: 18px; font-weight: 700; color: {{ $k['persen'] >= 90 ? '#5A7D5A' : ($k['persen'] >= 70 ? '#B8860B' : '#A85A52') }};">{{ $k['persen'] }}%</div>
                </div>

                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">
                    {{ $k['jumlah_siswa'] }} siswa |
                    Kerja: {{ $k['hari_kerja'] }} |
                    @if($k['hari_libur'] > 0)
                    <span style="color: #B8860B;">Libur: {{ $k['hari_libur'] }}</span> |
                    @endif
                    Target: <strong>{{ $k['target_hari'] }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 6px;">
                    <span style="color: #5A7D5A;">Sudah: {{ $k['terisi'] }} hari</span>
                    @if($k['kurang'] > 0)
                    <span style="color: #A85A52; font-weight: 600;">Kurang: {{ $k['kurang'] }} hari</span>
                    @else
                    <span style="color: #5A7D5A;">Lengkap</span>
                    @endif
                </div>

                @if($k['kurang'] > 0 && $k['kurang'] <= 3)
                <div style="font-size: 10px; color: #B8860B; margin-bottom: 4px;">Hampir lengkap — tinggal {{ $k['kurang'] }} hari lagi</div>
                @endif

                <div style="width: 100%; height: 5px; background: #f0ece4; border-radius: 3px;">
                    <div style="width: {{ $k['persen'] }}%; height: 100%; background: {{ $k['persen'] >= 90 ? '#5A7D5A' : ($k['persen'] >= 70 ? '#B8860B' : '#C62828') }}; border-radius: 3px;"></div>
                </div>

                @if($k['terakhir'])
                <div style="font-size: 10px; color: #999; margin-top: 4px;">
                    Terakhir: {{ \Carbon\Carbon::parse($k['terakhir'])->format('d/m/Y') }}
                </div>
                @else
                <div style="font-size: 10px; color: #A85A52; margin-top: 4px;">
                    Belum pernah mengisi jurnal
                </div>
                @endif
            </div>

            @if($k['kurang'] > 0 && isset($k['tanggal_kurang']) && $k['tanggal_kurang']->count() > 0)
            <div style="border: 1px solid #EF9A9A; border-radius: 6px; padding: 12px; background: #FFF8F8;">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #A85A52;">
                    Detail Kurang ({{ $k['kurang'] }} hari)
                </div>
                <div style="max-height: 140px; overflow-y: auto;">
                    @foreach($k['tanggal_kurang'] as $tgl)
                    <div style="font-size: 11px; color: #4B5563; padding: 3px 0; border-bottom: 1px dashed #EF9A9A;">
                        {{ $tgl->locale('id')->isoFormat('dddd, D MMM Y') }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @empty
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted);">Tidak ada kelas aktif dengan guru pengampu.</p>
    </div>
    @endforelse

    @endif
</div>
@endsection
