const CACHE_NAME = 'v3'; // Nome do cache (pode ser versionado)
const urlsToCache = [
    //CSS Bootstrap
    '/controle-de-ponto/Public/CSS/bootstrap.min.css',
    '/controle-de-ponto/Public/JS/bootstrap.bundle.js',

    //Chart JS
    "/controle-de-ponto/Public/JS/chartjs/dist/chart.umd.js",

    //IndexedDB;
    '/controle-de-ponto/Public/JS/indexedDB/Config.js',
    '/controle-de-ponto/Public/JS/indexedDB/Model.js',

    //Index/index;
    '/controle-de-ponto/Public/View/Index/index.php',
    '/controle-de-ponto/Public/View/Index/register.php',
    '/controle-de-ponto/Public/CSS/IDX/style.css',
    '/controle-de-ponto/Public/JS/IDX/authController.js',
    '/controle-de-ponto/Public/JS/IDX/registerController.js',

    //Layout; (não necessário, pois ambos inicializam o cache)
    //'/controle-de-ponto/Public/View/Layout/menu.php',
    //'/controle-de-ponto/Public/View/Layout/menu_housekeep.php',

    //User/index;
    //'/controle-de-ponto/Public/View/User/index.php',
    '/controle-de-ponto/Public/CSS/USR/dashboard.css',
    '/controle-de-ponto/Public/JS/USR/indexDashboard.js',

    //User/settings;
    //'/controle-de-ponto/Public/View/User/settings.php',
    '/controle-de-ponto/Public/CSS/USR/handworking.css',
    '/controle-de-ponto/Public/JS/USR/userController.js',

    //User/community;
    //'/controle-de-ponto/Public/View/User/community.php',

    //Housekeeping/index;
    //'/controle-de-ponto/Public/View/Housekeeping/index.php',
    '/controle-de-ponto/Public/JS/HKG/indexDashboard.js',

    //Housekeeping/users;
    //'/controle-de-ponto/Public/View/Housekeeping/users.php',

    //Demais arquivos;
    '/controle-de-ponto/Public/JS/script.js',
    '/controle-de-ponto/Public/Api/SystemPics/offlineLogo.png',
    '/controle-de-ponto/Public/Api/SystemPics/logo.png'

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
        console.log('SW: Ignorando requisição não-GET:', event.request.method, event.request.url);
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