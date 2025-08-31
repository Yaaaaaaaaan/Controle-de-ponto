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
 * [ASPECTO] Envolve uma função de API com lógica de manipulação de UI.
 * Gerencia o estado do botão, exibe feedback de carregamento, sucesso e erro.
 * @param {Function} apiFunction - A função assíncrona que executa a lógica de negócio.
 * @param {object} options - Opções de configuração.
 * @param {string} options.feedbackId - O ID do elemento para exibir o feedback.
 * @param {HTMLButtonElement} options.button - O botão de submit a ser desabilitado/reabilitado.
 * @returns {Function} Uma nova função "decorada" com os comportamentos do aspecto.
 */
export function withApiHandler(apiFunction, { feedbackId, button }) {
    return async function(...args) {
        const originalButtonText = button.textContent;
        button.disabled = true;
        button.textContent = 'Processando...';
        displayFeedback(feedbackId, 'Enviando dados...', 'info');

        try {
            await apiFunction(...args); // Executa a lógica de negócio original
            displayFeedback(feedbackId, 'Operação concluída com sucesso!', 'success');
            return true; // Retorna sucesso
        } catch (error) {
            console.error(`Erro na operação da API '${apiFunction.name}':`, error);
            displayFeedback(feedbackId, `Erro: ${error.message}`, 'error');
            return false; // Retorna falha
        } finally {
            button.disabled = false;
            button.textContent = originalButtonText; // Restaura o texto original
        }
    };
}