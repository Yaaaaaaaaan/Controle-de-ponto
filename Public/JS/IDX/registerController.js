// ==========================
// 📁 registerController.js
// ==========================

import { displayFeedback, showToast, withApiHandler } from '../Cogs/utils.js';


// 1. A lógica de negócio é separada em sua própria função
async function registerUser(data) {
    const response = await fetch('/Public/Api/regist.php', {
        method: 'POST',
        // ATENÇÃO: Enviando como application/x-www-form-urlencoded
        // para corresponder ao seu regist.php, que usa $_POST
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
    });

    if (!response.ok) {
        // Tenta extrair a mensagem de erro do JSON, se houver
        const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido no servidor.' }));
        throw new Error(errorData.message || `HTTP ${response.status}`);
    }

    const result = await response.json();
    if (!result.success) throw new Error(result.message);

    return result; // Retorna o resultado para uso futuro
}

// 2. O manipulador de evento agora orquestra e usa o AOP
async function handleRegisterSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const data = Object.fromEntries(new FormData(form).entries());

    // Validação de campos (pode ser expandida)
    if (!data.name || !data.nickname || !data.email || !data.password) {
        displayFeedback('responseAction', 'Todos os campos são obrigatórios.', 'error');
        return;
    }

    // Cria a função "decorada" com nosso aspecto AOP
    const handleApiRegister = withApiHandler(registerUser, { button });

    // Executa a função decorada
    const result = await handleApiRegister(data);

    // Ação pós-sucesso
    if (result) {
        showToast('Registro bem-sucedido! Redirecionando para o login...', 'success');
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2000);
    }
}

export function initializeRegisterController() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
}