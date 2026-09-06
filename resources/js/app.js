// Entry JS TARTIL — service worker + lonceng notifikasi siswa.

// ─── Daftarkan service worker untuk PWA ───
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Gagal daftar SW tidak boleh mengganggu halaman
        });
    });
}

// ─── Lonceng notifikasi siswa (hanya di layout siswa) ───
(function () {
    const bell = document.getElementById('notifikasi-bell');
    if (!bell) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const fetchJson = (url, options = {}) => {
        const { headers = {}, ...rest } = options;
        const method = rest.method || 'GET';
        return fetch(url, {
            ...rest,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...(method !== 'GET' ? { 'X-CSRF-TOKEN': csrfToken || '' } : {}),
                ...headers,
            },
        });
    };

    const badge = document.getElementById('notifikasi-badge');
    const panel = document.getElementById('notifikasi-panel');
    const list = document.getElementById('notifikasi-list');
    let pollingAktif = true;

    // Tandai sesi tidak login: hentikan polling tanpa error
    const hentikanPolling = () => {
        pollingAktif = false;
        if (badge) badge.hidden = true;
        if (panel) panel.hidden = true;
    };

    // Render daftar notifikasi terbaru ke dropdown
    const renderLatest = (latest) => {
        if (!list) return;
        list.innerHTML = '';
        if (!Array.isArray(latest) || latest.length === 0) {
            const kosong = document.createElement('div');
            kosong.className = 'notifikasi-kosong';
            kosong.textContent = 'Tidak ada notifikasi baru.';
            list.appendChild(kosong);
            return;
        }
        latest.forEach((item) => {
            const el = document.createElement('a');
            el.className = 'notifikasi-item';
            el.href = item.url || '#';
            el.innerHTML = `<span class="notifikasi-judul"></span><span class="notifikasi-waktu"></span>`;
            el.querySelector('.notifikasi-judul').textContent = item.judul || 'Notifikasi';
            el.querySelector('.notifikasi-waktu').textContent = item.waktu || '';
            el.addEventListener('click', (ev) => {
                ev.preventDefault();
                fetchJson(`/siswa/notifikasi/${item.id}/read`, { method: 'POST' })
                    .catch(() => {})
                    .finally(() => { window.location.href = item.url || '/siswa/dashboard'; });
            });
            list.appendChild(el);
        });
    };

    // Ambil jumlah belum dibaca + notifikasi terbaru
    const muatNotifikasi = () => {
        if (!pollingAktif) return;
        fetchJson('/siswa/notifikasi/unread-count')
            .then((res) => {
                if (res.status === 401 || res.redirected) {
                    hentikanPolling();
                    return null;
                }
                return res.ok ? res.json() : null;
            })
            .then((data) => {
                if (!data) return;
                const count = Number(data.count) || 0;
                if (badge) {
                    badge.textContent = count > 99 ? '99+' : String(count);
                    badge.hidden = count === 0;
                }
                renderLatest(data.latest);
            })
            .catch(() => {});
    };

    // Buka/tutup dropdown saat lonceng diklik
    const tombolBell = document.getElementById('notifikasi-tombol');
    if (tombolBell && panel) {
        tombolBell.addEventListener('click', (ev) => {
            ev.stopPropagation();
            panel.hidden = !panel.hidden;
            if (!panel.hidden) muatNotifikasi();
        });
        document.addEventListener('click', (ev) => {
            if (!panel.hidden && !panel.contains(ev.target)) panel.hidden = true;
        });
    }

    // Tombol tandai semua dibaca
    const tombolReadAll = document.getElementById('notifikasi-read-all');
    if (tombolReadAll) {
        tombolReadAll.addEventListener('click', () => {
            fetchJson('/siswa/notifikasi/read-all', { method: 'POST' })
                .catch(() => {})
                .finally(muatNotifikasi);
        });
    }

    // Konversi kunci VAPID base64 URL-safe ke Uint8Array
    const urlBase64ToUint8Array = (base64String) => {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
    };

    // Setup push: tombol "Aktifkan Notifikasi" muncul bila izin belum diminta
    const setupPush = async () => {
        if (!window.tartilVapidKey) return;
        if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
        if (Notification.permission !== 'default') return;

        const tombolAktif = document.getElementById('notifikasi-aktifkan');
        if (!tombolAktif) return;
        tombolAktif.hidden = false;

        tombolAktif.addEventListener('click', async () => {
            try {
                const izin = await Notification.requestPermission();
                if (izin !== 'granted') return;
                tombolAktif.hidden = true;

                const reg = await navigator.serviceWorker.ready;
                const subscription = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(window.tartilVapidKey),
                });
                const json = subscription.toJSON();
                await fetchJson('/siswa/notifikasi/push/subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        endpoint: json.endpoint,
                        keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
                        contentEncoding: 'aesgcm',
                    }),
                });
            } catch (e) {
                // Push tidak didukung/ditolak: polling lonceng tetap berjalan
            }
        });
    };

    muatNotifikasi();
    setInterval(muatNotifikasi, 60000);
    setupPush();
})();
