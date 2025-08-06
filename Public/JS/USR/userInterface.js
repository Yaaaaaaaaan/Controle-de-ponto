/*import { processUserData, checkForUserDataUpdates, lastProfilePictures } from '../JS/localStorage.js'; // Ajuste o caminho*/
import { upsertUser, processUserData, syncIndexedDBToServer, syncServerToIndexedDB, getAllUsers } from '../indexedDB/Model.js';

let isFirstLoad = true;

let userDataPromiseResolver;
const userDataPromise = new Promise(resolve => {
    userDataPromiseResolver = resolve;
});

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

    // Avisa a todos os outros scripts na página que os dados do usuário estão prontos.
    if (isFirstLoad) {
        console.log('Primeira carga: Disparando evento "userDataReady" com userId:', userData.userId);
        document.dispatchEvent(new CustomEvent('userDataReady', { detail: { userId: userData.userId } }));
        isFirstLoad = false; // Desativa o gatilho para as próximas vezes
    }
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

// --- PONTO DE ENTRADA DO SCRIPT ---
// Carrega os dados uma vez e "resolve" a promessa para notificar os outros scripts.
async function initializeUserData() {
    let userData = null;
    try {
        // 1. Tenta pegar os dados do servidor primeiro, pois são os mais recentes.
        userData = await processUserData();

        // 2. Se falhar (offline), tenta pegar do banco de dados local.
        if (!userData) {
            console.log("userInterface.js: Não foi possível obter dados do servidor, tentando IndexedDB...");
            const localUsers = await getAllUsers();
            if (localUsers && localUsers.length > 0) {
                userData = localUsers[0];
            }
        }

        // 3. Se finalmente temos os dados, atualiza a UI e "resolve" a promessa.
        if (userData) {
            updateUI(userData);
            userDataPromiseResolver(userData); // "AVISA" a todos que estão esperando.
        } else {
            // Rejeita a promessa se nenhum dado for encontrado
            userDataPromiseResolver(null);
            throw new Error("Dados do usuário não encontrados no servidor ou localmente.");
        }

    } catch (error) {
        console.error("Erro crítico ao inicializar a interface do usuário:", error);
        userDataPromiseResolver(null); // Notifica outros scripts sobre a falha.
    }
}

export function updateUI(userData) {
    if (!userData || !userData.userId) {
        console.warn("updateUI: Dados de usuário inválidos ou userId ausente.", userData);
        return;
    }
    const elements = {
        'responseName': userData.name,
        'responseNameCurto': `Olá, ${userData.name.split(' ')[0]}`,
        'responseEmail': userData.email,
    };
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });

    // Atualiza a imagem de perfil
    if (userData.profileUser) {
        const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
        const srcImage = imageBasePath + String(userData.profileUser).replace(/"/g, '');
        const picElement = document.getElementById('pPicture');
        if (picElement) picElement.src = srcImage;
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', initializeUserData, async () => {

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
    updateUIElements,
    userDataPromise
};