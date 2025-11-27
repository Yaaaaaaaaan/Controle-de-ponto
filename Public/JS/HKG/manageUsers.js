// Public/JS/HKG/manageUsers.js

let allUsers = []; // Armazena a lista localmente para busca rápida

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    setupSearch();
    setupFormSubmit();
});

// 1. Carregar Usuários da API
async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');
    
    try {
        const response = await fetch('../../Api/getUsers.php');
        if (!response.ok) throw new Error('Erro ao buscar usuários');
        
        allUsers = await response.json();
        renderTable(allUsers);
        
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Erro ao carregar dados.</td></tr>`;
    }
}

// 2. Renderizar Tabela
function renderTable(users) {
    const tbody = document.getElementById('usersTableBody');
    const countElement = document.getElementById('totalUsersCount');
    
    // --- PROTEÇÃO CONTRA ERROS ---
    // Se users for null, undefined ou não for um array, tratamos como vazio
    if (!users || !Array.isArray(users)) {
        console.error("Dados inválidos recebidos da API:", users);
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Erro: Dados inválidos. Verifique o Console.</td></tr>`;
        if(countElement) countElement.textContent = '0';
        return;
    }
    // -----------------------------

    if(countElement) countElement.textContent = users.length;
    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>`;
        return;
    }

    users.forEach(user => {
        const avatar = user.foto_perfil ? user.foto_perfil : '../../Api/userProfileImages/Profile.png';
        const badge = user.nivel_acesso == 1 
            ? '<span class="badge bg-primary bg-opacity-10 text-primary">Admin</span>' 
            : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Padrão</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4"><img src="${avatar}" class="avatar-img shadow-sm" style="width:40px; height:40px; border-radius:50%; object-fit:cover;"></td>
            <td>
                <div class="fw-bold text-dark">${user.nome_completo || 'Sem Nome'}</div>
                <div class="small text-muted">@${user.nome_usuario || 'user'}</div>
            </td>
            <td>${user.email}</td>
            <td>${badge}</td>
            <td class="text-end pe-4">
    <button class="btn btn-sm btn-light text-primary me-1 action-btn" onclick="editarUsuario(${user.id_usuario})" title="Editar">
        <i class="fas fa-pen"></i> </button>
    <button class="btn btn-sm btn-light text-danger action-btn" onclick="excluirUsuario(${user.id_usuario})" title="Inativar">
        <i class="fas fa-trash"></i> </button>
</td>
        `;
        tbody.appendChild(tr);
    });
}
// 3. Pesquisa em Tempo Real
function setupSearch() {
    const searchInput = document.getElementById('searchUser');
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = allUsers.filter(user => 
            user.nome_completo.toLowerCase().includes(term) || 
            user.nome_usuario.toLowerCase().includes(term) || 
            user.email.toLowerCase().includes(term)
        );
        renderTable(filtered);
    });
}

// 4. Preparar Modal "Novo" (Renomeado para clareza)
// 4. Preparar Modal "Novo"
window.prepararNovoUsuario = function() {
    document.getElementById('manageUserForm').reset();
    document.getElementById('userId').value = ''; 
    document.getElementById('userFormLabel').textContent = 'Novo Usuário';
    document.getElementById('passwordHelp').textContent = 'Obrigatório para novos usuários.';
    
    // Imagem padrão
    document.getElementById('previewAvatar').src = '../../Assets/img/userProfileImages/Profile.png';
    
    // --- CORREÇÃO: ESCONDER O BOTÃO ---
    const btnAvatar = document.getElementById('btnChangeAvatar');
    if (btnAvatar) {
        btnAvatar.classList.add('d-none'); // Adiciona classe que esconde
    }
    // ----------------------------------

    document.getElementById('lblSenhaAccordion').textContent = 'Definir senha';
    
    const collapseElement = document.getElementById('collapseOne');
    if (collapseElement && !collapseElement.classList.contains('show')) {
        new bootstrap.Collapse(collapseElement, { toggle: true });
    }
}

// 5. Abrir Modal "Editar"
window.editarUsuario = function(id) {
    const user = allUsers.find(u => u.id_usuario == id);
    if (!user) return;

    document.getElementById('userId').value = user.id_usuario;
    document.getElementById('userName').value = user.nome_completo;
    document.getElementById('userNickname').value = user.nome_usuario;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRank').value = user.nivel_acesso;
    document.getElementById('userPassword').value = '';
    
    document.getElementById('userFormLabel').textContent = 'Editar Usuário';
    document.getElementById('passwordHelp').textContent = 'Deixe vazio para manter a senha atual.';
    document.getElementById('lblSenhaAccordion').textContent = 'Deseja alterar a senha?';
    
    const collapseElement = document.getElementById('collapseOne');
    if (collapseElement && collapseElement.classList.contains('show')) {
        new bootstrap.Collapse(collapseElement, { toggle: true });
    }
    
    if(user.foto_perfil) {
        document.getElementById('previewAvatar').src = user.foto_perfil;
    } else {
        document.getElementById('previewAvatar').src = '../../Assets/img/userProfileImages/Profile.png';
    }

    // --- CORREÇÃO: MOSTRAR O BOTÃO ---
    const btnAvatar = document.getElementById('btnChangeAvatar');
    if (btnAvatar) {
        btnAvatar.classList.remove('d-none'); // Remove classe que esconde
    }
    // ----------------------------------

    const offcanvasEl = document.getElementById('userFormCanvas');
    const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
    bsOffcanvas.show();
}

// 6. Envio do Formulário (PREPARADO, MAS FALTA A API SAVE)
function setupFormSubmit() {
    const form = document.getElementById('manageUserForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        alert("A API de salvar (saveUser.php) será implementada no próximo passo!");
        // Aqui entraremos com o fetch para salvar
    });
}

function setupFormSubmit() {
    const form = document.getElementById('manageUserForm');
    if(!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('btnSaveUser'); // Adicione este ID no botão do HTML se não tiver
        const originalText = btn ? btn.innerText : 'Salvar';
        if(btn) { btn.disabled = true; btn.innerText = 'Salvando...'; }

        const formData = new FormData(form);

        try {
            // Caminho absoluto para evitar erro 404
            const response = await fetch('/Public/Api/saveUser.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Fecha o menu
                const offcanvasEl = document.getElementById('userFormCanvas');
                const modal = bootstrap.Offcanvas.getInstance(offcanvasEl);
                modal.hide();
                
                // Recarrega a lista
                loadUsers(); 
                
                // Feedback visual (opcional, se tiver toast)
                // alert('Salvo com sucesso!');
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Erro de conexão com o servidor.');
        } finally {
            if(btn) { btn.disabled = false; btn.innerText = originalText; }
        }
    });
}

// 8. Função de Excluir (Soft Delete)
window.excluirUsuario = async function(id) {
    if (!confirm('Tem certeza que deseja inativar este usuário?')) { return; }

    try {
        // CORRIGIDO O CAMINHO AQUI
        const response = await fetch('/Public/Api/deleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        // ...
    } catch (error) {
        console.error(error);
        alert('Erro ao processar exclusão.');
    }
}