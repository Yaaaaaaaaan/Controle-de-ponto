<?php
define('APP_RAN', true);
$pageId = 'page-hkg-users';
require_once __DIR__ . "/../Layout/header.php";

if ($userRank != 1) {
    header('Location: /Public/View/User/index.php');
    exit;
}
?>

<style>
    /* --- ESTILO GERAL DA PÁGINA --- */
    .avatar-img { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
    .table > :not(caption) > * > * { padding: 1rem 0.5rem; vertical-align: middle; }
    
    /* --- O SEGREDO DO DESIGN (REPLICANDO SEU PRINT) --- */
    
    /* Caixa do Input (O quadrado com borda cinza) */
    .input-box {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        margin-bottom: 1rem; /* Espaço entre os campos */
    }

    /* A Label pequena no topo */
    .input-box label {
        font-size: 0.75rem; /* Texto pequeno */
        color: #6c757d;     /* Cinza */
        margin-bottom: 0;
        display: block;
    }

    /* O campo de digitação em si */
    .input-box input, 
    .input-box select {
        border: none;       /* Remove a borda padrão do input */
        padding: 0;
        margin: 0;
        width: 100%;
        font-weight: 500;   /* Texto um pouco mais grosso */
        outline: none;      /* Remove o contorno azul padrão */
        background: transparent;
        height: auto;
        color: #212529;
    }

    /* Efeito ao clicar (Foco) */
    .input-box:focus-within {
        border-color: #198754; /* Verde igual ao seu botão */
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    /* Accordion (Para a senha) - Estilo limpo */
    .accordion-button:not(.collapsed) {
        color: #198754;
        background-color: rgba(25, 135, 84, 0.1);
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    
    /* Foto de Perfil no Form */
    .profile-preview-container {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        margin-bottom: 20px;
        border: 1px solid #f0f0f0;
    }
</style>

<div class="container-fluid p-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">Gerenciar Usuários</h3>
            <p class="text-muted small">Administre o acesso e permissões do sistema.</p>
        </div>
        <button class="btn btn-success shadow-sm" 
        data-bs-toggle="offcanvas" 
        data-bs-target="#userFormCanvas" 
        onclick="prepararNovoUsuario()">
    <i class="fas fa-plus me-2"></i> Novo Usuário
</button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" id="searchUser" placeholder="Buscar nome, email ou user...">
                    </div>
                </div>
                <div class="col-md-3 ms-auto text-end">
                    <span class="badge bg-light text-dark border me-2">Total: <span id="totalUsersCount">0</span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;"></th>
                        <th>Usuário</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="5" class="text-center py-4">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="userFormCanvas" aria-labelledby="userFormLabel" style="width: 450px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="userFormLabel">Novo Usuário</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 bg-light"> <form id="manageUserForm">
            <input type="hidden" id="userId" name="userId">
            
            <div class="profile-preview-container">
    <div class="position-relative d-inline-block">
        <img src="../../Assets/img/userProfileImages/Profile.png" id="previewAvatar" class="rounded-circle border" width="100" height="100" style="object-fit: cover;">
        
        <label for="avatarInput" id="btnChangeAvatar" class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow p-1 cursor-pointer" style="cursor: pointer;">
            <i class="fas fa-camera text-success p-1"></i>
        </label>
        
        <input type="file" id="avatarInput" class="d-none">
    </div>
    <div class="mt-2 small text-muted">Foto de perfil</div>
</div>

            <h6 class="mb-3 text-muted">Dados Pessoais</h6>

            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-box">
                        <label>Email</label>
                        <input type="email" id="userEmail" name="email" required placeholder="ex: nome@empresa.com">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-box">
                        <label>Username</label>
                        <input type="text" id="userNickname" name="nickname" required placeholder="usuario">
                    </div>
                </div>
            </div>

            <div class="input-box">
                <label>Nome Completo</label>
                <input type="text" id="userName" name="name" required placeholder="Digite o nome completo">
            </div>

            <div class="input-box">
                <label>Cargo / Permissão</label>
                <select id="userRank" name="rank">
                    <option value="2">Usuário Padrão</option>
                    <option value="1">Administrador</option>
                </select>
            </div>

            <div class="accordion mt-4 shadow-sm" id="accordionPassword">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <span id="lblSenhaAccordion">Deseja definir/alterar a senha?</span>
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionPassword">
                        <div class="accordion-body bg-white">
                            <div class="input-box mb-0">
                                <label>Nova Senha</label>
                                <input type="password" id="userPassword" name="password" placeholder="Digite para alterar">
                            </div>
                            <small class="text-muted mt-2 d-block">Deixe vazio para manter a senha atual (em edições).</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-5">
                <button type="submit" class="btn btn-success btn-lg text-white" style="font-weight: 500;">Submeter Alterações</button>
            </div>
        </form>
    </div>
</div>

<script src="../../JS/HKG/manageUsers.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . "/../Layout/footer.php"; ?>