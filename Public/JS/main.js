// ========================
// 📁 Main.js - Refatorado e Corrigido
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
    // Inicializa verificador de conexão globalmente
    initializeConnectionChecker();

    const pageId = document.body.id;
    const userType = document.body.dataset.userType;

    console.log(`[Main.js] Iniciando... Página: ${pageId}, Tipo Usuário: ${userType}`);

    // --- Lógica para Páginas Públicas (Login/Registro) ---
    if (pageId === 'page-login' || pageId === 'page-register') {
        if (pageId === 'page-login') {
            import('./IDX/authController.js').then(module => {
                if (typeof module.initializeAuthController === 'function') module.initializeAuthController();
            }).catch(err => console.error("Erro ao carregar authController:", err));
        } else if (pageId === 'page-register') {
            import('./IDX/registerController.js').then(module => {
                if (typeof module.initializeRegisterController === 'function') module.initializeRegisterController();
            }).catch(err => console.error("Erro ao carregar registerController:", err));
        }
        return; // Sai da função para não executar lógica de usuário logado
    }

    // --- Lógica para Páginas Autenticadas (USR ou HKG) ---
    
    // Inicializa controladores globais
    initializeSyncController();
    initializeUI();

    // Roteamento dinâmico baseado no tipo de usuário e ID da página
    
    // === USUÁRIO COMUM (USR) ===
    if (userType === 'usr') {
        switch (pageId) {
            case 'page-dashboard':
                Promise.all([
                    import('./USR/userInterface.js'),
                    import('./USR/indexDashboard.js'),
                    import('./USR/userController.js')
                ]).then(([ui, dashboard, controller]) => {
                    if (ui.initializeUserInterface) ui.initializeUserInterface();
                    if (dashboard.initializeDashboard) dashboard.initializeDashboard();
                    if (controller.initializeController) controller.initializeController();
                }).catch(err => console.error("Erro ao carregar módulos USR:", err));
                break;
                
            case 'page-settings':
                Promise.all([
                    import('./USR/userInterface.js'),
                    import('./USR/settingsController.js')
                ]).then(([ui, settings]) => {
                    if (ui.initializeUserInterface) ui.initializeUserInterface();
                    if (settings.initSettingsController) settings.initSettingsController();
                }).catch(err => console.error("Erro ao carregar settings USR:", err));
                break;
        }
    }

    // === ADMINISTRADOR (HKG) ===
    if (userType === 'hkg') {
        switch (pageId) {
            // Admin acessando Dashboard Pessoal (Reaproveita módulos USR)
            case 'page-dashboard':
                Promise.all([
                    import('./USR/userInterface.js'),
                    import('./USR/indexDashboard.js'),
                    import('./USR/userController.js')
                ]).then(([ui, dashboard, controller]) => {
                    if (ui.initializeUserInterface) ui.initializeUserInterface();
                    if (dashboard.initializeDashboard) dashboard.initializeDashboard();
                    if (controller.initializeController) controller.initializeController();
                });
                break;

            // Admin acessando Configurações Pessoais (Reaproveita módulos USR)
            case 'page-settings':
                Promise.all([
                    import('./USR/userInterface.js'),
                    import('./USR/settingsController.js')
                ]).then(([ui, settings]) => {
                    if (ui.initializeUserInterface) ui.initializeUserInterface();
                    if (settings.initSettingsController) settings.initSettingsController();
                });
                break;

            // Admin acessando Painel de Controle (Housekeeping Dashboard)
            case 'page-hkg-dashboard':
                console.log("[Main.js] Carregando módulo HKG Dashboard...");
                import('./HKG/dashboardController.js')
                    .then(module => {
                        console.log("[Main.js] Módulo Dashboard carregado. Iniciando...");
                        // Chama a função init() que exportamos explicitamente
                        if (module.init) module.init();
                        else console.warn("[Main.js] Aviso: Função init() não encontrada em dashboardController.js");
                    })
                    .catch(err => console.error("[Main.js] Erro ao carregar dashboardController:", err));
                break;

            // Admin acessando Gerenciamento de Usuários
            case 'page-hkg-users':
                console.log("[Main.js] Carregando módulo HKG Users...");
                import('./HKG/userController.js')
                    .then(module => {
                        console.log("[Main.js] Módulo Users carregado. Iniciando...");
                        // Chama a função init() que exportamos explicitamente
                        if (module.init) module.init(); 
                        else console.warn("[Main.js] Aviso: Função init() não encontrada em userController.js");
                    })
                    .catch(err => console.error("[Main.js] Erro ao carregar userController:", err));
                break;
                
            default:
                console.warn(`[Main.js] Nenhuma rota definida para pageId: ${pageId} no modo Admin.`);
        }
    }
}

// Inicializa tudo quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    registerServiceWorker();
    main();
});