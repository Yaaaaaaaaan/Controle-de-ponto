// ========================
// 📁 utils.js
// ========================

/**
 * Exibe uma mensagem de feedback em um elemento específico da página.
 * @param {string} elementId - O ID do elemento onde a mensagem será exibida.
 * @param {string} message - A mensagem a ser exibida.
 * @param {'success'|'error'|'info'} type - O tipo de mensagem para aplicar a classe CSS correta.
 */
export function displayFeedback(elementId, message, type = 'info') {
    const element = document.getElementById(elementId);
    if (!element) return;

    const typeToClass = {
        success: 'text-success',
        error: 'text-danger',
        info: 'text-muted'
    };

    element.textContent = message;
    element.className = typeToClass[type] || 'text-muted';
}
/**
 * [AOP - ASPECTO] Envolve uma função de API com lógica de manipulação de UI,
 * usando Toasts apenas para o resultado final (sucesso ou erro).
 * @param {Function} apiFunction - A função assíncrona que executa a lógica de negócio.
 * @param {object} options - Opções de configuração.
 * @param {HTMLButtonElement} options.button - O botão de submit a ser gerenciado.
 * @returns {Function} Uma nova função "decorada".
 */
export function withApiHandler(apiFunction, { button }) {
    return async function(...args) {
        const originalButtonText = button.textContent;
        button.disabled = true;
        button.textContent = 'Processando...';

        try {
            await apiFunction(...args); // Executa a lógica de negócio original

            showToast('Operação concluída com sucesso!', 'success');

            return true; // Retorna sucesso
        } catch (error) {
            console.error(`Erro na operação da API '${apiFunction.name}':`, error);

            // Mostra o toast de erro
            showToast(`Erro: ${error.message}`, 'error');

            return false; // Retorna falha
        } finally {
            // Reabilita o botão
            button.disabled = false;
            button.textContent = originalButtonText;
        }
    };
}


/**
 * [FUNÇÃO SHOWTOAST ATUALIZADA] para retornar o elemento e controlar o autohide.
 */
export function showToast(message, type = 'success', options = { autohide: true, delay: 5000 }) {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return null;

    const toastId = 'toast-' + Date.now();
    const bgClass = { success: 'bg-success', error: 'bg-danger', info: 'bg-info' }[type];
    const autohideAttr = options.autohide ? 'true' : 'false';
    const delayAttr = options.delay || 5000;

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="${autohideAttr}" data-bs-delay="${delayAttr}">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());

    return toastElement; // Retorna o elemento para controle externo
}
