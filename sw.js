const CACHE_NAME = 'animeflix-v1.0';
const urlsToCache = [
  '/',
  '/index.html',
  '/css/style.css',
  '/script.js',
  '/manifest.json',
  '/anime/naruto.html',
  '/anime/onepiece.html',
  '/anime/demon-slayer.html',
  '/anime/attack-on-titan.html'
];

self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});
