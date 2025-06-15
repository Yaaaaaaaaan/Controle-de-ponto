// ========================
// 📁 Model.js
// ========================
import {
    initializeDB
} from './Config.js';


const userDataStoreName = 'userData';
const pointControlStoreName = 'pointControl';

// Função para adicionar um novo usuário
async function addUser(user) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const addRequest = userDataStore.add(user);


        return new Promise((resolve, reject) => {
            addRequest.onsuccess = () => resolve(addRequest.result);
            addRequest.onerror = () => reject('Erro ao adicionar usuário: ' + addRequest.error);
        });
    } catch (error) {
        throw new Error('Erro ao adicionar usuário: ' + error);
    }
}

async function addPointControl(pointControlData) {
    try {
        // Validate required fields
        if (!pointControlData.id || !pointControlData.cod || !pointControlData.descricao) {
            throw new Error('Dados de controle de ponto incompletos. Necessário: id, cod, descricao');
        }

        // Add timestamp if not provided
        if (!pointControlData.dateIn) {
            pointControlData.dateIn = new Date().toISOString();
        }

        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readwrite');
        const pointControlStore = transaction.objectStore(pointControlStoreName);

        console.log('Adicionando dados de controle de ponto:', pointControlData);

        const addRequest = pointControlStore.add(pointControlData);

        return new Promise((resolve, reject) => {
            addRequest.onsuccess = () => {
                console.log('Dados de controle de ponto adicionados com sucesso!');
                resolve(addRequest.result);
            };
            addRequest.onerror = (event) => {
                console.error('Erro ao adicionar dados de controle de ponto:', event.target.error);
                reject('Erro ao adicionar dados de controle de ponto: ' + event.target.error);
            };
        });
    } catch (error) {
        console.error('Erro ao processar dados de controle de ponto:', error);
        throw new Error('Erro ao adicionar dados de controle de ponto: ' + error);
    }
}


// Função para obter um usuário pelo ID
async function getUserById(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const getRequest = userDataStore.get(id);


        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject('Erro ao obter usuário: ' + getRequest.error);
        });
    } catch (error) {
        throw new Error('Erro ao obter usuário: ' + error);
    }
}

// Função para obter um usuário pelo ID
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
        throw new Error('Erro ao obter usuário: ' + error);
    }
}


// Função para atualizar um usuário
async function updateUser(user) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const putRequest = userDataStore.put(user);


        return new Promise((resolve, reject) => {
            putRequest.onsuccess = () => resolve();
            putRequest.onerror = () => reject('Erro ao atualizar usuário: ' + putRequest.error);
        });
    } catch (error) {
        throw new Error('Erro ao atualizar usuário: ' + error);
    }
}


// Função para deletar um usuário pelo ID
async function deleteUser(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const deleteRequest = userDataStore.delete(id);


        return new Promise((resolve, reject) => {
            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject('Erro ao deletar usuário: ' + deleteRequest.error);
        });
    } catch (error) {
        throw new Error('Erro ao deletar usuário: ' + error);
    }
}


// Função para obter todos os usuários
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
// Função para obter dados de usuário específico ** TODO
async function getUser(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const getRequest = userDataStore.get(id);
        return new Promise((resolve, reject) => {
            getRequest.onsuccess = () => resolve(getRequest.result);
            getRequest.onerror = () => reject(getRequest.error);
        });
    } catch (error) {
        console.error("Erro ao obter usuário:", error);
        return [];
    }
}



// Função para sincronizar dados do IndexedDB com o servidor
async function syncIndexedDBToServer(userToken, theme) {
    try {
        if (!userToken || typeof theme === 'undefined') {
            console.error("Erro ao sincronizar com o servidor: Tema ou token de usuário não especificados");
            return;
        }

        const response = await fetch('../../Persistence/userData.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userToken, theme })
        });

        const data = await response.json();
        if (data.success) {
            console.log("Tema atualizado no servidor com sucesso!");
        } else {
            console.error("Erro ao atualizar tema no servidor:", data.message);
        }
    } catch (error) {
        //console.error("Erro ao comunicar com o servidor:", error);
    }
}



