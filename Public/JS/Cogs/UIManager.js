// ========================
// 📁 UIManager.js
// ========================

import { upsertUser, getUserById, fetchUserDataByToken, obterHoraFormatada } from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js';
import { initializeThemeFromLocalData } from './ThemeManager.js';

let userDataPromiseResolver;
const userDataPromise = new Promise(resolve => {
    userDataPromiseResolver = resolve;
});

function updateSharedUI(userData) {
    if (!userData) return;
    const srcImage = userData.profileUser || '/Public/assets/img/Profile.png';
    const pPicture = document.getElementById('pPicture');
    if (pPicture) pPicture.src = srcImage;
    const welcomeMessage = document.getElementById('responseNameCurto');
    if (welcomeMessage) welcomeMessage.textContent = `Olá, ${userData.name.split(' ')[0]}`;
}

export async function initializeUI() {
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    const localToken = localStorage.getItem('userToken');

    if (!activeUserId || !localToken) {
        if (!window.location.pathname.includes('/Index/')) window.location.href = '/Public/View/Index/';
        return;
    }

    let userData = null;
    try {
        if (isOnline) {
            userData = await fetchUserDataByToken(localToken);
        }
        if (!userData) {
            userData = await getUserById(activeUserId);
        }
        if (userData) {
            console.log(`[${obterHoraFormatada()}] UIManager: Dados carregados.`, userData);
            initializeThemeFromLocalData(userData);
            updateSharedUI(userData);
            userDataPromiseResolver(userData);
            document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));

            const userRank = userData.rank ?? 2; // Assume rank 2 (usuário) como padrão
            document.body.dataset.userType = userRank == 1 ? 'hkg' : 'usr';
            // 2. Dispara o evento que avisa o main.js e outras interfaces
            document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
        } else {
            localStorage.clear();
            window.location.href = '/Public/View/Index/';
        }
    } catch (error) {
        console.warn(`[${obterHoraFormatada()}] UIManager: A busca no servidor falhou, tentando fallback para IndexedDB. Erro:`, error);
        userData = await getUserById(activeUserId);
        if (userData) {
            console.log(`[${obterHoraFormatada()}] UIManager: Dados carregados do IndexedDB (fallback).`, userData);
            initializeThemeFromLocalData(userData);
            updateSharedUI(userData);
            userDataPromiseResolver(userData);
            document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId, userData: userData } }));
        } else {
            console.error(`[${obterHoraFormatada()}] UIManager: Falha crítica, não foi possível carregar dados.`);
            localStorage.clear();
            window.location.href = '/Public/View/Index/';
        }
    }
}

export async function triggerUIRefresh(freshUserData = null) {
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

export { userDataPromise };