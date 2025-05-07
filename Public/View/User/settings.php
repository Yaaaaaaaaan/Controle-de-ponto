<?php
    define('APP_RAN', true);
    require '../layout/menu.php';

    // TODO: OTIMIZAR A VERIFICAÇÃO DE LOGIN, REMOVER A SESSION ID E PASSAR A USAR A SESSION USERDATA.
    if ($_POST) {
        include_once '../../../App/controller/UserController.php';
        $userController = new UserController();

        // Verifica se há uma solicitação de histórico de usuário
        if (isset($_POST['registro'])) {
            $userHistory = $userController->showUserHistory($_POST['registro']);
        }
        // Verifica se o botão de salvar foi clicado
        if (isset($_POST['salvar']) && $_POST['salvar'] === 'salvar') {
            if (isset($_FILES['userPicture']) && $_FILES['userPicture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $userController->insertUserProfilePicture($_FILES['userPicture']);
                $_SESSION['userData_updated'] = true;
            }
        }

        // Verifica se uma imagem existente foi selecionada
        if (isset($_POST['selectedPicture'])) {
            $userController->updateProfilePicture($_POST['selectedPicture']);
            $_SESSION['userData_updated'] = true;
        }

        // Configuração do tema padrão
        $defaultTheme = isset($_POST['defaultTheme']) ? 1 : 0;

        // Verifica se existem os campos necessários para atualizar o usuário
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['nickname'])) {
            // Processa os campos de senha apenas se todos estiverem preenchidos
            $oldPassword = '';
            $newPassword = '';
            $confirmPassword = '';

            if (!empty($_POST['oldPassword']) && !empty($_POST['newPassword']) && !empty($_POST['confirmPassword'])) {
                $oldPassword = $_POST['oldPassword'];
                $newPassword = $_POST['newPassword'];
                $confirmPassword = $_POST['confirmPassword'];
            }

            $updateSuccess = $userController->updateUser(
                $_POST['name'],
                $_SESSION['id'],
                $_POST['email'],
                $_POST['nickname'],
                $oldPassword,
                $newPassword,
                $confirmPassword,
                $defaultTheme
            );

            $_SESSION['userData_updated'] = true;
        }
    }

    // Carrega as imagens do usuário para a interface
    include_once '../../../App/controller/pictureController.php';
    $controller = new pictureController();
    $pictures = $controller->getUserPictures();

    /*echo '<pre>';
    var_dump($_SESSION['userData']); // Verifica a string JSON armazenada
    echo '</pre>';*/


?>

