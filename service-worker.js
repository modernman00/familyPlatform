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

// service-worker.js

const putInCache = async(request, response) => {
    const cache = await caches.open("v1");
    await cache.put(request, response);
};

const cacheFirst = async({ request, fallbackUrl }) => {
    // First try to get the resource from the cache.
    const responseFromCache = await caches.match(request);
    if (responseFromCache) {
        return responseFromCache;
    }

    // If the response was not found in the cache,
    // try to get the resource from the network.
    try {
        const responseFromNetwork = await fetch(request);
        // If the network request succeeded, clone the response:
        // - put one copy in the cache, for the next time
        // - return the original to the app
        // Cloning is needed because a response can only be consumed once.
        putInCache(request, responseFromNetwork.clone());
        return responseFromNetwork;
    } catch (error) {
        // If the network request failed,
        // get the fallback response from the cache.
        const fallbackResponse = await caches.match(fallbackUrl);
        if (fallbackResponse) {
            return fallbackResponse;
        }
        // When even the fallback response is not available,
        // there is nothing we can do, but we must always
        // return a Response object.
        return new Response("Network error happened", {
            status: 408,
            headers: { "Content-Type": "text/plain" },
        });
    }
};

self.addEventListener("fetch", (event) => {
    event.respondWith(
        cacheFirst({
            request: event.request,
            fallbackUrl: "/",
        }),
    );
});

// THIS IS FIRED WHEN THE APP HAS NETWORK CONNECTIVITY

// self.addEventListener("sync", (event) => {
//     if (event.tag == "send-message") {
//         event.waitUntil(sendMessage());
//     }
// });



// self.addEventListener("fetch", (event) => {
//     // when seeking an HTML page
//     if (event.request.mode === "navigate") {
//         // Return to the index.html page
//         event.respondWith(caches.match("/"));
//         return;
//     }

//     // For every other request type
//     event.respondWith(
//         (async() => {
//             const cache = await caches.open(CACHE_NAME);
//             const cachedResponse = await cache.match(event.request.url);
//             if (cachedResponse) {
//                 // Return the cached response if it's available.
//                 return cachedResponse;
//             }
//             // Respond with a HTTP 404 response status.
//             return new Response(null, { status: 404 });
//         })(),
//     );
// });