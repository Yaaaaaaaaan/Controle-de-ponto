// ========================
// 📁 syncController.js
// ========================
import { getSyncQueue, clearSyncQueue, syncServerToIndexedDB } from '../indexedDB/Model.js';
import { userDataPromise } from '../USR/userInterface.js';
async function processSyncQueue() {
    // Adiciona uma verificação para evitar rodar a sincronização em páginas de login/públicas
    const userData = await Promise.race([
        userDataPromise,
        new Promise(resolve => setTimeout(() => resolve('timeout'), 1000))
    ]);
    if (!userData || userData === 'timeout') {
        console.log("syncController: Nenhum usuário logado detectado. Sincronização não iniciada.");
        return;
    }

    console.log("syncController: Conexão online detectada. Verificando fila de sincronização...");
    const actions = await getSyncQueue();
    if (actions.length === 0) {
        console.log("syncController: Fila de sincronização vazia.");
        return;
    }

    if (!userData.userToken) {
        console.error("syncController: Não foi possível sincronizar. Token do usuário não encontrado.");
        return;
    }

    try {
        const response = await fetch('/controle-de-ponto/Public/Api/sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                userToken: userData.userToken,
                actions: actions
            })
        });

        if (response.status === 401) {
            alert("Não foi possível sincronizar suas ações offline porque sua sessão está muito antiga. Por favor, faça o login novamente para garantir que seus dados sejam salvos.");
            // Opcional: redirecionar para o login
            // window.location.href = '/caminho/para/login';
            return; // Para a execução
        }

        const result = await response.json();

        if (result.success) {
            console.log("syncController: Fila sincronizada com o servidor com sucesso!");
            await clearSyncQueue();
            alert("Suas ações offline foram sincronizadas com sucesso!");
            await syncServerToIndexedDB(); // Força a busca de dados atualizados
            window.location.reload();
        } else {
            console.error("syncController: Falha ao sincronizar com o servidor:", result.message);
        }
    } catch (error) {
        console.error("syncController: Erro de rede ao tentar sincronizar:", error);
    }
}

/**
 * Inicializa os listeners do sync controller.
 */
function initializeSyncController() {
    window.addEventListener('online', processSyncQueue);
    // Verifica a fila uma vez ao carregar a página, caso o usuário já esteja online.
    if (navigator.onLine) {
        processSyncQueue();
    }
}

initializeSyncController();