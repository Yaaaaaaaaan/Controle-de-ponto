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

function updateDashboardCard(userData) {
    // Verifica se estamos na página do dashboard
    if (document.body.id !== 'page-dashboard' || !userData) return;

    const elements = {
        'responseName': userData.name,
        'responseNickname': userData.nickname,
        'responseEmail': userData.email,
    };
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element && value) {
            element.textContent = value;
        } else if (element) {
            element.textContent = ''; // Limpa o campo se não houver valor
        }
    });
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
        updateDashboardCard(userData);
    });
}