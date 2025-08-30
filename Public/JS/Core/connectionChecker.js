// ========================
// 📁 connectionChecker.js
// ========================
import { obterHoraFormatada } from '../indexedDB/Config.js';

// Estado inicial da aplicação. Assumimos online até que a primeira verificação falhe.
let isOnline = true;

/**
 * Dispara um evento global para notificar outras partes da aplicação sobre a mudança de status.
 * @param {string} eventName - O nome do evento ('app:online' or 'app:offline').
 * @param {boolean} status - O novo estado da conexão.
 */
function dispatchStatusChangeEvent(eventName, status) {
    console.log(`[${obterHoraFormatada()}] Status da conexão alterado para: ${status ? 'ONLINE' : 'OFFLINE'}. Disparando evento '${eventName}'.`);
    window.dispatchEvent(new CustomEvent(eventName, { detail: { isOnline: status } }));
}

/**
 * Tenta contatar o servidor para verificar a conectividade real.
 * Atualiza o estado e dispara eventos apenas se o estado mudar.
 */
async function checkServerStatus() {
    try {
        // CORREÇÃO: Adicionado um parâmetro "cache buster" para evitar o cache do Service Worker.
        const response = await fetch(`/Public/Api/healthCheck.php?t=${new Date().getTime()}`, { cache: 'no-store' });

        if (response.ok) {
            if (!isOnline) {
                isOnline = true;
                dispatchStatusChangeEvent('app:online', isOnline);
            }
        } else {
            if (isOnline) {
                isOnline = false;
                dispatchStatusChangeEvent('app:offline', isOnline);
            }
        }
    } catch (error) {
        if (isOnline) {
            isOnline = false;
            dispatchStatusChangeEvent('app:offline', isOnline);
        }
    }
}


/**
 * Inicializa o verificador de conexão.
 * @param {number} interval - O intervalo em milissegundos para verificar a conexão.
 */
function initializeConnectionChecker(interval = 15000) { // Verifica a cada 15 segundos
    console.log(`[${obterHoraFormatada()}] Verificador de conexão inicializado.`);

    // Verifica imediatamente no carregamento da página.
    checkServerStatus();

    // Configura a verificação periódica.
    setInterval(checkServerStatus, interval);
}

// Exporta o estado atual e a função de inicialização.
export { isOnline, initializeConnectionChecker };