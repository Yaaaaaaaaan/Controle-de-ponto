import { processUserData, checkForUserDataUpdates, lastProfilePictures } from '../localStorage.js';

// Função para atualizar imagem de perfil
function updateUIPicture(profileUser) {
    if (!profileUser) return;

    const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
    const srcImage = imageBasePath + profileUser.replace(/"/g, '');

    ['pPicture', 'pPictureModal'].forEach(id => {
        const element = document.getElementById(id);
        if (element) element.src = srcImage;
    });
}

// Função para atualizar elementos da interface
function updateUIElements(userData) {
    const elements = {
        'responseName': userData.name,
        'responseNameCurto': `Olá, ${userData.name.split(' ')[0]}`,
        'responseUserToken': userData.userToken,
        'responseEmail': userData.email,
        'responseRank': userData.rank,
        'responseNickname': userData.nickname,
        'responseTheme': userData.theme,
        'responseId': userData.id,
        'responseToken': userData.userToken
    };

    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });

    // Atualiza input ID separadamente
    const idInput = document.getElementById('responseIdInput');
    if (idInput) idInput.value = userData.id;

    //TODO: Verificar o responseToken para ser populado no formulário do modal de logout (inicialmente, está em layout/menu).
}

// Função para atualizar formulário de configurações
function updateSettingsForm(userData) {
    if (!window.location.pathname.includes('/User/settings.php')) return;

    const formFields = {
        'floatingInputName': userData.name,
        'floatingInputEmail': userData.email,
        'floatingInputNickname': userData.nickname
    };

    Object.entries(formFields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });

    setupThemeSwitch(userData.theme);
}



// Configuração do switch de tema
/*function setupThemeSwitch(theme) {
    const themeSwitch = document.getElementById('themeSwitch');
    if (!themeSwitch) return;

    themeSwitch.checked = theme == 1;
    updateTheme(themeSwitch.checked);

    themeSwitch.addEventListener('click', () => {
        updateTheme(themeSwitch.checked);
        localStorage.setItem('theme', themeSwitch.checked ? 1 : 0);
    });
}*/

async function sendThemeToServer(theme) {
    try {
        const response = await fetch('../../Persistence/userData.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ theme: theme })
        });

        const data = await response.json();
        if (!data.success) {
            console.error('Erro ao atualizar tema no servidor:', data.message);
        }
    } catch (error) {
        console.error('Erro ao comunicar com o servidor:', error);
    }
}

function setupThemeSwitch() {
    const themeSwitch = document.getElementById('themeSwitch');
    if (!themeSwitch) return;

    let currentTheme = localStorage.getItem('theme');

    if (currentTheme === null) {
        const initialTheme = document.getElementById('responseTheme').textContent;
        currentTheme = initialTheme;
    }

    themeSwitch.checked = currentTheme == 1;
    updateTheme(themeSwitch.checked);

    themeSwitch.addEventListener('click', () => {
        const newTheme = themeSwitch.checked ? 1 : 0;
        updateTheme(themeSwitch.checked);
        localStorage.setItem('theme', newTheme);
        sendThemeToServer(newTheme);
    });
}

function updateTheme(isDark) {
    document.body.dataset.bsTheme = isDark ? 'dark' : 'light';
}


// Inicialização
document.addEventListener('DOMContentLoaded', async () => {
    const userData = await processUserData();
    if (userData) {
        updateUIPicture(userData.profileUser);
        updateUIElements(userData);
        updateSettingsForm(userData);
        setupThemeSwitch();
    }

    await lastProfilePictures();

    // Verifica atualizações periodicamente
    setInterval(async () => {
        const updatedData = await checkForUserDataUpdates();
        if (updatedData) {
            updateUIPicture(updatedData.profileUser);
            updateUIElements(updatedData);
            updateSettingsForm(updatedData);
        }
    }, 5000);
});

// Exporta funções que podem ser necessárias em outros arquivos
export { updateUIPicture, updateUIElements, updateSettingsForm };