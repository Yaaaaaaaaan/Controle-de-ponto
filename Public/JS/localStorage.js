// ========================
// 📁 localStorage.js
// ========================
// Função principal para processar dados do usuário
async function processUserData() {
    try {
        // Verifica se há atualizações primeiro
        const response = await fetch('../../Persistence/userData.php?checkUpdates=true');
        const data = await response.json();

        // Se há dados atualizados, use-os
        if (data.dataUpdated && data.userData) {
            if (typeof data.userData === 'object' && Object.keys(data.userData).length > 0) {
                localStorage.setItem('userData', JSON.stringify(data.userData));
                console.log("Dados atualizados do servidor");
                return data.userData;
            }
        }

        // Se não há atualizações, use os dados do localStorage
        const savedUserData = localStorage.getItem("userData");
        if (savedUserData) {
            console.log("Usando dados do localStorage");
            return JSON.parse(savedUserData);
        }

        // Se não há dados no localStorage, busca dados completos
        const fullResponse = await fetch('../../Persistence/userData.php');
        const fullData = await fullResponse.json();
        const userData = fullData.userData;

        if (Object.keys(userData).length <= 0) {
            console.error("Dados do usuário inválidos ou vazios.");
            return null;
        }

        localStorage.setItem('userData', JSON.stringify(userData));
        return userData;

    } catch (error) {
        console.error("Erro ao processar dados do usuário:", error);
        return null;
    }

}

// Função para verificar atualizações dos dados
async function checkForUserDataUpdates() {
    try {
        const response = await fetch('../../Persistence/userData.php?checkUpdates=true');
        const data = await response.json();

        if (data.dataUpdated && data.userData) {
            if (typeof data.userData === 'object' && Object.keys(data.userData).length > 0) {
                localStorage.setItem('userData', JSON.stringify(data.userData));
                return data.userData;
            }
        }
        return null;
    } catch (error) {
        console.error('Erro ao verificar atualizações:', error);
        return null;
    }
}

// Função para buscar últimas fotos de perfil
async function lastProfilePictures() {
    try {
        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();
        return data.lastProfilePictures;
    } catch (error) {
        console.error('Erro ao buscar imagens de perfil:', error);
        return null;
    }
}

// Exporta as funções
export { processUserData, checkForUserDataUpdates, lastProfilePictures };