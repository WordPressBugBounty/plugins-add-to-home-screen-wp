// wp-content/plugins/add-to-home-screen-wp/athswp-sw.js
const CACHE_NAME = 'athswp-cache-v1';
const urlsToCache = [
    '/',
    '/wp-admin/',
    '/wp-admin/index.php',
    '/wp-admin/admin-ajax.php',
    '/wp-includes/css/dashicons.min.css',
    '/wp-includes/js/jquery/jquery.min.js',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});

self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (!cacheWhitelist.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});