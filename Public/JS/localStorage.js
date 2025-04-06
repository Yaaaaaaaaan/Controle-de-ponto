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
function processUserDataFromString(userDataString) {
    try {
        let UserData = JSON.parse(userDataString);

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
                nameCurto = name.indexOf(" ") == -1 ? name : name.substring(0, name.indexOf(" "));
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


            // Atualiza elementos na página
            updateUIElements(name, userToken, email, rank, nickname, theme, id, nameCurto, profileUser);
        }
    } catch (error) {
        console.error("Erro ao processar string de dados:", error);
    }
}

// Função para atualizar elementos da UI
function updateUIElements(name, userToken, email, rank, nickname, theme, id, nameCurto, profileUser) {
    // Atualiza elementos em outras páginas
    const responseName = document.getElementById("responseName");
    if (responseName) {
        responseName.textContent = name;
    }
    const responseUserToken = document.getElementById("responseUserToken");
    if (responseUserToken) {
        responseUserToken.textContent = userToken;
    }
    const emailElement = document.getElementById("responseEmail");
    if (emailElement) {
        emailElement.textContent = email;
    }
    const rankElement = document.getElementById("responseRank");
    if (rankElement) {
        rankElement.textContent = rank;
    }
    const nicknameElement = document.getElementById("responseNickname");
    if (nicknameElement) {
        nicknameElement.textContent = nickname;
    }
    const themeElement = document.getElementById("responseTheme");
    if (themeElement) {
        themeElement.textContent = theme;
    }
    const idElement = document.getElementById("responseId");
    if (idElement) {
        idElement.textContent = id;
    }
    if (name.indexOf(" ") == -1) {
        nameCurto = name;
    } else {
        nameCurto = name.substring(0, name.indexOf(" "));
    }
    const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
    if (profileUser) {
        const srcImage = imageBasePath + profileUser.replace(/"/g, '');
        const pPicture = document.getElementById("pPicture");
        const pPictureModal = document.getElementById("pPictureModal");
        if (pPicture) {
            pPicture.src = srcImage;
            pPictureModal.src = srcImage;
            //console.log("link:", srcImage); //apenas para verificação do link exibido no console.
        }
    }
}




async function lastProfilePictures(){
    try{
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();
        const lastProfilePictures = data.lastProfilePictures;

        // Faça algo com as imagens de perfil aqui
        console.log(lastProfilePictures);
        // Por exemplo, você pode mostrar as imagens em uma galeria
        // ...

    }catch(error){
        console.error('Erro ao buscar imagens de perfil:', error);
    }
}

document.addEventListener('DOMContentLoaded', function(){
processUserData();
lastProfilePictures();
});



