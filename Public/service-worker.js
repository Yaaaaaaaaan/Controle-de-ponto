
const CACHE_NAME = 'controle-de-ponto-v1';
const urlsToCache = [
    '/',
    '/controle-de-ponto/index.php', // Caminho correto para index.php
    '/controle-de-ponto/View/Layout/menu.php', // Caminho correto para menu.php
    'controle-de-ponto/Public/View/User/settings.php',
    'controle-de-ponto/Public/View/User/community.php',
    '/controle-de-ponto/Public/CSS/USR/handworking.css',
    '/controle-de-ponto/Public/CSS/USR/dashboard.css',
    '/controle-de-ponto/Public/JS/USR/userInterface.js',
    '/controle-de-ponto/Public/JS/script.js',
    '/controle-de-ponto/Public/JS/indexedDB/Config.js',
    '/controle-de-ponto/Public/JS/indexedDB/Model.js'
];

// Instalação do Service Worker
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function (cache) {
                console.log('Service Worker: Cache aberto e recursos adicionados');
                return cache.addAll(urlsToCache);
            })
            .catch(error => console.error('Service Worker: Falha ao abrir o cache ou adicionar recursos:', error))
    );
});

// Ativação do Service Worker
self.addEventListener('activate', function (event) {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
            .then(() => self.clients.claim()) // Força o Service Worker a controlar todas as páginas abertas
    );
    console.log('Service Worker: Ativado');
});

// Intercepta requisições de fetch
self.addEventListener('fetch', function (event) {
    event.respondWith(
        caches.match(event.request)
            .then(function (response) {
                if (response) {
                    // Retorna a resposta do cache se estiver disponível
                    return response;
                }
                // Se não estiver no cache, busca da rede
                return fetch(event.request).then(
                    function (fetchResponse) {
                        // Verifica se recebemos uma resposta válida
                        if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
                            return fetchResponse;
                        }

                        // Se a resposta for válida, coloca no cache
                        const responseToCache = fetchResponse.clone();

                        caches.open(CACHE_NAME)
                            .then(function (cache) {
                                cache.put(event.request, responseToCache);
                            });

                        return fetchResponse;
                    }
                );
            })
    );
});