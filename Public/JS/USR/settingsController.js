// ==========================
// 📁 settingsController.js
// ==========================
import {
    upsertUser,
    getUserById,
    addActionToSyncQueue,
    fetchUserDataByToken,
    obterHoraFormatada,
    getOfflineUserPictures,
    addHistoryEntry,
    getLocalHistory,
    replaceUserHistory
} from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js'; // Usaremos nosso verificador de conexão
import { userDataPromise, triggerSharedUIRefresh } from '../Cogs/UIManager.js';
import {displayFeedback, withApiHandler, showToast, getCurrentPosition} from '../Cogs/utils.js'; //withApiHandler é uma introdução ao AOP (programação orientada a aspectos)
import { applyTheme, initializeThemeFromLocalData } from '../Cogs/ThemeManager.js';

let confirmationModal;
let profilePhotoModal;

function getFormData() {
    const form = document.getElementById('formUserData');
    const email = document.getElementById('floatingInputEmail')?.value?.trim() || '';
    const nickname = document.getElementById('floatingInputNickname')?.value?.trim() || '';
    const name = document.getElementById('floatingInputName')?.value?.trim() || '';
    const defaultTheme = document.getElementById('themeSwitch')?.checked ? 1 : 0;

    const oldPassword = document.getElementById('floatingInputCurrentPassword')?.value || '';
    const newPassword = document.getElementById('floatingInputNewPassword')?.value || '';
    const confirmPassword = document.getElementById('floatingInputConfirmNewPassword')?.value || '';

    return { email, nickname, name, defaultTheme, oldPassword, newPassword, confirmPassword };
}

function validatePasswords(newPassword, confirmPassword, oldPassword) {
    if (!newPassword && !confirmPassword) return { ok: true };
    if (!oldPassword) return { ok: false, msg: 'Informe a senha atual.' };
    if (newPassword !== confirmPassword) return { ok: false, msg: 'A nova senha e a confirmação não coincidem.' };
    // Regras básicas (ex.: ao menos 1 maiúscula e 1 dígito)
    if (!/[A-Z]/.test(newPassword) || !/\d/.test(newPassword)) {
        return { ok: false, msg: 'A nova senha precisa de pelo menos 1 letra maiúscula e 1 número.' };
    }
    return { ok: true };
}

// Esta função agora só se preocupa com a comunicação e os dados.
async function submitSettingsOnline(payload) {
    const userToken = localStorage.getItem('userToken');
    const resp = await fetch('/Public/Api/userSettings.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${userToken}`
        },
        body: JSON.stringify(payload)
    });

    if (!resp.ok) {
        const errorData = await resp.json().catch(() => ({ message: 'Erro desconhecido do servidor.' }));
        throw new Error(errorData.message || `HTTP ${resp.status}`);
    }

    const data = await resp.json();
    if (!data?.success) throw new Error(data?.message || 'Falha ao atualizar usuário');

    // Após o sucesso, busca os dados frescos para atualizar o IndexedDB
    await fetchUserDataByToken(userToken);
}

// Função para submeter offline
async function submitSettingsOffline(actionPayload, localPatch) {
    const coords = await getCurrentPosition();
    await addActionToSyncQueue({ type: 'UPDATE_PROFILE', payload: actionPayload, timestamp: new Date().toISOString(), latitude: coords?.latitude, longitude: coords?.longitude });
    const activeUserId = parseInt(localStorage.getItem('activeUserId') || '0', 10);
    if (activeUserId) {
        const currentUser = await getUserById(activeUserId);
        if(currentUser) {
            await upsertUser({ ...currentUser, ...localPatch });
        }
    }
}

