// ========================
// 📁 authController.js
// ========================
import { 
    getUserByNickname, 
    isTokenValid, 
    storeAuthData, 
    syncServerToIndexedDB 
} from '../indexedDB/Model.js';

// Verificar se o servidor está online
async function isOnline() {
    try {
        const url = '/controle-de-ponto/Public/system/healthcheck.php';
        console.log("Tentando healthcheck em:", url);
        const response = await fetch(url, {
            method: 'HEAD',
            cache: 'no-cache'
        });
        console.log("Healthcheck response:", response.status);
        return response.status >= 200 && response.status < 300;
    } catch (error) {
        console.error("Erro ao verificar online:", error);
        return false;
    }
}

// Verificar se o usuário já está autenticado no IndexedDB
async function checkOfflineAuthentication() {
    try {
        // Verificar se há dados de sessão no localStorage
        const sessionData = localStorage.getItem('userSession');
        if (!sessionData) return false;

        const { nickname, userToken } = JSON.parse(sessionData);
        if (!nickname || !userToken) return false;

        // Verificar se o usuário existe no IndexedDB
        const user = await getUserByNickname(nickname);
        if (!user) return false;

        // Verificar se o token é válido
        const tokenValid = await isTokenValid(userToken);
        return tokenValid;
    } catch (error) {
        console.error("Erro ao verificar autenticação offline:", error);
        return false;
    }
}

// Processar resposta de autenticação do PHP
async function processAuthResponse() {
    try {
        // Verificar se há uma resposta de autenticação na URL
        const urlParams = new URLSearchParams(window.location.search);
        const authStatus = urlParams.get('auth');

        if (authStatus === 'success') {
            console.log("Autenticação bem-sucedida, sincronizando dados...");

            // Sincronizar dados do servidor para o IndexedDB
            await syncServerToIndexedDB();

            // Redirecionar para a página do usuário
            window.location.href = "../User/index.php";
            return true;
        }

        return false;
    } catch (error) {
        console.error("Erro ao processar resposta de autenticação:", error);
        return false;
    }
}

// Verificar autenticação ao carregar a página
document.addEventListener('DOMContentLoaded', async () => {
    // Verificar se já estamos autenticados offline
    const isAuthenticated = await checkOfflineAuthentication();
    if (isAuthenticated) {
        console.log("Usuário já autenticado offline, redirecionando...");
        window.location.href = "../User/index.php";
        return;
    }

    // Verificar se estamos retornando de uma autenticação bem-sucedida
    const authProcessed = await processAuthResponse();
    if (authProcessed) return;

    // Configurar o formulário de login
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLoginSubmit);
    }
});

// Manipular envio do formulário de login
async function handleLoginSubmit(e) {
    e.preventDefault();

    const nickname = document.getElementById("nickname").value;
    const password = document.getElementById("password").value;

    if (!nickname || !password) {
        alert("Por favor, preencha todos os campos.");
        return;
    }

    console.log("Iniciando verificação online/offline...");
    const online = navigator.onLine && await isOnline();
    console.log("Resultado da verificação online:", online);

    if (!online) {
        console.log("Modo Offline: Autenticando com IndexedDB");
        // LOGIN OFFLINE via IndexedDB
        const user = await getUserByNickname(nickname);
        if (user && user.userToken) {
            const tokenValid = await isTokenValid(user.userToken);
            if (tokenValid) {
                console.log("Token válido encontrado. Redirecionando...");

                // Armazenar dados da sessão no localStorage
                localStorage.setItem('userSession', JSON.stringify({
                    nickname: user.nickname,
                    userToken: user.userToken
                }));

                window.location.href = "../User/index.php";
            } else {
                alert("Token de usuário expirado. É necessário autenticar online.");
            }
        } else {
            alert("Usuário não encontrado ou não autenticado offline.");
        }
    } else {
        console.log("Modo Online: Submetendo formulário para PHP");
        // LOGIN ONLINE
        const form = document.getElementById("loginForm");
        form.method = 'post';
        form.action = window.location.href + '?auth=attempt';
        form.submit();
    }
}

// Ouvintes de eventos para mudanças de conexão
window.addEventListener('online', async () => {
    console.log('Navegador voltou a ficar online');
    // Sincronizar dados quando voltar a ficar online
    const isAuthenticated = await checkOfflineAuthentication();
    if (isAuthenticated) {
        await syncServerToIndexedDB();
    }
});

window.addEventListener('offline', () => {
    console.log('Navegador está offline');
});
