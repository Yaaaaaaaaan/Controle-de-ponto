<?php 
  session_start();
  error_reporting(0);
  ini_set('display_errors', 'Off');
  if ($_POST) {
      define('APP_RAN', true);
      include_once '../../../App/controller/UserController.php';
      if(isset($_POST['logout'])){$controller = new UserController();
      $controller->unAuthenticateUser($_POST['logout']);
  }}
  //validação de token e dados comuns de usuário
  if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
    $userData = $_SESSION['userData'];
  } else {
    $userData = false;
  }          
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Controle de ponto</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="../../JS/script.js"></script>
        <script src="../../JS/localStorage.js"></script>
      </head>
        <body>
        <script>
          
            // Verificando se os dados do usuário estão disponíveis
            if (<?= json_encode($userData !== false); ?>) {
                // Passando os dados PHP para o JavaScript
                let userData = <?= json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
              
                if (userData != null) {
                    // Armazenando os dados no localStorage
                    localStorage.setItem('userData', JSON.stringify(userData));

                    // Verificando se os dados foram armazenados corretamente
                    console.log('Dados do usuário armazenados no localStorage:', localStorage.getItem('userData'));

                    // Limpa os dados da sessão no servidor, se necessário
                    <?php unset($_SESSION['userData']); ?>
                } else {
                    console.error('Nenhum dado de usuário encontrado no localStorage.');
                }
            }
            

          

          /*    let updatedUserData = <?php //echo $userData; ?>;
              let currentUserData = JSON.parse(localStorage.getItem('userData'));
              let mergedData = { ...currentUserData, ...updatedUserData };
              localStorage.setItem('userData', JSON.stringify(mergedData));

          */
         

          // Recuperando a string do localStorage
          userDataString = localStorage.getItem("userData");

          // Convertendo a string para um objeto JavaScript
          UserData = JSON.parse(userDataString);

          // Acessando o valor dentro do array
          let userdata = UserData.split(",");
          let name = (userdata[1]);

          name = (userdata[1]).slice(8, -1);

          //faz a manipulação detalhada da string
          nameCurto = name.substring(0, name.indexOf(" "));

          

          function alterarCheckbox() {
            
            var defaultTheme = (userdata[5]);

            if(defaultTheme == 0){
              var themeSwitchShow = document.getElementById("themeSwitchShow");
              themeSwitchShow.checked = true;
            }
            
        }
        </script>
        
        <nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Controle de ponto</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="responseName">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id=""><p id="responseNameCurto"></p></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
          <li class="nav-item">
            <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/Estudos/Public/View/User/index.php'){echo 'active';} ?>" aria-current="page" href="../User/index.php">Home</a>
          </li>
          <li class="nav-item">
          <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/Estudos/Public/View/User/settings.php'){echo 'active';} ?>" aria-current="page" href="../User/settings.php">Settings</a>
          </li>
          <?php if($_SESSION['rank']=1){
            echo'<li class="nav-item">
                    <a class="nav-link" href="../Housekeeping/index.php">Admin</a>
                </li>';

                echo $_SERVER['REQUEST_URI'];
          }?>
            <li class="nav-item">
            <?php 
            if ($_SESSION['logged'] != true){
                header("Location:../Index/index.php"); 
            }

                
            ?>
            <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                Logout
            </button>
          </li>
          <li class="nav-item dropdown" style="<?php
              if (preg_match('/^.*\/settings\.php/', $_SERVER['REQUEST_URI'])) {
                  echo 'display:none;';
              }
          ?>">
           <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
               Other settings
           </a>
           <ul class="dropdown-menu dropdown-menu-dark">
               <li> 
                   <div class="form-check form-switch ms-3">
                    
                       <input class="form-check-input" type="checkbox" role="switch" id="themeSwitchShow" disabled>
                       <label class="form-check-label" for="themeSwitchShow">Dark mode</label>
                   </div>
               </li>
               <li><a href="../User/settings.php?darkMode#scrollspyHeading2" class="dropdown-item" href="#">Click to update</a></li>
               <li>
                   <hr class="dropdown-divider">
               </li>
               <li><a class="dropdown-item" href="#">Something else here</a></li>
           </ul>
       
       </li>
        </ul>
        <form class="d-flex mt-3" role="search">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-success" type="submit">Search</button>
        </form>
      </div>
    </div>
  </div>
</nav>
<br><br><br>
  <p id="responseNameCompleto"></p>
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">Really want exit?</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Do you really want to leave? If you are, you will need to log in again.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
                <input class="btn btn-success" type="submit" name="logout" value="Yes, go out.">
            </form>
      </div>
    </div>
  </div>
</div>     

        <script>                
          //exibindo no html
          document.getElementById("responseNameCurto").textContent = "Olá, " + nameCurto; 
        </script>
      </body>
    </html>