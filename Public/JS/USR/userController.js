// ========================
// 📁 userController.js
// ========================

import {
    addActionToSyncQueue,
    addPointControlRecordToServer,
    addPointControl,
    getPointControlByUserId,
    fetchUserDataByToken
} from '../indexedDB/Model.js';
import { userDataPromise, triggerUIRefresh } from '../Cogs/UIManager.js';
import { showToast, withApiHandler, UserFacingError, getCurrentPosition } from '../Cogs/utils.js';
import { isOnline } from '../Core/connectionChecker.js';

// --- A LÓGICA DE NEGÓCIO PURA ---
async function doOnlinePresence() {
    const coords = await getCurrentPosition();
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId) throw new Error('Sessão de utilizador inválida.');

    // --- VALIDAÇÃO NO FRONT-END ---
    const today = new Date().toISOString().split('T')[0];
    const localRecords = await getPointControlByUserId(activeUserId);

    // Verifica se já existe um registro para hoje, tanto online quanto offline
    if (localRecords.some(rec => rec.dateIn === today)) {
        throw new UserFacingError('Presença já registrada para hoje.');
    }
    // --- FIM DA VALIDAÇÃO ---

    const success = await addPointControlRecordToServer("Verificação pendente", coords); // Status inicial
    if (!success) {
        throw new UserFacingError('Falha ao registrar o ponto no servidor.');
    }

    const userToken = localStorage.getItem('userToken');
    await fetchUserDataByToken(userToken);
}

async function doOfflinePresence() {
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId) throw new Error('Sessão de utilizador inválida.');

    const coords = await getCurrentPosition();
    const today = new Date().toISOString().split('T')[0];
    const localRecords = await getPointControlByUserId(activeUserId);
    if (localRecords.some(rec => rec.dateIn === today)) {
        // Lança um erro customizado que o AOP pode capturar
        throw new UserFacingError('Presença já registrada para hoje.');
    }
    const status = "Verificação pendente";
    const obs = "offline";
    await addPointControl({ userId: activeUserId, status, dateIn: today, obs });
    await addActionToSyncQueue({ type: 'CREATE_POINT', payload: { status, obs, latitude: coords?.latitude, longitude: coords?.longitude }});
}

// --- O MANIPULADOR DE EVENTO QUE USA AOP ---
async function handleConfirmPresenceClick(event) {
    const button = event.currentTarget;

    if (isOnline) {
        const handleApiPresence = withApiHandler(doOnlinePresence, { button });
        const success = await handleApiPresence();
        if (success) {
            await triggerUIRefresh(); // Atualiza o dashboard sem recarregar
        }
    } else {
        // Para offline, podemos usar o AOP também, mas uma chamada direta é mais clara
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Processando...';
        try {
            await doOfflinePresence();
            showToast('Presença registrada offline!');
            await triggerUIRefresh();
        } catch (error) {
            showToast(`Aviso: ${error.message}`, 'info');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
}

// Lógica pura da API: Invalida o token no servidor.
async function doOnlineLogoutApi() {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) return;

    const response = await fetch('/Public/Api/logout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${userToken}`
        }
    });

    if (!response.ok) {
        console.warn('A invalidação do token no servidor falhou, mas o logout local prosseguirá.');
    }
}

// Lógica de limpeza do cliente: O que acontece no navegador.
function handleClientSideLogout() {
    // ESTA FUNÇÃO APENAS LIMPA O LOCALSTORAGE.
    // O INDEXEDDB PERMANECE INTACTO PARA PERMITIR O LOGIN OFFLINE FUTURO.
    try {
        localStorage.removeItem('activeUserId');
        localStorage.removeItem('userToken');
    } catch (error) {
        console.error("Erro durante a limpeza do logout local:", error);
    } finally {
        window.location.href = '/Public/View/Index/index.php';
    }
}

// O orquestrador principal que substitui sua função de logout existente.
async function handleLogout(event) {
    event.preventDefault();
    const button = event.currentTarget;
    if (button) button.style.pointerEvents = 'none'; // Evita cliques duplos

    if (isOnline) {
        // CAMINHO ONLINE: Tenta invalidar a sessão no servidor primeiro.
        const handleApiLogout = withApiHandler(doOnlineLogoutApi, {
            button,
            onSuccess: () => {
                showToast('Sessão encerrada com sucesso!', 'info');
                setTimeout(handleClientSideLogout, 1000);
            }
        });
        await handleApiLogout();
    } else {
        // CAMINHO OFFLINE: Nenhuma chamada de rede é feita.
        // Apenas executa a limpeza local diretamente.
        showToast('Sessão local encerrada.', 'info');
        handleClientSideLogout();
    }
}

async function handleThemeChange(newTheme) {
    if (isOnline) {
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

export { initializeController };