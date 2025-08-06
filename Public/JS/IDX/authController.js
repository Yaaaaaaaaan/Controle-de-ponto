// ========================
// 📁 authController.js
// ========================
import { getUserByNickname, storeAuthData, isTokenValid } from '../indexedDB/Model.js';

// Manipular envio do formulário de login
async function handleLoginSubmit(e) {
    e.preventDefault();
    const nickname = document.getElementById("nickname").value;
    const password = document.getElementById("password").value;

    if (!nickname) {
        alert("Por favor, preencha o nome de usuário.");
        return;
    }

    if (navigator.onLine) {
        // --- NOVA LÓGICA ONLINE ---
        console.log("Modo Online: Autenticando via API...");
        try {
            const response = await fetch('/controle-de-ponto/Public/Api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname, password })
            });
            const result = await response.json();

            if (result.success && result.userData) {
                await storeAuthData(result.userData);
                localStorage.setItem('activeUserId', result.userData.userId);
                localStorage.setItem('userToken', result.userData.userToken);
                window.location.href = "../User/index.php";
            } else {
                alert(result.message || "Falha na autenticação.");
            }
        } catch (error) {
            console.error("Erro ao tentar fazer login online:", error);
            alert("Ocorreu um erro de comunicação.");
        }
    } else {
        console.log("Modo Offline: Tentando reativar sessão local...");
        if (!password) {
            // Em modo offline, a senha não é necessária para reativar, mas o campo não pode estar vazio
            // para a experiência do usuário. Você pode optar por esconder o campo de senha se estiver offline.
        }

        const user = await getUserByNickname(nickname);
        if (!user) {
            alert("Usuário não encontrado no cache local. Conecte-se à internet para o primeiro login.");
            return;
        }

        // Busca o token associado a este usuário no IndexedDB
        const tokenData = await getUserTokenByUserId(user.userId);

        if (tokenData && await isTokenValid(tokenData.token)) {
            console.log("Sessão offline válida encontrada. Reativando...");
            localStorage.setItem('activeUserId', user.userId);
            localStorage.setItem('userToken', tokenData.token);
            window.location.href = "../User/index.php";
        } else {
            alert("Sua sessão offline expirou. Conecte-se à internet para renová-la.");
        }
    }
}

// --- PONTO DE ENTRADA DO SCRIPT ---
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLoginSubmit);
    }
});