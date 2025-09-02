// =========================
// 📁 Model.js
// =========================

import {
    initializeDB,
    userDataStoreName,
    pointControlStoreName,
    userTokenStoreName,
    syncQueueStoreName,
    userPicturesStoreName,
    obterHoraFormatada
} from './Config.js';

// ========================
// Funções para userData
// ========================

// Adicionar um novo usuário
async function upsertUser(userObjectToSave) {
    try {
        if (!userObjectToSave || !userObjectToSave.nickname) {
            throw new Error("Objeto de usuário ou nickname inválido para upsertUser.");
        }

        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);

        // --- LÓGICA DE PRESERVAÇÃO CENTRALIZADA ---

        // 1. Antes de salvar, busca o registro que já existe no banco de dados.
        // Isso nos permite ver se já existe um hash offline que precisa ser preservado.
        const existingUser = await new Promise((resolve, reject) => {
            const getRequest = userDataStore.get(userObjectToSave.nickname);
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => {
                // Mesmo se der erro ao buscar, continuamos, pois pode ser um usuário novo.
                console.warn(`[${obterHoraFormatada()}] Não foi possível encontrar usuário existente para merge. Pode ser um novo usuário.`, getRequest.error);
                resolve(undefined);
            };
        });

        // 2. Mescla os dados de forma inteligente.
        // O novo objeto (userObjectToSave) é a fonte da verdade,
        // mas garantimos que o hash do objeto antigo seja mantido se ele existir.
        const finalUserObject = {
            ...userObjectToSave, // Começa com todos os dados novos que vieram (do login ou da sincronização)
            offlinePasswordHash: existingUser?.offlinePasswordHash || userObjectToSave.offlinePasswordHash
            // A linha acima significa:
            // - Use o hash do 'existingUser', SE ELE EXISTIR.
            // - Caso contrário (||), use o hash do 'userObjectToSave' (o que acontece no primeiro login, quando o hash é passado).
        };

        // --- FIM DA LÓGICA DE PRESERVAÇÃO ---

        // 3. Salva o objeto final e mesclado, que agora garantidamente contém o hash se ele existia.
        const putRequest = userDataStore.put(finalUserObject);

        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => {
                console.log(`[${obterHoraFormatada()}] upsertUser (INTELIGENTE): Sucesso ao salvar/atualizar '${finalUserObject.nickname}'. Objeto final:`, finalUserObject);
                resolve(finalUserObject);
            };
            putRequest.onerror = () => {
                console.error(`[${obterHoraFormatada()}] upsertUser (INTELIGENTE): ERRO ao salvar/atualizar '${finalUserObject.nickname}'.`, putRequest.error);
                reject(`[${obterHoraFormatada()}] Erro ao adicionar/atualizar usuário: ` + putRequest.error);
            };
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro em upsertUser (INTELIGENTE):`, error);
        throw error;
    }
}

// Obter um usuário pelo ID
async function getUserById(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const index = userDataStore.index('userId');
        const getRequest = index.get(id);

        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject('Erro ao obter usuário: ' + getRequest.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao obter usuário por ID:`, error);
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
        console.error(`[${obterHoraFormatada()}] Erro ao obter usuário por nickname:`, error);
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
        console.error(`[${obterHoraFormatada()}] Erro ao obter usuário por token:`, error);
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
                console.log(`[${obterHoraFormatada()}] Usuário deletado com sucesso:`, nickname);
                resolve();
            };
            deleteRequest.onerror = () => reject(`[${obterHoraFormatada()}] Erro ao deletar usuário: ` + deleteRequest.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao deletar usuário:`, error);
        throw new Error(`[${obterHoraFormatada()}] Erro ao deletar usuário: ` + error);
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
        console.error(`[${obterHoraFormatada()}] Erro ao obter todos os usuários:`, error);
        return [];
    }
}

// obter imagens mais recentes
async function replaceUserPictures(userId, pictures) {
    try {
        const db = await initializeDB();
        // 1. Inicia UMA ÚNICA transação para apagar e adicionar
        const transaction = db.transaction(userPicturesStoreName, 'readwrite');
        const store = transaction.objectStore(userPicturesStoreName);

        // 2. Limpa os registros antigos do usuário
        const index = store.index('userId');
        const request = index.openCursor(IDBKeyRange.only(userId));

        request.onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                store.delete(cursor.primaryKey);
                cursor.continue();
            }
        };

        // 3. Adiciona os novos registros NA MESMA TRANSAÇÃO
        pictures.forEach(pic => {
            // A correção de sintaxe: usa o objeto 'pic' completo
            store.put(pic);
        });

        // 4. Retorna uma promessa que resolve quando a transação inteira terminar
        return new Promise((resolve, reject) => {
            transaction.oncomplete = () => {
                console.log(`Fotos para o usuário ${userId} foram substituídas com sucesso no IndexedDB.`);
                resolve();
            };
            transaction.onerror = (event) => {
                console.error(`Erro na transação de replaceUserPictures:`, event.target.error);
                reject(event.target.error);
            };
        });
    } catch (error) {
        console.error("Erro ao iniciar replaceUserPictures:", error);
        throw error;
    }
}

//Busca imagens no indexedDB.
async function getOfflineUserPictures(userId) {
    const db = await initializeDB();
    const transaction = db.transaction(userPicturesStoreName, 'readonly');
    const store = transaction.objectStore(userPicturesStoreName);
    const index = store.index('userId');
    const request = index.getAll(userId);

    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result || []);
        request.onerror = (event) => {
            console.error("Erro ao buscar fotos offline:", event.target.error);
            reject(event.target.error);
        };
    });
}

function cacheUserImages(urls) {
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        console.log(`[Model.js] Enviando ${urls.length} URLs de imagem para o Service Worker cachear.`);
        navigator.serviceWorker.controller.postMessage({
            type: 'CACHE_DYNAMIC_FILES',
            payload: urls
        });
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
        if (!pointData.userId || !pointData.status) {
            throw new Error('Dados de controle de ponto incompletos');
        }

        // Adicionar data atual se não fornecida
        if (!pointData.dateIn) {
            pointData.dateIn = new Date().toISOString();
        }

        const addRequest = pointControlStore.add(pointData);

        return new Promise((resolve, reject) => {
            addRequest.onsuccess = () => {
                console.log(`[${obterHoraFormatada()}] Registro de ponto adicionado com sucesso`);
                resolve(addRequest.result);
            };
            addRequest.onerror = () => reject(`[${obterHoraFormatada()}] Erro ao adicionar dados de controle de ponto: ` + addRequest.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao adicionar dados de controle de ponto:`, error);
        throw new Error(`[${obterHoraFormatada()}] Erro ao adicionar dados de controle de ponto: ` + error);
    }
}

