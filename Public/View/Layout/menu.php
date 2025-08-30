<?php 
    session_start();
    error_reporting(0);
    ini_set('display_errors', 'Off');
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Controle de ponto</title>
        <link rel="icon" href="../../Api/SystemPics/logo.png" type="image/x-icon">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">-->
        <link rel="stylesheet" href="../../CSS/USR/handworking.css">
        <link rel="stylesheet" href="../../CSS/bootstrap.min.css"> <!-- Criar condicional para bootstrap e chart.js caso CDN ou localmente; -->
        <link rel="stylesheet" href="../../CSS/USR/dashboard.css">
        <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>-->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../../JS/script.js"></script>
        <script src="../../JS/bootstrap.bundle.js"></script>
        <script src="../../JS/chartjs/dist/chart.umd.js"></script>
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/Public/service-worker.js')  // Ajuste o caminho se necessário
                        .then(function(registration) {
                            console.log('Service Worker registrado com sucesso:', registration.scope);
                        })
                        .catch(function(err) {
                            console.log('Falha ao registrar o Service Worker:', err);
                        });
                });
            }
        </script>
      </head>
        <body>       
            <nav class="navbar navbar-dark bg-dark fixed-top">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Controle de ponto</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="responseName">
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title"><span id="responseNameCurto"></span></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                                <li class="nav-item">
                                    <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/controle-de-ponto/Public/View/User/index.php'){echo 'active';} ?>" aria-current="page" href="../User/index.php">Inicial</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/controle-de-ponto/Public/View/User/settings.php'){echo 'active';} ?>" aria-current="page" href="../User/settings.php">Configurações</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/controle-de-ponto/Public/View/User/community.php'){echo 'active';} ?>" aria-current="page" href="../User/community.php">Comunidade</a>
                                </li>
                                    <li class="nav-item">
                                            <a class="nav-link" href="../Housekeeping/index.php">Admin</a>
                                    </li>

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
                                <li class="nav-item dropdown" style="<?php /*
                                    if (preg_match('/^.*\/settings\.php/', $_SERVER['REQUEST_URI'])) {
                                        echo 'display:none;';
                                    } */
                                    ?>
                                ">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Outras configurações
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                        <li>
                                            <div class="form-check form-switch ms-3">
                                               <input class="form-check-input" type="checkbox" role="switch" id="themeSwitch">
                                               <label class="form-check-label" for="themeSwitch">Modo Escuro</label>
                                            </div>
                                        </li>
                                        <p hidden id="responseTheme"><?php echo isset($userData['theme']) ? $userData['theme'] : 0; ?></p>
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
            <p id="responseNameCompleto"></p>
            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Tem certeza que desejas sair?</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Caso saia, será necessário efetuar o login novamente mais tarde.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>

                            <a class="btn btn-success" id="logoutBtn" href="#">Sim, desejo sair.</a>

                        </div>
                    </div>
                </div>
            </div>
            <script type="module" src="../../JS/Core/syncController.js"></script>
            <script type="module" src="../../JS/USR/userController.js"></script>
            <script type="module" src="../../JS/indexedDB/Model.js"></script>

        </body>
    </html>