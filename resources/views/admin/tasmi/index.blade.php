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

    <div class="card-tartil" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: var(--bg-soft, #f5f5f5); text-align: left;">
                        <th style="padding: 12px;">Video</th>
                        <th style="padding: 12px;">Siswa</th>
                        <th style="padding: 12px;">Juz</th>
                        <th style="padding: 12px;">Tanggal Tasmi'</th>
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
                        <td style="padding: 10px 12px;">{{ $v->siswa->nama }}<br><small style="color: var(--text-muted);">{{ $v->siswa->nis }}</small></td>
                        <td style="padding: 10px 12px;">Juz {{ $v->juz }}</td>
                        <td style="padding: 10px 12px;">{{ $v->tanggal_tasmi?->format('d/m/Y') ?? '-' }}</td>
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
                        <td colspan="7" style="padding: 32px; text-align: center; color: var(--text-muted);">Belum ada dokumentasi tasmi'. Klik "Tambah Video" untuk mengunggah.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 16px;">{{ $videos->links() }}</div>
</div>
@endsection
