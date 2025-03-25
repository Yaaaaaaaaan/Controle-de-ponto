<?php
define('APP_RAN', true);
require '../layout/menu.php';
//TODO: OTIMIZAR A VERIFICAÇÃO DE LOGIN, REMOVER A SESSION ID E PASSAR A USAR A SESSION USERDATA.
if ($_POST) {    
      include_once '../../../App/controller/UserController.php';
      $userController = new UserController();
    if(isset($_POST['registro'])){
      $userHistory = $userController->showUserHistory($_POST['registro']);
    }// TODO: FINALIZAR UPLOAD DE IMAGENS PARA O PERFIL;
    // Verifica o upload da imagem de perfil
    if(isset($_FILES['profilepic'])) {
      $userController->updateUserProfilePicture($_FILES['profilepic']);
    }
     if(isset($_POST['selectedPicture'])) {
      $userController->updateProfilePicture($_POST['selectedPicture']);
    }

    // TODO: RESTAURAR CONFIGURAÇÕES DE TEMAS CLARO E ESCURO, UTILIZANDO LOCALSTORAGE;
    $defaultTheme = isset($_POST['defaultTheme']) ? 1 : 0;
     //  TODO: RESTAURAR UPDATEUSER UTILIZANDO LOCALSTORAGE
    if ($_POST) {
        include_once '../../../App/controller/UserController.php';
        $userController = new UserController();

        if(isset($_POST['registro'])) {
            $userHistory = $userController->showUserHistory($_POST['registro']);
        }

        if(isset($_POST['selectedPicture'])) {
            $userController->updateProfilePicture($_POST['selectedPicture']);
        }

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

            $defaultTheme = isset($_POST['defaultTheme']) ? 1 : 0;


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
        }
    }

}

include_once '../../../App/controller/pictureController.php';
$controller = new pictureController();
$pictures = $controller->getUserPictures();

/*echo '<pre>';
var_dump($_SESSION['userData']); // Verifica a string JSON armazenada
echo '</pre>';*/

?>


