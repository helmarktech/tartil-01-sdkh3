@extends(auth('siswa')->check() ? 'layouts.siswa' : 'layouts.admin')
@section('title', $tasmi->judul)

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <a href="{{ route($rp.'.galeri') }}" style="font-size: 13px; color: var(--text-muted); text-decoration: none;">← Kembali ke Galeri</a>

    {{-- Video in-frame --}}
    <div class="card-tartil" style="padding: 0; overflow: hidden; margin-top: 12px;">
        <div style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000;">
            <iframe src="{{ $tasmi->embed_url }}" title="{{ $tasmi->judul }}"
                    style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe>
        </div>
        <div style="padding: 16px 20px;">
            <h1 style="font-size: 18px; font-weight: 700; margin: 0 0 6px; color: var(--text-primary);">{{ $tasmi->judul }}</h1>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
                <strong>{{ $tasmi->siswa->nama }}</strong> ({{ $tasmi->siswa->nis }}) · Juz {{ $tasmi->juz }}
                {{ $tasmi->tanggal_tasmi ? ' · Tasmi\' '.$tasmi->tanggal_tasmi->format('d F Y') : '' }}
            </div>
            @if($tasmi->deskripsi)
            <p style="font-size: 13px; color: var(--text-secondary, #555); margin: 8px 0 0; white-space: pre-line;">{{ $tasmi->deskripsi }}</p>
            @endif

            {{-- Aksi: like + share WA --}}
            <div style="display: flex; gap: 10px; margin-top: 14px; align-items: center; flex-wrap: wrap;">
                <form method="POST" action="{{ route($rp.'.like', $tasmi) }}">
                    @csrf
                    <button type="submit" class="btn-tartil{{ $sudahLike ? '' : '-outline' }}" style="display: inline-flex; align-items: center; gap: 6px;">
                        {{ $sudahLike ? '❤️' : '🤍' }} <span>{{ $tasmi->likes_count }}</span>
                    </button>
                </form>
                <a href="{{ $shareWa }}" target="_blank" rel="noopener" class="btn-tartil-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    📤 Share ke WhatsApp
                </a>
            </div>
        </div>
    </div>

    {{-- Komentar --}}
    <div class="card-tartil" style="padding: 20px; margin-top: 16px;">
        <h2 style="font-size: 15px; font-weight: 700; margin: 0 0 14px; color: var(--text-primary);">💬 Komentar ({{ $tasmi->comments->count() }})</h2>

        <form method="POST" action="{{ route($rp.'.komentar', $tasmi) }}" style="display: flex; gap: 8px; margin-bottom: 18px;">
            @csrf
            <input type="text" name="isi" class="form-input" maxlength="500" placeholder="Tulis komentar..." required style="flex: 1;">
            <button type="submit" class="btn-tartil">Kirim</button>
        </form>
        @error('isi')<div style="color: #c62828; font-size: 12px; margin: -10px 0 12px;">{{ $message }}</div>@enderror

        @forelse($tasmi->comments as $c)
        <div style="display: flex; gap: 10px; padding: 10px 0; border-top: 1px solid var(--border, #eee);">
            <div style="flex-shrink: 0; width: 34px; height: 34px; border-radius: 50%; background: var(--primary-soft, #e0f2ea); color: var(--primary, #0c8a5f); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                {{ strtoupper(mb_substr($c->nama_pengomentar, 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px;">
                    <strong>{{ $c->nama_pengomentar }}</strong>
                    <span style="color: var(--text-muted); font-size: 11px;"> · {{ $c->peran_pengomentar }} · {{ $c->created_at->diffForHumans() }}</span>
                </div>
                <div style="font-size: 13px; color: var(--text-primary); margin-top: 2px; white-space: pre-line;">{{ $c->isi }}</div>
            </div>
            @if($bisaModerasi || $c->milikAktor($aktor))
            <form method="POST" action="{{ route($rp.'.komentar.hapus', $c) }}" onsubmit="return confirm('Hapus komentar ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" title="Hapus komentar" style="background: none; border: none; color: #c62828; cursor: pointer; font-size: 14px; padding: 2px 6px;">✕</button>
            </form>
            @endif
        </div>
        @empty
        <div style="color: var(--text-muted); font-size: 13px; padding: 12px 0;">Belum ada komentar. Jadilah yang pertama!</div>
        @endforelse
    </div>
</div>
@endsection