// Função principal que é chamada no evento de submit
async function onFormSubmit(ev) {
    ev.preventDefault();
    const button = ev.submitter;
    // --- INÍCIO DA NOVA LÓGICA DE VALIDAÇÃO ---

    const coords = await getCurrentPosition();
    // 1. Coleta os dados do formulário
    const { email, nickname, name, defaultTheme, oldPassword, newPassword, confirmPassword } = getFormData();

    // 2. Busca os dados atuais do usuário no IndexedDB para comparação
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId) {
        showToast('Sessão de usuário inválida.', 'error');
        return;
    }
    const currentUser = await getUserById(activeUserId);

    // 3. Verifica se houve alguma alteração nos dados do perfil
    const profileDataChanged = currentUser.name !== name ||
        currentUser.email !== email ||
        currentUser.nickname !== nickname ||
        currentUser.theme !== defaultTheme;

    // 4. Verifica se o usuário está tentando alterar a senha
    const isPasswordChangeAttempt = oldPassword || newPassword || confirmPassword;

    // 5. Se NADA mudou, interrompe a execução
    if (!profileDataChanged && !isPasswordChangeAttempt) {
        showToast('Nenhuma alteração detectada.', 'info');
        // Reabilita o botão, pois o AOP não será chamado
        button.disabled = false;
        button.textContent = 'Salvar alterações';
        return;
    }

    // --- FIM DA NOVA LÓGICA DE VALIDAÇÃO ---

    // 6. A validação de senha existente continua, mas agora só roda se houver uma tentativa de mudança
    if (isPasswordChangeAttempt) {
        const pwdValidation = validatePasswords(newPassword, confirmPassword, oldPassword);
        if (!pwdValidation.ok) {
            showToast(pwdValidation.msg, 'error');
            return;
        }
    }

    // 7. Monta o payload SOMENTE com os dados que serão enviados
    const payload = { email, nickname, name, defaultTheme, timestamp: new Date().toISOString(), latitude: coords?.latitude, longitude: coords?.longitude };
    if (isPasswordChangeAttempt) {
        payload.passwordChange = { oldPassword, newPassword };
    }

    // 8. O fluxo online/offline continua como antes
    if (isOnline) {
        payload.obs = 'online';
        const handleApiSubmit = withApiHandler(submitSettingsOnline, { button });
        const success = await handleApiSubmit(payload);
        if (success) {
            // ADICIONA HISTÓRICO "ONLINE"
            await addHistoryEntry({
                userId: activeUserId,
                description: 'Informações de perfil atualizadas. | Origem: ' + payload.obs,
                timestamp: payload.timestamp,
                syncStatus: 'online'
            });
            document.dispatchEvent(new CustomEvent('historyShouldRefresh'));
            await triggerSharedUIRefresh();
        }
    } else {
        payload.obs = 'offline';
        const localPatch = { name, email, nickname, defaultTheme };
        await submitSettingsOffline(payload, localPatch);
        // ADICIONA HISTÓRICO "OFFLINE"
        await addHistoryEntry({
            userId: activeUserId,
            description: 'Informações de perfil salvas offline.',
            timestamp: payload.timestamp,
            syncStatus: 'offline'
        });
        document.dispatchEvent(new CustomEvent('historyShouldRefresh'));
        showToast('Alterações salvas offline. Sincronizando em breve.', 'info');
        await triggerSharedUIRefresh();
    }
}

async function fetchAndDisplayUserPictures() {
    const photoGallery = document.getElementById('photoGallery');
    if (!photoGallery) return;
    photoGallery.innerHTML = '<div class="spinner-border text-primary mx-auto" role="status"></div>';

    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    let pictures = [];

    try {
        if (isOnline) {
            console.log(obterHoraFormatada()," Modo Online: Buscando fotos da API.");
            const response = await fetch('/Public/Api/userPictures.php', {
                headers: { 'Authorization': `Bearer ${userToken}` }
            });

            // --- A CORREÇÃO ESTÁ AQUI ---
            // Precisamos processar a resposta e colocá-la na variável 'result'
            const result = await response.json();

            if (result.success) {
                pictures = result.pictures;
            } else {
                // Se a API retornar um erro, exibimos a mensagem
                throw new Error(result.message || 'Falha ao buscar fotos da API.');
            }
        } else {
            console.log(obterHoraFormatada()," Modo Offline: Buscando fotos do IndexedDB.");
            pictures = await getOfflineUserPictures(activeUserId);
        }

        photoGallery.innerHTML = '';
        if (pictures && pictures.length > 0) {
            // A sua lógica para exibir as fotos (forEach, etc.) está correta e continua aqui
            pictures.forEach(pic => {
                const col = document.createElement('div');
                col.className = 'col-4';
                const a = document.createElement('a');
                a.href = '#';
                a.title = `Definir "${pic.name}" como perfil`;
                a.onclick = (e) => {
                    e.preventDefault();
                    const confirmBtn = document.getElementById('confirmActionBtn');
                    confirmBtn.dataset.photoId = pic.picId;
                    confirmationModal.show();
                };
                const img = document.createElement('img');
                img.src = pic.path;
                img.alt = pic.name;
                img.className = `img-fluid rounded img-thumbnail ${pic.isProfile == 1 ? 'border-primary border-3' : ''}`;
                a.appendChild(img);
                col.appendChild(a);
                photoGallery.appendChild(col);
            });
        } else {
            photoGallery.textContent = 'Nenhuma foto encontrada.';
        }
    } catch (error) {
        photoGallery.textContent = 'Erro ao carregar fotos.';
        console.error(obterHoraFormatada()," Erro ao buscar fotos:", error);
    }
}

/**
 * Envia um novo arquivo de imagem para a API de upload.
 * @param {File} file - O arquivo de imagem a ser enviado.
 */
