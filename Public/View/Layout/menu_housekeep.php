<?php
    session_start();
    error_reporting(0);
    ini_set('display_errors', 'Off');
    if ($_POST) {
        define('APP_RAN', true);
        include_once '../../../App/controller/UserController.php';
        if(isset($_POST['logout'])){$controller = new UserController();
            $controller->unAuthenticateUser();
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
        <link rel="stylesheet" href="../../CSS/HKG/handworking.css">
        <link rel="stylesheet" href="../../CSS/USR/dashboard.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../../JS/script.js"></script>
        <script src="../../JS/localStorage.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Controle de ponto - Housekeeping</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title"><span id="responseNameCurto"></span></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/controle-de-ponto/Public/View/Housekeeping/index.php'){echo 'active';} ?>" aria-current="page" href="../Housekeeping/index.php">Inicial</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../User/index.php">Sair do admin</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if($_SERVER['REQUEST_URI'] == '/controle-de-ponto/Public/View/Housekeeping/users.php'){echo 'active';} ?>" href="../Housekeeping/users.php">Users</a>
                            </li>
                            <li class="nav-item">
                                <?php
                                if ($_SESSION['logged'] != true){
                                    header("Location:../Index/index.php");
                                }


                                ?>
                                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
                                    <input class="btn btn-link nav-link" type="submit" name="logout" value="Logout">
                                </form>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Dropdown
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown" style="<?php
                            if (preg_match('/^.*\/settings\.php/', $_SERVER['REQUEST_URI'])) {
                                echo 'display:none;';
                            }
                            ?>">
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
        <script type="module" src="../../JS/localStorage.js"></script>
        <script type="module" src="../../JS/USR/userInterface.js"></script>
        <script type="module">
            import { processUserData } from '../../JS/localStorage.js';
            import { updateUIElements } from '../../JS/USR/userInterface.js';

            document.addEventListener('DOMContentLoaded', async () => {
                const userData = await processUserData();
                if (userData) {
                    updateUIElements(userData);
                }
            });
        </script>
    </body>
</html>