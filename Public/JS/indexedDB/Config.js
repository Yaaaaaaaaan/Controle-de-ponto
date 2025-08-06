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
                userDataStore.createIndex('id', 'userId', { unique: true });
            }

            // Criar ou atualizar pointControl store
            if (!db.objectStoreNames.contains(pointControlStoreName)) {
                const pointControlStore = db.createObjectStore(pointControlStoreName, {
                    keyPath: 'cod', autoIncrement: true
                });
                pointControlStore.createIndex('userIdIdx', 'userId', { unique: false });
                pointControlStore.createIndex('status', 'status', { unique: false });
                pointControlStore.createIndex('date', 'dateIn', { unique: false });
            }

            // Criar ou atualizar userToken store
            if (!db.objectStoreNames.contains(userTokenStoreName)) {
                const userTokenStore = db.createObjectStore(userTokenStoreName, {
                    keyPath: 'token'
                });
                userTokenStore.createIndex('userId', 'userId', { unique: true });
                userTokenStore.createIndex('lastUpdated', 'lastUpdated', { unique: false });
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
