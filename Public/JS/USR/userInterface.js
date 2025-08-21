// No ficheiro: userInterface.js (VERSÃO REFATORADA E CORRIGIDA)

import { upsertUser, getUserById, fetchUserDataByToken, obterHoraFormatada } from '../indexedDB/Model.js';
import { initializeSyncController } from '../Core/syncController.js'; // Importamos o inicializador

// --- Variáveis Globais ---
let isFirstLoad = true;
let userDataPromiseResolver;
const userDataPromise = new Promise(resolve => {
    userDataPromiseResolver = resolve;
});

// --- Funções de Atualização da UI (continuam as mesmas) ---

function updateUIPicture(profileUser) {
    if (!profileUser) return;
    const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
    const srcImage = imageBasePath + String(profileUser).replace(/"/g, '');
    ['pPicture', 'pPictureModal'].forEach(id => {
        const element = document.getElementById(id);
        if (element) element.src = srcImage;
    });
}

function updateUIElements(userData) {
    if (!userData || !userData.name) {
        console.error(`[${obterHoraFormatada()}] updateUIElements: Dados de utilizador inválidos ou incompletos`, userData);
        return;
    }
    const elements = {
        'responseName': userData.name,
        'responseNameCurto': `Olá, ${userData.name.split(' ')[0]}`,
        'responseUserToken': userData.userToken,
        'responseEmail': userData.email,
        'responseRank': userData.rank,
        'responseNickname': userData.nickname,
        'responseTheme': userData.theme,
        'responseId': userData.id,
        'responseToken': userData.userToken
    };
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });

    // Dispara o evento que o indexDashboard.js está à espera
    if (isFirstLoad) {
        console.log(`[${obterHoraFormatada()}] Primeira carga: Disparando evento "userDataReady" com dados:`, userData);
        document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
        isFirstLoad = false;
    }
}

function updateSettingsForm(userData) {
    if (!window.location.pathname.includes('/User/settings.php') || !userData) return;
    const formFields = {
        'floatingInputName': userData.name,
        'floatingInputEmail': userData.email,
        'floatingInputNickname': userData.nickname,


    };
    Object.entries(formFields).forEach(([inputId, value]) => {
        const element = document.getElementById(inputId);
        if (element && value !== null && value !== undefined) {
            element.value = value;
        }
    });
}

// --- LÓGICA DE INICIALIZAÇÃO CENTRALIZADA ---

/**
 * [FUNÇÃO PRINCIPAL] Orquestra o carregamento de dados do utilizador e a atualização da UI.
 */
async function initializeUserData() {
    // 1. A FONTE DA VERDADE: Pegamos o ID do utilizador ativo do localStorage.
    const activeUserId = localStorage.getItem('activeUserId');
    const localToken = localStorage.getItem('userToken');

    if (!activeUserId || !localToken) {
        console.log(`[${obterHoraFormatada()}] Nenhum utilizador ativo encontrado no localStorage. Redirecionando para o login.`);
        if (!window.location.pathname.includes('/Index/')) {
            window.location.href = '/controle-de-ponto/Public/View/Index/';
        }
        return;
    }

    try {
        let userData = null;

        // 2. TENTA BUSCAR DADOS FRESCOS DO SERVIDOR (se online)
        if (navigator.onLine) {
            const serverData = await fetchUserDataByToken(localToken);
            if (serverData) {
                await upsertUser(serverData);
                userData = serverData;
            }
        }

        // 3. SE ESTIVER OFFLINE ou a busca falhar, busca do IndexedDB.
        if (!userData) {
            userData = await getUserById(parseInt(activeUserId, 10));
        }

        // 4. SE TEMOS DADOS, ATUALIZA A UI E "AVISA" OS OUTROS SCRIPTS
        if (userData) {
            console.log(`[${obterHoraFormatada()}] userInterface.js: Dados do utilizador carregados. Atualizando UI.`, userData);
            updateUIPicture(userData.profileUser);
            updateUIElements(userData);
            updateSettingsForm(userData);
            userDataPromiseResolver(userData); // Resolve a promessa para o userController
        } else {
            console.error(`[${obterHoraFormatada()}] Token/ID local inválido e nenhum dado no IndexedDB. Forçando logout.`);
            localStorage.clear();
            window.location.href = '/controle-de-ponto/Public/View/Index/';
        }

    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro crítico ao inicializar a interface do utilizador:`, error);
        userDataPromiseResolver(null);
    }
}

// --- PONTO DE ENTRADA DO SCRIPT ---

document.addEventListener('DOMContentLoaded', () => {
    // A única coisa que o DOMContentLoaded faz é chamar a função principal.
    initializeUserData();

    // Inicia o controlador de sincronização (que agora contém o setInterval)
    initializeSyncController();
});

// Exporta a promessa para que outros módulos possam esperar pelos dados.
export { userDataPromise };