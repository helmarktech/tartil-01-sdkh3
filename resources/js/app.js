// Entry JS TARTIL — service worker + lonceng notifikasi siswa + TSelect.

import '../css/app.css';

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
        if (!pollingAktif) return Promise.resolve();
        return fetchJson('/siswa/notifikasi/unread-count')
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

    // Buka/tutup dropdown saat lonceng diklik.
    // Saat dibuka: tampilkan notifikasi terbaru, lalu otomatis tandai semua
    // sudah dibaca (badge langsung hilang tanpa harus membuka satu per satu).
    const tombolBell = document.getElementById('notifikasi-tombol');
    if (tombolBell && panel) {
        tombolBell.addEventListener('click', (ev) => {
            ev.stopPropagation();
            panel.hidden = !panel.hidden;
            if (!panel.hidden) {
                muatNotifikasi().finally(() => {
                    fetchJson('/siswa/notifikasi/read-all', { method: 'POST' })
                        .catch(() => {})
                        .finally(() => { if (badge) badge.hidden = true; });
                });
            }
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

    // Kirim subscription push ke server; buat subscription baru bila belum ada
    const sinkronPush = async () => {
        const reg = await navigator.serviceWorker.ready;
        let subscription = await reg.pushManager.getSubscription();
        if (!subscription) {
            subscription = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(window.tartilVapidKey),
            });
        }
        const json = subscription.toJSON();
        const res = await fetchJson('/siswa/notifikasi/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                endpoint: json.endpoint,
                keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
            }),
        });
        if (!res.ok) throw new Error('Gagal menyimpan subscription push (HTTP ' + res.status + ')');
    };

    // Setup push: sinkron otomatis bila izin sudah granted; tombol bila izin belum diminta
    const setupPush = () => {
        if (!window.tartilVapidKey) return;
        if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) return;

        // Izin sudah diberikan (misal saat install PWA): pastikan subscription tersimpan di server
        if (Notification.permission === 'granted') {
            sinkronPush().catch((e) => console.warn('Sinkronisasi push gagal:', e));
            return;
        }
        if (Notification.permission !== 'default') return;

        const tombolAktif = document.getElementById('notifikasi-aktifkan');
        if (!tombolAktif) return;
        tombolAktif.hidden = false;

        tombolAktif.addEventListener('click', async () => {
            try {
                const izin = await Notification.requestPermission();
                if (izin !== 'granted') return;
                tombolAktif.hidden = true;
                await sinkronPush();
            } catch (e) {
                console.warn('Aktivasi push gagal:', e);
                tombolAktif.hidden = false;
            }
        });
    };

    muatNotifikasi();
    setInterval(muatNotifikasi, 60000);
    setupPush();
})();

