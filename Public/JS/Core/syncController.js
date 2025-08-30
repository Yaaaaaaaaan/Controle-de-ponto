// No ficheiro: /controle-de-ponto/Public/JS/Core/syncController.js (VERSÃO CORRIGIDA)

import { getSyncQueue, clearSyncQueue, syncServerToIndexedDB } from '../indexedDB/Model.js';

async function processSyncQueue() {
    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!userToken || !activeUserId) {
        console.log("syncController: Nenhum utilizador ativo para sincronizar.");
        return;
    }

    const actions = await getSyncQueue();
    if (actions.length === 0) {
        // Se a fila estiver vazia, não há nada para enviar.
        // Podemos remover esta verificação se quisermos que a sincronização de "volta" aconteça sempre.
        console.log("syncController: Fila de sincronização vazia.");
        return;
    }

    try {
        // --- AQUI ESTÁ A CORREÇÃO ---
        // Removido o prefixo "/controle-de-ponto"
        const response = await fetch('/Public/Api/sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken, actions })
        });
        // --- FIM DA CORREÇÃO ---

        // A partir daqui, a sua lógica de processamento da resposta JSON já está correta.
        // Vamos remover o código de depuração e voltar à versão funcional.
        if (!response.ok) {
            // Se a resposta não for 2xx, lança um erro para o bloco catch.
            throw new Error(`Erro do servidor: ${response.status} ${response.statusText}`);
        }

        const result = await response.json();

        if (result.success) {
            await clearSyncQueue();

            if (result.userData) {
                await upsertUser(result.userData);
            }
            if (result.pointControlData) {
                await replaceUserPointControl(activeUserId, result.pointControlData);
            }

            alert("Suas ações offline foram sincronizadas com sucesso!");
            window.location.reload();
        } else {
            console.error("A sincronização falhou:", result.message);
        }

    } catch (error) {
        console.error("syncController: Erro de rede ou JSON inválido ao tentar sincronizar fila:", error);
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