// Obter registros de ponto por usuário
async function getPointControlByUserId(userId) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readonly');
        const pointControlStore = transaction.objectStore(pointControlStoreName);
        const index = pointControlStore.index('userId');
        const request = index.getAll(userId);

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(`[${obterHoraFormatada()}] Erro ao obter registros de ponto: ` + request.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao obter registros de ponto por usuário:`, error);
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
            request.onerror = () => reject(`[${obterHoraFormatada()}] Erro ao obter todos os registros de ponto: ` + request.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao obter todos os registros de ponto:`, error);
        return [];
    }
}

// Função para deletar os registros por id de usuário
async function deletePointControlByUserId(userId) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readwrite');
        const store = transaction.objectStore(pointControlStoreName);
        const index = store.index('userId'); // Usa o índice de ID de usuário
        const request = index.openCursor(IDBKeyRange.only(userId)); // Abre um cursor para o ID específico

        request.onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                // Encontrou um registro para este usuário, então deleta.
                store.delete(cursor.primaryKey);
                cursor.continue(); // Procura pelo próximo registro do mesmo usuário
            }
        };

        return new Promise((resolve, reject) => {
            transaction.oncomplete = () => {
                console.log(`[${obterHoraFormatada()}] Registros de ponto para o usuário ${userId} foram limpos com sucesso.`);
                resolve();
            };
            transaction.onerror = (event) => {
                console.error(`[${obterHoraFormatada()}] Erro ao deletar registros de ponto para o usuário ${userId}:`, event.target.error);
                reject(event.target.error);
            };
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro em deletePointControlByUserId:`, error);
        throw error;
    }
}

// ========================
// Funções para userToken
// ========================

// Adicionar ou atualizar um token de usuário
async function setUserToken(tokenData) { // Agora recebe o objeto completo
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userTokenStoreName, 'readwrite');
        const userTokenStore = transaction.objectStore(userTokenStoreName);

        if (!tokenData || !tokenData.token) {
            throw new Error("Objeto de token inválido para setUserToken.");
        }

        const putRequest = userTokenStore.put(tokenData);

        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => {
                console.log(`[${obterHoraFormatada()}] | setUserToken: Token para userId ${tokenData.userId} salvo/atualizado com sucesso.`);
                resolve();
            };
            putRequest.onerror = (event) => {
                console.error(`[${obterHoraFormatada()}] | setUserToken: Erro ao salvar token.`, event.target.error);
                reject(event.target.error);
            };
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] | Erro ao inicializar setUserToken:`, error);
        throw error;
    }
}

