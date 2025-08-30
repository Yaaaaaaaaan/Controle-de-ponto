const CACHE_NAME = 'v3'; // Nome do cache (pode ser versionado)
const urlsToCache = [
    //CSS Bootstrap
    '/Public/CSS/bootstrap.min.css',
    '/Public/JS/bootstrap.bundle.js',

    //Chart JS
    "/Public/JS/chartjs/dist/chart.umd.js",

    //IndexedDB;
    '/Public/JS/indexedDB/Config.js',
    '/Public/JS/indexedDB/Model.js',

    //Index/index;
    '/Public/View/Index/index.php',
    '/Public/View/Index/register.php',
    '/Public/CSS/IDX/style.css',
    '/Public/JS/IDX/authController.js',
    '/Public/JS/IDX/registerController.js',

    //Layout; (não necessário, pois ambos inicializam o cache)
    //'/Public/View/Layout/menu.php',
    //'/Public/View/Layout/menu_housekeep.php',

    //User/index;
    '/Public/View/User/index.php',
    '/Public/CSS/USR/dashboard.css',
    '/Public/JS/USR/indexDashboard.js',

    //User/settings;
    '/Public/View/User/settings.php',
    '/Public/CSS/USR/handworking.css',
    '/Public/JS/USR/userController.js',

    //User/community;
    '/Public/View/User/community.php',

    //Housekeeping/index;
    '/Public/View/Housekeeping/index.php',
    '/Public/JS/HKG/indexDashboard.js',

    //Housekeeping/users;
    '/Public/View/Housekeeping/users.php',

    //Demais arquivos;
    '/Public/JS/script.js',
    '/Public/Api/SystemPics/offlineLogo.png',
    '/Public/Api/SystemPics/logo.png'

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

self.addEventListener('fetch', event => {
    // 1. IGNORAR REQUISIÇÕES QUE NÃO SÃO GET
    // Isso corrige o erro com requisições POST para sync.php. O navegador
    // simplesmente tentará a requisição, que falhará se estiver offline (comportamento correto).
    if (event.request.method !== 'GET') {
        //console.log('SW: Ignorando requisição não-GET:', event.request.method, event.request.url);
        return; // Deixa o navegador lidar com a requisição.
    }

    // 2. ESTRATÉGIA "NETWORK FALLING BACK TO CACHE"
    event.respondWith(
        // 2a. Tenta buscar o recurso da REDE primeiro.
        fetch(event.request)
            .then(networkResponse => {
                // Se a requisição de rede for bem-sucedida...

                // 2b. ...abre o cache e armazena uma cópia fresca da resposta para uso futuro.
                return caches.open(CACHE_NAME).then(cache => {
                    // Verificamos se a resposta é válida antes de cachear.
                    // Respostas 'basic' são do mesmo domínio.
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                        cache.put(event.request, networkResponse.clone());
                    }
                    // 2c. Retorna a resposta fresca da rede para a aplicação.
                    return networkResponse;
                });
            })
            .catch(() => {
                // Se a rede falhar, procuramos no cache
                console.warn(`SW: Falha na rede para ${event.request.url}. Servindo do cache.`);

                return caches.match(event.request).then(responseFromCache => {
                    // VERIFICAÇÃO IMPORTANTE:
                    // Se encontrarmos uma resposta no cache, retornamo-la.
                    if (responseFromCache) {
                        return responseFromCache;
                    }

                    // Se NÃO encontrarmos no cache (responseFromCache é undefined),
                    // retornamos uma resposta de erro de rede.
                    // Isto impede o TypeError e o crash do Service Worker.
                    console.error(`SW: O ficheiro ${event.request.url} não foi encontrado no cache.`);
                    return new Response('Recurso não encontrado no cache', { status: 404, statusText: 'Not Found in Cache' });
                });
            })
    );
});