async function processUserData() {
  try {
    const response = await fetch('../../Persistence/userData.php');
    const userData = await response.json();

    if (userData.length > 0) {
      // Armazenando os dados no localStorage
      localStorage.setItem('userData', JSON.stringify(userData));
      console.log('Dados armazenados no localStorage:', localStorage.getItem('userData'));

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
     nameCurto = name.substring(0, name.indexOf(" "));

      // Atualiza elementos em outras páginas
      const responseName = document.getElementById("responseName");
      if (responseName) {
          responseName.textContent = name;
      }
      const responseUserToken = document.getElementById("responseUserToken");
      if (responseUserToken) {
          responseUserToken.textContent = userToken;
      }
      const idElement = document.getElementById("id");
      if (idElement) {
          idElement.textContent = id;
      }
      const themeElement = document.getElementById("theme");
      if (themeElement) {
          themeElement.textContent = theme;
      }
      const nicknameElement = document.getElementById("nickname");
      if (nicknameElement) {
          nicknameElement.textContent = nickname;
      }
      const rankElement = document.getElementById("rank");
      if (rankElement) {
          rankElement.textContent = rank;
      }
      const emailElement = document.getElementById("email");
      if (emailElement) {
          emailElement.textContent = email;
      }

      // Atualiza o menu
      atualizarMenu(nameCurto);

      const imageBasePath = '/controle-de-ponto/App/Persistence/userProfileImages/';
      if (profileUser) {
          const srcImage = imageBasePath + profileUser.replace(/"/g, '');
          const pPicture = document.getElementById("pPicture");
          const pPictureModal = document.getElementById("pPictureModal");
          if (pPicture) {
              pPicture.src = srcImage;
              pPictureModal.src = srcImage;
              console.log("link:", srcImage);
          } else {
              console.error("Elemento pPicture não encontrado.");
          }
      } else {
          console.error("profileUser não encontrado no localStorage.");
      }
  } else {
      console.error("Dados do usuário inválidos ou vazios.");
  }
} catch (error) {
  console.error('Erro ao processar dados:', error);
}
}

function atualizarMenu(nameCurto) {
const responseNameCurto = document.getElementById("responseNameCurto");
if (responseNameCurto) {
  responseNameCurto.textContent = "Olá, " + nameCurto;
} else {
  console.log("Elemento responseNameCurto não encontrado em menu.php.");
}
}

document.addEventListener('DOMContentLoaded', function() {
processUserData();
});