async function uploadNewPicture(file, button) { // Passa o botão como argumento
    const coords = await getCurrentPosition();
    const userToken = localStorage.getItem('userToken');
    const formData = new FormData();
    formData.append('picture', file);
    if (coords) {
        formData.append('latitude', coords.latitude);
        formData.append('longitude', coords.longitude);
    }

    // A função de negócio pura
    const doUpload = async () => {
        const response = await fetch('/Public/Api/uploadPicture.php', {
            method: 'POST', headers: { 'Authorization': `Bearer ${userToken}` }, body: formData
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
    };

    // Aplica o aspecto AOP
    const handleUpload = withApiHandler(doUpload, { button });
    const success = await handleUpload();

    // Ações pós-sucesso
    if (success) {
        const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
        // Adiciona o histórico e dispara o evento
        await addHistoryEntry({ userId: activeUserId, description: `Upload de nova foto: ${file.name}`, timestamp: new Date().toISOString(), syncStatus: 'online'});
        document.dispatchEvent(new CustomEvent('historyShouldRefresh'));

        await triggerSharedUIRefresh();
        await fetchAndDisplayUserPictures();
        const profilePhotoModalEl = document.getElementById('profilePhotoModal');
        const profilePhotoModal = bootstrap.Modal.getInstance(profilePhotoModalEl);
        if (profilePhotoModal) profilePhotoModal.hide();
    }
}

/**
 * Chama a API para definir uma foto existente como a de perfil.
 * @param {number} photoId - O ID da foto a ser definida como perfil.
 */
async function setAsProfilePicture(photoId) {
    const userToken = localStorage.getItem('userToken');
    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!userToken || !activeUserId) return;

    try {
        const coords = await getCurrentPosition();
        const response = await fetch('/Public/Api/setProfilePicture.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${userToken}` },
            body: JSON.stringify({
                photoId,
                latitude: coords?.latitude,
                longitude: coords?.longitude})
        });
        const result = await response.json();

        if (result.success) {
            showToast('Foto de perfil atualizada!', 'success');

            await addHistoryEntry({
                userId: activeUserId,
                description:  `Definiu nova foto de perfil (ID da Foto: ${photoId})`,
                timestamp: new Date().toISOString(),
                syncStatus: 'online'
            })
            document.dispatchEvent(new CustomEvent('historyShouldRefresh'));

            await triggerSharedUIRefresh();
            await fetchAndDisplayUserPictures();
            if (profilePhotoModal) profilePhotoModal.hide();

        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(`Erro: ${error.message}`, 'error');
        console.error(obterHoraFormatada()," Erro ao definir foto:", error);
    }
}

async function handleThemeChange(event) {
    const newThemeValue = event.target.checked ? 1 : 0;
    applyTheme(newThemeValue); // Aplica a mudança visual imediatamente

    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);
    if (!activeUserId) return;

    if (isOnline) {
        try {
            const userToken = localStorage.getItem('userToken');
            const response = await fetch('/Public/Api/updateTheme.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${userToken}` },
                body: JSON.stringify({ theme: newThemeValue })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            // Atualiza o IndexedDB com os dados frescos do servidor
            await fetchUserDataByToken(userToken);
            console.log(obterHoraFormatada()," Preferência de tema salva no servidor.");
        } catch (error) {
            console.error(obterHoraFormatada()," Falha ao salvar tema no servidor:", error);
            // Opcional: Reverter a UI ou mostrar erro
        }
    } else {
        console.log(obterHoraFormatada()," Offline: Ação de mudança de tema adicionada à fila.");
        // Atualiza o IndexedDB localmente
        const currentUser = await getUserById(activeUserId);
        if (currentUser) {
            currentUser.theme = newThemeValue;
            await upsertUser(currentUser);
        }
        // Adiciona a ação à fila para sincronização futura
        await addActionToSyncQueue({ type: 'UPDATE_THEME', payload: { theme: newThemeValue }, timestamp: new Date().toISOString() });
    }
}

function renderHistory(historyData, tableBody) {
    tableBody.innerHTML = ''; // Limpa a tabela
    if (historyData && historyData.length > 0) {
        historyData.forEach(entry => {
            const row = tableBody.insertRow();
            const date = new Date(entry.timestamp);

            row.insertCell(0).textContent = entry.description;
            row.insertCell(1).textContent = date.toLocaleString('pt-BR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });


            // Adiciona a coluna de status
            const statusCell = row.insertCell(2);
            if (entry.syncStatus === 'offline') {
                statusCell.innerHTML = '<span class="badge bg-secondary">Offline</span>';
            } else {
                statusCell.innerHTML = '<span class="badge bg-success">Online</span>';
            }
        });
    } else {
        tableBody.innerHTML = '<tr><td colspan="3">Nenhum histórico encontrado.</td></tr>';
    }
}

async function fetchAndDisplayHistory(limit = 20, forceSync = false) {
    const historyTableBody = document.getElementById('historyTableBody');
    if (!historyTableBody) return;

    const activeUserId = parseInt(localStorage.getItem('activeUserId'), 10);

    // Função auxiliar para renderizar a partir da fonte de dados local (IndexedDB)
    const renderFromLocal = async (fetchLimit) => {
        try {
            const localHistory = await getLocalHistory(activeUserId, fetchLimit);
            renderHistory(localHistory, historyTableBody);
        } catch (error) {
            console.error(obterHoraFormatada()," Erro ao renderizar histórico local:", error);
            historyTableBody.innerHTML = '<tr><td colspan="3">Falha ao carregar histórico local.</td></tr>';
        }
    };

    // ETAPA 1: Renderiza imediatamente o que quer que esteja no IndexedDB.
    // Isso fornece uma UI instantânea para o usuário, seja online ou offline.
    await renderFromLocal(limit);

    // ETAPA 2: Se estiver online, sincroniza com o servidor.
    if (isOnline && forceSync) {
        try {
            const userToken = localStorage.getItem('userToken');
            // Busca um limite maior quando online para ter a visão completa
            const onlineLimit = 100;
            const response = await fetch(`/Public/Api/userHistory.php?limit=${onlineLimit}`, {
                headers: { 'Authorization': `Bearer ${userToken}` }
            });
            const result = await response.json();

            if (result.success) {
                // Atualiza o IndexedDB com os dados "oficiais" do servidor.
                await replaceUserHistory(activeUserId, result.history);

                // Renderiza novamente a partir do IndexedDB agora atualizado.
                await renderFromLocal(limit);
            }
        } catch (error) {
            console.warn(obterHoraFormatada()," Não foi possível sincronizar o histórico com o servidor. Exibindo dados locais.", error);
            // Se a busca online falhar, não fazemos nada, pois o usuário já está vendo os dados locais.
        }
    }
}

// Função que inicializa o controller
export async function initSettingsController() {
    const form = document.getElementById('formUserData');
    if (form) form.addEventListener('submit', onFormSubmit);

    const savePictureBtn = document.getElementById('savePictureBtn');
    if (savePictureBtn) {
        savePictureBtn.addEventListener('click', () => {
            const fileInput = document.getElementById('inputGroupFile04');
            if (fileInput.files.length > 0) {
                // Passa o próprio botão para o AOP gerenciar
                uploadNewPicture(fileInput.files[0], savePictureBtn);
                fileInput.value = '';
            } else {
                showToast('Por favor, selecione um arquivo.', 'info');
            }
        });
    }

    const profilePhotoModal = document.getElementById('profilePhotoModal');
    if (profilePhotoModal) {
        profilePhotoModal.addEventListener('show.bs.modal', fetchAndDisplayUserPictures);
    }

    const themeSwitch = document.getElementById('themeSwitch');
    if (themeSwitch) {
        themeSwitch.addEventListener('change', handleThemeChange);
    }

    const modalEl = document.getElementById('confirmationModal');
    if(modalEl) confirmationModal = new bootstrap.Modal(modalEl);

    const confirmBtn = document.getElementById('confirmActionBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            const photoIdToSet = confirmBtn.dataset.photoId;
            if (photoIdToSet) {

                // 1. TIRA O FOCO DO BOTÃO
                confirmBtn.blur();

                // 2. FECHA O MODAL DE CONFIRMAÇÃO
                if (confirmationModal) confirmationModal.hide();

                // 3. EXECUTA A AÇÃO PRINCIPAL
                setAsProfilePicture(photoIdToSet);
            }
        });
    }

    const searchHistoryBtn = document.getElementById('searchHistoryBtn');
    const limitInput = document.querySelector('input[name="registro"]');
    if (searchHistoryBtn && limitInput) {
        searchHistoryBtn.addEventListener('click', () => {
            const limit = parseInt(limitInput.value, 10) || 20;
            fetchAndDisplayHistory(limit, false);
        });
    }

    if (limitInput && searchHistoryBtn) {
        limitInput.addEventListener('keydown', (event) => {
            // Verifica se a tecla pressionada foi "Enter"
            if (event.key === 'Enter') {
                // 1. Impede o comportamento padrão (que seria submeter o formulário principal)
                event.preventDefault();

                // 2. Simula um clique no botão "Pesquisar"
                searchHistoryBtn.click();
            }
        });
    }


    document.addEventListener('historyShouldRefresh', () => {
        fetchAndDisplayHistory(limitInput?.value || 20, false);
    });

    await fetchAndDisplayHistory(20, true);
}