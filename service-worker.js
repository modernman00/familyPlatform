const Version = "1.0.0";
const CacheName = `cache-${Version}`;
const CacheFiles = [
    "/",
    "/index.php",
    "/public/style.css",
    "/public/index.js",
    "/public/img/favicon/android-chrome-192x192.png",
    "/public/img/favicon/android-chrome-512x512.png",
    "/public/img/favicon/apple-touch-icon.png"
];

// On install, cache the static resources
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
        .open(CacheName)
        .then(function(cache) {
            return cache.addAll(CacheFiles);
        })
    );
});

// delete old caches on activate

self.addEventListener("activate", (event) => {
    event.waitUntil(
        (async() => {
            const names = await caches.keys();
            await Promise.all(
                names.map((name) => {
                    if (name !== CACHE_NAME) {
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

self.addEventListener("fetch", (event) => {
    // when seeking an HTML page
    if (event.request.mode === "navigate") {
        // Return to the index.html page
        event.respondWith(caches.match("/"));
        return;
    }

    // For every other request type
    event.respondWith(
        (async() => {
            const cache = await caches.open(CACHE_NAME);
            const cachedResponse = await cache.match(event.request.url);
            if (cachedResponse) {
                // Return the cached response if it's available.
                return cachedResponse;
            }
            // Respond with a HTTP 404 response status.
            return new Response(null, { status: 404 });
        })(),
    );
});