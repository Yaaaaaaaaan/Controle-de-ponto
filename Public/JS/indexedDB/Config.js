// ========================
// 📁 Config.js
// ========================
const dbName = 'PCDB';
const dbVersion = 1;
const userDataStoreName = 'userData';
const pointControlStoreName = 'pointControl'

let db;

function initializeDB() {
    return new Promise((resolve, reject) => {
        if (db) {
            resolve(db);
            return;
        }

        const request = indexedDB.open(dbName, dbVersion);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(userDataStoreName)) {
                const userDataStore = db.createObjectStore(userDataStoreName, {
                    keyPath: 'nickname', name: 'userData'
                });
                userDataStore.createIndex('userData', 'userToken', {
                    unique: true
                });
            }
            if (!db.objectStoreNames.contains(pointControlStoreName)) {
                const pointControlStore = db.createObjectStore(pointControlStoreName, {
                    keyPath: 'id', name: 'pointControl'
                });
                pointControlStore.createIndex('pointControl', 'cod', {
                    unique: true
                });
                pointControlStore.createIndex('pointControl', 'descricao', {
                    unique: true
                });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = (event) => {
            reject('Erro ao abrir o banco de dados: ' + event.target.errorCode);
        };
    });
}

export { initializeDB, userDataStoreName, pointControlStoreName };
