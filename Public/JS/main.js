// ========================
// 📁 Main.js
// ========================

import { initializeUI } from './Cogs/UIManager.js';
import { initializeSyncController } from './Core/syncController.js';
import { initializeConnectionChecker } from './Core/connectionChecker.js';

function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/Public/service-worker.js')
                .then(function(registration) {
                    console.log('Service Worker registrado com sucesso:', registration.scope);
                })
                .catch(function(err) {
                    console.log('Falha ao registrar o Service Worker:', err);
                });
        });
    }
}

function main() {
    initializeConnectionChecker();

    const pageId = document.body.id;
    const userType = document.body.dataset.userType;

    // Lógica para páginas públicas
    if (pageId === 'page-login' || pageId === 'page-register') {
        if (pageId === 'page-login') {
            import('./IDX/authController.js').then(module => module.initializeAuthController());
        } else if (pageId === 'page-register') {
            import('./IDX/registerController.js').then(module => module.initializeRegisterController());
        }
    } else {
        // Lógica para páginas autenticadas
        initializeSyncController();
        initializeUI();

        // Carrega os módulos base, que podem ser necessários para todos os tipos de usuário
        // dependendo da página em que eles estiverem.

        // Módulos específicos para páginas de usuário comum
        if (userType === 'usr') {
            switch (pageId) {
                case 'page-dashboard':
                    import('./USR/userInterface.js').then(module => module.initializeUserInterface());
                    import('./USR/indexDashboard.js').then(module => module.initializeDashboard());
                    import('./USR/userController.js').then(module => module.initializeController());
                    break;
                case 'page-settings':
                    import('./USR/userInterface.js').then(module => module.initializeUserInterface());
                    import('./USR/settingsController.js').then(module => module.initSettingsController());
                    break;
            }
        }

        // Módulos específicos para páginas de administrador
        if (userType === 'hkg') {
            switch (pageId) {
                case 'page-dashboard':
                    // Se um administrador acessar a página de dashboard de usuário
                    import('./USR/userInterface.js').then(module => module.initializeUserInterface());
                    import('./USR/indexDashboard.js').then(module => module.initializeDashboard());
                    import('./USR/userController.js').then(module => module.initializeController());
                    break;
                case 'page-settings':
                    // Se um administrador acessar a página de configurações
                    import('./USR/userInterface.js').then(module => module.initializeUserInterface());
                    import('./USR/settingsController.js').then(module => module.initSettingsController());
                    break;
                case 'page-hkg-dashboard':
                    // Se um administrador acessar a página de admin
                    import('./HKG/hkgInterface.js').then(module => module.initializeHkgInterface());
                    import('./HKG/hkgController.js').then(module => module.initializeHkgController());
                    break;
                case 'page-hkg-users':
                    // Se um administrador acessar a página de admin
                    import('./HKG/hkgInterface.js').then(module => module.initializeHkgInterface());
                    import('./HKG/hkgController.js').then(module => module.initializeHkgController());
                    break;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    registerServiceWorker();
    main();
});