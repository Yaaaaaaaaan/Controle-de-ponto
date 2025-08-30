// No ficheiro: userController.js (VERSÃO REFATORADA)

import { addActionToSyncQueue, addPointControlRecordToServer, addPointControl, syncServerToIndexedDB } from '../indexedDB/Model.js';
import { userDataPromise } from './userInterface.js'; // Importa a promessa

async function handleOnlinePresence() {
    console.log("Online: A confirmar presença diretamente no servidor...");
    const success = await addPointControlRecordToServer("Verificação pendente");
    if (success) {
        alert('Presença confirmada com sucesso online!');
        // Sincroniza os dados de "volta" para garantir que a UI é atualizada com todos os dados.
        await syncServerToIndexedDB();
        window.location.reload();
    } else {
        alert('Falha ao confirmar a presença online.');
        // Opcional: Adicionar à fila de sincronização como um fallback se a API falhar.
        const action = { type: 'CREATE_POINT', payload: { status: 'Verificação pendente' }, timestamp: new Date().toISOString() };
        await addActionToSyncQueue(action);
    }
}

async function handleOfflinePresence() {
    console.log("Offline: A registar ponto localmente e a adicionar à fila de sincronização.");
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId || isNaN(activeUserId)) {
        alert("Erro: Sessão de utilizador inválida.");
        return;
    }
    const status = "Verificação pendente";

    // 1. Salva na 'pointControl' para que o utilizador veja a atualização na UI.
    const newPointRecord = { userId: activeUserId, status, dateIn: new Date().toISOString().split('T')[0] };
    await addPointControl(newPointRecord);

    // 2. Salva na 'syncQueue' para ser enviado ao servidor mais tarde.
    const action = { type: 'CREATE_POINT', payload: { status }, timestamp: new Date().toISOString() };
    await addActionToSyncQueue(action);

    alert('Você está offline. A sua presença foi registada.');
    window.location.reload();
}


// --- 3. FUNÇÃO PRINCIPAL SIMPLIFICADA ---
async function handleConfirmPresenceClick(event) {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'A processar...';

    try {
        if (navigator.onLine) {
            await handleOnlinePresence();
        } else {
            await handleOfflinePresence();
        }
    } catch (error) {
        console.error("Erro ao confirmar presença:", error);
        alert('Ocorreu um erro ao confirmar a presença.');
    } finally {
        // Reverte o estado do botão independentemente do resultado.
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