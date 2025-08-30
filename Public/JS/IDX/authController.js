// ========================
// 📁 authController.js (Refatorado)
// ========================
import { getUserByNickname, storeAuthData, isTokenValid, getUserTokenByUserId } from '../indexedDB/Model.js';

// --- FUNÇÃO DE LOGIN ONLINE ---
// Tenta autenticar contra o servidor. Se falhar, aciona o fallback para o modo offline.
async function handleOnlineLogin(nickname, password) {
    console.log("Modo Online: Tentando autenticar via API...");
    try {
        const response = await fetch('/Public/Api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nickname, password })
        });

        if (!response.ok) {
            throw new Error(`Falha na resposta do servidor: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.session) {
            // --- AQUI ESTÁ A MUDANÇA ---
            // Desestruturamos a resposta para obter os objetos já separados pelo back-end
            const { userData, tokenData } = result.session;

            // 1. Lógica de SESSÃO (localStorage)
            localStorage.setItem('activeUserId', userData.userId);
            localStorage.setItem('userToken', tokenData.userToken);

            // 2. LÓGICA DE DADOS SEMI-PERSISTENTES (IndexedDB)
            const offlinePasswordHash = await hashPassword(password);

            // 3. Passamos os objetos já separados para o Model.
            await storeAuthData(userData, tokenData, offlinePasswordHash);

            window.location.href = "../User/index.php";
        } else {
            alert(result.message || "Falha na autenticação.");
        }
    } catch (error) {
        console.warn("Falha na comunicação com o servidor. Acionando fallback para modo offline.", error);
        await handleOfflineLogin(nickname, password);
    }
}


// --- FUNÇÃO DE LOGIN OFFLINE ---
// Valida as credenciais do usuário contra os dados salvos localmente no IndexedDB.
async function handleOfflineLogin(nickname, password) {
    console.log("Modo Offline: Autenticando localmente...");

    if (!password) {
        alert("Por favor, digite sua senha para acesso offline.");
        return;
    }

    const user = await getUserByNickname(nickname);
    if (!user || !user.offlinePasswordHash) {
        alert("Usuário não encontrado ou não configurado para acesso offline. Conecte-se à internet para o primeiro login.");
        return;
    }

    const enteredPasswordHash = await hashPassword(password);

    if (enteredPasswordHash === user.offlinePasswordHash) {
        console.log("Autenticação offline bem-sucedida.");

        // --- INÍCIO DA CORREÇÃO ---
        // Buscamos o token que está no IndexedDB.
        const tokenData = await getUserTokenByUserId(user.userId);

        // AQUI ESTÁ A MUDANÇA CRÍTICA:
        // Se a autenticação por senha foi bem-sucedida, nós SEMPRE definimos
        // o token no localStorage para permitir o acesso à aplicação.
        // O token é necessário para saber quem é o utilizador ativo, mesmo que esteja expirado.
        localStorage.setItem('activeUserId', user.userId);

        if (tokenData && tokenData.token) {
            localStorage.setItem('userToken', tokenData.token);
            // Verificamos a validade do token apenas para AVISAR no console.
            if (!await isTokenValid(tokenData.token)) {
                console.warn("Token de sessão offline expirado. A sincronização com o servidor falhará até o próximo login online.");
            }
        } else {
            // Se por algum motivo não houver token, removemo-lo para evitar inconsistências.
            localStorage.removeItem('userToken');
        }

        // Redirecionamos o utilizador para a página principal.
        window.location.href = "../User/index.php";
        // --- FIM DA CORREÇÃO ---

    } else {
        alert("Senha incorreta.");
    }
}

// --- FUNÇÃO PRINCIPAL (HANDLER DO SUBMIT) ---
// Orquestra qual função de login chamar.
async function handleLoginSubmit(e) {
    e.preventDefault();
    const nickname = document.getElementById("nickname").value;
    const password = document.getElementById("password").value;

    if (!nickname) {
        alert("Por favor, preencha o nome de usuário.");
        return;
    }

    if (navigator.onLine) {
        // Se o NAVEGADOR diz que tem rede, tentamos a via online (que tem fallback).
        await handleOnlineLogin(nickname, password);
    } else {
        // Se o NAVEGADOR já sabe que não tem rede, vamos direto para o offline.
        await handleOfflineLogin(nickname, password);
    }
}

// Função de hash (continua a mesma)
async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
}

// --- PONTO DE ENTRADA DO SCRIPT ---
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLoginSubmit);
    }
});