// ─── TSelect: dropdown kustom seragam untuk <select class="form-input"> ───
// Daftar opsi native browser diganti panel bergaya Tartil. Select asli tetap
// di DOM (tersembunyi) sehingga submit form, validasi required, dan handler
// onchange="this.form.submit()" tetap berfungsi.
(function () {
    const CARET = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
    const CHECK = '<svg class="tselect-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

    function tutup(wrapper) {
        wrapper.classList.remove('open', 'up');
        wrapper.querySelector('.tselect-btn')?.setAttribute('aria-expanded', 'false');
    }

    function tutupSemua(kecuali) {
        document.querySelectorAll('.tselect.open').forEach((w) => {
            if (w !== kecuali) tutup(w);
        });
    }

    function setAktif(wrapper, item) {
        wrapper.querySelectorAll('.tselect-item.is-active').forEach((i) => i.classList.remove('is-active'));
        if (item) {
            item.classList.add('is-active');
            item.scrollIntoView({ block: 'nearest' });
        }
    }

    function buka(wrapper) {
        tutupSemua(wrapper);
        wrapper.classList.add('open');
        wrapper.querySelector('.tselect-btn')?.setAttribute('aria-expanded', 'true');

        // Buka ke atas kalau ruang di bawah tidak cukup
        const list = wrapper.querySelector('.tselect-list');
        const tinggiList = Math.min(280, list.scrollHeight || 280);
        const ruangBawah = window.innerHeight - wrapper.getBoundingClientRect().bottom;
        if (tinggiList > ruangBawah && ruangBawah < wrapper.getBoundingClientRect().top) {
            wrapper.classList.add('up');
        }

        const terpilih = list.querySelector('.tselect-item.is-selected')
            || list.querySelector('.tselect-item:not(.is-disabled)');
        setAktif(wrapper, terpilih);
    }

    function sinkronLabel(wrapper, select) {
        const label = wrapper.querySelector('.tselect-label');
        const opt = select.selectedOptions[0];
        label.textContent = opt ? opt.textContent : '';
        label.classList.toggle('is-placeholder', !!opt && opt.value === '');

        wrapper.querySelectorAll('.tselect-item').forEach((item) => {
            item.classList.toggle('is-selected', item.dataset.value === select.value);
        });
    }

    function pilih(wrapper, select, item) {
        if (!item || item.classList.contains('is-disabled')) return;
        select.value = item.dataset.value;
        sinkronLabel(wrapper, select);
        tutup(wrapper);
        wrapper.querySelector('.tselect-btn')?.focus();
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function buatItem(wrapper, select, opt) {
        const item = document.createElement('div');
        item.className = 'tselect-item';
        item.dataset.value = opt.value;
        item.setAttribute('role', 'option');
        if (opt.disabled) item.classList.add('is-disabled');
        if (opt.value === select.value) item.classList.add('is-selected');

        const teks = document.createElement('span');
        teks.textContent = opt.textContent;
        item.appendChild(teks);
        item.insertAdjacentHTML('beforeend', CHECK);

        item.addEventListener('click', () => pilih(wrapper, select, item));
        return item;
    }

    function enhance(select) {
        if (select.dataset.tselectInit) return;
        if (select.multiple || select.size > 1) return;
        select.dataset.tselectInit = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'tselect';
        if (select.style.width) wrapper.style.width = select.style.width;

        // Tombol bergaya .form-input agar identik dengan select lama
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'form-input tselect-btn';
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        if (select.disabled) btn.classList.add('is-disabled');
        const inlineStyle = select.getAttribute('style');
        if (inlineStyle) btn.setAttribute('style', inlineStyle);

        const label = document.createElement('span');
        label.className = 'tselect-label';
        btn.appendChild(label);
        btn.insertAdjacentHTML('beforeend', '<span class="tselect-caret">' + CARET + '</span>');

        const list = document.createElement('div');
        list.className = 'tselect-list';
        list.setAttribute('role', 'listbox');

        Array.from(select.children).forEach((child) => {
            if (child.tagName === 'OPTGROUP') {
                const grup = document.createElement('div');
                grup.className = 'tselect-group';
                grup.textContent = child.label;
                list.appendChild(grup);
                Array.from(child.children).forEach((opt) => list.appendChild(buatItem(wrapper, select, opt)));
            } else if (child.tagName === 'OPTION') {
                list.appendChild(buatItem(wrapper, select, child));
            }
        });

        // Rakit: select asli ikut di dalam wrapper (tersembunyi tapi ikut submit)
        select.parentNode.insertBefore(wrapper, select);
        select.classList.add('tselect-native');
        select.tabIndex = -1;
        wrapper.appendChild(select);
        wrapper.appendChild(btn);
        wrapper.appendChild(list);

        sinkronLabel(wrapper, select);

        // Interaksi
        btn.addEventListener('click', () => {
            if (select.disabled) return;
            wrapper.classList.contains('open') ? tutup(wrapper) : buka(wrapper);
        });

        btn.addEventListener('keydown', (e) => {
            if (select.disabled) return;
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
                e.preventDefault();
                buka(wrapper);
            }
        });

        wrapper.addEventListener('keydown', (e) => {
            if (!wrapper.classList.contains('open')) return;
            const items = Array.from(wrapper.querySelectorAll('.tselect-item:not(.is-disabled)'));
            const aktif = wrapper.querySelector('.tselect-item.is-active');
            const idx = items.indexOf(aktif);

            if (e.key === 'Escape') {
                tutup(wrapper);
                wrapper.querySelector('.tselect-btn')?.focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                setAktif(wrapper, items[Math.min(idx + 1, items.length - 1)]);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setAktif(wrapper, items[Math.max(idx - 1, 0)]);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                pilih(wrapper, select, aktif);
            } else if (e.key === 'Tab') {
                tutup(wrapper);
            }
        });

        // Sinkron bila value select diubah via script lain
        select.addEventListener('change', () => sinkronLabel(wrapper, select));
    }

    function enhanceSemua(root) {
        root.querySelectorAll('select.form-input').forEach(enhance);
    }

    document.addEventListener('DOMContentLoaded', () => {
        enhanceSemua(document);

        // Select yang ditambahkan dinamis (via JS) ikut di-enhance
        new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                m.addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;
                    if (node.matches?.('select.form-input')) enhance(node);
                    if (node.querySelectorAll) enhanceSemua(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.tselect')) tutupSemua();
    });
})();
