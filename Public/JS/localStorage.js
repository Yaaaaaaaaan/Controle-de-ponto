async function processUserData(){
    try{
        const savedUserData = localStorage.getItem("userData");

        if (savedUserData) {
            // Se existem dados no localStorage, use-os diretamente
            console.log("Usando dados do localStorage");
            console.log(savedUserData);
            processUserDataFromString(savedUserData);
            return; // Importante: sai da função sem fazer a requisição
        }

        // Se não existem dados no localStorage, tenta buscar da API
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();
        console.log("Dados recebidos da API:", data);

        const userData = data.userData;

        // Verifica se há dados válidos recebidos da API
        if (Object.keys(userData).length <= 0) {
            console.error("Dados do usuário inválidos ou vazios.");
            return; // Sai da função se não há dados válidos
        }

        // Armazena no localStorage os novos dados da API
        localStorage.setItem('userData', JSON.stringify(userData));
        console.log('Novos dados armazenados no localStorage');
        console.log(userData);
        // Processa os dados recebidos
        processUserDataFromString(localStorage.getItem("userData"));
        // Limpar a sessão depois de armazenar no localStorage
        fetch('../../Persistence/userData.php?clearSession=true')
            .then(response => response.json())
            .then(data => {
                console.log('Sessão limpa:', data.sessionCleared);
            });

    } catch (error) {
        console.error("Erro ao processar dados do usuário:", error);
    }
}

// Função para processar os dados do usuário a partir da string do localStorage
// Função para processar os dados do usuário a partir da string do localStorage
function processUserDataFromString(userDataString) {
    try {
        let UserData = JSON.parse(userDataString);
        console.log("Processando dados do usuário:", UserData); // Depuração

        // IMPORTANTE: Verifica a estrutura dos dados
        if (typeof UserData === 'object' && !Array.isArray(UserData)) {
            // Se os dados são um objeto JavaScript (não um array)
            let name = UserData.name || '';
            let userToken = UserData.userToken || '';
            let email = UserData.email || '';
            let rank = UserData.rank || '';
            let nickname = UserData.nickname || '';
            let theme = UserData.theme || '';
            let id = UserData.id || '';
            let profileUser = UserData.profileUser || '';

            // Manipulação do nome
            let nameCurto = '';
            if (name) {
                nameCurto = "Olá, "+name.indexOf(" ") == -1 ? name : name.substring(0, name.indexOf(" "));
            }

            // Atualiza elementos na página
            updateUIElements(name, userToken, email, rank, nickname, theme, id, nameCurto, profileUser);
        } else {
            // Se os dados são uma string JSON formatada (como no código original)
            let userdata = UserData.split(",");
            let name = (userdata[1]).slice(8, -1);
            let userToken = (userdata[0]).slice(14, -1);
            let email = (userdata[2]).slice(9, -1);
            let rank = (userdata[3]).slice(7);
            let nickname = (userdata[4]).slice(12, -1);
            let theme = (userdata[5]).slice(8);
            let id = (userdata[6]).slice(5);
            let profileUser = (userdata[7]).slice(14, -1);

            // Manipulação do nome
            let nameCurto = '';
            if (name.indexOf(" ") == -1) {
                nameCurto = "Olá, "+name;
            } else {
                nameCurto = "Olá, "+name.substring(0, name.indexOf(" "));
            }

            // Atualiza elementos na página
            updateUIElements(name, userToken, email, rank, nickname, theme, id, nameCurto, profileUser);
        }
    } catch (error) {
        console.error("Erro ao processar string de dados:", error);
        console.error("String que causou o erro:", userDataString);
    }
}

