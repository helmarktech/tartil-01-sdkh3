@extends('layouts.siswa')

@section('title', 'Hafalan Al-Quran')

@section('content')
<style>
.haf-juz-grid {
    display: grid;
    grid-template-columns: repeat(15, 1fr);
    gap: 4px;
    margin-bottom: 16px;
}
.haf-juz-item {
    aspect-ratio: 1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    transition: transform 0.15s;
    cursor: default;
}
.haf-juz-item:hover { transform: scale(1.15); }
.haf-status-hafal { background: #0c8a5f; color: #fff; }
.haf-status-murajaah { background: #6a1b9a; color: #fff; }
.haf-status-setengah_hafal { background: #e65100; color: #fff; }
.haf-status-baru { background: #1565c0; color: #fff; }
.haf-status-belum { background: #e5e5e5; color: #aaa; }

.haf-legend { display: flex; gap: 16px; flex-wrap: wrap; font-size: 11px; color: #666; }
.haf-legend span { display: inline-flex; align-items: center; gap: 6px; }
.haf-legend-dot { width: 14px; height: 14px; border-radius: 4px; }

.haf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.haf-table th {
    text-align: left;
    padding: 10px 12px;
    background: #f8faf8;
    font-size: 11px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0;
}
.haf-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.haf-table tr:hover td { background: #f8faf8; }

.haf-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}
.haf-badge-hafal { background: #d4edda; color: #155724; }
.haf-badge-murajaah { background: #f3e5f5; color: #6a1b9a; }
.haf-badge-setengah_hafal { background: #e3f2fd; color: #1565c0; }
.haf-badge-baru { background: #fff3cd; color: #856404; }

.haf-surat-card {
    background: #f8faf8;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.haf-surat-nomor {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #0c8a5f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}
.haf-surat-info { flex: 1; min-width: 0; }
.haf-surat-nama { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.haf-surat-meta { font-size: 11px; color: #666; margin-top: 2px; }

.haf-btn-konfirmasi {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 18px; background: #0c8a5f; color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; width: 100%;
}
.haf-btn-konfirmasi:hover { background: #0a6b4a; }

@media (max-width: 640px) {
    .haf-juz-grid { grid-template-columns: repeat(10, 1fr); }
}
@media (max-width: 420px) {
    .haf-juz-grid { grid-template-columns: repeat(6, 1fr); }
    .haf-table th, .haf-table td { padding: 8px 6px; font-size: 12px; }
}
</style>

<div class="siswa-page-header">
    <div class="siswa-page-icon" style="background: linear-gradient(135deg, #0c8a5f, #065f43);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
    </div>
    <div>
        <h1 class="siswa-page-title">Hafalan Al-Quran</h1>
        <p class="siswa-page-subtitle">Pantau progres hafalan Anda</p>
    </div>
</div>

{{-- Progress Juz --}}
<div class="sd-section" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 class="sd-section-title" style="margin: 0;">Progress Juz ({{ $totalJuzHafal }}/30)</h2>
        @if($juzAktif)
        <span style="font-size: 12px; color: #0c8a5f; background: #e8f5e9; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
            &#128308; Setoran Terakhir: Juz {{ $juzAktif->juz }} ({{ \App\Models\HafalanTahfidz::labelStatus($juzAktif->status) }})
        </span>
        @endif
    </div>

    <div class="haf-juz-grid">
        @foreach($progressJuz as $pj)
            <div class="haf-juz-item haf-status-{{ $pj['status'] ?? 'belum' }}"
                 title="Juz {{ $pj['juz'] }}: {{ $pj['status'] ? \App\Models\HafalanTahfidz::labelStatus($pj['status']) : 'Belum' }}{{ $pj['surat'] ? ' - ' . $pj['surat'] : '' }}">
                {{ $pj['juz'] }}
            </div>
        @endforeach
    </div>

    <div class="haf-legend">
        <span><span class="haf-legend-dot" style="background:#0c8a5f;"></span> Hafal</span>
        <span><span class="haf-legend-dot" style="background:#6a1b9a;"></span> Murojaah</span>
        <span><span class="haf-legend-dot" style="background:#e65100;"></span> Setengah Hafal</span>
        <span><span class="haf-legend-dot" style="background:#1565c0;"></span> Baru</span>
        <span><span class="haf-legend-dot" style="background:#e5e5e5;"></span> Belum</span>
    </div>
</div>

{{-- Surat yang Telah Dihafal --}}
<div class="sd-section" style="margin-bottom: 20px;">
    <h2 class="sd-section-title" style="margin-bottom: 16px;">Surat yang Telah Dihafal ({{ $suratHafalList->count() }} surat)</h2>

    @if($suratHafalList->isEmpty())
        <div style="text-align: center; padding: 32px; color: #888;">
            <p>Belum ada surat yang ditandai hafal oleh guru.</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
            @foreach($suratHafalList as $sh)
            <div class="haf-surat-card">
                <div class="haf-surat-nomor">{{ $sh->surat->urutan ?? '-' }}</div>
                <div class="haf-surat-info">
                    <div class="haf-surat-nama">{{ $sh->surat?->nama_latin ?? '-' }}</div>
                    <div class="haf-surat-meta">
                        Juz {{ $sh->juz }} &middot; Ayat {{ $sh->ayat_mulai }}{{ $sh->ayat_selesai ? '-'.$sh->ayat_selesai : '' }}
                        &middot; {{ $sh->tanggal_hafalan?->format('d/m/Y') }}
                        @if($sh->kualitas)
                            &middot; {{ \App\Models\HafalanTahfidz::labelKualitas($sh->kualitas) }}
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Riwayat Setoran --}}
<div class="sd-section">
    <h2 class="sd-section-title">Riwayat Setoran ({{ $hafalanList->count() }})</h2>

    @if($hafalanList->isEmpty())
        <div style="text-align: center; padding: 40px; color: #888;">
            <div style="font-size: 48px; margin-bottom: 16px;">&#128218;</div>
            <p>Belum ada data setoran hafalan.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="haf-table">
                <thead>
                    <tr>
                        <th>Juz</th>
                        <th>Surat</th>
                        <th>Ayat</th>
                        <th>Status</th>
                        <th>Kualitas</th>
                        <th>Tanggal Setoran</th>
                        <th>Tanggal Konfirmasi Ortu</th>
                        <th>Guru</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hafalanList as $h)
                    <tr>
                        <td><strong style="color: #0c8a5f;">Juz {{ $h->juz }}</strong></td>
                        <td>{{ $h->surat?->nama_latin ?? '-' }}</td>
                        <td>{{ $h->ayat_mulai }}{{ $h->ayat_selesai ? '-' . $h->ayat_selesai : '' }}</td>
                        <td><span class="haf-badge haf-badge-{{ $h->status }}">{{ \App\Models\HafalanTahfidz::labelStatus($h->status) }}</span></td>
                        <td>{{ \App\Models\HafalanTahfidz::labelKualitas($h->kualitas) }}</td>
                        <td style="color: #888; font-size: 12px;">{{ $h->tanggal_hafalan?->format('d/m/Y') }}</td>
                        <td style="font-size: 12px;">
                            @if($h->dikonfirmasi_orang_tua_at)
                                <span style="color: #0c8a5f; font-weight: 600;">{{ $h->dikonfirmasi_orang_tua_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span style="color: #c62828; background: #ffebee; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;">Belum dikonfirmasi</span>
                            @endif
                        </td>
                        <td style="color: #888; font-size: 12px;">{{ $h->guru?->nama ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Form Konfirmasi Orang Tua --}}
        @if($hafalanBelumDikonfirmasi->isNotEmpty())
        <div style="margin-top: 20px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;">
            <form method="POST" action="{{ route('siswa.hafalan.konfirmasi') }}" id="formKonfirmasiHafalan">
                @csrf
                <input type="hidden" name="redirect" value="hafalan">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="font-size: 13px; font-weight: 700; color: #92400e;">
                        &#128100; Konfirmasi Monitoring Orang Tua
                    </div>
                    <label style="font-size: 12px; color: #92400e; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <input type="checkbox" id="pilihSemuaHafalan" style="cursor: pointer;">
                        Pilih semua
                    </label>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                    @foreach($hafalanBelumDikonfirmasi as $h)
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #fff; border: 1px solid #fde68a; border-radius: 8px; cursor: pointer; font-size: 13px; flex-wrap: wrap;">
                        <input type="checkbox" name="hafalan_ids[]" value="{{ $h->id }}" class="checkbox-hafalan">
                        <div style="flex: 1; min-width: 0; word-break: break-word;">
                            <strong>Juz {{ $h->juz }}</strong>
                            @if($h->surat) · {{ $h->surat->nama_latin }}@endif
                            · {{ $h->ayat_mulai }}{{ $h->ayat_selesai ? '-'.$h->ayat_selesai : '' }}
                            <span style="color: #78716c; font-size: 11px;">(setoran {{ $h->tanggal_hafalan?->format('d/m/Y') }})</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="haf-btn-konfirmasi" onclick="return confirm('Konfirmasi setoran yang dipilih?')">
                    Konfirmasi Setoran Terpilih
                </button>
            </form>
        </div>

        <script>
        document.getElementById('pilihSemuaHafalan').addEventListener('change', function() {
            document.querySelectorAll('.checkbox-hafalan').forEach(cb => cb.checked = this.checked);
        });
        </script>
        @endif
    @endif
</div>

@endsection
