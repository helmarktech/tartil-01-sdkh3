@extends('layouts.admin')
@section('title', 'Dokumentasi Tasmi\'')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Dokumentasi Tasmi'</h1>
            <p class="page-subtitle">Kelola video tasmi' siswa (link YouTube)</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.tasmi.galeri') }}" class="btn-tartil-outline" style="text-decoration: none;">🎬 Lihat Galeri</a>
            <a href="{{ route('admin.tasmi.create') }}" class="btn-tartil" style="text-decoration: none;">+ Tambah Video</a>
        </div>
    </div>

    {{-- Statistik ringkas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 26px; font-weight: 700; color: var(--text-primary);">{{ $statistik['video'] }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total Video</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 26px; font-weight: 700; color: var(--text-primary);">{{ $statistik['siswa'] }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Siswa Terdokumentasi</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 26px; font-weight: 700; color: var(--text-primary);">{{ $statistik['juz'] }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Juz Terdokumentasi</div>
        </div>
        <div class="card-tartil" style="padding: 16px; text-align: center;">
            <div style="font-size: 26px; font-weight: 700; color: var(--text-primary);">{{ $statistik['ditonton'] }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total Ditonton</div>
        </div>
    </div>

    {{-- Rekap detail per juz --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden; margin-bottom: 20px;">
        <div style="padding: 14px 16px; border-bottom: 1px solid var(--border, #e5e5e5); font-weight: 700; font-size: 14px;">Rekap Tasmi' per Juz</div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: var(--bg-soft, #f5f5f5); text-align: left;">
                        <th style="padding: 10px 12px;">Juz</th>
                        <th style="padding: 10px 12px; text-align: center;">Jumlah Video</th>
                        <th style="padding: 10px 12px; text-align: center;">Jumlah Siswa</th>
                        <th style="padding: 10px 12px;">Nama Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapJuz as $r)
                    <tr style="border-top: 1px solid var(--border, #e5e5e5);">
                        <td style="padding: 10px 12px; white-space: nowrap;"><strong>Juz {{ $r['juz'] }}</strong></td>
                        <td style="padding: 10px 12px; text-align: center;">{{ $r['video'] }}</td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 999px; background: #e7f6ef; color: #0c8a5f; font-size: 12px; font-weight: 700;">{{ $r['siswaUnik']->count() }} siswa</span>
                        </td>
                        <td style="padding: 10px 12px; font-size: 12px; color: var(--text-muted);">
                            {{ $r['siswaUnik']->pluck('nama')->implode(', ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 24px; text-align: center; color: var(--text-muted);">Belum ada data tasmi'.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: var(--bg-soft, #f5f5f5); text-align: left;">
                        <th style="padding: 12px;">Video</th>
                        <th style="padding: 12px;">Siswa</th>
                        <th style="padding: 12px;">Juz</th>
                        <th style="padding: 12px;">Tanggal Tasmi'</th>
                        <th style="padding: 12px; text-align: center;">👁</th>
                        <th style="padding: 12px; text-align: center;">♥</th>
                        <th style="padding: 12px; text-align: center;">💬</th>
                        <th style="padding: 12px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videos as $v)
                    <tr style="border-top: 1px solid var(--border, #e5e5e5);">
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <img src="{{ $v->thumbnail }}" alt="" style="width: 72px; height: 42px; object-fit: cover; border-radius: 6px;">
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $v->judul }}</div>
                                    <a href="{{ $v->youtube_url }}" target="_blank" rel="noopener" style="font-size: 11px; color: var(--text-muted);">YouTube ↗</a>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;">
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 999px; background: #e7f6ef; color: #0c8a5f; font-size: 11px; font-weight: 700;">{{ $v->siswas_count }} siswa</span><br>
                            <small style="color: var(--text-muted);">{{ $v->nama_siswa_list }}</small>
                        </td>
                        <td style="padding: 10px 12px;">Juz {{ $v->juz }}</td>
                        <td style="padding: 10px 12px;">{{ $v->tanggal_tasmi?->format('d/m/Y') ?? '-' }}</td>
                        <td style="padding: 10px 12px; text-align: center;">{{ $v->jumlah_ditonton }}</td>
                        <td style="padding: 10px 12px; text-align: center;">{{ $v->likes_count }}</td>
                        <td style="padding: 10px 12px; text-align: center;">{{ $v->comments_count }}</td>
                        <td style="padding: 10px 12px; white-space: nowrap;">
                            <a href="{{ route('admin.tasmi.show', $v) }}" class="btn-tartil-outline" style="padding: 4px 10px; font-size: 12px; text-decoration: none;">Lihat</a>
                            <a href="{{ route('admin.tasmi.edit', $v) }}" class="btn-tartil-outline" style="padding: 4px 10px; font-size: 12px; text-decoration: none;">Edit</a>
                            <form method="POST" action="{{ route('admin.tasmi.destroy', $v) }}" style="display: inline;" onsubmit="return confirm('Hapus video tasmi\' ini beserta like & komentarnya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-tartil-outline" style="padding: 4px 10px; font-size: 12px; color: #c62828;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 32px; text-align: center; color: var(--text-muted);">Belum ada dokumentasi tasmi'. Klik "Tambah Video" untuk mengunggah.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 16px;">{{ $videos->links() }}</div>
</div>
@endsection
