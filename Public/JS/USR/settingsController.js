import {
    upsertUser,
    getUserById,
    addActionToSyncQueue,
    syncServerToIndexedDB,
    obterHoraFormatada
} from '../indexedDB/Model.js';

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

async function populateFormFromIndexedDB() {
    try {
        const activeUserId = parseInt(localStorage.getItem('activeUserId') || '0', 10);
        if (!activeUserId) return;
        const user = await getUserById(activeUserId);
        if (!user) return;

        const emailEl = document.getElementById('floatingInputEmail');
        const nickEl  = document.getElementById('floatingInputNickname');
        const nameEl  = document.getElementById('floatingInputName');
        const themeEl = document.getElementById('themeSwitch');

        if (emailEl) emailEl.value = user.email || '';
        if (nickEl)  nickEl.value  = user.nickname || '';
        if (nameEl)  nameEl.value  = user.name || '';
        if (themeEl) themeEl.checked = !!user.defaultTheme;
    } catch (e) {
        console.warn(`[${obterHoraFormatada()}] settingsController: falha ao popular formulário`, e);
    }
}

async function submitSettingsOnline(payload) {
    const userToken = localStorage.getItem('userToken');
    const resp = await fetch('/Public/Api/user.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${userToken}`
        },
        body: JSON.stringify(payload)
    });

    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const data = await resp.json();
    if (!data?.success) throw new Error(data?.message || 'Falha ao atualizar usuário');

    // Puxa do servidor para garantir consistência
    await syncServerToIndexedDB();
}

async function submitSettingsOffline(actionPayload, localPatch) {
    // Salva ação para sync
    await addActionToSyncQueue({ type: 'UPDATE_PROFILE', payload: actionPayload, timestamp: new Date().toISOString() });

    // Atualiza imediatamente a visão local
    const activeUserId = parseInt(localStorage.getItem('activeUserId') || '0', 10);
    if (activeUserId) {
        const current = await getUserById(activeUserId);
        await upsertUser({ ...current, ...localPatch });
    }
}

async function onFormSubmit(ev) {
    ev.preventDefault();

    const { email, nickname, name, defaultTheme, oldPassword, newPassword, confirmPassword } = getFormData();

    const pwd = validatePasswords(newPassword, confirmPassword, oldPassword);
    if (!pwd.ok) { alert(pwd.msg); return; }

    // Payload para API (só envia o que faz sentido)
    const payload = { email, nickname, name, defaultTheme };
    if (newPassword) payload.passwordChange = { oldPassword, newPassword };

    try {
        if (navigator.onLine) {
            await submitSettingsOnline(payload);
            alert('Alterações feitas com sucesso!');
            location.reload();
        } else {
            await submitSettingsOffline(
                payload,
                // patch local (não gravamos senha localmente)
                { email, nickname, name, defaultTheme }
            );
            alert('Você está offline. Alterações salvas localmente e serão sincronizadas depois.');
        }
    } catch (e) {
        console.error('Erro ao submeter configurações:', e);
        alert('Não foi possível salvar suas alterações.');
    }
}

export function initSettingsController() {
    populateFormFromIndexedDB();
    const form = document.getElementById('formUserData');
    if (form) form.addEventListener('submit', onFormSubmit);
}
