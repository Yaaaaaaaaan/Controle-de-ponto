// ========================
// 📁 UIManager.js
// ========================


import { upsertUser, getUserById, fetchUserDataByToken, obterHoraFormatada } from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js';

let userDataPromiseResolver;
const userDataPromise = new Promise(resolve => {
    userDataPromiseResolver = resolve;
});

// Funções que atualizam elementos COMUNS a todas as páginas
function updateSharedUI(userData) {
    if (!userData) return;

    // Atualiza a foto de perfil (presente em todas as páginas)
    const srcImage = userData.profileUser || '/Public/assets/img/Profile.png';
    const pPicture = document.getElementById('pPicture');
    if (pPicture) pPicture.src = srcImage;

    // Atualiza o nome do usuário no menu (presente em todas as páginas)
    const welcomeMessage = document.getElementById('responseNameCurto');
    if (welcomeMessage) welcomeMessage.textContent = `Olá, ${userData.name.split(' ')[0]}`;
}

/**
 * [NÚCLEO] Inicializa a UI base, carrega os dados e avisa as interfaces específicas.
 */
async function initializeCoreUI() {
    const activeUserId = localStorage.getItem('activeUserId');
    const localToken = localStorage.getItem('userToken');

    if (!activeUserId || !localToken) {
        if (!window.location.pathname.includes('/Index/')) window.location.href = '/Public/View/Index/';
        return;
    }

    try {
        let userData = null;
        if (isOnline) {
            userData = await fetchUserDataByToken(localToken);
        }
        if (!userData) {
            userData = await getUserById(parseInt(activeUserId, 10));
        }

        if (userData) {
            console.log(`[${obterHoraFormatada()}] CoreUIManager: Dados carregados.`, userData);
            updateSharedUI(userData);

            userDataPromiseResolver(userData);
            // Dispara o evento que as interfaces específicas (USR, HKG) vão ouvir
            document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
        } else {
            localStorage.clear();
            window.location.href = '/Public/View/Index/';
        }
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro crítico ao inicializar CoreUIManager:`, error);
        userDataPromiseResolver(null);
    }
}
async function triggerUIRefresh() {
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId) return;

    console.log(`[${obterHoraFormatada()}] CoreUIManager: Disparando atualização da UI...`);
    // Busca os dados mais recentes que já estão no IndexedDB
    const userData = await getUserById(activeUserId);

    if (userData) {
        // Re-dispara o evento que os dashboards e outras páginas estão ouvindo
        document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
    }
}

export { userDataPromise, initializeCoreUI, triggerUIRefresh };