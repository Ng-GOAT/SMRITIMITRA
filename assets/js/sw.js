const CACHE_NAME = 'smritimitra-v1';
const urlsToCache = [
    '/SmritiMitra/',
    '/SmritiMitra/assets/css/style.css',
    '/SmritiMitra/assets/js/sidebar.js',
    '/SmritiMitra/assets/js/settings.js',
    '/SmritiMitra/assets/js/companion.js',
    '/SmritiMitra/assets/js/notifications.js',
    '/SmritiMitra/pages/games.php',
    '/SmritiMitra/pages/medicines.php',
    '/SmritiMitra/pages/memories.php',
    '/SmritiMitra/pages/companion.php',
    '/SmritiMitra/pages/profile.php',
    '/SmritiMitra/pages/settings.php',
    '/SmritiMitra/pages/caregiver.php'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(names =>
            Promise.all(names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.url.includes('/api/')) {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return new Response(JSON.stringify({ error: 'Offline', offline: true }), {
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request)
                    .then(fetchResponse => {
                        return caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, fetchResponse.clone());
                            return fetchResponse;
                        });
                    })
                )
                .catch(() => caches.match('/SmritiMitra/index.php'))
        );
    }
});