<style>
   .image-radio-container {
        position: relative;
        width: 30%;
        margin: 5px; /* Adiciona margem para espaçamento */
    }

    .image-radio-container img {
        max-width: 100%;
        max-height: 125px;
        object-fit: contain;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .image-radio-container input[type="radio"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 1;
    }

    .image-radio-container input[type="radio"]:checked + img {
        border-color: #007bff;
    }

    .image-container {
        display: flex;
        flex-wrap: nowrap; /* Impede a quebra de linha */
        justify-content: center;
    }

    .d-flex.justify-content-center.mt-3 button {
        z-index: 1;
    }

    @media (max-width: 576px) {
        .image-radio-container {
            width: 95%;
        }
    }

    @media (max-width: 375px) {
        .image-container {
            flex-direction: row; /* Alinha as imagens em linha */
        }

        .image-radio-container {
            width: 30%;
        }
    }
</style>
<div class="container">
  <main>
  <div data-bs-spy="scroll" data-bs-target="#navbar-example2"  data-bs-smooth-scroll="true" tabindex="0">
    <div class="py-5 text-center mt-5 pt-5">
      <h2>Configurações</h2>
      <p class="lead">Informações de usuário</p>
    </div>
    <div class="row">
      <div class="col-md-5 col-lg-4 order-md-last">
        <div class="row">
          <div class="col-md-12">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-primary">Meu perfil</span>
            </h4>
            <div class="text-center border rounded py-2 mb-3">
            <div class="settings"><img id="pPicture"></div>
              <small class="text-body-secondary"><a href="" class="nav-link" data-bs-toggle="modal" data-bs-target="#profilePhoto">
                    Mude sua foto de perfil...
                  </a></small>
            </div>
          </div>
        </div>
        
       
      </div>
      <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Meus dados</h4>
        <form action="settings.php" method="post" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-sm-6">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="floatingInputEmail" value="" placeholder="name@example.com">
                    <label for="floatingInputEmail">Email address</label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-floating mb-3">
                    <input type="text" name="nickname" class="form-control" id="floatingInputNickname" value="" placeholder="Username">
                    <label for="floatingInputNickname">Username</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="floatingInputName" placeholder="Name" value="">
                    <label for="floatingInputName">Name</label>
                </div>
            </div>          
            <div class="accordion" id="scrollspyHeading2"> <!-- TODO: RESTAURAR ALTERAÇÃO DE SENHA -->
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                  Desejas alterar a senha? <a class="ms-1 text-danger-emphasis">Clique aqui!</a>
                  </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#scrollspyHeading2">
                    <div class="accordion-body">
                        <strong>Please enter your new password and confirm it.</strong> It must, by default, contain at least one number and one upper and lower case letter, it is recommended to use symbols such as <code>"!@#$%&*"</code>
                        <div class="row mt-3">
                        <div class="col-sm-12">
                                <div class="form-floating mb-3">
                                    <input type="password" name="oldPassword" class="form-control" id="floatingInputCurrentPassword" placeholder="password">
                                    <label for="floatingInputCurrentPassword">current password</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="password" name="newPassword" class="form-control" id="floatingInputNewPassword" placeholder="password">
                                    <label for="floatingInputNewPassword">New password</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="password" name="confirmPassword" class="form-control" id="floatingInputConfirmNewPassword" placeholder="Password confirmation">
                                    <label for="floatingInputConfirmNewPassword">Password confirmation</label>
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
                      <input name="defaultTheme" class="form-check-input" type="checkbox" role="switch" id="themeSwitch" <?php if($_SESSION['defaultTheme'] == 1){echo "checked";} ?>>
                      <label class="form-check-label" for="themeSwitch">Modo escuro</label>
                    </div>
                          <div class="row">
                              <div class="col-md-12">
                                  <hr>
                                  <p class="lead text-body-secondary">Faça upload de novas fotos ao sistema.</p>
                                  <form action="settings.php" method="post" enctype="multipart/form-data">
                                       <div class="input-group">
                                           <input type="hidden" name="namePic" value="">
                                           <input type="file" name="profilepic" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                           <button class="btn btn-outline-secondary" type="submit">Salvar</button>
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
                    <table class="table">
                      <thead>
                          <tr>
                              <th>Nome</th>
                              <th>Username</th>
                              <th>Descrição</th>
                              <th>Data</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($userHistory as $history) : ?>
                              <tr>
                                  <td><?php echo htmlspecialchars($history['uname']); ?></td>
                                  <td><?php echo htmlspecialchars($history['username']); ?></td>
                                  <td><?php echo htmlspecialchars($history['description']); ?></td>
                                  <td><?php echo htmlspecialchars($history['dateIn']); ?></td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                    </table>
                    <form action="settings.php" method="post">
                      <input type="text" name="registro" placeholder ="quantidade de registros a serem exibidos">
                      <button type="submit">Atualizar</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <hr class="my-4">
          <?php if($_POST){
            echo $_SESSION['response'];
            unset($_SESSION['response']);          
          } 
           ?>
          <button class="w-100 btn-lg btn btn-success" type="submit">Submeter</button>
        </form>
      </div>
    </div>
  </div>
  </main>
  <footer class="my-5 pt-5 text-body-secondary text-center text-small">
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
                        <text class="text-body-secondary">Essa é sua foto atual</text>
                    </div>
                    <center><hr style="width:50%;"></center>
                    
                </div>
                <div class="row" style="margin-left:0px;">
                    <div class="col-md-12">
                        <form method="post" action="settings.php">
                            <div class="image-container">
                                <?php foreach ($pictures as $picture) : ?>
                                    <label class="image-radio-container">
                                        <input type="radio" name="selectedPicture" value="<?php echo $picture['cod']; ?>">
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
                                        <input type="radio" name="selectedPicture" value="<?php echo $picture['cod']; ?>">
                                        <img src="<?php echo $picture['path']; ?>" class="d-block w-100" alt="Foto de Perfil">
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




<script>
    document.getElementById('updateProfilePicBtn').addEventListener('click', function() {
        const form = document.getElementById('profilePicForm');
        const formData = new FormData(form);

        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                // Atualize a imagem do perfil na página sem recarregar
                const profilePicContainer = document.querySelectorAll('.settings img, .modal-body .row .text-center img');
                const selectedPicture = document.querySelector('input[name="selectedPicture"]:checked');
                if (selectedPicture) {
                    const newSrc = selectedPicture.nextElementSibling.src;
                    profilePicContainer.forEach(image => {
                        image.src = newSrc;
                    });
                }
                // Exiba alguma mensagem de sucesso ou erro
                //console.log(data); // Você pode analisar a resposta do servidor aqui.
            })
            .catch(error => {
                console.error('Erro ao atualizar a foto de perfil:', error);
                // Exiba alguma mensagem de erro para o usuário
            });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Recuperar os dados do localStorage
        const userDataString = localStorage.getItem("userData");

        // Verificar se existem dados armazenados
        if (userDataString) {
            try {
                // Converter a string para um array
                let userdata = JSON.parse(userDataString).split(",");

                // Extrair os valores necessários
                let name = (userdata[1]).slice(8, -1);
                let email = (userdata[2]).slice(9, -1);
                let nickname = (userdata[4]).slice(12, -1);

                // Preencher os campos de input
                const nameInput = document.getElementById("floatingInputName");
                const emailInput = document.getElementById("floatingInputEmail");
                const nicknameInput = document.getElementById("floatingInputNickname");

                if (nameInput) nameInput.value = name;
                if (emailInput) emailInput.value = email;
                if (nicknameInput) nicknameInput.value = nickname;

                //console.log("Dados carregados do localStorage com sucesso!"); //verificação carga dados
            } catch (error) {
                //console.error("Erro ao processar dados do localStorage:", error); //verificação carga dados
            }
        } else {
            //console.log("Nenhum dado de usuário encontrado no localStorage"); //verificação carga dados
        }
    });


</script>