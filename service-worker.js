const Version = "1.2";
const CacheName = `cache-${Version}`;
const CacheFiles = [
    "/",
    "/index",
    "/login",
    "/register",
    "/allMembers",
    "/member/ProfilePage",
    "/organogram",
    "/allMembers/getProfile",
    "/public/style.css",
    "/public/index.js",
    "/public/img/favicon/android-chrome-192x192.png",
    "/public/img/favicon/android-chrome-512x512.png",
    "/public/img/favicon/apple-touch-icon.png",
    "public/img/photos/",
    "/public/img/post/",
    "/public/img/profile/",
    '/public/img/favicon/favicon.ico',
    "/public/img/celebrate.jpeg",
    "/public/img/favicon/favicon-32x32.png"
];

// get all profile images posted to the server and add them to the cache on install 

//TODO - fix this
// self.addEventListener("install", (event) => {
//   event.waitUntil(
//     (async () => {
//       const images = await fetch("/public/img/profile/");
//       const imagesArray = await images.json();
//       console.log(imagesArray);
//       imagesArray.forEach(async (image) => {
//         const response = await fetch(`/public/img/profile/${image}`);
//         const blob = await response.blob();
//         const cache = await caches.open(CacheName);
//         cache.put(`/public/img/profile/${image}`, new Response(blob));
//       });
//     })(),
//   );
// });

// On install, cache the static resources
self.addEventListener("install", (event) => {
  console.log("[Service Worker] Install");
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CacheName);
       console.log("[Service Worker] Caching all: app shell and content");
      await cache.addAll(CacheFiles);
    })(),
  );
});


// delete old caches on activate

self.addEventListener("activate", (event) => {
    event.waitUntil(
        (async() => {
            const names = await caches.keys();
            await Promise.all(
                names.map((name) => {
                    if (name !== CacheName) {
                        return caches.delete(name);
                    }
                }),
            );
            await clients.claim();
        })(),
    );
});

// On fetch, intercept server requests
// and respond with cached responses instead of going to network

// service-worker.js

const putInCache = async(request, response) => {
    const cache = await caches.open("v1");
    await cache.put(request, response);
};

/**
 * Attempts to retrieve a resource using a cache-first strategy.
 *
 * @param {object} options - Options for the cache-first strategy.
 * @param {Request} options.request - The request to retrieve.
 * @param {string} options.fallbackUrl - The URL to fall back to if the request fails.
 * @return {Response} The retrieved response, or a fallback response if the request fails.
 */
// Fetch event - stale-while-revalidate strategy
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          caches.open(CacheName).then((cache) => {
            cache.put(event.request, networkResponse.clone());
          });
        }
        return networkResponse;
      }).catch((error) => {
          console.error('Fetch failed; returning cached page instead:', error);
          // Optional: Serve a fallback page if the network fails
          return caches.match('/index');
        });

      return cachedResponse || fetchPromise;
    }).catch((error) => {
      console.error('Error matching cache or fetching:', error);
    })
  );
});

// Periodic sync event - sync content
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'content-sync') {
    event.waitUntil(syncContent());
  }
});

async function syncContent() {
  try {
    const response = await fetch('/api/get-updates');
    const data = await response.json();
    console.log('Periodic content sync completed:', data);

    // Update cache or IndexedDB with the new data
  } catch (error) {
    console.error('Error during periodic content sync:', error);
  }
}

// Push event - handle notifications
self.addEventListener('push', (event) => {
  try {
    const data = event.data ? JSON.parse(event.data.text()) : {};
    const options = {
      body: data.body || 'You have a new notification',
      icon: '/icons/icon-192x192.png',
      badge: '/icons/badge-72x72.png',
      data: {
        url: data.url || '/',
      },
    };

    event.waitUntil(
      self.registration.showNotification(data.title || 'New Notification', options)
    );
  } catch (error) {
    console.error('Error in push event:', error);
  }
});


// Notification click event - focus or open a window
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then((clientList) => {
      for (const client of clientList) {
        if (client.url === event.notification.data.url && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(event.notification.data.url);
      }
    })
  );
});
