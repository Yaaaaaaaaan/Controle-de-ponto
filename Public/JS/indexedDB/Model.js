// ========================
// 📁 Model.js
// ========================
import {
    initializeDB,
    userDataStoreName,
    pointControlStoreName,
    userTokenStoreName,
    syncQueueStoreName,
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
        const index = userDataStore.index('idIdx');
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
        const index = pointControlStore.index('userIdIdx');
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
        const index = store.index('userIdIdx'); // Usa o índice de ID de usuário
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
        const response = await fetch('/Public/Api/userToken.php', {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) return null;

        const result = await response.json();

        // A resposta agora vem como { success: true, data: { userData: ..., tokenData: ... } }
        if (result.success && result.data) {
            // Desestrutura os dados recebidos
            const { userData, tokenData } = result.data;

            // Antes de retornar, já salvamos os dados nos locais corretos no IndexedDB
            // Passamos null para o hash, pois a função upsertUser inteligente irá preservar o que já existe.
            await storeAuthData(userData, tokenData, null);

            // Retorna apenas os dados do perfil para a UI, como esperado.
            return userData;
        }
        return null;
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro de rede ao buscar dados por token:`, error);
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

// Sincronizar dados do usuário do servidor para o IndexedDB
async function processUserData() {
    try {
        const response = await fetch('../../Api/userData.php');
        const fullData = await response.json();

        console.log(`[${obterHoraFormatada()}] processUserData: Resposta do servidor:`, fullData);

        const serverUserData = fullData.userData;

        if (!serverUserData || typeof serverUserData !== 'object' || Object.keys(serverUserData).length === 0) {
            console.error(`[${obterHoraFormatada()}] Dados do usuário inválidos ou vazios do servidor.`);
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
        console.error(`[${obterHoraFormatada()}] Erro ao processar dados do usuário:`, error);
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
            console.log(`[${obterHoraFormatada()}] Dispositivo offline, sincronização adiada`);
            return false;
        }

        const response = await fetch('../../Api/userData.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken, theme })
        });

        const data = await response.json();
        if (data.success) {
            console.log(`[${obterHoraFormatada()}] Dados sincronizados com o servidor com sucesso!`);
            return true;
        } else {
            console.error(`[${obterHoraFormatada()}] Erro ao sincronizar dados com o servidor:`, data.message);
            return false;
        }
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao comunicar com o servidor:`, error);
        return false;
    }
}

