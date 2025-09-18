// ========================
// 📁 service-worker.js
// ========================
const CACHE_NAME = 'v3-static'; // Renomeado para clareza
const DYNAMIC_CACHE_NAME = 'v3-dynamic'; // Novo cache para conteúdo do usuário
const urlsToCache = [
    //CSS Bootstrap
    '/Public/CSS/bootstrap.min.css',
    '/Public/JS/bootstrap.bundle.js',

    //Chart JS
    "/Public/JS/chartjs/dist/chart.umd.js",

    //Cogs;
    '/Public/JS/Cogs/utils.js',
    '/Public/JS/Cogs/UIManager.js',

    //Core;
    '/Public/JS/Core/connectionChecker.js',
    '/Public/JS/Core/syncController.js',

    //IndexedDB;
    '/Public/JS/indexedDB/Config.js',
    '/Public/JS/indexedDB/Model.js',

    //Index/index;
    '/Public/View/Index/index.php',
    '/Public/View/Index/register.php',
    '/Public/CSS/IDX/style.css',
    '/Public/JS/IDX/authController.js',
    '/Public/JS/IDX/registerController.js',

    //User
    '/Public/JS/USR/userInterface.js',

    //User/index;
    '/Public/View/User/index.php',
    '/Public/CSS/USR/dashboard.css',
    '/Public/JS/USR/indexDashboard.js',
    '/Public/JS/USR/userController.js',

    //User/settings;
    '/Public/View/User/settings.php',
    '/Public/CSS/USR/handworking.css',
    '/Public/JS/USR/settingsController.js',

    //User/community;
    '/Public/View/User/community.php',

    //Housekeeping/index;
    '/Public/View/Housekeeping/index.php',
    '/Public/JS/HKG/indexDashboard.js',

    //Housekeeping/users;
    '/Public/View/Housekeeping/users.php',

    //Demais arquivos;
    '/Public/JS/script.js',
    '/Public/Assets/img/offlineLogo.png',
    '/Public/Assets/img/logo.png'
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
    const cacheWhitelist = [CACHE_NAME, DYNAMIC_CACHE_NAME]; // Lista de caches a serem mantidos
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    // Se o nome do cache não estiver na nossa lista, ele é deletado
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        console.log('SW: Deletando cache antigo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // --- CORREÇÃO ADICIONADA AQUI ---
    // Se a URL do pedido contém '/Public/Api/', o Service Worker deve ignorá-la.
    // O navegador irá tratar a requisição de rede normalmente.
    if (url.pathname.startsWith('/Public/Api/')) {
        return; // Deixa a requisição passar, ignorando o cache.
    }
    // --- FIM DA CORREÇÃO ---

    // A sua lógica de "Network falling back to cache" para os outros arquivos continua.
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                return caches.open(CACHE_NAME).then(cache => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                        cache.put(event.request, networkResponse.clone());
                    }
                    return networkResponse;
                });
            })
            .catch(() => {
                console.warn(`SW: Falha na rede para ${event.request.url}. Servindo do cache.`);
                return caches.match(event.request).then(responseFromCache => {
                    if (responseFromCache) {
                        return responseFromCache;
                    }
                    console.error(`SW: O ficheiro ${event.request.url} não foi encontrado no cache.`);
                    return new Response('Recurso não encontrado no cache', { status: 404, statusText: 'Not Found in Cache' });
                });
            })
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CACHE_DYNAMIC_FILES') {
        const urlsToCache = event.data.payload;
        console.log('SW: Recebida mensagem para cachear arquivos dinâmicos:', urlsToCache);

        event.waitUntil(
            caches.open(DYNAMIC_CACHE_NAME).then((cache) => {
                // O método addAll faz o fetch e o cache de uma vez
                return cache.addAll(urlsToCache).catch(error => {
                    console.error('SW: Falha ao cachear um ou mais arquivos dinâmicos:', error);
                });
            })
        );
    }
});