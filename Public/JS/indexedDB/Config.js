// ========================
// 📁 Config.js
// ========================
const dbName = 'PCDB';
const dbVersion = 2; // Incrementado para forçar upgrade
const userDataStoreName = 'userData';
const pointControlStoreName = 'pointControl';
const userTokenStoreName = 'userToken';

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

            // Criar ou atualizar userData store
            if (!db.objectStoreNames.contains(userDataStoreName)) {
                const userDataStore = db.createObjectStore(userDataStoreName, {
                    keyPath: 'nickname'
                });
                userDataStore.createIndex('userTokenIdx', 'userToken', { unique: true });
                userDataStore.createIndex('idIdx', 'id', { unique: true });
            }

            // Criar ou atualizar pointControl store
            if (!db.objectStoreNames.contains(pointControlStoreName)) {
                const pointControlStore = db.createObjectStore(pointControlStoreName, {
                    keyPath: 'cod', autoIncrement: true
                });
                pointControlStore.createIndex('userIdIdx', 'uidUserFK', { unique: false });
                pointControlStore.createIndex('statusIdx', 'status', { unique: false });
                pointControlStore.createIndex('dateIdx', 'dateIn', { unique: false });
            }

            // Criar ou atualizar userToken store
            if (!db.objectStoreNames.contains(userTokenStoreName)) {
                const userTokenStore = db.createObjectStore(userTokenStoreName, {
                    keyPath: 'token'
                });
                userTokenStore.createIndex('userIdIdx', 'uidUserFK', { unique: true });
                userTokenStore.createIndex('lastUpdatedIdx', 'lastUpdated', { unique: false });
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

export { initializeDB, userDataStoreName, pointControlStoreName, userTokenStoreName };
