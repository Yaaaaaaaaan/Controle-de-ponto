// ==========================
// 📁 authController.js
// ==========================
import { getUserByNickname, storeAuthData, isTokenValid, getUserTokenByUserId } from '../indexedDB/Model.js';
import { displayFeedback, showToast, withApiHandler } from '../Cogs/utils.js';
import { isOnline } from '../Core/connectionChecker.js';


// --- FUNÇÃO DE LOGIN ONLINE ---
// Tenta autenticar contra o servidor. Se falhar, aciona o fallback para o modo offline.
async function doOnlineLogin(nickname, password, button) {
    const originalButtonText = button.textContent;
    button.disabled = true;
    button.textContent = 'Autenticando...';

    try {
        const response = await fetch('/Public/Api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nickname, password })
        });

        const result = await response.json();

        // Se a resposta NÃO for OK (ex: 401 - senha errada), trata como erro
        if (!response.ok) {
            // Usa a mensagem específica vinda da API
            throw new Error(result.message || 'Credenciais inválidas.');
        }

        // Se chegou aqui, o login foi bem-sucedido
        const { userData, tokenData } = result.session;
        localStorage.setItem('activeUserId', userData.userId);
        localStorage.setItem('userToken', tokenData.userToken);

        const offlinePasswordHash = await hashPassword(password);
        await storeAuthData(userData, tokenData, offlinePasswordHash);

        showToast( 'Login bem-sucedido! Redirecionando...', 'success');
        window.location.href = "../User/index.php";

    } catch (error) {
        // Exibe a mensagem de erro específica (ex: "Usuário ou senha inválidos.")
        showToast( error.message, 'error');
        console.warn("Falha na tentativa de login online:", error);
    } finally {
        button.disabled = false;
        button.textContent = originalButtonText;
    }
}

// --- FUNÇÃO DE LOGIN OFFLINE ---
// Valida as credenciais do usuário contra os dados salvos localmente no IndexedDB.
async function handleOfflineLogin(nickname, password) {
    console.log("Modo Offline: Autenticando localmente...");

    if (!password) {
        displayFeedback('responseAction', "Por favor, digite sua senha para acesso offline.", 'error');
        return;
    }

    const user = await getUserByNickname(nickname);
    if (!user || !user.offlinePasswordHash) {
        displayFeedback('responseAction', "Usuário não encontrado ou não configurado para acesso offline.", 'error');
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
        displayFeedback('responseAction', "Senha incorreta.", 'error');
    }
}

// --- FUNÇÃO PRINCIPAL (HANDLER DO SUBMIT) ---
// Orquestra qual função de login chamar.
async function handleLoginSubmit(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const nickname = form.nickname.value;
    const password = form.password.value;

    if (!nickname || !password) {
        showToast( "Usuário e senha são obrigatórios.", 'error');
        return;
    }

    if (isOnline) {
        // Chamamos a função de login diretamente, pois ela agora tem seu próprio handler de UI
        await doOnlineLogin(nickname, password, button);
    } else {
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
export function initializeAuthController() {
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLoginSubmit);
    }
}