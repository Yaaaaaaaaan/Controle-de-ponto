// ========================
// 📁 Model.js
// ========================
import {
    initializeDB
} from './Config.js';


const userDataStoreName = 'userData';


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
// Função para obter todos os usuários
async function getUser(id) {
    try {
        const db = await initializeDB();
        const transaction = db.transaction(userDataStoreName, 'readonly');
        const userDataStore = transaction.objectStore(userDataStoreName);
        const getRequest = userDataStore.get(id);
        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
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


        if (data && data.userData && Array.isArray(data.userData) && data.userData.length > 0) {
            userDataStore.clear();
            data.userData.forEach(user => { // Acesse data.userData aqui
                userDataStore.add(user);
            });
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


export {
    addUser,
    getUserById,
    updateUser,
    deleteUser,
    getUser,
    getAllUsers,
    syncIndexedDBToServer,
    syncServerToIndexedDB,
    processUserData
};