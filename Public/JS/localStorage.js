const USER_DATA_EXPIRATION_TIME = 3600000; // 1 hora (em milissegundos)

async function processUserData() {
  try {
    const response = await fetch('../../Persistence/userData.php');
    const latestUserData = await response.json();

    if (latestUserData && Object.keys(latestUserData).length > 0) {
      const storedUserDataString = localStorage.getItem('userData');
      let storedUserData = storedUserDataString ? JSON.parse(storedUserDataString) : null;

      if (!storedUserData || !isUserDataValid(storedUserData) || JSON.stringify(storedUserData.data) !== JSON.stringify(latestUserData)) {
        // Atualiza o localStorage com os dados mais recentes
        const userDataToStore = {
          data: latestUserData,
          timestamp: Date.now()
        };
        localStorage.setItem('userData', JSON.stringify(userDataToStore));
        console.log('Dados do usuário atualizados no localStorage:', userDataToStore);
        storedUserData = userDataToStore;
      } else {
        console.log('Dados do usuário no localStorage estão atualizados.');
      }

      // Processa os dados do usuário
      processUserDataDetails(storedUserData.data);
    } else {
      console.error('Nenhum dado de usuário encontrado no servidor.');
    }
  } catch (error) {
    console.error('Erro ao processar dados do usuário:', error);
  }
}

function isUserDataValid(userData) {
  return userData && userData.data && userData.timestamp && (Date.now() - userData.timestamp < USER_DATA_EXPIRATION_TIME);
}

function processUserDataDetails(userData) {
  document.getElementById("responseName").textContent = userData.name;
  document.getElementById("responseUserToken").textContent = userData.userToken;
  document.getElementById("id").textContent = userData.id;
  document.getElementById("theme").textContent = userData.theme;
  document.getElementById("nickname").textContent = userData.nickname;
  document.getElementById("rank").textContent = userData.rank;
  document.getElementById("email").textContent = userData.email;
  document.getElementById("responseNameCurto").textContent = "Olá, " + userData.name.split(" ")[0];

  const profilePicDir = userData.profileUser.replace(/\\/g, "").slice(1, -1);
  if (profilePicDir) {
    document.getElementById('profilePic').src = profilePicDir;
  } else {
    console.error("Caminho da imagem de perfil inválido.");
  }
}

processUserData();