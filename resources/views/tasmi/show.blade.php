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
                · 👁 Ditonton {{ $tasmi->jumlah_ditonton }} kali
            </div>
            @if($tasmi->deskripsi)
            <p style="font-size: 13px; color: var(--text-secondary, #555); margin: 8px 0 0; white-space: pre-line;">{{ $tasmi->deskripsi }}</p>
            @endif

            {{-- Aksi: like (AJAX, tanpa refresh agar video tidak terputus) + share WA --}}
            <div style="display: flex; gap: 10px; margin-top: 14px; align-items: center; flex-wrap: wrap;">
                <button type="button" id="btnLike" class="btn-tartil{{ $sudahLike ? '' : '-outline' }}" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span id="btnLikeIkon">{{ $sudahLike ? '❤️' : '🤍' }}</span> <span id="btnLikeCount">{{ $tasmi->likes_count }}</span>
                </button>
                <span id="btnLikeKet" style="font-size: 12px; color: var(--text-muted);">
                    {{ $sudahLike ? 'Anda menyukai video ini — klik lagi untuk batal' : 'Klik tombol ❤️ ini jika Anda menyukai video ini' }}
                </span>
                <a href="{{ $shareWa }}" target="_blank" rel="noopener" class="btn-tartil-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    📤 Share ke WhatsApp
                </a>
            </div>
        </div>
    </div>

    {{-- Komentar (AJAX — tanpa refresh agar video tidak terputus) --}}
    <div class="card-tartil" style="padding: 20px; margin-top: 16px;">
        <h2 style="font-size: 15px; font-weight: 700; margin: 0 0 14px; color: var(--text-primary);">💬 Komentar (<span id="komentarCount">{{ $tasmi->comments->count() }}</span>)</h2>

        <form id="formKomentar" style="display: flex; gap: 8px; margin-bottom: 18px;">
            <input type="text" name="isi" id="inputKomentar" class="form-input" maxlength="500" placeholder="Tulis komentar..." required style="flex: 1;">
            <button type="submit" class="btn-tartil">Kirim</button>
        </form>
        <div id="komentarError" style="color: #c62828; font-size: 12px; margin: -10px 0 12px; display: none;"></div>

        <div id="daftarKomentar">
        @foreach($tasmi->comments as $c)
        <div id="komentar-{{ $c->id }}" style="display: flex; gap: 10px; padding: 10px 0; border-top: 1px solid var(--border, #eee);">
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
            <button type="button" class="btn-hapus-komentar" data-url="{{ route($rp.'.komentar.hapus', $c) }}" title="Hapus komentar" style="background: none; border: none; color: #c62828; cursor: pointer; font-size: 14px; padding: 2px 6px;">✕</button>
            @endif
        </div>
        @endforeach
        </div>
        <div id="komentarKosong" style="color: var(--text-muted); font-size: 13px; padding: 12px 0; {{ $tasmi->comments->isNotEmpty() ? 'display: none;' : '' }}">Belum ada komentar. Jadilah yang pertama!</div>
    </div>
</div>

<script>
// Like via AJAX — halaman tidak di-refresh sehingga video yang sedang
// diputar tidak ikut terulang dari awal.
document.getElementById('btnLike').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    fetch('{{ route($rp.'.like', $tasmi) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
        .then(function (d) {
            document.getElementById('btnLikeIkon').textContent = d.liked ? '❤️' : '🤍';
            document.getElementById('btnLikeCount').textContent = d.count;
            document.getElementById('btnLikeKet').textContent = d.liked
                ? 'Anda menyukai video ini — klik lagi untuk batal'
                : 'Klik tombol ❤️ ini jika Anda menyukai video ini';
            btn.classList.toggle('btn-tartil', d.liked);
            btn.classList.toggle('btn-tartil-outline', !d.liked);
        })
        .catch(function () {})
        .finally(function () { btn.disabled = false; });
});

// ─── Komentar via AJAX (tanpa refresh) ───
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var daftar = document.getElementById('daftarKomentar');
    var kosong = document.getElementById('komentarKosong');
    var counter = document.getElementById('komentarCount');

    function buatItemKomentar(d) {
        var item = document.createElement('div');
        item.id = 'komentar-' + d.id;
        item.style.cssText = 'display: flex; gap: 10px; padding: 10px 0; border-top: 1px solid var(--border, #eee);';

        var avatar = document.createElement('div');
        avatar.style.cssText = 'flex-shrink: 0; width: 34px; height: 34px; border-radius: 50%; background: var(--primary-soft, #e0f2ea); color: var(--primary, #0c8a5f); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;';
        avatar.textContent = d.inisial;

        var body = document.createElement('div');
        body.style.cssText = 'flex: 1; min-width: 0;';
        var meta = document.createElement('div');
        meta.style.fontSize = '13px';
        var nama = document.createElement('strong');
        nama.textContent = d.nama;
        var sub = document.createElement('span');
        sub.style.cssText = 'color: var(--text-muted); font-size: 11px;';
        sub.textContent = ' · ' + d.peran + ' · ' + d.waktu;
        meta.appendChild(nama);
        meta.appendChild(sub);
        var isi = document.createElement('div');
        isi.style.cssText = 'font-size: 13px; color: var(--text-primary); margin-top: 2px; white-space: pre-line;';
        isi.textContent = d.isi;
        body.appendChild(meta);
        body.appendChild(isi);

        var hapus = document.createElement('button');
        hapus.type = 'button';
        hapus.className = 'btn-hapus-komentar';
        hapus.dataset.url = d.hapus_url;
        hapus.title = 'Hapus komentar';
        hapus.style.cssText = 'background: none; border: none; color: #c62828; cursor: pointer; font-size: 14px; padding: 2px 6px;';
        hapus.textContent = '✕';

        item.appendChild(avatar);
        item.appendChild(body);
        item.appendChild(hapus);
        return item;
    }

    // Kirim komentar tanpa refresh
    document.getElementById('formKomentar').addEventListener('submit', function (e) {
        e.preventDefault();
        var input = document.getElementById('inputKomentar');
        var err = document.getElementById('komentarError');
        err.style.display = 'none';
        fetch('{{ route($rp.'.komentar', $tasmi) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ isi: input.value }),
        })
            .then(function (r) {
                if (r.status === 422) return r.json().then(function (d) { throw d; });
                return r.ok ? r.json() : Promise.reject(r);
            })
            .then(function (d) {
                daftar.insertBefore(buatItemKomentar(d), daftar.firstChild); // komentar terbaru di atas
                counter.textContent = d.count;
                kosong.style.display = 'none';
                input.value = '';
            })
            .catch(function (d) {
                err.textContent = (d && d.errors && d.errors.isi) ? d.errors.isi[0] : 'Komentar gagal dikirim.';
                err.style.display = 'block';
            });
    });

    // Hapus komentar tanpa refresh (delegasi — berlaku juga untuk komentar baru)
    daftar.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-hapus-komentar');
        if (!btn) return;
        if (!confirm('Hapus komentar ini?')) return;
        fetch(btn.dataset.url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function () {
                var item = btn.closest('[id^="komentar-"]');
                if (item) item.remove();
                counter.textContent = parseInt(counter.textContent, 10) - 1;
                if (daftar.children.length === 0) kosong.style.display = 'block';
            })
            .catch(function () {});
    });
})();
</script>
@endsection
