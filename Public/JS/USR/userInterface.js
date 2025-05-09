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
// Configuração do switch de tema
function setupThemeSwitch(userData) {
    const themeSwitch = document.getElementById('themeSwitch');
    if (!themeSwitch) return;

    themeSwitch.checked = userData.theme == 1;
    updateTheme(themeSwitch.checked);

    themeSwitch.addEventListener('click', () => {
        const newTheme = themeSwitch.checked ? 1 : 0;
        updateTheme(themeSwitch.checked);

        // Atualiza a propriedade theme no objeto userData
        userData.theme = newTheme;
        console.log("userData.theme atualizado:", userData.theme);

        // Salva o objeto userData atualizado no localStorage
        localStorage.setItem('userData', JSON.stringify(userData));

        // Envia userToken e theme para o servidor
        sendUserDataToServer(userData.userToken, newTheme);
    });
}

async function sendUserDataToServer(userToken, theme) {
    try {
        const response = await fetch('../../Persistence/userData.php', { // Ajuste o caminho se necessário
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ userToken: userToken, theme: theme }) // Envia userToken e theme
        });

        const data = await response.json();
        if (!data.success) {
            console.error('Erro ao atualizar tema no servidor:', data.message);
        } else {
            console.log('Tema atualizado no servidor com sucesso!');
        }
    } catch (error) {
        console.error('Erro ao comunicar com o servidor:', error);
    }
}

function updateTheme(isDark) {
    document.body.dataset.bsTheme = isDark ? 'dark' : 'light';
}


// Inicialização
document.addEventListener('DOMContentLoaded', async () => {
    const userDataFromStorage = localStorage.getItem('userData');
    let userData;

    if (userDataFromStorage) {
        userData = JSON.parse(userDataFromStorage);
    } else {
        userData = await processUserData();
        if (userData) {
            localStorage.setItem('userData', JSON.stringify(userData)); // Salva userData inicial
        }
    }

    if (userData) {
        updateUIPicture(userData.profileUser);
        updateUIElements(userData);
        updateSettingsForm(userData);
        setupThemeSwitch(userData); // Passa userData para setupThemeSwitch
    }

    await lastProfilePictures();

    // Verifica atualizações periodicamente
    setInterval(async () => {
        const updatedDataFromServer = await checkForUserDataUpdates();
        if (updatedDataFromServer) {
            updateUIPicture(updatedDataFromServer.profileUser);
            updateUIElements(updatedDataFromServer);
            updateSettingsForm(updatedDataFromServer);

            // Atualiza o userData local e no localStorage com os dados do servidor
            localStorage.setItem('userData', JSON.stringify(updatedDataFromServer));
            // Se setupThemeSwitch precisar ser re-inicializado com os novos dados:
            // setupThemeSwitch(updatedDataFromServer);
        }
    }, 5000);
});

// Exporta funções que podem ser necessárias em outros arquivos
export { updateUIPicture, updateUIElements, updateSettingsForm };