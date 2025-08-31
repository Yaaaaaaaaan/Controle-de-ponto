// ========================
// 📁 userInterface.js
// ========================

function updateSettingsForm(userData) {
    if (!window.location.pathname.includes('/User/settings.php') || !userData) return;

    const formFields = {
        'floatingInputName': userData.name,
        'floatingInputEmail': userData.email,
        'floatingInputNickname': userData.nickname,
    };
    Object.entries(formFields).forEach(([inputId, value]) => {
        const element = document.getElementById(inputId);
        if (element && value !== null && value !== undefined) {
            element.value = value;
        }
    });
    const themeSwitch = document.getElementById('themeSwitch');
    if (themeSwitch) {
        themeSwitch.checked = userData.theme == 1;
    }
}

/**
 * Inicializa a interface específica para a seção de Usuário (USR).
 */
export function initializeUserInterface() {
    // Ouve o evento disparado pelo CoreUIManager
    document.addEventListener('userDataReady', (event) => {
        const userData = event.detail.userData;
        console.log("Interface USR: Dados recebidos, atualizando componentes específicos.");

        // Executa as funções de UI que SÓ existem na área do usuário
        updateSettingsForm(userData);
        // Adicione outras funções específicas de USR aqui...
    });
}