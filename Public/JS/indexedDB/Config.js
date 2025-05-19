// ========================
// 📁 Config.js
// ========================
const dbName = 'PCDB';
const dbVersion = 1;
const userDataStoreName = 'userData';

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
                    keyPath: 'userToken'
                });
                //userDataStore.createIndex('nickname', 'nickname', {
                //    unique: true
                //});
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

export { initializeDB, userDataStoreName };