// Sincronizar dados do servidor com o IndexedDB
async function syncServerToIndexedDB() {
    try {
        if (!navigator.onLine) {
            console.log(`[${obterHoraFormatada()}] Dispositivo offline, sincronização adiada`);
            return false;
        }
        const activeUserId = localStorage.getItem('activeUserId');
        if (!activeUserId) {
            console.warn(`[${obterHoraFormatada()}] syncServerToIndexedDB: Nenhum utilizador ativo encontrado no localStorage. Sincronização abortada.`);
            return false;
        }

        // 1. Busca os dados mais recentes do servidor
        const userResponse = await fetch('../../Api/userData.php');
        const serverData = await userResponse.json();

        if (serverData && serverData.userData) {
            // Usa um nome claro para o objeto que veio do servidor
            const userDataFromServer = serverData.userData;
            if (String(userDataFromServer.userId) !== String(activeUserId)) {
                console.error(`[${obterHoraFormatada()}] syncServerToIndexedDB: Conflito de dados! Utilizador ativo é ${activeUserId}, mas o servidor enviou dados para ${userDataFromServer.userId}. Sincronização abortada.`);
                return false;
            }
            // --- INÍCIO DA CORREÇÃO ---

            // 2. ANTES de salvar, primeiro busca o registro local ATUAL para ver se ele tem um hash.
            // Usa o nickname que veio do servidor como chave para encontrar o usuário local.
            const localUser = await getUserByNickname(userDataFromServer.nickname);

            // 3. Verificamos se há um hash offline no registro local.
            if (localUser && localUser.offlinePasswordHash) {
                // 4. Se houver, garantimos que ele seja PRESERVADO no objeto que veio do servidor.
                // Adiciona a propriedade de volta ao objeto antes de salvá-lo.
                userDataFromServer.offlinePasswordHash = localUser.offlinePasswordHash;
                console.log(`[${obterHoraFormatada()}] syncServerToIndexedDB: Hash offline preservado durante a sincronização.`);
            }
            // --- FIM DA CORREÇÃO ---

            // 5. Agora sim, salva o objeto atualizado (e completo).
            await upsertUser(userDataFromServer);

            // A lógica para o token continua a mesma...
            if (userDataFromServer.userToken && userDataFromServer.userId) {
                await setUserToken(userDataFromServer.userToken, userDataFromServer.userId);
            }
        }

        // Buscar dados de controle de ponto
        const pointResponse = await fetch('../../Api/pointControl.php');
        const pointData = await pointResponse.json();

        // Verificamos se temos o ID do usuário que estamos sincronizando (vindo da primeira parte da função)
        const currentUserId = serverData?.userData?.userId;

        if (pointData && pointData.pointControl && Array.isArray(pointData.pointControl) && currentUserId) {
            // 1. CHAMA A NOVA FUNÇÃO para deletar apenas os registros do usuário atual.
            await deletePointControlByUserId(currentUserId);

            // 2. ADICIONA OS NOVOS DADOS vindos do servidor.
            const db = await initializeDB();
            const transaction = db.transaction(pointControlStoreName, 'readwrite');
            const pointControlStore = transaction.objectStore(pointControlStoreName);

            for (const point of pointData.pointControl) {
                // Garantimos que o registro tenha o userId correto antes de salvar
                point.userId = currentUserId;
                pointControlStore.add(point);
            }

            await new Promise(resolve => transaction.oncomplete = resolve);
            console.log(`[${obterHoraFormatada()}] Novos registros de ponto para o usuário ${currentUserId} foram adicionados.`);
        }
// --- FIM DA CORREÇÃO ---

        console.log(`[${obterHoraFormatada()}] Sincronização com o servidor concluída com sucesso`);
        return true;
    } catch (error) {
        console.error(`[${obterHoraFormatada()}] Erro ao sincronizar dados com o servidor:`, error);
        return false;
    }
}

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

        if (!navigator.onLine) {
            console.log(`[${obterHoraFormatada()}] Dispositivo offline. Ação será adicionada à fila de sincronização.`);
            // Esta função só deve ser chamada online, a lógica offline está no userController.js
            return false;
        }

        // --- INÍCIO DA MUDANÇA ---

        // 1. Obter o token de autenticação do localStorage.
        const userToken = localStorage.getItem('userToken');

        // 2. Se não houver token, a requisição não pode ser autenticada.
        if (!userToken) {
            console.error(`[${obterHoraFormatada()}] Erro ao bater o ponto: Token de utilizador não encontrado.`);
            alert("Sessão inválida. Por favor, faça o login novamente.");
            return false;
        }

        // --- FIM DA MUDANÇA ---

        console.log(`[${obterHoraFormatada()}] Enviando registo de ponto para o servidor com status: ${status}`);

        // O caminho da API foi corrigido para ser absoluto, como nas outras chamadas.
        const response = await fetch('/Public/Api/pointControl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 3. Adiciona o cabeçalho 'Authorization' com o token.
                // O formato "Bearer <token>" é um padrão de mercado.
                'Authorization': `Bearer ${userToken}`
            },
            // O corpo da requisição agora envia apenas o que o PHP espera.
            body: JSON.stringify({
                status: status
            })
        });

        if (!response.ok) {
            // Se a resposta for 401 (Não Autorizado) ou outro erro, trata aqui.
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
        console.error(`[${obterHoraFormatada()}] Erro fatal ao comunicar com o servidor para bater o ponto:`, error);
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
    //funções de serviço
    obterHoraFormatada,

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
