// ==========================
// 📁 settingsController.js
// ==========================
import {
    upsertUser,
    getUserById,
    addActionToSyncQueue,
    fetchUserDataByToken,
    obterHoraFormatada
} from '../indexedDB/Model.js';
import { isOnline } from '../Core/connectionChecker.js'; // Usaremos nosso verificador de conexão
import { userDataPromise, triggerUIRefresh } from '../Cogs/UIManager';
import { displayFeedback, withApiHandler } from '../Cogs/utils.js'; //withApiHandler é uma introdução ao AOP (programação orientada a aspectos)

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
            feedbackId: 'settingsResponse',
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
    photoGallery.innerHTML = '<div class="spinner-border text-primary mx-auto" role="status"><span class="visually-hidden">Carregando...</span></div>';
    const userToken = localStorage.getItem('userToken');
    try {
        const response = await fetch('/Public/Api/userPictures.php', {
            headers: { 'Authorization': `Bearer ${userToken}` }
        });
        const result = await response.json();
        photoGallery.innerHTML = '';
        if (result.success && result.pictures.length > 0) {
            result.pictures.forEach(pic => {
                const col = document.createElement('div');
                col.className = 'col-4';
                const a = document.createElement('a');
                a.href = '#';
                a.title = `Definir "${pic.nome_foto}" como perfil`;
                a.onclick = (e) => {
                    e.preventDefault();
                    if (confirm(`Deseja definir esta imagem como sua foto de perfil?`)) {
                        setAsProfilePicture(pic.foto_id);
                    }
                };
                const img = document.createElement('img');
                img.src = pic.caminho_arquivo;
                img.alt = pic.nome_foto;
                img.className = `img-fluid rounded img-thumbnail ${pic.perfil == 1 ? 'border-primary border-3' : ''}`;
                a.appendChild(img);
                col.appendChild(a);
                photoGallery.appendChild(col);
            });
        } else {
            photoGallery.textContent = 'Nenhuma foto encontrada. Envie uma nova na página de configurações.';
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
async function uploadNewPicture(file) {
    const userToken = localStorage.getItem('userToken');
    const formData = new FormData();
    formData.append('picture', file);

    try {
        const response = await fetch('/Public/Api/uploadPicture.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${userToken}` },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert('Upload realizado com sucesso!');
            await triggerUIRefresh();
            fetchAndDisplayUserPictures(); // Atualiza a galeria com a nova foto
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        alert(`Erro no upload: ${error.message}`);
        console.error("Erro no upload:", error);
    }
}

/**
 * Chama a API para definir uma foto existente como a de perfil.
 * @param {number} photoId - O ID da foto a ser definida como perfil.
 */
async function setAsProfilePicture(photoId) {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        alert('Sessão expirada. Faça login novamente.');
        return;
    }
    try {
        const response = await fetch('/Public/Api/setProfilePicture.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${userToken}` },
            body: JSON.stringify({ photoId })
        });
        const result = await response.json();
        if (result.success) {
            alert('Foto de perfil atualizada!');
            await triggerUIRefresh();

            // Recarrega a galeria para mostrar a nova foto marcada
            fetchAndDisplayUserPictures();
        } else {
            throw new Error(result.message || 'Falha ao definir foto de perfil.');
        }
    } catch (error) {
        alert(`Erro ao definir foto de perfil: ${error.message}`);
        console.error("Erro ao definir foto:", error);
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
                uploadNewPicture(fileInput.files[0]);
                fileInput.value = '';
            } else {
                alert('Por favor, selecione um arquivo.');
            }
        });
    }

    const profilePhotoModal = document.getElementById('profilePhotoModal');
    if (profilePhotoModal) {
        profilePhotoModal.addEventListener('show.bs.modal', fetchAndDisplayUserPictures);
    }
}