import { upsertUser, processUserData, syncIndexedDBToServer, syncServerToIndexedDB, getAllUsers, addPointControlRecordToServer , clearObjectStore } from '../indexedDB/Model.js';
import { userDataPromise } from './userInterface.js';

// Necessário revisar e refatorar toda lógica aqui.

async function getPointControlDataForDashboard() {
    try {
        const pointControlData = await getAllPointControlData();
        // Manipular os dados para o formato necessário para o dashboard
        const processedData = processPointControlData(pointControlData);
        return processedData;
    } catch (error) {
        console.error("Erro ao obter dados de pointControl para o dashboard:", error);
        return [];
    }
}

async function handleConfirmPresenceClick(event) {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'Enviando...';

    try {
        const success = await addPointControlRecordToServer("Confirmado");
        if (success) {
            //alert('Presença confirmada com sucesso!');
            await syncServerToIndexedDB(); // Sincroniza para garantir que o IndexedDB local tenha o novo registro.
            window.location.reload(); // Recarrega a página para o gráfico ser redesenhado.
        } else {
            //alert('Falha ao confirmar a presença. O registro para hoje pode já existir.');
        }
    } catch (error) {
        console.error("Erro ao confirmar presença:", error);
        //alert('Ocorreu um erro. Verifique o console.');
    } finally {
        button.disabled = false;
        button.textContent = 'Confirmar Presença';
    }
}

/**
 * Anexa os event listeners aos elementos da página.
 */
function attachEventListeners() {
    const confirmButton = document.getElementById('confirmPresenceBtn');
    if (confirmButton) {
        confirmButton.addEventListener('click', handleConfirmPresenceClick);
    }
    // Outros listeners (ex: formulário de settings, botões de logout) podem ser adicionados aqui.

    const logoutButton = document.getElementById('logoutBtn');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }
}

async function initializeController() {
    // Pausa a execução até que a promessa do userInterface.js seja resolvida.
    const userData = await userDataPromise;
    if (userData) {
        // Só anexa os listeners se o usuário estiver carregado e logado.
        console.log("userController.js: Dados do usuário prontos. Anexando listeners de eventos.");
        attachEventListeners();
    } else {
        console.warn("userController.js: Dados do usuário não foram carregados, listeners não anexados.");
    }
}

initializeController();
function processPointControlData(data) {
    // Lógica para formatar os dados (agrupar, calcular, etc.)
    // Exemplo:
    const formattedData = data.map(item => ({
        id: item.id,
        description: item.description,
        date: item.dateIn,
    }));
    return formattedData;
}

// Função para atualizar a UI do dashboard com os dados de pointControl
function updateDashboardPointControl(data) {
    const totalRegistrosElement = document.getElementById('totalRegistros');
    if (totalRegistrosElement) {
        totalRegistrosElement.textContent = data.length;
    }
    // ... atualizar outros elementos do dashboard
}

// Função para exibir histórico do usuário

function showHistory(userData) {

}

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
function updateUIElements(userData, pointControl) {
    if (!userData || !userData.name) {
        console.error("updateUIElements: Dados de usuário inválidos ou incompletos", userData);
        return;
    }
   /* if(!pointControl || !pointControl.codigo){
        console.error("updateUIElements: Dados de usuário inválidos ou incompletos", pointControl);
        return;
    }*/
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
async function updateSettingsForm(userData) {
    // Verifique se estamos na página correta
    if (!window.location.pathname.includes('/User/settings.php')) return;

    // Garante que userData tenha dados
    if (!userData) {
        console.warn("updateSettingsForm: userData está vazio. Buscando no IndexedDB...");
        try {
            const allUsers = await getAllUsers(); // Supondo que getAllUsers retorna um array
            if (allUsers && allUsers.length > 0) {
                userData = allUsers[0]; // Pega o primeiro usuário
            } else {
                console.error("updateSettingsForm: Nenhum dado de usuário encontrado no IndexedDB.");
                return;
            }
        } catch (error) {
            console.error("updateSettingsForm: Erro ao buscar dados do IndexedDB:", error);
            return;
        }
    }

    // Define um objeto com os mapeamentos entre IDs do input e propriedades do userData
    const formUserData = {
        'floatingInputName': userData.name,
        'floatingInputEmail': userData.email,
        'floatingInputNickname': userData.nickname,
        //'settingsRank': userData.rank,
        //'settingsId': userData.id,
        //'settingsToken': userData.userToken

    };

    // Itere sobre as entradas do objeto e atualize os valores dos inputs
    Object.entries(formUserData).forEach(([inputId, value]) => {
        const element = document.getElementById(inputId);
        if (element && value !== null && value !== undefined) {
            element.value = value;
        } else if (!element) {
            console.warn(`Input com ID '${inputId}' não encontrado em settings.php`);
        } else {
            console.warn(`Valor para input '${inputId}' é nulo ou indefinido.`);
        }
    });
}


async function sendUserDataToServer(userData) {
    try {
        const response = await fetch('../../Api/userData.php', {
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
        console.error('userController.js - sendUserDataToServer: Erro ao comunicar com o servidor:', error);
    }
}


function updateTheme(isDark) {
    document.body.dataset.bsTheme = isDark ? 'dark' : 'light';
}

async function handleLogout() {
    try {
        console.log("Iniciando processo de logout no cliente...");

        // 1. LIMPA OS DADOS DO NAVEGADOR (Funciona 100% offline)
        await Promise.all([
            clearObjectStore('userData'),
            clearObjectStore('pointControl')
        ]);
        localStorage.clear();
        console.log("Dados locais limpos com sucesso.");

        // 2. ENCERRA A SESSÃO NO SERVIDOR (Apenas se estiver online)
        if (navigator.onLine) {
            console.log("Online. Notificando o servidor para encerrar a sessão...");
            // O JavaScript chama o novo endpoint de logout
            await fetch('/controle-de-ponto/Public/Api/logout.php');
            console.log("Comando de encerramento de sessão enviado ao servidor.");
        }

        // 3. REDIRECIONA O USUÁRIO
        //alert("Você foi desconectado com sucesso.");
        window.location.href = '/controle-de-ponto/Public/View/Index/';

    } catch (error) {
        console.error("Ocorreu um erro durante o logout:", error);
    }
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
                    // const { userToken, theme } = localData[0];
                    // console.log("Sincronização de cliente para servidor desativada temporariamente.");
                    // await syncIndexedDBToServer(userToken, theme);
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
    getPointControlDataForDashboard,
    updateDashboardPointControl,
    updateUIPicture,
    updateUIElements,
    userDataPromise
};