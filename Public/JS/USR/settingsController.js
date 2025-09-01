// ==========================
// 📁 settingsController.js
// ==========================
import {
    upsertUser,
    getUserById,
    addActionToSyncQueue,
    fetchUserDataByToken,
    obterHoraFormatada,
    getOfflineUserPictures
} from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js'; // Usaremos nosso verificador de conexão
import { userDataPromise, triggerUIRefresh } from '../Cogs/UIManager.js';
import { displayFeedback, withApiHandler, showToast } from '../Cogs/utils.js'; //withApiHandler é uma introdução ao AOP (programação orientada a aspectos)
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
    await addActionToSyncQueue({ type: 'UPDATE_PROFILE', payload: actionPayload, timestamp: new Date().toISOString() });
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

    // 1. Coleta e valida os dados (lógica de negócio)
    const { email, nickname, name, defaultTheme, oldPassword, newPassword, confirmPassword } = getFormData();
    const pwdValidation = validatePasswords(newPassword, confirmPassword, oldPassword);
    if (!pwdValidation.ok) {
        displayFeedback('settingsResponse', pwdValidation.msg, 'error');
        return;
    }
    const payload = { email, nickname, name, defaultTheme };
    if (newPassword && oldPassword) {
        payload.passwordChange = { oldPassword, newPassword };
    }

    // 2. Decide entre o caminho online e offline
    if (isOnline) {
        // 3. CRIA A FUNÇÃO "DECORADA" APLICANDO O ASPECTO
        const handleApiSubmit = withApiHandler(submitSettingsOnline, {
            button: button
        });

        // 4. EXECUTA A FUNÇÃO DECORADA
        const success = await handleApiSubmit(payload);
        if (success) {
            await triggerUIRefresh();
        }
    } else {
        // A lógica offline não precisa do handler de API, então continua a mesma
        const localPatch = { email, nickname, name, defaultTheme: defaultTheme ? 1 : 0 };
        await submitSettingsOffline(payload, localPatch);
        displayFeedback('settingsResponse', 'Você está offline. As alterações foram salvas e serão sincronizadas.', 'success');
        await triggerUIRefresh();
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
            console.log("Modo Online: Buscando fotos da API.");
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
            console.log("Modo Offline: Buscando fotos do IndexedDB.");
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
        console.error("Erro ao buscar fotos:", error);
    }
}

/**
 * Envia um novo arquivo de imagem para a API de upload.
 * @param {File} file - O arquivo de imagem a ser enviado.
 */
async function uploadNewPicture(file, button) { // Passa o botão como argumento
    const userToken = localStorage.getItem('userToken');
    const formData = new FormData();
    formData.append('picture', file);

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
        await triggerUIRefresh();
        await fetchAndDisplayUserPictures();
        if (profilePhotoModal) profilePhotoModal.hide();
    }
}

/**
 * Chama a API para definir uma foto existente como a de perfil.
 * @param {number} photoId - O ID da foto a ser definida como perfil.
 */
async function setAsProfilePicture(photoId) {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) return;

    try {
        const response = await fetch('/Public/Api/setProfilePicture.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${userToken}` },
            body: JSON.stringify({ photoId })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Foto de perfil atualizada!', 'success');
            if (profilePhotoModal) profilePhotoModal.hide();
            await fetchUserDataByToken(userToken);
            await triggerUIRefresh();
            await fetchAndDisplayUserPictures();
            if (profilePhotoModal) profilePhotoModal.hide();

        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(`Erro: ${error.message}`, 'error');
        console.error("Erro ao definir foto:", error);
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
            console.log("Preferência de tema salva no servidor.");
        } catch (error) {
            console.error("Falha ao salvar tema no servidor:", error);
            // Opcional: Reverter a UI ou mostrar erro
        }
    } else {
        console.log("Offline: Ação de mudança de tema adicionada à fila.");
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

async function fetchAndDisplayHistory(limit = 20) {
    const historyTableBody = document.getElementById('historyTableBody');
    if (!historyTableBody) return;
    historyTableBody.innerHTML = '<tr><td colspan="2">Buscando histórico...</td></tr>';

    try {
        const userToken = localStorage.getItem('userToken');
        const response = await fetch(`/Public/Api/userHistory.php?limit=${limit}`, {
            headers: { 'Authorization': `Bearer ${userToken}` }
        });
        const result = await response.json();

        if (result.success && result.history.length > 0) {
            historyTableBody.innerHTML = ''; // Limpa a tabela
            result.history.forEach(entry => {
                const row = historyTableBody.insertRow();
                const date = new Date(entry.data_ocorrencia);
                row.insertCell(0).textContent = entry.descricao;
                row.insertCell(1).textContent = date.toLocaleString('pt-BR');
            });
        } else {
            historyTableBody.innerHTML = '<tr><td colspan="2">Nenhum histórico encontrado.</td></tr>';
        }
    } catch (error) {
        console.error("Erro ao buscar histórico:", error);
        historyTableBody.innerHTML = '<tr><td colspan="2">Falha ao carregar histórico.</td></tr>';
    }
}

// Função que inicializa o controller
export function initSettingsController() {
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

    const searchHistoryBtn = document.getElementById('searchHistoryBtn');
    if (searchHistoryBtn) {
        searchHistoryBtn.addEventListener('click', () => {
            const limitInput = document.querySelector('input[name="registro"]');
            const limit = limitInput.value || 20;
            fetchAndDisplayHistory(limit);
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

    const historyAccordion = document.getElementById('collapseThree');
    if (historyAccordion) {
        historyAccordion.addEventListener('show.bs.collapse', () => fetchAndDisplayHistory());
    }

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
}