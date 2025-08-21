// No ficheiro: userController.js (VERSÃO REFATORADA)

import { addActionToSyncQueue, addPointControlRecordToServer, syncServerToIndexedDB } from '../indexedDB/Model.js';
import { userDataPromise } from './userInterface.js'; // Importa a promessa

async function handleConfirmPresenceClick(event) {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'Enviando...';

    try {
        if (navigator.onLine) {
            // Lógica ONLINE que estava no index.php
            const success = await addPointControlRecordToServer("Verificação pendente");
            if (success) {
                alert('Presença confirmada com sucesso!');
                await syncServerToIndexedDB(); // Atualiza os dados locais com os do servidor
                window.location.reload(); // Recarrega para redesenhar o gráfico
            } else {
                alert('Falha ao confirmar a presença.');
            }
        } else {
            // Lógica OFFLINE que já estava aqui
            const action = {
                type: 'addPoint',
                payload: { status: 'Verificação pendente' } // Use o mesmo status
            };
            await addActionToSyncQueue(action);
            alert('Você está offline. Sua presença foi registrada e será sincronizada quando houver conexão.');
        }
    } catch (error) {
        console.error("Erro ao confirmar presença:", error);
        alert('Ocorreu um erro ao confirmar a presença.');
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
            await fetch('/controle-de-ponto/Public/Api/logout.php');
        }
    } finally {
        window.location.href = '/controle-de-ponto/Public/View/Index/';
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