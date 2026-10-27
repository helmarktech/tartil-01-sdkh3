@extends(auth('siswa')->check() ? 'layouts.siswa' : 'layouts.admin')
@section('title', 'Dokumentasi Tasmi\'')

@section('content')
<div>
    <div class="page-header" style="margin-bottom: 16px;">
        <h1 class="page-title-display">🎬 Dokumentasi Tasmi'</h1>
        <p class="page-subtitle">Saksikan dokumentasi tasmi' para siswa</p>
    </div>

    {{-- Filter --}}
    <form method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
        <input type="text" name="cari" class="form-input" placeholder="Cari judul / nama siswa..." value="{{ request('cari') }}" style="flex: 1; min-width: 180px;">
        <select name="juz" class="form-input" style="min-width: 110px;">
            <option value="">Semua Juz</option>
            @for($j = 1; $j <= 30; $j++)
            <option value="{{ $j }}" {{ request('juz') == $j ? 'selected' : '' }}>Juz {{ $j }}</option>
            @endfor
        </select>
        <button type="submit" class="btn-tartil">Filter</button>
    </form>

    @if($videos->isEmpty())
    <div class="card-tartil" style="padding: 40px; text-align: center; color: var(--text-muted);">
        Belum ada dokumentasi tasmi'.
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
        @foreach($videos as $v)
        <a href="{{ route($rp.'.show', $v) }}" class="card-tartil" style="padding: 0; overflow: hidden; text-decoration: none; display: block;">
            <div style="position: relative;">
                <img src="{{ $v->thumbnail }}" alt="{{ $v->judul }}" style="width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block;" loading="lazy">
                <span style="position: absolute; top: 8px; left: 8px; background: rgba(12,138,95,0.92); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px;">Juz {{ $v->juz }}</span>
            </div>
            <div style="padding: 12px 14px;">
                <div style="font-weight: 600; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">{{ $v->judul }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">{{ $v->siswa->nama }}{{ $v->tanggal_tasmi ? ' · '.$v->tanggal_tasmi->format('d/m/Y') : '' }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">👁 {{ $v->jumlah_ditonton }} &nbsp;·&nbsp; ♥ {{ $v->likes_count }} &nbsp;·&nbsp; 💬 {{ $v->comments_count }}</div>
            </div>
        </a>
        @endforeach
    </div>

    <div style="margin-top: 20px;">{{ $videos->links() }}</div>
    @endif
</div>
@endsection
