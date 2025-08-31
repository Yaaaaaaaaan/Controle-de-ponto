// ========================
// 📁 main.js
// ========================

// Importa os módulos ESSENCIAIS que rodam em TODAS as páginas
import { initializeCoreUI } from './Cogs/UIManager.js';
import { initializeSyncController } from './Core/syncController.js';
import { initializeConnectionChecker } from './Core/connectionChecker.js';

function main() {
    initializeConnectionChecker();

    const pageId = document.body.id;
    const userType = document.body.dataset.userType; // Ex: 'usr' ou 'hkg'

    // Se a página NÃO for pública, inicializa o núcleo da UI
    if (pageId !== 'page-login' && pageId !== 'page-register') {
        initializeSyncController();
        initializeCoreUI(); // O núcleo sempre roda para usuários logados
    }

    // Carrega a interface e os controllers específicos para cada tipo de usuário/página
    switch (userType) {
        case 'usr':
            import('./USR/userInterface.js').then(module => module.initializeUserInterface());

            if (pageId === 'page-dashboard') {
                import('./USR/indexDashboard.js').then(module => module.initializeDashboard());
                import('./USR/userController.js').then(module => module.initializeController());
            } else if (pageId === 'page-settings') {
                import('./USR/settingsController.js').then(module => module.initSettingsController());
            }
            break;

        case 'hkg':
            // import('./HKG/userInterface.js').then(module => module.initializeHkgInterface());
            // ...e assim por diante para o housekeeping
            break;
    }

    // Carrega controllers de páginas públicas
    if (pageId === 'page-login') {
        import('./IDX/authController.js').then(module => module.initializeAuthController());
    } else if (pageId === 'page-register') {
        import('./IDX/registerController.js').then(module => module.initializeRegisterController());
    }
}

document.addEventListener('DOMContentLoaded', main);