// ========================
// 📁 Model.js
// ========================
import {
    initializeDB,
    userDataStoreName,
    pointControlStoreName,
    userTokenStoreName
} from './Config.js';

// ========================
// Funções para userData
// ========================

// Adicionar um novo usuário
async function addUser(user) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);

        // Verificar se o usuário já existe
        const existingUser = await getUserByNickname(user.nickname);
        if (existingUser) {
            // Atualizar usuário existente
            return updateUser(user);
        }

        const addRequest = userDataStore.add(user);
        return new Promise((resolve, reject) => {
            addRequest.onsuccess = () => {
                console.log("Usuário adicionado com sucesso:", user.nickname);
                resolve(addRequest.result);
            };
            addRequest.onerror = () => reject('Erro ao adicionar usuário: ' + addRequest.error);
        });
    } catch (error) {
        console.error('Erro ao adicionar usuário:', error);
        throw new Error('Erro ao adicionar usuário: ' + error);
    }
}

// Obter um usuário pelo ID
async function getUserById(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const index = userDataStore.index('idIdx');
        const getRequest = index.get(id);

        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject('Erro ao obter usuário: ' + getRequest.error);
        });
    } catch (error) {
        console.error('Erro ao obter usuário por ID:', error);
        return null;
    }
}

// Obter um usuário pelo nickname
async function getUserByNickname(nickname) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const getRequest = userDataStore.get(nickname);

        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject('Erro ao obter usuário: ' + getRequest.error);
        });
    } catch (error) {
        console.error('Erro ao obter usuário por nickname:', error);
        return null;
    }
}

// Obter um usuário pelo token
async function getUserByToken(token) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const index = userDataStore.index('userTokenIdx');
        const getRequest = index.get(token);

        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject('Erro ao obter usuário por token: ' + getRequest.error);
        });
    } catch (error) {
        console.error('Erro ao obter usuário por token:', error);
        return null;
    }
}

// Atualizar um usuário
async function updateUser(user) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const putRequest = userDataStore.put(user);

        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => {
                console.log("Usuário atualizado com sucesso:", user.nickname);
                resolve(user);
            };
            putRequest.onerror = () => reject('Erro ao atualizar usuário: ' + putRequest.error);
        });
    } catch (error) {
        console.error('Erro ao atualizar usuário:', error);
        throw new Error('Erro ao atualizar usuário: ' + error);
    }
}

// Deletar um usuário pelo ID
async function deleteUser(nickname) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const deleteRequest = userDataStore.delete(nickname);

        return new Promise((resolve, reject) => {
            deleteRequest.onsuccess = () => {
                console.log("Usuário deletado com sucesso:", nickname);
                resolve();
            };
            deleteRequest.onerror = () => reject('Erro ao deletar usuário: ' + deleteRequest.error);
        });
    } catch (error) {
        console.error('Erro ao deletar usuário:', error);
        throw new Error('Erro ao deletar usuário: ' + error);
    }
}

// Obter todos os usuários
async function getAllUsers() {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const request = userDataStore.getAll();

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    } catch (error) {
        console.error("Erro ao obter todos os usuários:", error);
        return [];
    }
}

// ========================
// Funções para pointControl
// ========================

// Adicionar um novo registro de ponto
async function addPointControl(pointData) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readwrite');
        const pointControlStore = transaction.objectStore(pointControlStoreName);

        // Garantir que temos os campos necessários
        if (!pointData.uidUserFK || !pointData.status) {
            throw new Error('Dados de controle de ponto incompletos');
        }

        // Adicionar data atual se não fornecida
        if (!pointData.dateIn) {
            pointData.dateIn = new Date().toISOString();
        }

        const addRequest = pointControlStore.add(pointData);

        return new Promise((resolve, reject) => {
            addRequest.onsuccess = () => {
                console.log("Registro de ponto adicionado com sucesso");
                resolve(addRequest.result);
            };
            addRequest.onerror = () => reject('Erro ao adicionar dados de controle de ponto: ' + addRequest.error);
        });
    } catch (error) {
        console.error('Erro ao adicionar dados de controle de ponto:', error);
        throw new Error('Erro ao adicionar dados de controle de ponto: ' + error);
    }
}

// Obter registros de ponto por usuário
async function getPointControlByUserId(userId) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readonly');
        const pointControlStore = transaction.objectStore(pointControlStoreName);
        const index = pointControlStore.index('userIdIdx');
        const request = index.getAll(userId);

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject('Erro ao obter registros de ponto: ' + request.error);
        });
    } catch (error) {
        console.error('Erro ao obter registros de ponto por usuário:', error);
        return [];
    }
}

// Obter todos os registros de ponto
async function getAllPointControl() {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readonly');
        const pointControlStore = transaction.objectStore(pointControlStoreName);
        const request = pointControlStore.getAll();

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject('Erro ao obter todos os registros de ponto: ' + request.error);
        });
    } catch (error) {
        console.error('Erro ao obter todos os registros de ponto:', error);
        return [];
    }
}

// ========================
// Funções para userToken
// ========================

// Adicionar ou atualizar um token de usuário
async function setUserToken(token, userId) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userTokenStoreName, 'readwrite');
        const userTokenStore = transaction.objectStore(userTokenStoreName);

        const tokenData = {
            token: token,
            uidUserFK: userId,
            lastUpdated: new Date().toISOString()
        };

        const putRequest = userTokenStore.put(tokenData);

        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => {
                console.log("Token de usuário atualizado com sucesso");
                resolve(tokenData);
            };
            putRequest.onerror = () => reject('Erro ao atualizar token de usuário: ' + putRequest.error);
        });
    } catch (error) {
        console.error('Erro ao atualizar token de usuário:', error);
        throw new Error('Erro ao atualizar token de usuário: ' + error);
    }
}

