// /Public/JS/Core/syncController.js
import {
    getSyncQueue,
    clearSyncQueue,
    syncServerToIndexedDB,
    obterHoraFormatada
} from '../indexedDB/Model.js';

async function processSyncQueue() {
    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId') || '0', 10);
    if (!userToken || !activeUserId) return;

    const actions = await getSyncQueue();
    if (!Array.isArray(actions) || actions.length === 0) {
        console.log(`[${obterHoraFormatada()}] syncController: Fila de sincronização vazia.`);
        return;
    }

    console.log(`[${obterHoraFormatada()}] syncController: A enviar ${actions.length} ações para o servidor...`);

    try {
        const resp = await fetch('/Public/Api/sync.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${userToken}`
            },
            body: JSON.stringify({ actions })
        });

        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const result = await resp.json();
        if (!result?.success) throw new Error(result?.message || 'Falha na sincronização');

        // Limpa a fila local
        await clearSyncQueue();

        // Baixa do servidor (perfil + pontos) para “voltar” tudo consistente
        await syncServerToIndexedDB();

        console.log(`[${obterHoraFormatada()}] syncController: Fila sincronizada.`);
    } catch (err) {
        console.error(`[${obterHoraFormatada()}] syncController: Erro de rede ou JSON inválido ao tentar sincronizar fila:`, err);
    }
}

function initializeSyncController() {
    window.addEventListener('online', processSyncQueue);
    if (navigator.onLine) processSyncQueue();
}

export { initializeSyncController, processSyncQueue };
