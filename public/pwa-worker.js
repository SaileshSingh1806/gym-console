/* No authenticated responses or mutation requests are stored. */
const CACHE = 'gdh-pwa-offline-v1';
const OFFLINE = '/pwa/offline.html';
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.add(new Request(OFFLINE, {cache: 'reload'}))));
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
    if (!/^\/app\/(portal|trainer)(\/|$)/.test(url.pathname)) return;
    event.respondWith(fetch(request, {cache: 'no-store'}).catch(async () =>
        (await caches.match(OFFLINE)) || new Response('Connection unavailable. Reconnect and try again.', {status: 503, headers: {'Content-Type': 'text/plain'}})
    ));
});
