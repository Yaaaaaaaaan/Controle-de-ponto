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

     // define valores ao html (provisório, futuramente retrabalhar com return, e inserir tudo dentro de array.)

     document.getElementById("responseName").textContent = name; 
     document.getElementById("responseUserToken").textContent = userToken; 
     document.getElementById("id").textContent = id;
     document.getElementById("theme").textContent = theme;
     document.getElementById("nickname").textContent = nickname;
     document.getElementById("rank").textContent = rank;
     document.getElementById("email").textContent = email;
     document.getElementById("responseNameCurto").textContent = "Olá, " + nameCurto; 

      
        
  
const profilePicDirOld = profileUser.replace(/\\/g,"");
const profilePicDir = profilePicDirOld.substring(1, profilePicDirOld.length - 1);
// Verificando se o caminho é válido antes de atribuir ao src
if (profilePicDir) {
  document.getElementById('profilePic').src = profilePicDir;
} else {
  console.error("Caminho da imagem inválido.");
}
        profilePic = document.getElementById('profilePic')
        profilePic.src = profilePicDir;

    } else {
      console.error('Nenhum dado de usuário encontrado');
    }
  } catch (error) {
    console.error('Erro ao processar dados:', error);
  }
}

processUserData();