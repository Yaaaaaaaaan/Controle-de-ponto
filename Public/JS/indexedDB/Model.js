// ========================
// 📁 Model.js
// ========================
import {
    initializeDB,
    userDataStoreName,
    pointControlStoreName,
    userTokenStoreName,
    syncQueueStoreName
} from './Config.js';

// ========================
// Funções para userData
// ========================

// Adicionar um novo usuário
async function upsertUser(user) {
    try {
        // Garantir que temos um objeto de usuário válido
        if (!user || !user.nickname) {
            throw new Error("Objeto de usuário ou nickname inválido.");
        }

        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);

        // put() adiciona se não existe, ou atualiza se já existe. Perfeito!
        const putRequest = userDataStore.put(user);

        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => {
                console.log("Usuário adicionado/atualizado com sucesso:", user.nickname);
                // Retorna o objeto do usuário para consistência com outras funções
                resolve(user);
            };
            putRequest.onerror = () => {
                console.error('Erro no put do usuário:', putRequest.error);
                reject('Erro ao adicionar/atualizar usuário: ' + putRequest.error);
            };
        });
    } catch (error) {
        console.error('Erro em addOrUpdateUser:', error);
        throw error; // Propaga o erro
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

//busca dados do usuário usando token.
async function fetchUserDataByToken(token) {
    if (!token) return null;

    try {
        const response = await fetch('/controle-de-ponto/Public/Api/userToken.php', {
            method: 'GET',
            headers: {
                // Envia o token no cabeçalho, como é o padrão de mercado
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            console.warn("Sessão no servidor expirada ou inválida.");
            // Aqui você pode acionar uma lógica de logout forçado
            return null;
        }

        const result = await response.json();
        return result.success ? result.userData : null;

    } catch (error) {
        console.error("Erro de rede ao buscar dados por token:", error);
        return null;
    }
}

//Verifica token para autenticação
async function getUserTokenByUserId(userId) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userTokenStoreName, 'readonly');
        const tokenStore = transaction.objectStore(userTokenStoreName);
        // Usa o índice 'userId' que criamos na store de tokens
        const index = tokenStore.index('userId');
        const request = index.get(userId);

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject('Erro ao obter token por userId: ' + request.error);
        });
    } catch (error) {
        console.error('Erro em getUserTokenByUserId:', error);
        return null;
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
        const response = await fetch('../../Api/userData.php');
        const fullData = await response.json();

        console.log("processUserData: Resposta do servidor:", fullData);

        const serverUserData = fullData.userData;

        if (!serverUserData || typeof serverUserData !== 'object' || Object.keys(serverUserData).length === 0) {
            console.error("Dados do usuário inválidos ou vazios do servidor.");
            return null;
        }

        // Adicionar ou atualizar o usuário no IndexedDB
        await upsertUser(serverUserData);

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
            //console.error("Erro ao sincronizar com o servidor: Tema ou token de usuário não especificados");
            return false;
        }

        // Verificar se estamos online
        if (!navigator.onLine) {
            console.log("Dispositivo offline, sincronização adiada");
            return false;
        }

        const response = await fetch('../../Api/userData.php', {
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
        const userResponse = await fetch('../../Api/userData.php');
        const userData = await userResponse.json();

        if (userData && userData.userData) {
            if (Array.isArray(userData.userData)) {
                // Processar múltiplos usuários
                for (const user of userData.userData) {
                    await upsertUser(user);
                    if (user.userToken && user.id) {
                        await setUserToken(user.userToken, user.id);
                    }
                }
            } else if (typeof userData.userData === 'object') {
                // Processar um único usuário
                await upsertUser(userData.userData);
                if (userData.userData.userToken && userData.userData.id) {
                    await setUserToken(userData.userData.userToken, userData.userData.id);
                }
            }
        }

        // Buscar dados de controle de ponto
        const pointResponse = await fetch('../../Api/pointControl.php');
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
        await upsertUser(userData);

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

/**
 * Envia um novo registro de ponto (bater o ponto) para o servidor.
 * @param {number} status - O status do ponto (ex: 1 para entrada, 0 para saída).
 * @returns {Promise<boolean>} - Retorna true se bem-sucedido, false caso contrário.
 */
async function addPointControlRecordToServer(status) {
    try {
        // Validar se o status foi fornecido
        if (typeof status === 'undefined') {
            console.error("Erro ao bater o ponto: o status não foi especificado.");
            return false;
        }

        // Verificar se estamos online
        if (!navigator.onLine) {
            console.log("Dispositivo offline. Ação de bater o ponto será sincronizada depois.");
            // Aqui você poderia salvar a tentativa em uma fila no IndexedDB para sincronizar depois.
            return false;
        }

        console.log(`Enviando registro de ponto para o servidor com status: ${status}`);

        // AQUI ESTÁ A OPÇÃO 3!
        const response = await fetch('../../Api/pointControl.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'addRecord', // Informa ao PHP exatamente o que fazer
                status: status       // Envia o status do ponto
            })
        });

        const data = await response.json();

        if (data.success) {
            console.log("Ponto batido com sucesso no servidor!");
            // Após o sucesso, você pode querer atualizar os dados locais
            // chamando a função que busca os dados de ponto novamente.
            return true;
        } else {
            console.error("Erro ao bater o ponto no servidor:", data.message);
            return false;
        }

    } catch (error) {
        console.error("Erro fatal ao comunicar com o servidor para bater o ponto:", error);
        return false;
    }
}



