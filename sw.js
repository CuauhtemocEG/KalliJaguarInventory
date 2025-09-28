const CACHE_NAME = 'kalli-jaguar-inventory-v1.0.0';
const urlsToCache = [
    '/',
    '/index.php',
    '/index.php?page=home',
    '/index.php?page=login',
    '/pages/login.php',
    '/css/sb-admin-2.min.css',
    '/css/style.css',
    '/js/sb-admin-2.min.js',
    '/js/ajax.js',
    '/js/functions.js',
    '/img/logo.png',
    '/img/Kalli-Amarillo.png',
    '/img/Login.jpg',
    'https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    'https://cdn.tailwindcss.com'
];

// Instalar Service Worker y cachear recursos
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Caching files');
                return cache.addAll(urlsToCache.map(url => {
                    // Convertir URLs relativas a absolutas
                    return new Request(url, { mode: 'cors' });
                }));
            })
            .catch(error => {
                console.error('Service Worker: Error caching files', error);
            })
    );
    self.skipWaiting(); // Activar inmediatamente
});

// Activar Service Worker y limpiar caches antiguos
self.addEventListener('activate', event => {
    console.log('Service Worker: Activated');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Deleting old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim(); // Controlar todas las páginas inmediatamente
});

// Interceptar peticiones de red
self.addEventListener('fetch', event => {
    // Solo cachear peticiones GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Ignorar peticiones a APIs externas que cambien frecuentemente
    if (event.request.url.includes('/api/') && 
        (event.request.url.includes('generarReporte') || 
         event.request.url.includes('loginHandler') ||
         event.request.url.includes('updateStock'))) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Devolver del cache si existe
                if (response) {
                    console.log('Service Worker: Serving from cache', event.request.url);
                    return response;
                }

                // Si no existe en cache, hacer petición de red
                return fetch(event.request)
                    .then(response => {
                        // Verificar que la respuesta es válida
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clonar la respuesta para guardar en cache
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                console.log('Service Worker: Caching new resource', event.request.url);
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // En caso de error de red, mostrar página offline personalizada
                        if (event.request.destination === 'document') {
                            return caches.match('/pages/offline.html');
                        }
                    });
            })
    );
});

// Manejar mensajes del cliente
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Notificación push (opcional para futuras funcionalidades)
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/img/logo.png',
            badge: '/img/Kalli-Amarillo.png',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: 1
            }
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

console.log('Service Worker: Loaded successfully');
