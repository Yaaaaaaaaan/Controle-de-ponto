//Controller.js
document.getElementById("loginForm").addEventListener("submit", async function (e) {

    e.preventDefault();

    const nickname = document.getElementById("nickname").value;
    const password = document.getElementById("password").value;

    if (!navigator.onLine) {
        // LOGIN OFFLINE via IndexedDB
        const user = await getUserByNickname(nickname);
        if (!user) {
            alert("Usuário não encontrado no cache offline.");
            return;
        }

        const passwordMatch = password === user.password; // Aqui idealmente comparar hash
        if (passwordMatch) {
            // Simula sessão local (pode usar localStorage também)
            //localStorage.setItem("loggedUser", JSON.stringify(user));
            window.location.href = "../User/index.html";
        } else {
            alert("Senha incorreta (offline)");
        }
    } else {
        // LOGIN ONLINE (permite que o PHP processe normalmente)
        this.submit();
    }
});

async function getUserByNickname(nickname) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open("userData", 1);
        request.onerror = () => reject("Erro ao abrir IndexedDB");
        request.onsuccess = () => {
            const db = request.result;
            const tx = db.transaction("users", "readonly");
            const store = tx.objectStore("users");
            const getReq = store.get(nickname);

            getReq.onsuccess = () => resolve(getReq.result);
            getReq.onerror = () => reject("Erro ao buscar usuário");
        };
    });
}
