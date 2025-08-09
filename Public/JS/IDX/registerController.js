async function handleRegisterSubmit(event) {
    event.preventDefault(); // Impede o recarregamento da página

    const form = event.currentTarget;
    const submitButton = form.querySelector('button[type="submit"]');
    const responseElement = document.getElementById('response');

    // Coleta os dados do formulário
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    submitButton.disabled = true;
    responseElement.textContent = 'Registrando...';
    responseElement.className = 'text-muted';

    try {
        const response = await fetch('/controle-de-ponto/Public/Api/regist.php', {
            method: 'POST',
            body: new URLSearchParams(data)
        });

        const result = await response.json();

        if (result.success) {
            responseElement.className = 'text-success';
            responseElement.textContent = result.message + ' Redirecionando para o login...';
            setTimeout(() => {
                window.location.href = 'index.php'; // Redireciona para a página de login
            }, 2000);
        } else {
            responseElement.className = 'text-danger';
            responseElement.textContent = result.message;
        }

    } catch (error) {
        console.error('Erro ao registrar:', error);
        responseElement.className = 'text-danger';
        responseElement.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
    } finally {
        submitButton.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
});