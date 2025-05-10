/*import { processUserData, checkForUserDataUpdates, lastProfilePictures } from '../JS/localStorage.js'; // Ajuste o caminho*/
import { updateUser, processUserData, syncIndexedDBToServer, syncServerToIndexedDB, getAllUsers } from '../indexedDB/Model.js';


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
    if (!userData || !userData.name) {
        console.error("updateUIElements: Dados de usuário inválidos ou incompletos", userData);
        return;
    }
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

    const idInput = document.getElementById('responseIdInput');
    if (idInput) idInput.value = userData.id;
}

// Função para atualizar formulário de configurações
function updateSettingsForm(userData) {
    if (!window.location.pathname.includes('/User/settings.php')) return;


    const formFields = {
        'settingsName': userData.name,
        'settingsEmail': userData.email,
        'settingsNickname': userData.nickname,
        'settingsRank': userData.rank,
        'settingsId': userData.id,
        'settingsToken': userData.userToken
    };


    Object.entries(formFields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });
}


async function sendUserDataToServer(userData) {
    try {
        const response = await fetch('../../Persistence/userData.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        const data = await response.json();
        if (data.success) {
            console.log('Dados do usuário sincronizado no servidor com sucesso!');
        }
    } catch (error) {
        console.error('userInterface.js - sendUserDataToServer: Erro ao comunicar com o servidor:', error);
    }
}


function updateTheme(isDark) {
    document.body.dataset.bsTheme = isDark ? 'dark' : 'light';
}


// Inicialização
document.addEventListener('DOMContentLoaded', async () => {

    try {
        const localUserData = await getAllUsers(); // Busca dados locais primeiro
        if (localUserData && localUserData.length > 0) {
            updateUIPicture(localUserData[0].profileUser);
            updateUIElements(localUserData[0]);
            updateSettingsForm(localUserData[0]);
        }

        const serverUserData = await processUserData();
        if (serverUserData && serverUserData.name) {
            updateUIPicture(serverUserData.profileUser);
            updateUIElements(serverUserData);
            updateSettingsForm(serverUserData);
        }

        // Verifica atualizações periodicamente (e sincroniza o IndexedDB com o servidor)
        setInterval(async () => {
            try {
                await syncServerToIndexedDB();
                const localData = await getAllUsers();
                if (localData && localData.length > 0) {
                    const { userToken, theme } = localData[0];
                    await syncIndexedDBToServer(userToken, theme);
                }
                const updatedDataFromServer = await getAllUsers(); // Verifique se getAllUsers() retorna os dados no formato esperado
                if (updatedDataFromServer && updatedDataFromServer.length > 0) {
                    updateUIPicture(updatedDataFromServer[0].profileUser);
                    updateUIElements(updatedDataFromServer[0]);
                    updateSettingsForm(updatedDataFromServer[0]);
                }
            } catch (error) {
                //console.error("Erro no loop de atualização:", error);
            }
        }, 5000);
    } catch (error) {
        //console.error("Erro durante a inicialização:", error);
    }
});


// Exporta funções que podem ser necessárias em outros arquivos
export {
    updateUIPicture,
    updateUIElements
};