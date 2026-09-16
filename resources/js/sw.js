/* Simbiosis News - Service Worker
   - cache-first for static assets
   - network-first for public pages
   - never cache auth/admin routes
*/
const VERSION = 'simbiosis-v7';
const STATIC_CACHE = `static-${VERSION}`;
const PAGES_CACHE = `pages-${VERSION}`;

const OFFLINE_URL = '/offline.html';
const DENY_PREFIXES = ['/harmony-access', '/register', '/password'];

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(STATIC_CACHE);
    await cache.addAll([OFFLINE_URL, '/manifest.json']);
    self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.map((k) => {
      if (![STATIC_CACHE, PAGES_CACHE].includes(k)) return caches.delete(k);
    }));
    self.clients.claim();
  })());
});

function isDenied(url) {
  try {
    const u = new URL(url);
    return DENY_PREFIXES.some(p => u.pathname.startsWith(p));
  } catch { return false; }
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  if (isDenied(req.url)) return;

  const dest = req.destination;
  const isAsset = ['style','script','image','font'].includes(dest) || req.url.includes('/build/');

  if (isAsset) {
    // cache-first
    event.respondWith((async () => {
      const cache = await caches.open(STATIC_CACHE);
      const hit = await cache.match(req);
      if (hit) return hit;
      const res = await fetch(req);
      if (res.ok) cache.put(req, res.clone());
      return res;
    })());
    return;
  }

  // network-first for pages
  event.respondWith((async () => {
    const cache = await caches.open(PAGES_CACHE);
    try {
      const res = await fetch(req);
      if (res.ok && res.headers.get('content-type')?.includes('text/html')) {
        cache.put(req, res.clone());
      }
      return res;
    } catch {
      const cached = await cache.match(req);
      return cached || caches.match(OFFLINE_URL);
    }
  })());
});
