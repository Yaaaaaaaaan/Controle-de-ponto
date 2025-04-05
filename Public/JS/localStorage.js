async function processUserData(){
    try{
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();

        // Usar data.userData em vez de userData diretamente
        const userData = data.userData;

        if(Object.keys(userData).length > 0){
            // Armazenando os dados no localStorage
            localStorage.setItem('userData', JSON.stringify(userData));

            //console.log('Dados armazenados no localStorage:', localStorage.getItem('userData')); //apenas para verificação no console.

            // Recuperando a string do localStorage
            userDataString = localStorage.getItem("userData");
            // Convertendo a string para um objeto JavaScript
            let UserData = JSON.parse(userDataString);

            // Acessando os valores dentro do array
            let userdata = UserData.split(",");
            let name = (userdata[1]);
            name = (userdata[1]).slice(8, -1);
            userToken = (userdata[0]).slice(14, -1);
            email = (userdata[2]).slice(9, -1);
            rank = (userdata[3]).slice(7);
            nickname = (userdata[4]).slice(12, -1);
            theme = (userdata[5]).slice(8);
            id = (userdata[6]).slice(5);
            profileUser = (userdata[7]).slice(14, -1);

            //faz a manipulação detalhada da string
            if(name.indexOf(" ") == -1){
                nameCurto = name;
            }else{
                nameCurto = name.substring(0, name.indexOf(" "));
            }

            // Atualiza elementos em outras páginas
            const responseName = document.getElementById("responseName");
            if(responseName){
                responseName.textContent = name;
            }
            const responseUserToken = document.getElementById("responseUserToken");
            if(responseUserToken){
                responseUserToken.textContent = userToken;
            }
            const idElement = document.getElementById("responseId");
            if(idElement){
                idElement.textContent = id;
            }
            const themeElement = document.getElementById("responseTheme");
            if(themeElement){
                themeElement.textContent = theme;
            }
            const nicknameElement = document.getElementById("responseNickname");
            if(nicknameElement){
                nicknameElement.textContent = nickname;
            }
            const rankElement = document.getElementById("responseRank");
            if(rankElement){
                rankElement.textContent = rank;
            }
            const emailElement = document.getElementById("responseEmail");
            if(emailElement){
                emailElement.textContent = email;
            }

            // Atualiza os campos do formulário
            if(window.location.pathname.endsWith('/User/settings.php')){
                preencherCamposFormulario();
            }

            // Atualiza o menu
            atualizarMenu(nameCurto);

            const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
            if(profileUser){
                const srcImage = imageBasePath + profileUser.replace(/"/g, '');
                const pPicture = document.getElementById("pPicture");
                const pPictureModal = document.getElementById("pPictureModal");
                if(pPicture){
                    pPicture.src = srcImage;
                    pPictureModal.src = srcImage;
                    //console.log("link:", srcImage); //apenas para verificação do link exibido no console.
                }else{
                    console.error("Elemento pPicture não encontrado.");
                }
            }else{
                console.error("profileUser não encontrado no localStorage.");
            }
        }else{
                console.error("Dados do usuário inválidos ou vazios. ", localStorage.getItem('userData')); );
            }
    }catch(error){
        console.error('Erro ao processar dados:', error);
    }
}

function atualizarMenu(nameCurto){
    const responseNameCurto = document.getElementById("responseNameCurto");
    if(responseNameCurto){
        responseNameCurto.textContent = "Olá, " + nameCurto;
    }else{
        //console.log("Elemento responseNameCurto não encontrado em menu.php."); //verificação falha
        responseNameCurto.textContent = "Olá!"
    }
}

async function lastProfilePictures(){
    try{
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();
        const profilePictures = data.lastProfilePictures;

        // Faça algo com as imagens de perfil aqui
        console.log(profilePictures);

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