// Função para processar dados do usuário
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

        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);

        userDataStore.clear();
        userDataStore.add(serverUserData); // Assumindo objeto único

        await new Promise((resolve, reject) => {
            transaction.oncomplete = () => resolve();
            transaction.onerror = () => reject('Erro na transação IndexedDB');
        });

        return serverUserData; // ← objeto, não array

    } catch (error) {
        console.error("Erro ao processar dados do usuário:", error);
        return null;
    }
}
//Model.js

// Função para sincronizar dados do servidor com o IndexedDB
async function syncServerToIndexedDB() {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readwrite');
        const userDataStore = transaction.objectStore(userDataStoreName);


        const response = await fetch('../../Persistence/userData.php');
        const data = await response.json();


        if (data && data.userData) {
            userDataStore.clear();

            // Handle both array and object formats
            if (Array.isArray(data.userData) && data.userData.length > 0) {
                data.userData.forEach(user => {
                    userDataStore.add(user);
                });
            } else if (typeof data.userData === 'object' && Object.keys(data.userData).length > 0) {
                userDataStore.add(data.userData);
            }

            await new Promise((resolve, reject) => {
                transaction.oncomplete = resolve;
                transaction.onerror = reject;
            });
            console.log("syncServerToIndexedDB: Dados sincronizados do servidor para IndexedDB.");
        } else {
            console.log("syncServerToIndexedDB: Sem dados para sincronizar.");
        }
    } catch (error) {
        console.error("syncServerToIndexedDB: Erro de sincronismo de dados.", error);
    }
}


// Função para obter todos os registros de controle de ponto
async function getAllPointControlData() {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(pointControlStoreName, 'readonly');
        const pointControlStore = transaction.objectStore(pointControlStoreName);
        const request = pointControlStore.getAll();

        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    } catch (error) {
        console.error("Erro ao obter dados de controle de ponto:", error);
        return [];
    }
}

// Função para obter registros de controle de ponto de um usuário específico
async function getPointControlDataByUserId(userId) {
    try {
        const allData = await getAllPointControlData();
        return allData.filter(item => item.uidUserFK === userId);
    } catch (error) {
        console.error("Erro ao filtrar dados de controle de ponto por usuário:", error);
        return [];
    }
}

// Função para sincronizar dados de controle de ponto com o servidor
async function syncPointControlData() {
    try {
        // Primeiro, tenta obter dados do servidor
        const response = await fetch('../../Persistence/pointControl.php');
        const data = await response.json();

        // Se tiver dados do servidor, atualiza o IndexedDB
        if (data && data.pointControl) {
            const db = await initializeDB();
            const transaction = db.transaction(pointControlStoreName, 'readwrite');
            const pointControlStore = transaction.objectStore(pointControlStoreName);

            // Limpa os dados existentes
            pointControlStore.clear();

            // Adiciona os novos dados
            if (Array.isArray(data.pointControl)) {
                for (const item of data.pointControl) {
                    pointControlStore.add(item);
                }
            } else if (typeof data.pointControl === 'object') {
                pointControlStore.add(data.pointControl);
            }

            await new Promise((resolve, reject) => {
                transaction.oncomplete = resolve;
                transaction.onerror = reject;
            });

            console.log("Dados de controle de ponto sincronizados do servidor para IndexedDB");
            return true;
        }

        return false;
    } catch (error) {
        console.error("Erro ao sincronizar dados de controle de ponto:", error);
        return false;
    }
}

export {
    addUser,
    getUserById,
    updateUser,
    deleteUser,
    getUser,
    getAllUsers,
    syncIndexedDBToServer,
    syncServerToIndexedDB,
    processUserData,
    addPointControl,
    getAllPointControlData,
    getPointControlDataByUserId,
    syncPointControlData
};
