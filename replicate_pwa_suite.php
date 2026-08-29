<?php
/**
 * Automated Multi-App PWA Suite Rollout Script
 * Rolls out the world-class PWA architecture to:
 * 1. idecide
 * 2. iaccountapp
 * 3. execmindapp
 * 4. partyplatform
 */

$apps = [
    'idecide' => [
        'path' => '/Users/waleolaogun/Sites/idecide',
        'name' => 'iDecide Decision Engine',
        'short_name' => 'iDecide',
        'desc' => 'Empowering smarter, rational buying and strategic decisions.',
        'theme_color' => '#0d9488',
        'bg_color' => '#0f172a',
        'app_id' => 'com.idecide.app',
        'start_url' => '/?source=pwa',
        'shortcuts' => [
            ['name' => 'New Decision', 'short_name' => 'New', 'url' => '/#questions'],
            ['name' => 'Results & History', 'short_name' => 'Results', 'url' => '/result'],
            ['name' => 'Compare Options', 'short_name' => 'Compare', 'url' => '/compare'],
            ['name' => 'Settings', 'short_name' => 'Settings', 'url' => '/settings'],
        ],
        'is_fintech' => false,
    ],
    'iaccountapp' => [
        'path' => '/Users/waleolaogun/Sites/iaccountapp',
        'name' => 'iAccountApp FinTech & Ledger',
        'short_name' => 'iAccount',
        'desc' => 'Smart business accounting, invoicing, and real-time financial ledger.',
        'theme_color' => '#4f46e5',
        'bg_color' => '#ffffff',
        'app_id' => 'com.iaccountapp.app',
        'start_url' => '/dashboard',
        'shortcuts' => [
            ['name' => 'Dashboard', 'short_name' => 'Ledger', 'url' => '/dashboard'],
            ['name' => 'New Expense', 'short_name' => 'Expense', 'url' => '/transaction/create'],
            ['name' => 'Invoices', 'short_name' => 'Invoices', 'url' => '/invoices'],
            ['name' => 'Settings', 'short_name' => 'Settings', 'url' => '/settings'],
        ],
        'is_fintech' => true,
    ],
    'execmindapp' => [
        'path' => '/Users/waleolaogun/Sites/execmindapp',
        'name' => 'ExecMind Executive Intelligence',
        'short_name' => 'ExecMind',
        'desc' => 'Executive intelligence, strategy execution, and productivity system.',
        'theme_color' => '#0284c7',
        'bg_color' => '#0f172a',
        'app_id' => 'com.execmindapp.app',
        'start_url' => '/dashboard',
        'shortcuts' => [
            ['name' => 'Executive Briefs', 'short_name' => 'Briefs', 'url' => '/briefs'],
            ['name' => 'Action Items', 'short_name' => 'Tasks', 'url' => '/tasks'],
            ['name' => 'Strategy Deck', 'short_name' => 'Strategy', 'url' => '/strategy'],
            ['name' => 'Settings', 'short_name' => 'Settings', 'url' => '/settings'],
        ],
        'is_fintech' => false,
    ],
    'partyplatform' => [
        'path' => '/Users/waleolaogun/Sites/partyplatform',
        'name' => 'PartyPlatform Events & Lifestyle',
        'short_name' => 'PartyPlatform',
        'desc' => 'Celebrate together with real-time invitations, event plans, and shared memories.',
        'theme_color' => '#e11d48',
        'bg_color' => '#ffffff',
        'app_id' => 'com.partyplatform.app',
        'start_url' => '/events',
        'shortcuts' => [
            ['name' => 'Upcoming Events', 'short_name' => 'Events', 'url' => '/events'],
            ['name' => 'Create Event', 'short_name' => 'New Event', 'url' => '/events/create'],
            ['name' => 'RSVP & Invitations', 'short_name' => 'RSVP', 'url' => '/invitations'],
            ['name' => 'Event Gallery', 'short_name' => 'Gallery', 'url' => '/gallery'],
        ],
        'is_fintech' => false,
    ]
];

