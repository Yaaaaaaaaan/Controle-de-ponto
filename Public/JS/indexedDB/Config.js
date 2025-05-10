const dbName = 'PCDB';
const dbVersion = 1;
const userDataStoreName = 'userData';


let db;


// Função para inicializar o banco de dados (Singleton)
function initializeDB() {
    return new Promise((resolve, reject) => {
        if (db) { // Se o banco já estiver aberto, use a instância existente
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
                //userDataStore.createIndex('userToken', 'userToken', {
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


export {
    initializeDB
};