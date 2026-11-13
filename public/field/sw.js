/**
 * Precision IT Systems Field App - Service Worker
 * Location: /field/sw.js
 * Scope: /field/
 * Strictly does NOT cache authenticated pages
 */

const CACHE_NAME = 'precision-field-webapk-v1';
const OFFLINE_URL = '/offline';

const PRECACHE_STATIC_ASSETS = [
    OFFLINE_URL,
    '/field/manifest.webmanifest',
    '/favicon.png',
    '/favicon.ico',
    '/icons/field-192.png',
    '/icons/field-512.png'
];

// Install: Pre-cache static offline shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate: Delete old caches and claim clients under scope /field/
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Handler
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only process GET requests
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Skip non-http schemes
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // 1. Navigation / HTML Document Requests:
    // STRICT: Do NOT cache authenticated pages.
    // Try live network first; if offline, fallback to pre-cached /offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                const offlinePage = await cache.match(OFFLINE_URL);
                return offlinePage || new Response('Offline', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: { 'Content-Type': 'text/plain' }
                });
            })
        );
        return;
    }

    // 2. Static Assets (Vite build assets, fonts, icons, manifest, stylesheets)
    // Strictly exclude private user media and uploaded photos from persistent browser cache.
    const isPrivateMedia = url.pathname.includes('/attendance_photos/') ||
                           url.pathname.includes('/worker_photos/') ||
                           url.pathname.includes('/attendance-photos/') ||
                           url.pathname.includes('/worker-photos/') ||
                           url.pathname.includes('/site-surveys/') ||
                           url.pathname.includes('/site_surveys/') ||
                           url.pathname.includes('/leave_attachments/') ||
                           url.pathname.includes('/expense_receipts/') ||
                           url.pathname.includes('/jcr-photos/');

    if (
        !isPrivateMedia &&
        (url.pathname.startsWith('/build/') ||
         url.pathname.startsWith('/icons/') ||
         url.pathname === '/field/manifest.webmanifest' ||
         url.pathname.match(/\.(css|js|woff2?|ttf|eot|png|jpe?g|svg|webp|ico)$/))
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Stale-While-Revalidate: return cache and update in background
                    fetch(request)
                        .then((networkResponse) => {
                            if (networkResponse && networkResponse.status === 200) {
                                caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                            }
                        })
                        .catch(() => {});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // 3. Fallback for any other requests
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

