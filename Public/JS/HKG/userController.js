import * as UserService from './userService.js';
import * as UI from './userInterface.js';

let state = { users: [] };

// Auto-inicialização (Isso permite que o main.js funcione apenas com o import)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init(); // Se já carregou via main.js atrasado, roda direto
}

function init() {
    loadUsers();
    setupEventListeners();
}

async function loadUsers() {
    UI.showLoading(true);
    try {
        const data = await UserService.fetchAllUsers();
        state.users = data;
        UI.renderTable(state.users);
    } catch (error) {
        console.error(error);
        // Opcional: UI.showError('Erro ao carregar');
    } finally {
        // RenderTable remove o loading
    }
}

function setupEventListeners() {
    const searchInput = document.getElementById('searchUserInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = state.users.filter(u => 
                u.nome_completo.toLowerCase().includes(term) ||
                u.nome_usuario.toLowerCase().includes(term) ||
                u.email.toLowerCase().includes(term)
            );
            UI.renderTable(filtered);
        });
    }

    const btnNew = document.getElementById('btnNewUser');
    if (btnNew) {
        btnNew.addEventListener('click', () => UI.openOffcanvas('create'));
    }

    const form = document.getElementById('manageUserForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    const tbody = document.getElementById('usersTableBody');
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const btnEdit = e.target.closest('.btn-action-edit');
            const btnDelete = e.target.closest('.btn-action-delete');

            if (btnEdit) {
                const id = btnEdit.dataset.id;
                const user = state.users.find(u => u.id_usuario == id);
                if (user) UI.openOffcanvas('edit', user);
            }

            if (btnDelete) {
                handleDelete(btnDelete.dataset.id);
            }
        });
    }
}

export async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    UI.toggleButtonLoading('btnSaveUser', true);

    try {
        await UserService.saveUser(formData);
        UI.closeOffcanvas();
        await loadUsers();
    } catch (error) {
        alert(error.message);
    } finally {
        UI.toggleButtonLoading('btnSaveUser', false);
    }
}

export async function handleDelete(id) {
    if (!confirm('Atenção: O usuário perderá acesso ao sistema. Continuar?')) return;
    try {
        await UserService.deleteUser(id);
        await loadUsers();
    } catch (error) {
        alert(error.message);
    }
}