// Função para atualizar elementos da UI
// Função para atualizar elementos da UI
function updateUIElements(name, userToken, email, rank, nickname, theme, id, nameCurto, profileUser) {
    console.log("Atualizando UI com:", { name, email, nickname, nameCurto }); // Depuração

    // Atualiza elementos em outras páginas
    const responseName = document.getElementById("responseName");
    if (responseName) {
        responseName.textContent = name;
    }

    // IMPORTANTE: Atualiza o elemento responseNameCurto que estava faltando
    const responseNameCurto = document.getElementById("responseNameCurto");
    if (responseNameCurto) {
        responseNameCurto.textContent = nameCurto || nickname || name;
        console.log("Nome curto atualizado:", responseNameCurto.textContent); // Depuração
    }

    const responseUserToken = document.getElementById("responseUserToken");
    if (responseUserToken) {
        responseUserToken.textContent = userToken;
    }

    const responseEmail = document.getElementById("responseEmail");
    if (responseEmail) {
        responseEmail.textContent = email;
    }

    const responseRank = document.getElementById("responseRank");
    if (responseRank) {
        responseRank.textContent = rank;
    }

    const responseNickname = document.getElementById("responseNickname");
    if (responseNickname) {
        responseNickname.textContent = nickname;
    }

    const responseTheme = document.getElementById("responseTheme");
    if (responseTheme) {
        responseTheme.textContent = theme;
    }

    const responseId = document.getElementById("responseId");
    if (responseId) {
        responseId.textContent = id;
    }

    // Atualiza campos de formulário se estamos na página de configurações
    if (window.location.href.includes('../View/User/settings.php')) {
        const nameInput = document.getElementById("name");
        const emailInput = document.getElementById("email");
        const nicknameInput = document.getElementById("nickname");

        if (nameInput) nameInput.value = name;
        if (emailInput) emailInput.value = email;
        if (nicknameInput) nicknameInput.value = nickname;
    }

    // Processa a imagem de perfil
    const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
    if (profileUser) {
        const srcImage = imageBasePath + profileUser.replace(/"/g, '');
        const pPicture = document.getElementById("pPicture");
        const pPictureModal = document.getElementById("pPictureModal");
        if (pPicture) {
            pPicture.src = srcImage;
            if (pPictureModal) {
                pPictureModal.src = srcImage;
            }
            console.log("Imagem atualizada:", srcImage); // Depuração
        }
    }
}




async function lastProfilePictures(){
    try{
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();
        const lastProfilePictures = data.lastProfilePictures;

        // Faça algo com as imagens de perfil aqui
        //console.log(lastProfilePictures);
        // Por exemplo, você pode mostrar as imagens em uma galeria
        // ...

    }catch(error){
        console.error('Erro ao buscar imagens de perfil:', error);
    }
}
function checkForUserDataUpdates() {
    fetch('../../Persistence/userData.php?checkUpdates=true')
        .then(response => response.json())
        .then(data => {
            console.log("Verificando atualizações:", data); // Depuração

            if (data.dataUpdated && data.userData) {
                console.log('Dados atualizados na sessão, atualizando localStorage');

                // Importante: verificar se os dados são válidos
                if (typeof data.userData === 'object' && Object.keys(data.userData).length > 0) {
                    // Atualiza o localStorage com os novos dados
                    localStorage.setItem('userData', JSON.stringify(data.userData));
                    console.log("Novo localStorage:", localStorage.getItem('userData')); // Depuração

                    // Atualiza a interface com os novos dados
                    processUserDataFromString(JSON.stringify(data.userData));

                    // Se estivermos na página de configurações, mostra uma notificação
                    /*if (window.location.href.includes('settings.php')) {
                        notifyUserSuccess('Dados atualizados com sucesso');
                    }*/
                } else {
                    console.error("Dados inválidos recebidos da API:", data.userData);
                }
            }
        })
        .catch(error => {
            console.error('Erro ao verificar atualizações:', error);
        });
}

// Função para exibir notificação de sucesso
/*function notifyUserSuccess(message) {
    // Se você tem um elemento para notificações
    const notificationElement = document.getElementById('notificationArea');
    if (notificationElement) {
        notificationElement.innerHTML = `<div class="alert alert-success">${message}</div>`;
        setTimeout(() => {
            notificationElement.innerHTML = '';
        }, 3000);
    } else {
        // Fallback para alert se não houver elemento de notificação
       // alert(message);
    }
}*/

// Verificar atualizações a cada 5 segundos
//setInterval(checkForUserDataUpdates, 5000);

document.addEventListener('DOMContentLoaded', function(){
processUserData();
lastProfilePictures();
checkForUserDataUpdates();
});



