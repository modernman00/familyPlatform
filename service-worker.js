const Version = "1.3";
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
  "/public/img/photos/",
  "/public/img/post/",
  "/public/img/profile/",
  '/public/img/favicon/favicon.ico',
  "/public/img/celebrate.jpeg",
  "/public/img/favicon/favicon-32x32.png"
];

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

// Delete old caches on activate
self.addEventListener("activate", (event) => {
  event.waitUntil(
    (async () => {
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
        return caches.match('/index'); // Optional: Serve a fallback page if the network fails
      });

      return cachedResponse || fetchPromise;
    }).catch((error) => {
      console.error('Error matching cache or fetching:', error);
    })
  );
});

// Utility function to show notification
function showNotification({ title, body, url }) {
  const options = {
    body,
    icon: '/public/img/favicon/android-chrome-192x192.png',
    badge: '/public/img/favicon/android-chrome-192x192.png',
    data: { url },
    requireInteraction: true, // Keeps notification visible until user interacts
    actions: [
      { action: "open", title: "View" }
    ]
  };
  self.registration.showNotification(title, options);
}

// Push event - handle notifications
self.addEventListener('push', function (event) {
  let data = {};

  try {
    data = event.data ? event.data.json() : {};
  } catch (e) {
    console.error("Push event error:", e);
  }

  const notificationData = {
    body: data.body || 'You have a new notification',
    title: data.title || 'New Notification',
    url: data.url || '/',
  };

  event.waitUntil(showNotification(notificationData));
});

// Notification click event - open the link when clicked
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
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

// Handle notification close event (optional)
self.addEventListener('notificationclose', (event) => {
  console.log('Notification closed', event.notification);
});

// Periodic sync event - sync content
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'content-sync') {
    console.log('Periodic Sync triggered:', event.tag);
    event.waitUntil(syncContent());
  }
});

// Sync content function
async function syncContent() {
  try {
    console.log('Performing periodic content sync...');
    const battery = await navigator.getBattery();
    const isCharging = battery.charging;
    const batteryLevel = battery.level;
    console.log(`Battery charging: ${isCharging}, level: ${batteryLevel}`);

    // Adjust sync frequency for low battery
    if (!isCharging && batteryLevel < 0.2) {
      console.warn('Battery is low, deferring sync.');
      return;
    }

    // Fetch new data from the server
    const yourId = localStorage.getItem('requesterId');
    const famCode = localStorage.getItem('requesterFamCode');
    const notificationURL = `/member/notifications/id/${yourId}/${famCode}`;

    const response = await fetch(notificationURL);
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();
    console.log('Fetched new content:', data);

    // Notify user of successful sync
    self.registration.showNotification('Sync Successful', {
      body: 'Your content has been updated!',
      icon: '/public/img/favicon/android-chrome-192x192.png'
    });

    if (data.length === 0) {
      console.log('No new notifications to display.');
      return;
    }

    data.forEach((notification) => {
      // Check the type of notification and display accordingly
      if (notification.type === 'Friend Request') {
        showNotification({
          title: 'Friend Request!',
          body: notification.notification_name,
          url: `${process.env.MIX_APP_URL}/member/ProfilePage`,
        });
      } else {
        showNotification({
          title: notification.notification_name,
          body: notification.notification_body,
          url: `${process.env.MIX_APP_URL}/member/ProfilePage`
        });
      }
    });

    // Update cache or IndexedDB with the new data
    // Implement your caching logic here

  } catch (error) {
    console.error('Error during periodic content sync:', error);

    // Send a push notification for failure
    self.registration.showNotification('Sync Failed', {
      body: 'We could not update your content. Please check your connection.',
      icon: '/public/img/favicon/android-chrome-192x192.png'
    });
  }
}