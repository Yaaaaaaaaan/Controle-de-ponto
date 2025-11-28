import { handleUserSubmit, handleDeleteUser } from './userController.js';
let _localUsersData = [];

export function renderTable(users) {
    _localUsersData = users;
    const tbody = document.getElementById('usersTableBody');
    const countEl = document.getElementById('totalUsersCount');
    
    if (countEl) countEl.textContent = users.length;
    tbody.innerHTML = '';

    if (!users || users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>`;
        return;
    }

    users.forEach(user => {
        const avatar = user.foto_perfil || '../../Assets/img/userProfileImages/Profile.png';
        const badgeClass = user.nivel_acesso == 1 ? 'bg-primary text-primary' : 'bg-secondary text-secondary';
        const badgeLabel = user.nivel_acesso == 1 ? 'Admin' : 'Padrão';
        const badgeHtml = `<span class="badge ${badgeClass} bg-opacity-10">${badgeLabel}</span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4"><img src="${avatar}" class="rounded-circle border" style="width:40px; height:40px; object-fit:cover;"></td>
            <td><div class="fw-bold text-dark">${user.nome_completo || 'Sem Nome'}</div><div class="small text-muted">@${user.nome_usuario}</div></td>
            <td>${user.email}</td>
            <td>${badgeHtml}</td>
            <td class="text-end pe-4">
                <button class="btn btn-sm btn-light text-primary me-1 btn-action-edit" data-id="${user.id_usuario}" title="Editar"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-light text-danger btn-action-delete" data-id="${user.id_usuario}" title="Inativar"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

export function showLoading(isLoading) {
    const tbody = document.getElementById('usersTableBody');
    if (isLoading && tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span> Carregando...</td></tr>';
}

export function toggleButtonLoading(btnId, isLoading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (isLoading) {
        btn.dataset.originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
    } else {
        btn.disabled = false;
        if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
    }
}

export function openOffcanvas(mode, userData = null) {
    const form = document.getElementById('manageUserForm');
    const label = document.getElementById('userFormLabel');
    const btnAvatar = document.getElementById('btnChangeAvatar');
    const imgAvatar = document.getElementById('previewAvatar');
    const accordionLabel = document.getElementById('lblSenhaAccordion');
    
    form.reset();
    
    // Reset Accordion
    const collapse = document.getElementById('collapseOne');
    if (collapse && collapse.classList.contains('show')) {
        new bootstrap.Collapse(collapse, { toggle: true });
    }

    if (mode === 'create') {
        label.textContent = 'Novo Usuário';
        document.getElementById('userId').value = '';
        if(btnAvatar) btnAvatar.classList.add('d-none');
        if(imgAvatar) imgAvatar.src = '../../Assets/img/userProfileImages/Profile.png';
        if(accordionLabel) accordionLabel.textContent = 'Definir Senha';
        
        if (collapse && !collapse.classList.contains('show')) new bootstrap.Collapse(collapse, { toggle: true });

    } else if (mode === 'edit' && userData) {
        label.textContent = 'Editar Usuário';
        document.getElementById('userId').value = userData.id_usuario;
        document.getElementById('userName').value = userData.nome_completo;
        document.getElementById('userNickname').value = userData.nome_usuario;
        document.getElementById('userEmail').value = userData.email;
        document.getElementById('userRank').value = userData.nivel_acesso;
        
        if(btnAvatar) btnAvatar.classList.remove('d-none');
        if(imgAvatar) imgAvatar.src = userData.foto_perfil || '../../Assets/img/userProfileImages/Profile.png';
        if(accordionLabel) accordionLabel.textContent = 'Alterar Senha?';
    }

    const canvas = new bootstrap.Offcanvas(document.getElementById('userFormCanvas'));
    canvas.show();
}

export function closeOffcanvas() {
    const el = document.getElementById('userFormCanvas');
    const instance = bootstrap.Offcanvas.getInstance(el);
    if (instance) instance.hide();
}