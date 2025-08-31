// ==========================
// 📁 registerController.js
// ==========================

import { displayFeedback } from '../Cogs/utils.js';


async function handleRegisterSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const submitButton = form.querySelector('button[type="submit"]');

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    submitButton.disabled = true;
    displayFeedback('responseAction', 'Registrando...', 'info');

    try {
        const response = await fetch('/Public/Api/regist.php', {
            method: 'POST',
            body: new URLSearchParams(data)
        });
        const result = await response.json();
        if (result.success) {
            displayFeedback('responseAction', result.message + ' Redirecionando...', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            displayFeedback('responseAction', result.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao registrar:', error);
        displayFeedback('responseAction', 'Ocorreu um erro de comunicação. Tente novamente.', 'error');
    } finally {
        submitButton.disabled = false;
    }
}

export function initializeRegisterController() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
}