//busca dados do usuário usando token.
async function fetchUserDataByToken(token) {
    if (!token) return null;
    try {
        const response = await fetch('/Public/Api/initialData.php', {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) {
            console.error("A resposta do initialData.php não foi OK.");
            return null;
        }
        const result = await response.json();

        if (result.success && result.data) {
            const { userData, tokenData, pointControlData, userPictures } = result.data;
            await upsertUser(userData);
            await setUserToken({ ...tokenData, token: tokenData.userToken, userId: userData.userId });
            await replaceUserPointControl(userData.userId, pointControlData);
            await replaceUserPictures(userData.userId, userPictures);

            if (userPictures && userPictures.length > 0) {
                const pictureUrls = userPictures.map(pic => pic.path); // Extrai apenas os caminhos
                cacheUserImages(pictureUrls); // Manda para o Service Worker
            }

            console.log(`[${obterHoraFormatada()}] Sincronização via initialData.php concluída.`);
            return userData;
        }
        return null;
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro de rede ao buscar dados iniciais:`, error);
        throw error; // Relança o erro para que a chamada original saiba que falhou.
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
            request.onerror = () => reject(`[${obterHoraFormatada()}] Erro ao obter token por userId: ` + request.error);
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro em getUserTokenByUserId:`, error);
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
                console.error(`[${obterHoraFormatada()}] Erro ao verificar token:`, getRequest.error);
                resolve(false);
            };
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao verificar validade do token:`, error);
        return false;
    }
}

// ========================
// Funções de sincronização
// ========================

// Função para armazenar dados de autenticação no IndexedDB
/**
 * Armazena os dados semi-persistentes do usuário no IndexedDB.
 * @param {object} userDataFromApi - O objeto de dados do usuário que veio da API.
 * @param {string} offlinePasswordHash - O hash gerado no cliente para validação offline.
 */
async function storeAuthData(userData, tokenData, offlinePasswordHash) {
    try {
        if (!userData || !tokenData || !userData.nickname || !tokenData.userToken) {
            throw new Error("Dados de utilizador ou token inválidos para storeAuthData.");
        }

        // 1. MONTA E SALVA o objeto userData limpo
        // Se offlinePasswordHash não for nulo, ele é adicionado/atualizado.
        // Se for nulo, o upsertUser inteligente irá preservar o que já existe.
        const userDataToStore = { ...userData, offlinePasswordHash };
        await upsertUser(userDataToStore);
        console.log(`[${obterHoraFormatada()}] | storeAuthData: Objeto 'userData' salvo:`, userDataToStore);

        // 2. MONTA E SALVA o objeto userToken
        const tokenDataToStore = {
            token: tokenData.userToken,
            userId: userData.userId,
            tokenDate: tokenData.tokenDate,
            tokenExpiry: tokenData.tokenExpiry,
            lastUpdated: new Date().toISOString()
        };
        await setUserToken(tokenDataToStore);

        return true;
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] | Erro em storeAuthData:`, error);
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
        if (typeof status === 'undefined') {
            console.error(`[${obterHoraFormatada()}] Erro ao bater o ponto: o status não foi especificado.`);
            return false;
        }

        const userToken = localStorage.getItem('userToken');
        if (!userToken) {
            console.error(`[${obterHoraFormatada()}] Erro ao bater o ponto: Token de utilizador não encontrado.`);
            alert("Sessão inválida. Por favor, faça o login novamente.");
            return false;
        }

        console.log(`[${obterHoraFormatada()}] Enviando registo de ponto para o servidor com status: ${status}`);

        const response = await fetch('/Public/Api/pControl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${userToken}`
            },
            body: JSON.stringify({
                status: status
            })
        });

        if (!response.ok) {
            console.error(`[${obterHoraFormatada()}] Erro do servidor ao bater o ponto:`, response.status, response.statusText);
            alert('Falha na autenticação ao registar o ponto. A sua sessão pode ter expirado.');
            return false;
        }

        const data = await response.json();

        if (data.success) {
            console.log(`[${obterHoraFormatada()}] Ponto batido com sucesso no servidor!`);
            return true;
        } else {
            console.error(`[${obterHoraFormatada()}] Erro ao bater o ponto no servidor:`, data.message);
            return false;
        }

    } catch (error) {
        // --- A CORREÇÃO ESTÁ AQUI ---
        console.error(`[${obterHoraFormatada()}] Erro fatal ao comunicar com o servidor para bater o ponto:`, error);
        // Depois de registrar o erro, nós o relançamos para que o chamador (userController.js) possa capturá-lo.
        throw error;
    }
}

