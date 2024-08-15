<?php
include_once "../layout/menu.php";

if ($_POST) {
    include_once '../../../App/controller/UserController.php';
    $controller = new UserController();
    if($_POST['showHistory']){
      $userHistory = $controller->showUserHistory();
    }
    

    $defaultTheme = isset($_POST['defaultTheme']) ? 1 : 0;
    $updateSuccess = $controller->updateUser(
      $_POST['name'], 
      $_SESSION['id'], 
      $_POST['email'], 
      $_POST['nickname'], 
      $_POST['oldPassword'], 
      $_POST['newPassword'], 
      $_POST['confirmPassword'],
      $defaultTheme
    );
    if ($updateSuccess) {
      $_SESSION['name'] = $_POST['name'];
      $_SESSION['email'] = $_POST['email'];
      $_SESSION['nickname'] = $_POST['nickname'];
      $_SESSION['defautTheme'] = $_POST['defautTheme'];
      
      header("Location: settings.php");
    }
    
    //$this->$controller->$updateUserProfilePicture();
}
?>
<script>
  // Recuperando a string do localStorage
  let userDataString = localStorage.getItem("userData");

// Convertendo a string para um objeto JavaScript
UserData = JSON.parse(userDataString);

// Acessando o valor dentro do array
let userdata = UserData.split(",");
let name = (userdata[1]);


name = (userdata[1]).slice(8, -1);


//faz a manipulação detalhada da string
nameCurto = name.substring(0, name.indexOf(" "));
</script>
<div class="container">
  <main>
  <div data-bs-spy="scroll" data-bs-target="#navbar-example2"  data-bs-smooth-scroll="true" tabindex="0">
    <div class="py-5 text-center mt-5 pt-5">
      <h2>Configurações</h2>
      <p class="lead">Informações de usuário</p>
    </div>
    <div class="row g-5">
      <div class="col-md-5 col-lg-4 order-md-last">
        <div class="row">
          <div class="col-md-12">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-primary">Seu perfil</span>
            </h4>
            <div class="text-center border rounded py-2 mb-3">
              <?php echo '<img src="../../../App' . $_SESSION['lastImageProfileUser'] . '" alt="Imagem do usuário" style="width:226px;" >'; ?>
              <small class="text-body-secondary"><a href="" class="nav-link" data-bs-toggle="modal" data-bs-target="#profilePhoto">
                    Mude sua foto de perfil...
                  </a></small>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-12">
            <form class="card p-2">
              <div class="input-group">
                 
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Seus dados</h4>
        <form action="settings.php" method="post" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-sm-6">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="floatingInput" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" placeholder="name@example.com">
                    <label for="floatingInput">Email address</label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-floating mb-3">
                    <input type="text" name="nickname" class="form-control" id="floatingInput" value="<?php echo htmlspecialchars($_SESSION['nickname']); ?>" placeholder="Username">
                    <label for="floatingInput">Username</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="responseNameCompleto" placeholder="Name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
                    <label for="responseNameCompleto">Name</label>
                </div>
            </div>          
            <div class="accordion" id="scrollspyHeading2">
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                  Are you looking to change your password? <a class="ms-1 text-danger-emphasis">Click here!</a>
                  </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#scrollspyHeading2">
                    <div class="accordion-body">
                        <strong>Please enter your new password and confirm it.</strong> It must, by default, contain at least one number and one upper and lower case letter, it is recommended to use symbols such as <code>"!@#$%&*"</code>
                        <div class="row mt-3">
                        <div class="col-sm-12">
                                <div class="form-floating mb-3">
                                    <input type="password" name="oldPassword" class="form-control" id="floatingInput" placeholder="password">
                                    <label for="floatingInput">current password</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="password" name="newPassword" class="form-control" id="floatingInput" placeholder="password">
                                    <label for="floatingInput">New password</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="password" name="confirmPassword" class="form-control" id="floatingInput" placeholder="Password confirmation">
                                    <label for="floatingInput">Password confirmation</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Other preferences
                  </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse <?php if($_SERVER['REQUEST_URI'] == '/Estudos/Public/View/User/settings.php?darkMode'){echo 'show ';} ?>" data-bs-parent="#scrollspyHeading2">
                  <div class="accordion-body">
                    <strong>Don't forget to save your changes!</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                    <div class="row">
                      <div class="col-md-12">
                      <div class="form-check form-switch ms-3">
                      <input name="defaultTheme" class="form-check-input" type="checkbox" role="switch" id="themeSwitch" <?php if($_SESSION['defaultTheme'] == 1){echo "checked";} ?>>
                      <label class="form-check-label" for="themeSwitch">Dark mode</label>
                    </div>
                      </div>
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
                    <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">

                      <input type="hidden" value="1" name="showHistory">
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
          <button class="w-100 btn-lg btn btn-success" type="submit">Update</button>
        </form>
      </div>
    </div>
  </div>
  </main>
  <footer class="my-5 pt-5 text-body-secondary text-center text-small">
    <p class="mb-1">&copy; 2024 Ondetem.io</p>
  </footer>
</div>

<!-- Modal -->
<div class="modal fade" id="profilePhoto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="profilePhotoLabel">Your Profile</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4"></div>
          <div class="col-md-6">My recent photo</div>
          <div class="col-md-2"></div>
        </div>
        <div class="row">
        <div class="position-relative">
          
          <div class="text-center"><?php echo '<img src="../../../App' . $_SESSION['lastImageProfileUser'] . '" alt="Imagem do usuário" style="width:280px;" >'; ?></div>
          
        </div>
        </div>
        
        
        <div class="col-md-12 mt-1">
          <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" class="needs-validation" enctype="multipart/form-data" novalidate>
            <div class="input-group">
              <input type="file" multiple name="pics[]" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
              <button class="btn btn-outline-secondary" type="submit">Submit</button>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Understood</button>
      </div>
    </div>
  </div>
</div>


<script>

          //Nada disso ainda funciona.
          const input = document.getElementById('responseNameCompleto');
          input.value = name;

          $(document).ready(function() {
            $('#meuInput').val(localStorage.getItem('nomeUsuario'));
          });

  //document.getElementById("responseNameCompleto").textContent = name;

  document.body.dataset.bsTheme = <?php echo $_SESSION['defaultTheme'] == 1 ? "'dark'" : "'light'"; ?>;
  const themeSwitch = document.getElementById('themeSwitch');
  themeSwitch.addEventListener('change', () => {
    const newTheme = themeSwitch.checked ? 'dark' : 'light';
    document.body.dataset.bsTheme = newTheme;
  });
</script>