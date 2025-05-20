const CACHE_NAME = 'v1'; // Nome do cache (pode ser versionado)
const urlsToCache = [
    //IndexedDB;
    '/controle-de-ponto/Public/JS/indexedDB/Config.js',
    '/controle-de-ponto/Public/JS/indexedDB/Model.js',
    '/controle-de-ponto/Public/JS/indexedDB/Controller.js',

    //Index/index;
    '/controle-de-ponto/Public/View/Index/index.php',
    '/controle-de-ponto/Public/CSS/IDX/style.css',
    '/controle-de-ponto/Public/JS/IDX/authController.js',

    //Layout; (não necessário, pois ambos inicializam o cache)
    //'/controle-de-ponto/Public/View/Layout/menu.php',
    //'/controle-de-ponto/Public/View/Layout/menu_housekeep.php',

    //User/index;
    '/controle-de-ponto/Public/View/User/index.php',
    '/controle-de-ponto/Public/CSS/USR/dashboard.css',
    '/controle-de-ponto/Public/JS/USR/indexDashboard.js',

    //User/settings;
    '/controle-de-ponto/Public/View/User/settings.php',
    '/controle-de-ponto/Public/CSS/USR/handworking.css',
    '/controle-de-ponto/Public/JS/USR/userController.js',

    //User/community;
    '/controle-de-ponto/Public/View/User/community.php',

    //Housekeeping/index;
    '/controle-de-ponto/Public/View/Housekeeping/index.php',
    '/controle-de-ponto/Public/JS/HKG/indexDashboard.js',

    //Housekeeping/users;
    '/controle-de-ponto/Public/View/Housekeeping/users.php',

    //Demais arquivos;
    '/controle-de-ponto/Public/JS/script.js'

];
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Cache aberto!');
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return cacheNames.map(function(cacheName) {
                if (cacheName !== CACHE_NAME) {
                    return caches.delete(cacheName);
                }
            });
        })
    );
});

self.addEventListener('fetch', function(event) {
    const url = new URL(event.request.url);
    // Intercepta a requisição para a página inicial e serve do cache
    if (url.pathname === '/controle-de-ponto/index.php' && event.request.mode === 'navigate') {
        event.respondWith(caches.match('/controle-de-ponto/Public/View/Index/Index.php'));
    } else {
        event.respondWith(
            caches.match(event.request)
                .then(function(response) {
                    if (response) {
                        return response;
                    }
                    return fetch(event.request).then(function(fetchResponse) {
                        if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
                            return fetchResponse;
                        }

                        const responseToCache = fetchResponse.clone();

                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                cache.put(event.request, responseToCache);
                            });

                        return fetchResponse;
                    });
                })
        );
    }
});