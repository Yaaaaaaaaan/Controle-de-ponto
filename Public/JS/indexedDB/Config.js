// ========================
// 📁 Config.js
// ========================
const dbName = 'PCDB';
const dbVersion = 1;
const userDataStoreName = 'userData';
const pointControlStoreName = 'pointControl';

const userPicturesStoreName = 'userPictures';


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
                    keyPath: 'nickname'
                });
                userDataStore.createIndex('userData', 'userToken', {
                    unique: true
                });
            }
            if (!db.objectStoreNames.contains(pointControlStoreName)) {
                const pointControlStore = db.createObjectStore(pointControlStoreName, {
                    keyPath: 'id'
                });
                pointControlStore.createIndex('pointControlCod', 'cod', {
                    unique: true
                });
                pointControlStore.createIndex('pointControlDesc', 'descricao', {
                    unique: false
                });
            }
            if (!db.objectStoreNames.contains(userPicturesStoreName)) {
                const userPicturesStore = db.createObjectStore(userPicturesStoreName, {
                    keyPath: 'id'
                });
                userPicturesStore.createIndex('userPictures', 'userToken', {})
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
