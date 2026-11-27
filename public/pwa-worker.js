/* No authenticated responses or mutation requests are stored. */
const CACHE = 'gdh-pwa-offline-v5';
const OFFLINE = '/pwa/offline.html';
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.add(new Request(OFFLINE, {cache: 'reload'}))).catch(error => { console.warn('Offline page unavailable', error); }));
});
self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        for (const key of await caches.keys()) {
            if (key.startsWith('gdh-pwa-offline-') && key !== CACHE) await caches.delete(key);
        }
        await self.clients.claim();
    })());
});
self.addEventListener('message', event => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET' || request.mode !== 'navigate') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;
    const legacy = /^\/app\/(portal|trainer|pwa)(\/|$)/.test(url.pathname);
    const branded = /^\/app\/[^/]+\/(member|trainer)(\/login)?\/?$/.test(url.pathname);
    if (!legacy && !branded) return;
    event.respondWith(fetch(request, {cache: 'no-store'}).catch(async () =>
        (await caches.match(OFFLINE)) || new Response('Connection unavailable. Reconnect and try again.', {status: 503, headers: {'Content-Type': 'text/plain'}})
    ));
});

/* Push content is deliberately generic; account information stays behind login. */
self.addEventListener('push', event => {
    event.waitUntil((async () => {
        let payload = {};
        try { payload = event.data ? event.data.json() : {}; } catch (_) {}
        const target = typeof payload.url === 'string' && /^\/app\/[a-zA-Z0-9_-]+\/(member|trainer)$/.test(payload.url) ? payload.url : '/app/portal';
        await self.registration.showNotification(String(payload.title || 'Gym update').slice(0, 100), {
            body: String(payload.body || 'Open your gym app to see the latest update.').slice(0, 240),
            icon: '/pwa/icons/member-192.png',
            data: {url: target}
        });
    })());
});
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/app/portal', self.location.origin);
    if (target.origin !== self.location.origin) return;
    event.waitUntil(self.clients.openWindow(target.href));
});
