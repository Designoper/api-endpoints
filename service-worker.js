self.addEventListener('install', event => {
	event.waitUntil(
		caches.open('v1').then(cache => {
			return cache.addAll([
				'./index.html',
				'./assets/css/common/reset.css',
				'./assets/css/common/reset.css',
				'./assets/css/common/reset.css',
				'./assets/css/common/reset.css',
				'./assets/css/index.css',
				'./assets/js/Fetch.js',
				'./assets/js/Categoria.js',
				'./assets/js/Libro.js',
			]);
		})
	);
});

self.addEventListener('fetch', event => {
	event.respondWith(
		caches.match(event.request).then(response => {
			return response || fetch(event.request);
		})
	);
});
