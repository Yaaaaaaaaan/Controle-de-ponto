
/**
 * Aplica o tema visual (light/dark) à página.
 * @param {0|1} themeValue - 0 para claro, 1 para escuro.
 */
export function applyTheme(themeValue) {
    const theme = themeValue == 1 ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', theme);
    console.log(`Tema alterado para: ${theme}`);
}

/**
 * Lê os dados do usuário do IndexedDB e aplica o tema salvo.
 * @param {object} userData - O objeto de dados do usuário.
 */
export function initializeThemeFromLocalData(userData) {
    if (userData && userData.theme !== undefined) {
        applyTheme(userData.theme);
    }
}

//teste