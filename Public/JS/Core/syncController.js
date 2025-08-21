// No ficheiro: /controle-de-ponto/Public/JS/Core/syncController.js (VERSÃO CORRIGIDA)

import { getSyncQueue, clearSyncQueue, syncServerToIndexedDB } from '../indexedDB/Model.js';

async function processSyncQueue() {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        console.log("syncController: Nenhum token encontrado para sincronização da fila.");
        return;
    }

    const actions = await getSyncQueue();
    if (actions.length === 0) return;

    try {
        const response = await fetch('/controle-de-ponto/Public/Api/sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken: userToken, actions: actions })
        });

        const result = await response.json();
        if (result.success) {
            await clearSyncQueue();
            alert("Suas ações offline foram sincronizadas com sucesso!");
            window.location.reload();
        }
    } catch (error) {
        console.error("syncController: Erro de rede ao tentar sincronizar fila:", error);
    }
}

/**
 * Inicializa os listeners e o loop de sincronização.
 */
function initializeSyncController() {
    window.addEventListener('online', processSyncQueue);

    if (navigator.onLine) {
        processSyncQueue();
    }
}

export { initializeSyncController };