// Verificar se um token é válido
async function isTokenValid(token) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userTokenStoreName, 'readonly');
        const userTokenStore = transaction.objectStore(userTokenStoreName);
        const getRequest = userTokenStore.get(token);

        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => {
                const tokenData = getRequest.result;
                if (!tokenData) {
                    resolve(false);
                    return;
                }

                // Verificar se o token não expirou (24 horas)
                const lastUpdated = new Date(tokenData.lastUpdated);
                const now = new Date();
                const diffHours = (now - lastUpdated) / (1000 * 60 * 60);

                resolve(diffHours < 24);
            };
            getRequest.onerror = () => {
                console.error('Erro ao verificar token:', getRequest.error);
                resolve(false);
            };
        });
    } catch (error) {
        console.error('Erro ao verificar validade do token:', error);
        return false;
    }
}

// ========================
// Funções de sincronização
// ========================

// Sincronizar dados do usuário do servidor para o IndexedDB
async function processUserData() {
    try {
        const response = await fetch('../../Persistence/userData.php');
        const fullData = await response.json();

        console.log("processUserData: Resposta do servidor:", fullData);

        const serverUserData = fullData.userData;

        if (!serverUserData || typeof serverUserData !== 'object' || Object.keys(serverUserData).length === 0) {
            console.error("Dados do usuário inválidos ou vazios do servidor.");
            return null;
        }

        // Adicionar ou atualizar o usuário no IndexedDB
        await addUser(serverUserData);

        // Se temos um token, atualizá-lo também
        if (serverUserData.userToken && serverUserData.id) {
            await setUserToken(serverUserData.userToken, serverUserData.id);
        }

        return serverUserData;
    } catch (error) {
        console.error("Erro ao processar dados do usuário:", error);
        return null;
    }
}

// Sincronizar dados do IndexedDB com o servidor
async function syncIndexedDBToServer(userToken, theme) {
    try {
        if (!userToken || typeof theme === 'undefined') {
            console.error("Erro ao sincronizar com o servidor: Tema ou token de usuário não especificados");
            return false;
        }

        // Verificar se estamos online
        if (!navigator.onLine) {
            console.log("Dispositivo offline, sincronização adiada");
            return false;
        }

        const response = await fetch('../../Persistence/userData.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken, theme })
        });

        const data = await response.json();
        if (data.success) {
            console.log("Dados sincronizados com o servidor com sucesso!");
            return true;
        } else {
            console.error("Erro ao sincronizar dados com o servidor:", data.message);
            return false;
        }
    } catch (error) {
        console.error("Erro ao comunicar com o servidor:", error);
        return false;
    }
}

// Sincronizar dados do servidor com o IndexedDB
async function syncServerToIndexedDB() {
    try {
        // Verificar se estamos online
        if (!navigator.onLine) {
            console.log("Dispositivo offline, sincronização adiada");
            return false;
        }

        // Buscar dados do usuário
        const userResponse = await fetch('../../Persistence/userData.php');
        const userData = await userResponse.json();

        if (userData && userData.userData) {
            if (Array.isArray(userData.userData)) {
                // Processar múltiplos usuários
                for (const user of userData.userData) {
                    await addUser(user);
                    if (user.userToken && user.id) {
                        await setUserToken(user.userToken, user.id);
                    }
                }
            } else if (typeof userData.userData === 'object') {
                // Processar um único usuário
                await addUser(userData.userData);
                if (userData.userData.userToken && userData.userData.id) {
                    await setUserToken(userData.userData.userToken, userData.userData.id);
                }
            }
        }

        // Buscar dados de controle de ponto
        const pointResponse = await fetch('../../Persistence/pointControl.php');
        const pointData = await pointResponse.json();

        if (pointData && pointData.pointControl && Array.isArray(pointData.pointControl)) {
            const db = await initializeDB();
            const transaction = db.transaction(pointControlStoreName, 'readwrite');
            const pointControlStore = transaction.objectStore(pointControlStoreName);

            // Limpar dados antigos
            await new Promise((resolve, reject) => {
                const clearRequest = pointControlStore.clear();
                clearRequest.onsuccess = resolve;
                clearRequest.onerror = reject;
            });

            // Adicionar novos dados
            for (const point of pointData.pointControl) {
                await new Promise((resolve, reject) => {
                    const addRequest = pointControlStore.add(point);
                    addRequest.onsuccess = resolve;
                    addRequest.onerror = reject;
                });
            }
        }

        console.log("Sincronização com o servidor concluída com sucesso");
        return true;
    } catch (error) {
        console.error("Erro ao sincronizar dados com o servidor:", error);
        return false;
    }
}

// Função para armazenar dados de autenticação no IndexedDB
async function storeAuthData(userData) {
    try {
        if (!userData || !userData.nickname || !userData.userToken) {
            console.error("Dados de autenticação inválidos");
            return false;
        }

        // Armazenar dados do usuário
        await addUser(userData);

        // Armazenar token
        if (userData.id) {
            await setUserToken(userData.userToken, userData.id);
        }

        console.log("Dados de autenticação armazenados com sucesso");
        return true;
    } catch (error) {
        console.error("Erro ao armazenar dados de autenticação:", error);
        return false;
    }
}

export {
    // Funções de usuário
    addUser,
    getUserById,
    getUserByNickname,
    getUserByToken,
    updateUser,
    deleteUser,
    getAllUsers,

    // Funções de controle de ponto
    addPointControl,
    getPointControlByUserId,
    getAllPointControl,

    // Funções de token
    setUserToken,
    isTokenValid,

    // Funções de sincronização
    processUserData,
    syncIndexedDBToServer,
    syncServerToIndexedDB,
    storeAuthData
};
