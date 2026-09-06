/* ══════════════════════════════════════════════════════
   Service Worker TARTIL — cache aset statis & push notifikasi
   ══════════════════════════════════════════════════════ */

const VERSION = 'v1';
const CACHE_ASSETS = `tartil-assets-${VERSION}`;
const CACHE_CORE = `tartil-core-${VERSION}`;

// Aset inti yang diprecache saat install
const PRECACHE = [
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/images/logo-sd-khadijah-3.jpg',
];

// Prefix URL statis same-origin yang di-cache (cache-first)
const ASSET_PREFIX = ['/build/', '/icons/', '/images/', '/css/', '/favicon'];

// Install: precache aset inti
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_CORE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Activate: hapus cache versi lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key.startsWith('tartil-') && key !== CACHE_ASSETS && key !== CACHE_CORE)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

// Fetch: hanya GET same-origin ke aset statis yang di-cache; sisanya network-only
self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    const isAsetStatis = ASSET_PREFIX.some((prefix) => url.pathname.startsWith(prefix));
    if (!isAsetStatis) return; // halaman HTML & API: network-only (default browser)

    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;
            return fetch(request).then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_ASSETS).then((cache) => cache.put(request, clone));
                }
                return response;
            });
        })
    );
});

// Push: tampilkan notifikasi dari payload JSON
self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = {};
    }

    const title = payload.title || 'TARTIL';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: { url: (payload.data && payload.data.url) || '/siswa/dashboard' },
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

// Klik notifikasi: buka/fokus ke URL tujuan
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/siswa/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
            return null;
        })
    );
});
