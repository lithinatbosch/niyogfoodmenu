/**
 * Service Worker
 * Kids Menu Planner Application
 * Handles offline caching and PWA functionality
 */

const CACHE_NAME = 'kids-menu-planner-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/index.php',
    '/foods.php',
    '/planner.php',
    '/calendar.php',
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/js/foods.js',
    '/assets/js/planner.js',
    '/assets/js/calendar.js',
    '/assets/icons/icon-192.png',
    '/manifest.json'
];

// Install event - cache assets
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Caching assets');
                return cache.addAll(ASSETS_TO_CACHE);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Skip API requests from caching
    if (event.request.url.includes('/api/')) {
        return fetch(event.request);
    }
    
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request)
                    .then((fetchResponse) => {
                        // Don't cache non-successful responses
                        if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type === 'error') {
                            return fetchResponse;
                        }
                        
                        // Clone the response
                        const responseToCache = fetchResponse.clone();
                        
                        // Add to cache
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                        
                        return fetchResponse;
                    });
            })
            .catch(() => {
                // Return offline page if available
                if (event.request.mode === 'navigate') {
                    return caches.match('/index.php');
                }
            })
    );
});

// Handle messages from clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Background sync (for future enhancement)
self.addEventListener('sync', (event) => {
    console.log('Service Worker: Background sync');
    
    if (event.tag === 'sync-meals') {
        event.waitUntil(syncMeals());
    }
});

// Sync meals function (placeholder for future implementation)
async function syncMeals() {
    // Implement background sync logic here
    console.log('Syncing meals...');
}

// Push notification (for future enhancement)
self.addEventListener('push', (event) => {
    console.log('Service Worker: Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New notification',
        icon: '/assets/icons/icon-192.png',
        badge: '/assets/icons/icon-72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        }
    };
    
    event.waitUntil(
        self.registration.showNotification('Kids Menu Planner', options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Notification clicked');
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/')
    );
});
