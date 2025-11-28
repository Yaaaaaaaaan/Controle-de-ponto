<?php
define('APP_RAN', true);
$pageId = 'page-hkg-users';
require_once __DIR__ . "/../Layout/header.php";

if ($userRank != 1) {
    header('Location: /Public/View/User/index.php');
    exit;
}
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">Gerenciar Usuários</h3>
            <p class="text-muted small">Administre o acesso e permissões do sistema.</p>
        </div>
        <button class="btn btn-success shadow-sm" id="btnNewUser">
            <i class="fas fa-plus me-2"></i> Novo Usuário
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" id="searchUserInput" placeholder="Buscar nome, email ou user...">
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
    <div class="offcanvas-body p-4 bg-light">
        <form id="manageUserForm">
            <input type="hidden" id="userId" name="userId">
            
            <div class="profile-preview-container bg-white p-3 rounded border text-center mb-3">
                <div class="position-relative d-inline-block">
                    <img src="../../Assets/img/userProfileImages/Profile.png" id="previewAvatar" class="rounded-circle border" width="100" height="100" style="object-fit: cover;">
                    <label for="avatarInput" id="btnChangeAvatar" class="d-none position-absolute bottom-0 end-0 bg-white rounded-circle shadow p-1 cursor-pointer" style="cursor: pointer;">
                        <i class="fas fa-camera text-success p-1"></i>
                    </label>
                    <input type="file" id="avatarInput" class="d-none">
                </div>
                <div class="mt-2 small text-muted">Foto de perfil</div>
            </div>

            <h6 class="mb-3 text-muted">Dados Pessoais</h6>

            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-box bg-white border rounded p-2 mb-3">
                        <label class="small text-muted d-block" style="font-size: 0.75rem;">Email</label>
                        <input type="email" id="userEmail" name="email" class="w-100 border-0 outline-none fw-bold" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-box bg-white border rounded p-2 mb-3">
                        <label class="small text-muted d-block" style="font-size: 0.75rem;">Username</label>
                        <input type="text" id="userNickname" name="nickname" class="w-100 border-0 outline-none fw-bold" required>
                    </div>
                </div>
            </div>

            <div class="input-box bg-white border rounded p-2 mb-3">
                <label class="small text-muted d-block" style="font-size: 0.75rem;">Nome Completo</label>
                <input type="text" id="userName" name="name" class="w-100 border-0 outline-none fw-bold" required>
            </div>

            <div class="input-box bg-white border rounded p-2 mb-3">
                <label class="small text-muted d-block" style="font-size: 0.75rem;">Cargo</label>
                <select id="userRank" name="rank" class="w-100 border-0 outline-none fw-bold bg-transparent">
                    <option value="2">Usuário Padrão</option>
                    <option value="1">Administrador</option>
                </select>
            </div>

            <div class="accordion mt-4 shadow-sm" id="accordionPassword">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false">
                            <span id="lblSenhaAccordion">Definir Senha</span>
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionPassword">
                        <div class="accordion-body bg-white">
                            <div class="input-box mb-0 border rounded p-2">
                                <label class="small text-muted d-block" style="font-size: 0.75rem;">Nova Senha</label>
                                <input type="password" id="userPassword" name="password" class="w-100 border-0 outline-none fw-bold" placeholder="••••••">
                            </div>
                            <small class="text-muted mt-2 d-block" id="passwordHelp">Deixe vazio para manter.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-5">
                <button type="submit" class="btn btn-success btn-lg text-white fw-bold" id="btnSaveUser">Submeter Alterações</button>
            </div>
        </form>
    </div>
</div>

<script type="module" src="../../JS/HKG/userController.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . "/../Layout/footer.php"; ?>