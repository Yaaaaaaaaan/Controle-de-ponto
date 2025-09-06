// ==========================
// 📁 authController.js
// ==========================
import { getUserByNickname, storeAuthData, getUserTokenByUserId, isTokenValid } from '../indexedDB/Model.js';
import { showToast, withApiHandler } from '../Cogs/utils.js';
import { isOnline } from '../Core/connectionChecker.js';

// --- LÓGICA DE NEGÓCIO SEPARADA ---

// 1. Apenas a chamada à API: faz o fetch e retorna os dados ou lança um erro.
async function doLoginApi(nickname, password) {
    const response = await fetch('/Public/Api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nickname, password })
    });

    const result = await response.json();
    if (!response.ok) {
        throw new Error(result.message || 'Credenciais inválidas.');
    }
    return result.session; // Retorna os dados da sessão em caso de sucesso
}

// 2. O que fazer após o sucesso do login online.
async function handleLoginSuccess(session, password) {
    const { userData, tokenData } = session;
    localStorage.setItem('activeUserId', userData.userId);
    localStorage.setItem('userToken', tokenData.userToken);

    const offlinePasswordHash = await hashPassword(password);
    await storeAuthData(userData, tokenData, offlinePasswordHash);

    showToast('Login bem-sucedido! Redirecionando...', 'success');
    setTimeout(() => {
        window.location.href = "../User/index.php";
    }, 1500);
}

// 3. Lógica de login offline (mantida como estava, usando showToast).
async function handleOfflineLogin(nickname, password) {
    console.log("Modo Offline: Autenticando localmente...");

    if (!password) {
        showToast("Por favor, digite sua senha para acesso offline.", 'info');
        return;
    }

    const user = await getUserByNickname(nickname);
    if (!user || !user.offlinePasswordHash) {
        showToast("Usuário não encontrado ou não configurado para acesso offline.", 'error');
        return;
    }

    const enteredPasswordHash = await hashPassword(password);
    if (enteredPasswordHash === user.offlinePasswordHash) {
        console.log("Autenticação offline bem-sucedida.");
        localStorage.setItem('activeUserId', user.userId);
        const tokenData = await getUserTokenByUserId(user.userId);

        if (tokenData && tokenData.token) {
            localStorage.setItem('userToken', tokenData.token);
        } else {
            localStorage.removeItem('userToken');
        }

        showToast('Login offline bem-sucedido! Redirecionando...', 'success');
        setTimeout(() => { window.location.href = "../User/index.php"; }, 1500);

    } else {
        showToast("Senha incorreta.", 'error');
    }
}

// --- ORQUESTRADOR PRINCIPAL ---

// 4. A ÚNICA declaração de handleLoginSubmit.
async function handleLoginSubmit(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const nickname = form.nickname.value;
    const password = form.password.value;

    if (!nickname || !password) {
        showToast("Usuário e senha são obrigatórios.", 'error');
        return;
    }

    if (isOnline) {
        // Usa o AOP, passando a função da API e o callback de sucesso.
        const handleApiLogin = withApiHandler(doLoginApi, {
            button,
            onSuccess: (session) => handleLoginSuccess(session, password)
        });
        await handleApiLogin(nickname, password);
    } else {
        await handleOfflineLogin(nickname, password);
    }
}

// --- FUNÇÕES AUXILIARES ---

async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// --- PONTO DE ENTRADA DO SCRIPT ---
export function initializeAuthController() {
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLoginSubmit);
    }
}