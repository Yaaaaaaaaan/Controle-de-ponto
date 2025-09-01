<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: /Public/View/Index/index.php');
    exit;
}

$userData = isset($_SESSION['userData']) ? json_decode($_SESSION['userData'], true) : null;
$userRank = $userData['rank'] ?? 2;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Ponto</title>
    <link rel="icon" href="../../Assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="../../CSS/USR/dashboard.css">
    <link rel="stylesheet" href="../../CSS/USR/handworking.css">

    <script>
        (function() {
            try {
                const theme = JSON.parse(localStorage.getItem('userData')).theme;
                document.documentElement.setAttribute('data-bs-theme', theme == 1 ? 'dark' : 'light');
            } catch (e) { document.documentElement.setAttribute('data-bs-theme', 'light'); }
        })();
    </script>
</head>
<body id="<?php echo $pageId ?? ''; ?>" data-user-type="<?php echo $userRank == 1 ? 'hkg' : 'usr'; ?>">
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/Public/View/User/index.php" data-nav-link>Controle de Ponto</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><span id="responseNameCurto"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <?php if ($userRank == 1): ?>
                        <?php if($pageId == 'page-dashboard'|| $pageId == 'page-settings'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($pageId == 'page-dashboard') ? 'active' : ''; ?>" href="/Public/View/User/index.php" data-nav-link>Inicial</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($pageId == 'page-settings') ? 'active' : ''; ?>" href="/Public/View/User/settings.php" data-nav-link>Configurações</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/Public/View/Housekeeping/index.php" data-nav-link>Administrativo</a>
                            </li>


                        <?php endif; ?>

                        <?php if($pageId == 'page-hkg-dashboard'|| $pageId == 'page-hkg-users'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($pageId == 'page-hkg-dashboard') ? 'active' : ''; ?>" href="/Public/View/Housekeeping/index.php" data-nav-link>Administrativo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($pageId == 'page-hkg-users') ? 'active' : ''; ?>" href="/Public/View/Housekeeping/users.php" data-nav-link>Gerir usuários</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($pageId == 'page-dashboard') ? 'active' : ''; ?>" href="/Public/View/User/index.php" data-nav-link>Voltar ao início</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($userRank == 0): ?> /<!-- Usuários comuns visualizam a partir daqui-->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($pageId == 'page-dashboard') ? 'active' : ''; ?>" href="/Public/View/User/index.php" data-nav-link>Inicial</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($pageId == 'page-settings') ? 'active' : ''; ?>" href="/Public/View/User/settings.php" data-nav-link>Configurações</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Preferências</a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <div class="form-check form-switch mx-3 my-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="themeSwitch">
                                        <label class="form-check-label" for="themeSwitch">Modo Escuro</label>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            Logout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main id="app-content" class="container-fluid mt-5 pt-4">

    <div class="modal fade" id="logoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="logoutModalLabel">Tem certeza que desejas sair?</h1>
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