// =============================
// Funções de controle de acesso
// =============================

/**
 * Remove todos os registros de um Object Store específico.
 * @param {string} storeName - O nome do Object Store a ser limpo (ex: 'userData').
 * @returns {Promise<void>} - Uma promessa que é resolvida quando a limpeza é concluída.
 */
async function clearObjectStore(storeName) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(storeName, 'readwrite');
        const objectStore = transaction.objectStore(storeName);
        const clearRequest = objectStore.clear();

        return new Promise((resolve, reject) => {
            clearRequest.onsuccess = () => {
                console.log(`Object Store '${storeName}' limpo com sucesso.`);
                resolve();
            };
            clearRequest.onerror = (event) => {
                console.error(`Erro ao limpar o Object Store '${storeName}':`, event.target.error);
                reject(event.target.error);
            };
        });
    } catch (error) {
        console.error(`Erro ao iniciar a transação para limpar '${storeName}':`, error);
        throw error;
    }
}

//Histórico de uso do sistema offline
async function addActionToSyncQueue(action) {
    const db = await initializeDB();
    const transaction = db.transaction(syncQueueStoreName, 'readwrite');
    const store = transaction.objectStore(syncQueueStoreName);
    action.timestamp = new Date(); // Adiciona um timestamp
    store.add(action);
    return new Promise(resolve => transaction.oncomplete = resolve);
}

//Manipulação do histórico de uso do sistema offline
async function getSyncQueue() {
    const db = await initializeDB();
    const transaction = db.transaction(syncQueueStoreName, 'readonly');
    const store = transaction.objectStore(syncQueueStoreName);
    return store.getAll();
}

//Manipulação do histórico de uso do sistema offline
async function clearSyncQueue() {
    const db = await initializeDB();
    const transaction = db.transaction(syncQueueStoreName, 'readwrite');
    const store = transaction.objectStore(syncQueueStoreName);
    store.clear();
    return new Promise(resolve => transaction.oncomplete = resolve);
}

export {
    // Funções de usuário
    upsertUser,
    getUserById,
    getUserByNickname,
    getUserByToken,
    deleteUser,
    getAllUsers,

    // Funções de controle de ponto
    addPointControl,
    getPointControlByUserId,
    getAllPointControl,
    addPointControlRecordToServer,

    // Funções de token
    setUserToken,
    fetchUserDataByToken,
    getUserTokenByUserId,
    isTokenValid,

    // Funções de sincronização
    processUserData,
    syncIndexedDBToServer,
    syncServerToIndexedDB,
    storeAuthData,

    // Funções de controle de acesso
    clearObjectStore,
    addActionToSyncQueue,
    getSyncQueue,
    clearSyncQueue

};
