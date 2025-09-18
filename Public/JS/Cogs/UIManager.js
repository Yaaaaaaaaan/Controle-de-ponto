// ========================
// 📁 UIManager.js
// ========================

import { upsertUser, getUserById, fetchUserDataByToken, obterHoraFormatada } from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js';
import { initializeThemeFromLocalData } from './ThemeManager.js';

// A promessa para outros módulos esperarem pelos dados
let userDataPromiseResolver;
export const userDataPromise = new Promise(resolve => {
    userDataPromiseResolver = resolve;
});

function updateSharedUI(userData) {
    if (!userData) return;
    const srcImage = userData.profileUser || '/Public/assets/img/userProfileImages/Profile.png';
    const pPicture = document.getElementById('pPicture');
    if (pPicture) pPicture.src = srcImage;
    const welcomeMessage = document.getElementById('responseNameCurto');
    if (welcomeMessage) welcomeMessage.textContent = `Olá, ${userData.name.split(' ')[0]}`;
}

// --- FUNÇÃO initializeUI REATORADA E SIMPLIFICADA ---
export async function initializeUI() {
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    const localToken = localStorage.getItem('userToken');

    // 1. Guardião de autenticação (continua igual)
    if (!activeUserId || !localToken) {
        if (!window.location.pathname.includes('/Index/')) {
            window.location.href = '/Public/View/Index/';
        }
        return;
    }

    let userData = null;
    try {
        // 2. ESTRATÉGIA OFFLINE-FIRST: Tenta carregar os dados do IndexedDB primeiro.
        // Isto fornece uma UI instantânea, melhorando a performance percebida.
        userData = await getUserById(activeUserId);

        // 3. Se estiver online, tenta buscar dados frescos do servidor para atualizar.
        if (isOnline) {
            const freshUserData = await fetchUserDataByToken(localToken);
            // Se a busca no servidor for bem-sucedida, usa os dados frescos.
            // Se falhar (retornar null), mantém os dados locais que já carregamos.
            if (freshUserData) {
                userData = freshUserData;
            }
        }
    } catch (error) {
        console.warn(`[${obterHoraFormatada()}] UIManager: Falha na comunicação com o servidor. A usar dados locais, se disponíveis. Erro:`, error);
        // Não é preciso fazer nada aqui, pois a variável 'userData' já contém os dados do IndexedDB (se existirem).
    }

    // 4. BLOCO DE DECISÃO FINAL - SEM DUPLICAÇÃO
    if (userData) {
        // Lógica de sucesso (executada apenas UMA VEZ)
        console.log(`[${obterHoraFormatada()}] UIManager: Dados carregados.`, userData);

        initializeThemeFromLocalData(userData);
        updateSharedUI(userData);

        const userRank = userData.rank ?? 2; // Assume rank 2 (usuário) como padrão
        document.body.dataset.userType = userRank == 1 ? 'hkg' : 'usr';

        // Resolve a promessa e dispara o evento apenas UMA VEZ
        userDataPromiseResolver(userData);
        document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
    } else {
        // Lógica de falha crítica (se não encontrou dados em nenhum lugar)
        console.error(`[${obterHoraFormatada()}] UIManager: Falha crítica, não foi possível carregar dados do servidor ou do IndexedDB.`);
        localStorage.clear();
        window.location.href = '/Public/View/Index/';
    }
}

export async function triggerSharedUIRefresh(freshUserData = null) {
    let userData = freshUserData;
    if (!userData) {
        const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
        if (!activeUserId) return;
        userData = await getUserById(activeUserId);
    }
    if (userData) {
        updateSharedUI(userData);
        document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
    }
}