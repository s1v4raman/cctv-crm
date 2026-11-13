/**
 * Precision IT Systems Field App - Service Worker
 * Scope: /
 * Strictly does NOT cache authenticated pages
 */

const CACHE_NAME = 'precision-field-v2';
const OFFLINE_URL = '/offline';

const PRECACHE_STATIC_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
    '/favicon.png',
    '/favicon.ico',
    '/logo-mark.png',
    '/icons/icon-192x192.png',
    '/icons/icon-192.png',
    '/icons/icon-512x512.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512x512.png',
    '/icons/icon-maskable-512.png'
];

// Install: Pre-cache static offline shell and assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate: Delete old caches immediately and claim clients
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

    // Skip non-http schemes (chrome-extension, etc.)
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // 1. Navigation / HTML Document Requests:
    // STRICT RULE: Do NOT cache authenticated pages.
    // Try live network first; if offline, fallback to generic /offline page.
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

    // 2. Static Assets (Vite build assets, fonts, icons, manifest, stylesheets):
    // Cache static assets so offline view and PWA styling render instantly.
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
         url.pathname === '/manifest.json' ||
         url.pathname.match(/\.(css|js|woff2?|ttf|eot|png|jpe?g|svg|webp|ico)$/))
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Stale-While-Revalidate: serve cached version and update cache in background
                    fetch(request)
                        .then((networkResponse) => {
                            if (networkResponse && networkResponse.status === 200) {
                                caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                            }
                        })
                        .catch(() => {/* Ignore background fetch failure */});
                    return cachedResponse;
                }

                // Not in cache: fetch from network and store copy in cache
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

    // 3. Fallback for any other requests: Network with cache fallback (no saving)
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
