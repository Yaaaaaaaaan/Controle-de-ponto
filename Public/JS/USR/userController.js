// No ficheiro: userController.js (VERSÃO REFATORADA)

import {
    addActionToSyncQueue,
    addPointControlRecordToServer,
    addPointControl,
    syncServerToIndexedDB,
    getPointControlByUserId
} from '../indexedDB/Model.js';
import { userDataPromise } from './userInterface.js'; // Importa a promessa
import { isOnline } from '../Core/connectionChecker.js';

async function handleOnlinePresence() {
    console.log("Online: A confirmar presença diretamente no servidor...");
    try {
        const success = await addPointControlRecordToServer("Verificação pendente");
        if (success) {
            alert('Presença confirmada com sucesso online!');
            await syncServerToIndexedDB();
            window.location.reload();
        } else {
            alert('Falha ao confirmar a presença online.');
        }
    } catch (error) {
        // ESTE BLOCO É A CHAVE DA SOLUÇÃO:
        // Se o 'fetch' falhar por falta de rede, o erro é capturado aqui.
        console.warn("A tentativa online falhou por erro de rede. Acionando modo offline.", error);
        // Em vez de parar, ele chama a função offline para continuar o processo.
        await handleOfflinePresence();
    }
}

// FUNÇÃO OFFLINE (Com a lógica anti-duplicação)
async function handleOfflinePresence() {
    console.log("Offline: Tentando registrar ponto localmente.");
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId || isNaN(activeUserId)) {
        alert("Erro: Sessão de utilizador inválida.");
        return;
    }

    const today = new Date().toISOString().split('T')[0];
    const localRecords = await getPointControlByUserId(activeUserId);
    const hasRecordForToday = localRecords.some(record => record.dateIn === today);

    if (hasRecordForToday) {
        alert('Você já registou a sua presença hoje no modo offline.');
        return;
    }

    console.log("Nenhum registro local para hoje. Salvando...");
    const status = "Verificação pendente";
    const obs = "offline";

    const newPointRecord = { userId: activeUserId, status, dateIn: today };
    await addPointControl(newPointRecord); // Salva na UI

    const action = { type: 'CREATE_POINT', payload: { status, obs }, timestamp: new Date().toISOString() };
    await addActionToSyncQueue(action); // Salva na fila de sincronização

    alert('Você está offline. A sua presença foi registada.');
    window.location.reload();
}

// FUNÇÃO PRINCIPAL SIMPLIFICADA
async function handleConfirmPresenceClick(event) {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'A processar...';

    try {
        // A lógica é sempre tentar online. Se a rede falhar,
        // a própria handleOnlinePresence se encarrega de chamar a handleOfflinePresence.
        await handleOnlinePresence();
    } catch (error) {
        console.error("Erro inesperado ao confirmar presença:", error);
        alert('Ocorreu um erro inesperado.');
    } finally {
        button.disabled = false;
        button.textContent = 'Confirmar Presença';
    }
}


async function handleLogout(event) {
    event.preventDefault();
    try {
        localStorage.removeItem('activeUserId');
        localStorage.removeItem('userToken');
        if (navigator.onLine) {
            await fetch('/Public/Api/logout.php');
        }
    } finally {
        window.location.href = '/Public/View/Index/';
    }
}

async function handleThemeChange(newTheme) {
    if (navigator.onLine) {
        // Lógica online: Tenta atualizar diretamente no servidor
        const success = await syncThemeToServer(newTheme); // Uma nova função que fala com a API
        if (success) {
            // Se funcionou, atualiza o IndexedDB localmente
            const activeUserId = localStorage.getItem('activeUserId');
            const user = await getUserById(parseInt(activeUserId, 10));
            user.theme = newTheme;
            await upsertUser(user);
        }
    } else {
        // Lógica OFFLINE: Adiciona a ação à fila
        console.log("Offline. Ação de mudança de tema adicionada à fila de sincronização.");
        const action = {
            type: 'updateTheme', // Um novo tipo de ação
            payload: { theme: newTheme },
            timestamp: new Date()
        };
        await addActionToSyncQueue(action);

        // Atualiza a UI imediatamente para o utilizador ver a mudança
        const activeUserId = localStorage.getItem('activeUserId');
        const user = await getUserById(parseInt(activeUserId, 10));
        user.theme = newTheme;
        await upsertUser(user);
        alert("Você está offline. A sua preferência de tema foi guardada e será sincronizada.");
    }
}

/**
 * Anexa os event listeners aos elementos da página.
 */
function attachEventListeners() {
    const confirmButton = document.getElementById('confirmPresenceBtn');
    if (confirmButton) {
        confirmButton.addEventListener('click', handleConfirmPresenceClick);
    }
    const logoutButton = document.getElementById('logoutBtn');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }
    // Adicione outros listeners aqui (ex: formulário de settings)
}

/**
 * Inicializa o controlador, esperando pelos dados do utilizador.
 */
async function initializeController() {
    // Pausa a execução até que a promessa do userInterface.js seja resolvida.
    const userData = await userDataPromise;
    if (userData) {
        console.log("userController.js: Dados do utilizador prontos. Anexando listeners.");
        attachEventListeners();
    } else {
        console.warn("userController.js: Dados do utilizador não carregaram. Listeners não anexados.");
    }
}

// Inicia o controlador.
initializeController();