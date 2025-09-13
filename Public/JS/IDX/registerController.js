// ==========================
// 📁 registerController.js
// ==========================

import { showToast, withApiHandler, getCurrentPosition } from '../Cogs/utils.js';
import { storeAuthData } from '../indexedDB/Model.js';
import { hashPassword } from './authController.js';


// 1. A lógica de negócio é separada em sua própria função
async function registerUser(data) {
    const response = await fetch('/Public/Api/regist.php', {
        method: 'POST',
        // ATENÇÃO: Enviando como application/x-www-form-urlencoded
        // para corresponder ao seu regist.php, que usa $_POST
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const result = await response.json();
    if (!response.ok) {
        throw new Error(result.message || 'Erro desconhecido no servidor.');
    }
    if (!result.success){
        throw new Error(result.message);
    }

    return result; // Retorna o resultado para uso futuro
}

async function handleRegisterSuccess(result, password) {
    const {session} = result;
    const { userData, tokenData } = session;

    localStorage.setItem('activeUserId', userData.userId);
    localStorage.setItem('userToken', tokenData.userToken);

    const offlinePasswordHash = await hashPassword(password);
    await storeAuthData(userData, tokenData, offlinePasswordHash);

    showToast('Registro bem-sucedido! A entrar...', 'success');
    setTimeout(()=>
    {
        console.log("redirecionando para User/index.php...")
        window.location.href = "../User/index.php";
    }, 2000);
}

// 3. O manipulador de evento agora orquestra e usa o AOP
async function handleRegisterSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const data = Object.fromEntries(new FormData(form).entries());
    const password = data.password;

    // Validação de campos (pode ser expandida)
    if (!data.name || !data.nickname || !data.email || !data.password) {
        showToast( 'Todos os campos são obrigatórios.', 'error');
        return;
    }

    //Captura as coordenadas antes de enviar o registo.
    const coords = await getCurrentPosition();
    if (coords){
        data.latitude = coords.latitude;
        data.longitude = coords.longitude;
    }

    // Cria a função "decorada" com nosso aspecto AOP
    const handleApiRegister = withApiHandler(registerUser, { button
    , onSuccess: (result) => handleRegisterSuccess(result, password)});

    await handleApiRegister(data);

}

export function initializeRegisterController() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
}