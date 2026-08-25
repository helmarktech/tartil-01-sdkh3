@extends('layouts.admin')
@section('title', 'Manajemen Hari Libur')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Manajemen Hari Libur</h1>
            <p class="page-subtitle">
                @if($semesterAktif)
                    Pengaturan hari libur untuk seluruh kelas tartil — Semester: <strong>{{ $semesterAktif->nama }}</strong>
                @else
                    <span style="color: #c62828;">Tidak ada semester aktif</span>
                @endif
            </p>
        </div>
    </div>

    @if(!$semesterAktif)
    <div class="card-tartil" style="padding: 32px; text-align: center;">
        <p style="color: var(--text-muted);">Tidak dapat mengatur hari libur. Tidak ada semester yang aktif saat ini.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-tartil" style="margin-top: 12px; text-decoration: none;">Buka Tahun Ajaran</a>
    </div>
    @else

    {{-- Form Tandai Libur Massal --}}
    <div class="card-tartil" style="padding: 20px; margin-bottom: 20px;">
        <form method="POST" action="{{ route('admin.kelas-libur.store') }}">
            @csrf
            <input type="hidden" name="semua_kelas" value="1">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 12px;">Tanggal Libur</label>
                    <input type="date" name="tanggal" class="form-input" required style="width: 100%;">
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Kegiatan OSIS" required style="width: 100%;">
                </div>
                <div>
                    <button type="submit" class="btn-tartil" style="width: 100%;">+ Tandai Libur Massal</button>
                </div>
            </div>
        </form>
        <div style="margin-top: 12px; font-size: 11px; color: var(--text-muted);">
            Tanggal yang ditandai akan otomatis diterapkan ke <strong>seluruh kelas tartil aktif</strong>.
        </div>
    </div>

    {{-- Daftar Hari Libur --}}
    <div class="card-tartil" style="padding: 20px;">
        <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--text-primary);">Daftar Hari Libur</h3>

        @if($liburList->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($liburList as $l)
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 8px; background: #fff;">
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <div style="min-width: 80px; text-align: center; padding: 8px 12px; background: #f5f0eb; border-radius: 6px;">
                        <div style="font-size: 18px; font-weight: 700; color: var(--text-primary);">{{ $l['tanggal']->format('d') }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">{{ $l['tanggal']->format('M Y') }}</div>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: var(--text-primary);">{{ $l['tanggal']->locale('id')->isoFormat('dddd') }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ $l['keterangan'] }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ $l['jumlah_kelas'] }} kelas terdampak</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <button type="button" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;" onclick="toggleDetail('detail-{{ $loop->index }}')">Detail</button>
                    <form method="POST" action="{{ route('admin.kelas-libur.destroy-by-tanggal', ['tanggal' => $l['tanggal']->format('Y-m-d')]) }}" style="display: inline;" onsubmit="return confirm('Hapus tanda libur {{ $l['tanggal']->format('d/m/Y') }} untuk semua kelas?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger-sm" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                    </form>
                </div>
            </div>

            {{-- Detail Kelas Terdampak --}}
            <div id="detail-{{ $loop->index }}" style="display: none; margin: -4px 0 8px 0; padding: 14px 16px; background: #f8f9fa; border: 1px solid var(--border); border-radius: 8px;">
                <div style="font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">Kelas yang ditandai libur:</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @foreach($l['items'] as $item)
                    <span style="display: inline-block; padding: 4px 10px; background: #fff; border: 1px solid var(--border); border-radius: 20px; font-size: 11px; color: var(--text-secondary);">{{ $item->kelas?->nama ?? '-' }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 32px; color: var(--text-muted);">
            Belum ada hari libur yang ditandai di semester ini.
        </div>
        @endif
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}
</script>
@endpush
