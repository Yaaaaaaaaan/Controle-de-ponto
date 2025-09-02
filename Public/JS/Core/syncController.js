// ==========================
// 📁 syncController.js
// ==========================
import {withApiHandler ,showToast} from '../Cogs/utils.js';

import { getSyncQueue, clearSyncQueue, obterHoraFormatada } from '../indexedDB/Model.js';
// Não precisamos mais do syncServerToIndexedDB daqui, pois a sincronização de "volta"
// será feita dentro do próprio processSyncQueue.

async function processSyncQueue() {
    // A lógica interna de processSyncQueue continua a mesma...
    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!userToken || !activeUserId) { return; }

    const actions = await getSyncQueue();
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
            console.log(`[${obterHoraFormatada()}] syncController: Fila limpa com sucesso.`);

            // Opcional, mas recomendado: Sincronizar os dados de volta para garantir consistência.
            // A sua API sync.php já retorna os dados atualizados, podemos usá-los para atualizar o IndexedDB
            // ou simplesmente chamar uma função de sincronização geral.
            // Para simplicidade, vamos apenas recarregar a página, que já dispara uma nova sincronização.

            showToast("Suas ações offline foram sincronizadas com sucesso!", 'info');
            window.location.reload();
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
    // ATENÇÃO: A LÓGICA DE INICIALIZAÇÃO MUDOU!

    // 1. Removemos o listener antigo do navegador.
    // window.removeEventListener('online', processSyncQueue);

    // 2. Adicionamos o listener para o nosso novo evento personalizado.
    window.addEventListener('app:online', processSyncQueue);

    console.log(`[${obterHoraFormatada()}] SyncController agora está ouvindo pelo evento 'app:online'.`);

    // 3. A tentativa inicial de sincronização agora é responsabilidade do connectionChecker.
    // Ele fará a primeira verificação no carregamento e, se estiver online, disparará o evento.
}

export { initializeSyncController };