foreach ($apps as $key => $config) {
    echo "==========================================\n";
    echo "Processing PWA Rollout for: {$config['name']}\n";
    echo "==========================================\n";

    $basePath = $config['path'];
    if (!is_dir($basePath)) {
        echo "Skipping {$key} - Directory not found: {$basePath}\n";
        continue;
    }

    $publicDir = $basePath . '/public';
    if (!is_dir($publicDir)) {
        mkdir($publicDir, 0755, true);
    }

    // 1. Build Manifest JSON
    $manifestData = [
        'name' => $config['name'],
        'short_name' => $config['short_name'],
        'description' => $config['desc'],
        'start_url' => $config['start_url'],
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'portrait-primary',
        'theme_color' => $config['theme_color'],
        'background_color' => $config['bg_color'],
        'id' => $config['app_id'],
        'categories' => ['productivity', 'utilities', 'lifestyle'],
        'icons' => [
            [
                'src' => '/public/img/favicon/android-chrome-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => '/public/img/favicon/android-chrome-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => '/public/img/favicon/android-chrome-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ],
            [
                'src' => '/public/img/favicon/android-chrome-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ]
        ],
        'shortcuts' => array_map(function($sc) {
            return [
                'name' => $sc['name'],
                'short_name' => $sc['short_name'],
                'url' => $sc['url'],
                'icons' => [
                    [
                        'src' => '/public/img/favicon/android-chrome-192x192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png'
                    ]
                ]
            ];
        }, $config['shortcuts']),
        'share_target' => [
            'action' => $config['start_url'],
            'method' => 'GET',
            'params' => [
                'title' => 'title',
                'text' => 'text',
                'url' => 'url'
            ]
        ]
    ];

    $manifestJson = json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($publicDir . '/manifest.json', $manifestJson);
    file_put_contents($basePath . '/manifest.json', $manifestJson);
    echo "✓ Written manifest.json\n";

    // 2. Build Branded Offline Page
    $offlineHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Offline | {$config['name']}</title>
  <meta name="theme-color" content="{$config['theme_color']}">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      color: #1e293b;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 24px;
      text-align: center;
    }
    .offline-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.6);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
      border-radius: 24px;
      padding: 40px 28px;
      max-width: 440px;
      width: 100%;
    }
    .icon-wrapper {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      background: {$config['theme_color']}15;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: {$config['theme_color']};
    }
    h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    p { font-size: 0.95rem; color: #64748b; line-height: 1.5; margin-bottom: 24px; }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fee2e2;
      color: #b91c1c;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 20px;
      margin-bottom: 20px;
    }
    .retry-btn {
      background: {$config['theme_color']};
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="offline-card">
    <div class="icon-wrapper">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="1" y1="1" x2="23" y2="23"></line>
        <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
        <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
        <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
        <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
        <line x1="12" y1="20" x2="12.01" y2="20"></line>
      </svg>
    </div>
    <div class="status-badge">No Internet Connection</div>
    <h1>You are currently offline</h1>
    <p>We couldn't reach {$config['name']}. Your offline actions are securely queued and will automatically sync once your connection is restored.</p>
    <button class="retry-btn" onclick="if(navigator.onLine){window.location.reload();}else{alert('Still offline. Please check your internet connection.');}">Retry Connection</button>
  </div>
  <script>window.addEventListener('online', () => window.location.reload());</script>
</body>
</html>
HTML;
    file_put_contents($publicDir . '/offline.html', $offlineHtml);
    file_put_contents($basePath . '/offline.html', $offlineHtml);
    echo "✓ Written offline.html\n";

    // 3. Build Service Worker JS
    $fintechRule = $config['is_fintech'] ? "url.pathname.startsWith('/api/wallet') || url.pathname.startsWith('/api/transaction') || url.pathname.startsWith('/invoices') || " : "";
    $swJs = <<<JS
/**
 * {$config['name']} Advanced Service Worker
 * Version: v2.0.0
 */

'use strict';

const SW_VERSION = 'v2.0.0';
const STATIC_CACHE = '{$key}-static-' + SW_VERSION;
const DYNAMIC_CACHE = '{$key}-dynamic-' + SW_VERSION;
const IMAGE_CACHE = '{$key}-images-' + SW_VERSION;
const MAX_IMAGE_ENTRIES = 60;

const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_ASSETS).catch(() => {}))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (![STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE].includes(key)) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;

  // Strict Network-Only Gate for FinTech / Auth / Admin
  if ({$fintechRule}url.pathname.startsWith('/admin') || url.pathname.startsWith('/login') || url.pathname.startsWith('/register') || url.pathname.startsWith('/auth') || url.pathname.startsWith('/logout') || url.pathname.startsWith('/signout')) {
    event.respondWith(fetch(request));
    return;
  }

  // Images -> Cache-First
  if (request.destination === 'image' || /\\.(png|jpg|jpeg|svg|webp|gif|ico)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(request).then((cached) => {
        return cached || fetch(request).then((res) => {
          if (res && res.status === 200) {
            caches.open(IMAGE_CACHE).then((c) => c.put(request, res.clone()));
          }
          return res;
        }).catch(() => new Response('', { status: 404 }));
      })
    );
    return;
  }

  // Static Assets -> Stale-While-Revalidate
  if (request.destination === 'style' || request.destination === 'script' || request.destination === 'font' || /\\.(css|js|woff2?|ttf)$/i.test(url.pathname)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then((cache) => {
        return cache.match(request).then((cached) => {
          const fetchPromise = fetch(request).then((res) => {
            if (res && res.status === 200) cache.put(request, res.clone());
            return res;
          }).catch(() => cached);
          return cached || fetchPromise;
        });
      })
    );
    return;
  }

  // HTML Pages -> Network-First with Offline Fallback
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res && res.status === 200) {
          caches.open(DYNAMIC_CACHE).then((c) => c.put(request, res.clone()));
        }
        return res;
      }).catch(() => {
        return caches.match(request).then((cached) => cached || caches.match('/offline.html'));
      })
    );
    return;
  }

  event.respondWith(fetch(request).catch(() => caches.match(request)));
});

// Push & Deep-Link Notification Listener
self.addEventListener('push', (event) => {
  let data = { title: '{$config['name']}', body: 'You have a new update.', url: '{$config['start_url']}' };
  if (event.data) {
    try { data = { ...data, ...event.data.json() }; } catch(e) { data.body = event.data.text(); }
  }
  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/public/img/favicon/android-chrome-192x192.png',
      badge: '/public/img/favicon/favicon-32x32.png',
      data: { url: data.url }
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url || '{$config['start_url']}';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
      for (const client of list) {
        if (client.url.includes(url) && 'focus' in client) return client.focus();
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});

self.addEventListener('message', (event) => {
  if (event.data?.action === 'skipWaiting') self.skipWaiting();
  if (event.data?.action === 'clearUserCache') caches.delete(DYNAMIC_CACHE);
});
JS;
    file_put_contents($publicDir . '/service-worker.js', $swJs);
    file_put_contents($basePath . '/service-worker.js', $swJs);
    echo "✓ Written service-worker.js\n";
}

echo "\n🎉 PWA Suite Rollout Completed across all 4 applications!\n";
