// ========================
// 📁 authController.js
// ========================

async function isOnline() {
    try {
        const url = '/controle-de-ponto/Public/system/healthcheck.php'; // URL alterada
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

document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const nickname = document.getElementById("nickname").value;
    const password = document.getElementById("password").value;

    console.log("Iniciando verificação online/offline...");
    const online = navigator.onLine && await isOnline();
    console.log("Resultado da verificação online:", online);

    if (!online) {
        console.log("Modo Offline: Autenticando com IndexedDB");
        // LOGIN OFFLINE via IndexedDB
        const userTokenExists = await checkUserTokenExists(nickname);
        if (userTokenExists) {
            console.log("Token encontrado. Redirecionando...");
            window.location.href = "../User/index.php";
        } else {
            alert("Usuário não autenticado offline ou token expirado.");
        }
    } else {
        console.log("Modo Online: Submetendo formulário para PHP");
        // LOGIN ONLINE
        const form = document.getElementById("loginForm");
        form.method = 'post';
        form.submit(); // Submete o formulário para o index.php
        // **NÃO FAZ NENHUMA MANIPULAÇÃO ADICIONAL AQUI**
    }
});

async function checkUserTokenExists(nickname) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open("PCDB", 1);
        request.onerror = () => reject("Erro ao abrir IndexedDB");
        request.onsuccess = () => {
            const db = request.result;
            const tx = db.transaction("userData", "readonly");
            const store = tx.objectStore("userData");
            const getReq = store.get(nickname);

            getReq.onsuccess = () => {
                const user = getReq.result;
                resolve(user && user.userToken);
            };
            getReq.onerror = () => reject("Erro ao buscar usuário");
        };
    });
}

async function getUserByNickname(nickname) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open("PCDB", 1);
        request.onerror = () => reject("Erro ao abrir IndexedDB");
        request.onsuccess = () => {
            const db = request.result;
            const tx = db.transaction("userData", "readonly");
            const store = tx.objectStore("userData");
            const getReq = store.get(nickname);

            getReq.onsuccess = () => resolve(getReq.result);
            getReq.onerror = () => reject("Erro ao buscar usuário");
        };
    });
}

// Ouvintes de eventos para mudanças de conexão
window.addEventListener('online', () => {
    console.log('Navegador voltou a ficar online');
});

window.addEventListener('offline', () => {
    console.log('Navegador está offline');
});