// NOVA FUNÇÃO: Limpa os registros de um usuário e insere os novos
async function replaceUserPointControl(userId, recordsFromServer) {
    if (!recordsFromServer) return;

    const db = await initializeDB();
    const transaction = db.transaction(pointControlStoreName, 'readwrite');
    const store = transaction.objectStore(pointControlStoreName);
    const index = store.index('userId');

    // 1. Pega todos os registros que já existem localmente para este usuário.
    const localRecords = await new Promise((resolve, reject) => {
        const request = index.getAll(userId);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });

    // 2. Cria um Set com as chaves únicas (id_usuario + data_registro) dos registros locais
    // para uma verificação rápida. A chave do banco é 'cod', mas a chave lógica é a data.
    const localRecordKeys = new Set(localRecords.map(rec => rec.dateIn));

    // 3. Itera sobre os registros do servidor e adiciona apenas os que não existem localmente.
    recordsFromServer.forEach(serverRec => {
        if (!localRecordKeys.has(serverRec.dateIn)) {
            console.log(`[Model.js] Adicionando novo registro de ponto do servidor: ${serverRec.dateIn}`);
            store.put(serverRec);
        }
    });

    return new Promise((resolve, reject) => {
        transaction.oncomplete = resolve;
        transaction.onerror = (event) => reject(event.target.error);
    });
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
                console.log(`[${obterHoraFormatada()}] Object Store '${storeName}' limpo com sucesso.`);
                resolve();
            };
            clearRequest.onerror = (event) => {
                console.error(`[${obterHoraFormatada()}] Erro ao limpar o Object Store '${storeName}':`, event.target.error);
                reject(event.target.error);
            };
        });
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao iniciar a transação para limpar '${storeName}':`, error);
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
    const request = store.getAll(); // Pega a requisição

    // Embrulha a requisição em uma Promise para garantir que `await` funcione corretamente
    return new Promise((resolve, reject) => {
        request.onsuccess = () => {
            resolve(request.result); // Resolve a Promise com o array de ações
        };
        request.onerror = (event) => {
            console.error(`[${obterHoraFormatada()}] Erro ao buscar a fila de sincronização:`, event.target.error);
            reject(event.target.error);
        };
    });
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
    //funções de serviço
    obterHoraFormatada,

    // Funções de usuário
    upsertUser,
    getUserById,
    getUserByNickname,
    getUserByToken,
    deleteUser,
    getAllUsers,
    replaceUserPictures,
    getOfflineUserPictures,

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
    storeAuthData,
    replaceUserPointControl,

    // Funções de controle de acesso
    clearObjectStore,
    addActionToSyncQueue,
    getSyncQueue,
    clearSyncQueue

};
