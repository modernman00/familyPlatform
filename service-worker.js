/**
 * FamilyPlatform Advanced Service Worker
 * World-Class PWA Engine:
 * - Versioned Cache Management (Static, Dynamic, Images)
 * - Strict Network-Only Gate for Sensitive / FinTech / Auth Endpoints
 * - Stale-While-Revalidate for CSS/JS/Fonts
 * - Cache-First with LRU Expiration for Images
 * - Network-First with Offline Fallback (/offline.html) for HTML Pages
 * - Background Push Notification & Deep Link Handler
 * - Background Sync Integration
 * - Secure Cache Purge on Logout
 */

'use strict';

const SW_VERSION = 'v2.0.0';
const STATIC_CACHE = `fp-static-${SW_VERSION}`;
const DYNAMIC_CACHE = `fp-dynamic-${SW_VERSION}`;
const IMAGE_CACHE = `fp-images-${SW_VERSION}`;
const MAX_IMAGE_CACHE_ENTRIES = 60;

// Core App Shell Files for Immediate Offline Availability
const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/public/css/main.css',
  '/public/js/manifest.js',
  '/public/js/vendor.js',
  '/public/js/index.js',
  '/public/img/favicon/android-chrome-192x192.png',
  '/public/img/favicon/android-chrome-512x512.png',
  '/public/img/favicon/apple-touch-icon.png',
  '/public/img/favicon/favicon-32x32.png',
  '/public/img/favicon/favicon-16x16.png',
  '/public/img/favicon/favicon.ico',
  '/resources/images/avatarM.png',
  '/resources/images/avatarF.png'
];

/**
 * 1. Install Event: Precache App Shell & Skip Waiting
 */
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      console.log('[SW] Precaching Core App Shell');
      return cache.addAll(PRECACHE_ASSETS).catch((err) => {
        console.warn('[SW] Precache partial warning:', err);
      });
    })
  );
});

/**
 * 2. Activate Event: Claim Clients & Prune Outdated Caches
 */
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (![STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE].includes(key)) {
            console.log(`[SW] Pruning Old Cache: ${key}`);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

/**
 * Helper: Limit Cache Size (LRU Eviction)
 */
async function limitCacheSize(cacheName, maxEntries) {
  try {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxEntries) {
      await cache.delete(keys[0]);
      limitCacheSize(cacheName, maxEntries);
    }
  } catch (err) {
    console.warn('[SW] Cache limit error:', err);
  }
}

/**
 * 3. Fetch Event Routing Matrix
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Non-GET requests (POST, PUT, DELETE) are strictly Network-Only
  if (request.method !== 'GET') {
    return;
  }

  // A. Strict Network-Only Gate for FinTech, Auth, Admin & CSRF
  const isSensitiveEndpoint =
    url.pathname.startsWith('/api/wallet') ||
    url.pathname.startsWith('/api/transaction') ||
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/register') ||
    url.pathname.startsWith('/auth') ||
    url.pathname.startsWith('/webauthn') ||
    url.pathname.startsWith('/signout') ||
    url.pathname.includes('/tests/clear-rate-limit');

  if (isSensitiveEndpoint) {
    event.respondWith(fetch(request));
    return;
  }

  // B. Images & Avatars -> Cache-First Strategy
  const isImage =
    request.destination === 'image' ||
    url.pathname.startsWith('/resources/images') ||
    url.pathname.startsWith('/public/img') ||
    /\.(png|jpg|jpeg|svg|webp|gif|ico)$/i.test(url.pathname);

  if (isImage) {
    event.respondWith(cacheFirstStrategy(request, IMAGE_CACHE, MAX_IMAGE_CACHE_ENTRIES));
    return;
  }

  // C. Static CSS, JS & Web Fonts -> Stale-While-Revalidate Strategy
  const isStaticAsset =
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'font' ||
    url.hostname.includes('fonts.googleapis.com') ||
    url.hostname.includes('fonts.gstatic.com') ||
    url.hostname.includes('kit.fontawesome.com') ||
    url.hostname.includes('cdn.jsdelivr.net') ||
    /\.(css|js|woff2?|ttf|eot)$/i.test(url.pathname);

  if (isStaticAsset) {
    event.respondWith(staleWhileRevalidateStrategy(request, STATIC_CACHE));
    return;
  }

  // D. HTML Document Pages & Feeds -> Network-First with Offline Fallback
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(networkFirstWithFallback(request));
    return;
  }

  // Default: Network-First
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});

/**
 * Strategy: Cache-First with Expiration
 */
async function cacheFirstStrategy(request, cacheName, maxEntries = 60) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
      limitCacheSize(cacheName, maxEntries);
    }
    return networkResponse;
  } catch (err) {
    // If image fails and not in cache, fallback to default avatar for avatar requests
    if (request.url.includes('avatar') || request.url.includes('profile')) {
      const fallback = await caches.match('/resources/images/avatarM.png');
      if (fallback) return fallback;
    }
    return new Response('', { status: 404, statusText: 'Not Found' });
  }
}

/**
 * Strategy: Stale-While-Revalidate
 */
async function staleWhileRevalidateStrategy(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);

  const fetchPromise = fetch(request)
    .then((networkResponse) => {
      if (networkResponse && networkResponse.status === 200) {
        cache.put(request, networkResponse.clone());
      }
      return networkResponse;
    })
    .catch(() => cachedResponse);

  return cachedResponse || fetchPromise;
}

/**
 * Strategy: Network-First with /offline.html Fallback
 */
async function networkFirstWithFallback(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (err) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    const offlinePage = await caches.match('/offline.html');
    return offlinePage || new Response('<h1>Offline</h1><p>Please check your connection.</p>', {
      headers: { 'Content-Type': 'text/html' }
    });
  }
}

/**
 * 4. Web Push Notification Listener
 */
self.addEventListener('push', (event) => {
  let data = {
    title: 'FamilyPlatform Update',
    body: 'You have a new family message or update.',
    icon: '/public/img/favicon/android-chrome-192x192.png',
    badge: '/public/img/favicon/favicon-32x32.png',
    url: '/profilePage',
    tag: 'family-notification'
  };

  if (event.data) {
    try {
      const payload = event.data.json();
      data = { ...data, ...payload };
    } catch (e) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon,
    badge: data.badge,
    tag: data.tag,
    data: { url: data.url },
    vibrate: [100, 50, 100],
    actions: data.actions || [
      { action: 'open', title: 'Open' },
      { action: 'close', title: 'Dismiss' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

/**
 * 5. Notification Click & Deep Link Router
 */
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'close') return;

  const targetUrl = event.notification.data?.url || '/profilePage';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});

/**
 * 6. Background Sync API Handler
 */
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-family-posts' || event.tag === 'sync-offline-queue') {
    console.log('[SW] Background sync triggered:', event.tag);
    event.waitUntil(
      self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
          client.postMessage({ type: 'DRAIN_OFFLINE_QUEUE' });
        });
      })
    );
  }
});

/**
 * 7. Message Listener (Skip Waiting & Security Logout Purge)
 */
self.addEventListener('message', (event) => {
  if (event.data?.action === 'skipWaiting') {
    self.skipWaiting();
  }

  // Marcus's SecOps Logout Cache Purge
  if (event.data?.action === 'clearUserCache') {
    console.log('[SW] Purging dynamic user caches upon logout');
    caches.delete(DYNAMIC_CACHE);
  }
});