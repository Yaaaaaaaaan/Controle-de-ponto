<?php
define('APP_RAN', true);
$pageId = 'page-settings'; // Apenas define a identidade da página
require_once __DIR__ . "/../Layout/header.php"; // Inclui o cabeçalho e menu
?>

    <div class="container">
        <main>
            <div data-bs-spy="scroll" data-bs-target="#navbar-example2"  data-bs-smooth-scroll="true" tabindex="0">
                <div class="py-4 text-center mt-2 pt-2">
                    <h2 class="mt-1">Configurações</h2>
                    <p class="lead">Informações de usuário</p>
                </div>
                <div class="row">
                    <div class="col-md-5 col-lg-4 order-md-last">
                        <h4 class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-primary">Meu perfil</span>
                        </h4>
                        <div class="polaroid">
                            <img id="pPicture" alt="Imagem de perfil">
                            <div class="polaroid-caption">
                                <small>
                                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                                        Quero mudar minha foto de perfil...
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 col-lg-8">
                        <h4 class="mb-3">Meus dados</h4>

                        <form id="formUserData" method="POST" novalidate>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="floatingInputEmail" placeholder="name@example.com" autocomplete="email">
                                        <label for="floatingInputEmail">Email</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="nickname" class="form-control" id="floatingInputNickname" placeholder="Username" autocomplete="username">
                                        <label for="floatingInputNickname">Username</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="name" class="form-control" id="floatingInputName" placeholder="Name" autocomplete="name">
                                        <label for="floatingInputName">Nome</label>
                                    </div>
                                </div>
                                <div class="accordion" id="accordionSettings">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                Desejas alterar a senha? <a class="ms-1 text-danger-emphasis">Clique aqui!</a>
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                            <div class="accordion-body">
                                                <div class="form-floating mb-3">
                                                    <input type="password" name="oldPassword" class="form-control" id="floatingInputCurrentPassword" placeholder="password" autocomplete="current-password">
                                                    <label for="floatingInputCurrentPassword">Senha atual</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input type="password" name="newPassword" class="form-control" id="floatingInputNewPassword" placeholder="password" autocomplete="new-password">
                                                    <label for="floatingInputNewPassword">Nova senha</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input type="password" name="confirmPassword" class="form-control" id="floatingInputConfirmNewPassword" placeholder="Password confirmation" autocomplete="new-password">
                                                    <label for="floatingInputConfirmNewPassword">Confirmação de nova senha</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                Outras preferências
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                            <div class="accordion-body">
                                                <div class="form-check form-switch ms-3">
                                                    <input name="defaultTheme" class="form-check-input" type="checkbox" role="switch" id="themeSwitch">
                                                    <label class="form-check-label" for="themeSwitch">Modo escuro</label>
                                                </div>
                                                <hr>
                                                <p class="lead text-body-secondary">Upload de novas fotos</p>
                                                <div class="input-group">
                                                    <input type="file" name="userPicture" class="form-control" id="inputGroupFile04" accept="image/*">
                                                    <button id="savePictureBtn" class="btn btn-outline-success" type="button">Salvar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                Histórico de uso
                                            </button>
                                        </h2>
                                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                            <div class="accordion-body">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" placeholder="Quantidade de registros" name="registro">
                                                    <button id="searchHistoryBtn" class="btn btn-outline-secondary" type="button">Pesquisar</button>
                                                </div>
                                                <hr>

                                                <div class="table-responsive" style="height:250px; overflow-y:auto;">
                                                    <table class="table table-hover">
                                                        <thead class="sticky-top" style="background-color: var(--bs-body-bg);">
                                                        <tr>
                                                            <th scope="col">Descrição</th>
                                                            <th scope="col">Data</th>
                                                            <th scope="col">Status</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody id="historyTableBody">
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 text-center">
                                    <span id="settingsResponse"></span>
                                </div>
                                <hr class="my-4">
                                <button name="submeter" value="submeter" class="w-100 btn-lg btn btn-success" type="submit">Submeter Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="profilePhotoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="profilePhotoLabel">Meu perfil</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-body-secondary">Clique em uma imagem abaixo para defini-la como sua nova foto de perfil.</p>
                            <hr>
                            <div class="row row-cols-2 row-cols-md-3 g-3" id="photoGallery">
                            </div>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmationModalLabel">Confirmar Ação</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="confirmationModalBody">
                            Deseja definir esta imagem como sua foto de perfil?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmActionBtn">Sim, definir</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<?php
require_once __DIR__ . "/../Layout/footer.php";
?>