<html>
    <head>

    </head>
    <body>
        <div class="container">
            <main>
                <div data-bs-spy="scroll" data-bs-target="#navbar-example2"  data-bs-smooth-scroll="true" tabindex="0">
                    <div class="py-4 text-center mt-2 pt-2">
                        <h2 class="mt-4">Configurações</h2>
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
                                        <a href="" class="nav-link" data-bs-toggle="modal" data-bs-target="#profilePhoto">
                                            Quero mudar minha foto de perfil...
                                        </a>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-8">
                            <h4 class="mb-3">Meus dados</h4>

                            <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating mb-3">
                                            <input form="formUserData" type="email" class="form-control" name="email" id="floatingInputEmail" value="" placeholder="name@example.com">
                                            <label for="floatingInputEmail">Email</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating mb-3">
                                            <input form="formUserData" type="text" name="nickname" class="form-control" id="floatingInputNickname" value="" placeholder="Username">
                                            <label for="floatingInputNickname">Username</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input form="formUserData" type="text" name="name" class="form-control" id="floatingInputName" placeholder="Name" value="">
                                            <label for="floatingInputName">Nome</label>
                                        </div>
                                    </div>
                                    <div class="accordion" id="scrollspyHeading2">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                Desejas alterar a senha? <a class="ms-1 text-danger-emphasis">Clique aqui!</a>
                                              </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#scrollspyHeading2">
                                                <div class="accordion-body">
                                                    <p class="lead text-body-secondary">Alteração de senha</p>
                                                    <strong>Por favor, digite a sua nova senha e confirme-a.</strong> Ela deve, por padrão, conter pelo menos um número e uma letra maiúscula e minúscula. É recomendável usar símbolos como <code>"!@#$%&*"</code>
                                                    <div class="row mt-3">
                                                        <div class="col-sm-12">
                                                            <div class="form-floating mb-3">
                                                                <input form="formUserData" type="password" name="oldPassword" class="form-control" id="floatingInputCurrentPassword" placeholder="password">
                                                                <label for="floatingInputCurrentPassword">Senha atual</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-floating mb-3">
                                                                <input form="formUserData" type="password" name="newPassword" class="form-control" id="floatingInputNewPassword" placeholder="password">
                                                                <label for="floatingInputNewPassword">Nova senha</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-floating mb-3">
                                                                <input form="formUserData" type="password" name="confirmPassword" class="form-control" id="floatingInputConfirmNewPassword" placeholder="Password confirmation">
                                                                <label for="floatingInputConfirmNewPassword">Confirmação de nova senha</label>
                                                            </div>
                                                        </div>
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
                                            <div id="collapseTwo" class="accordion-collapse collapse <?php if($_SERVER['REQUEST_URI'] == '/Estudos/Public/View/User/settings.php?darkMode'){echo 'show ';} ?>" data-bs-parent="#scrollspyHeading2">
                                                <div class="accordion-body">
                                                    <p class="mb-3 lead text-body-secondary">Modo escuro</p>
                                                    <strong>Não se esqueça de salvar as alterações!</strong> Caso não as salve, elas serão perdidas.
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch ms-3">
                                                                <input form="formUserData" name="defaultTheme" class="form-check-input" type="checkbox" role="switch" id="themeSwitch" <?php if($_SESSION['defaultTheme'] == 1){echo "checked";} ?>>
                                                                <label class="form-check-label" for="themeSwitch">Modo escuro</label>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <hr>
                                                                    <p class="lead text-body-secondary">Upload de novas fotos</p>
                                                                    <form action="settings.php" method="post" enctype="multipart/form-data">
                                                                        <div class="input-group">
                                                                            <input type="file" name="userPicture" class="form-control" id="inputGroupFile04"
                                                                                aria-describedby="inputGroupFileAddon04" aria-label="Upload" accept="image/*">
                                                                            <button  name="salvar" value="salvar" class="btn btn-outline-secondary" type="submit">Salvar</button>

                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item"> <!-- TODO: DECIDIR SE EXISTIRÁ HISTÓRICO DE USO PARA USUÁRIO, OU APENAS ADMINISTRATIVO (HOUSEKEEPING), E RESTAURÁ-LO.-->
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                    Histórico de uso
                                                </button>
                                            </h2>
                                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#scrollspyHeading2">
                                                <div class="accordion-body">
                                                    <p class="lead text-body-secondary">Histórico de uso</p>
                                                        <form action="settings.php" method="post">
                                                            <div class="input-group mb-3">
                                                                <input type="text" class="form-control" placeholder="Quantidade de registros" name="registro" aria-label="quantidade de registros a serem exibidos">
                                                                <button class="btn btn-outline-secondary" type="submit">Pesquisar</button>
                                                            </div>
                                                        </form>
                                                        <hr>
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Descrição</th>
                                                                    <th>Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($userHistory as $history) : ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($history['description']); ?></td>
                                                                        <td><?php echo htmlspecialchars($history['dateIn']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-4">
                                    <?php
                                        if($_POST){
                                            echo $_SESSION['response'];
                                            if (isset($_SESSION['response']) && strpos($_SESSION['response'], 'Alterações feitas com sucesso!') !== false){
                                                echo "
                                                        <script>
                                                            // Força uma nova requisição para atualizar o localStorage
                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                // Primeiro atualiza o localStorage
                                                                processUserData().then(() => {
                                                                    // Depois preenche os campos do formulário
                                                                    updateUIElements();
                                                                });
                                                            });
                                                        </script>
                                                    ";
                                            }
                                            unset($_SESSION['response']);
                                        }
                                    ?>
                                <form action="settings.php" id="formUserData" method="post" class="needs-validation" novalidate>
                                    <button name="submeter" value="submeter" class="w-100 btn-lg btn btn-success" type="submit">Submeter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="my-5 pt-2 text-body-secondary text-center text-small">
                <p class="mb-1">&copy; 2024 Controle de ponto</p>
            </footer>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="profilePhoto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="profilePhotoLabel">Meu perfil</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="position-relative">
                        <div class="text-center">
                        <img id="pPictureModal">
                        </div>
                        <text class="text-body-secondary">Minha foto atual</text>
                    </div>
                    <center><hr style="width:50%;"></center>
                    
                </div>
                <div class="row" style="margin-left:0px;">
                    <div class="col-md-12">
                        <form method="post" action="settings.php">
                            <div class="image-container">
                                <?php foreach ($pictures as $picture) : ?>
                                    <label class="image-radio-container">
                                        <input type="radio" name="selectedPicture" value="<?= $picture['cod']; ?>">
                                        <img src="<?php echo $picture['path']; ?>" class="d-block w-100" alt="Foto de Perfil">
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <text class="text-body-secondary">Essas são suas últimas três fotos adicionadas, Selecione uma.</text>
                            <div class="d-flex justify-content-center mt-3">
                                <button type="submit" id="updateProfilePicBtn" style="text-align: center; display: block; margin: 0 auto;" name="updateProfilePic" class="btn btn-outline-primary w-100">Atualizar Foto de Perfil</button>
                            </div>
                        </form>

                        <!--<form method="post" id="profilePicForm">
                            <div class="image-container">
                                <?php foreach ($pictures as $picture) : ?>
                                    <label class="image-radio-container">
                                        <input type="radio" name="selectedPicture" value="<?= $picture['cod']; ?>">
                                        <img src="<?= $picture['path']; ?>" class="d-block w-100" alt="Foto de Perfil">
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <text class="text-body-secondary">Essas são suas últimas três fotos adicionadas, Selecione uma.</text>
                            <div class="d-flex justify-content-center mt-3">
                                <button type="button" id="updateProfilePicBtn" class="btn btn-outline-primary w-100">Atualizar Foto de Perfil</button>
                            </div>
                        </form>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </body>
</html>