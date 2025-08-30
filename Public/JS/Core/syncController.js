// No ficheiro: /controle-de-ponto/Public/JS/Core/syncController.js (VERSÃO CORRIGIDA)

import { getSyncQueue, clearSyncQueue, syncServerToIndexedDB, obterHoraFormatada} from '../indexedDB/Model.js';


async function processSyncQueue() {
    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!userToken || !activeUserId) { return; }

    const actions = await getSyncQueue();
    // Se a fila estiver vazia, não há nada para enviar.
    // Em produção, você pode querer adicionar uma lógica para sincronizar
    // periodicamente mesmo com a fila vazia para buscar atualizações.
    if (actions.length === 0) {
        console.log(`[${obterHoraFormatada()}] syncController: Fila de sincronização vazia.`);
        return;
    }

    console.log(`[${obterHoraFormatada()}] syncController: A enviar ${actions.length} ações para o servidor...`);

    try {
        const response = await fetch('/Public/Api/sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken, actions })
        });

        if (!response.ok) throw new Error(`[${obterHoraFormatada()}] Erro do servidor: ${response.status}`);

        const result = await response.json();

        if (result.success) {
            await clearSyncQueue();

            // --- PROCESSAR OS DADOS DE "VOLTA" ---
            if (result.userData) {
                // A função upsertUser inteligente já preserva o hash offline
                await upsertUser(result.userData);
            }
            if (result.pointControlData) {
                await replaceUserPointControl(activeUserId, result.pointControlData);
            }

            alert("Suas ações offline foram sincronizadas com sucesso!");
            window.location.reload(); // Recarrega para exibir os dados mais recentes
        } else {
            console.error(`[${obterHoraFormatada()}] A sincronização falhou:`, result.message);
        }

    } catch (error) {
        console.error(`[${obterHoraFormatada()}] syncController: Erro de rede ou JSON inválido ao tentar sincronizar fila:`, error);
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
