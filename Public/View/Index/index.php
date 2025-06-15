<?php
error_reporting(0);
define('APP_RAN', true);
session_start();

error_log("index.php executado"); // Log no início

$uri = $_SERVER['REQUEST_URI'];
error_log("URI: " . $uri); // Log da URI

// Rota para healthcheck (DEVE SER A PRIMEIRA VERIFICAÇÃO)
if ($uri === '/controle-de-ponto/index.php/system/healthcheck') {
    error_log("Rota healthcheck detectada");
    include_once 'App/Controller/SystemController.php';
    $controller = new SystemController();
    $controller->healthcheck();
    error_log("Healthcheck executado");
    exit;
}

// Se o usuário já está logado, redireciona (ANTES DO LOGIN)
if (isset($_SESSION['logged'])) {
    error_log("Usuário já logado. Redirecionando...");
    header('Location: ../User/index.php');
    exit;
}

// Processamento do formulário de login (APENAS SE NÃO FOR HEALTHCHECK)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Processando formulário de login");
    include_once '../../../App/Controller/UserController.php';
    $controller = new UserController();
    $auth_success = $controller->authenticateUser($_POST['nickname'], $_POST['password']);

    if ($auth_success) {
        error_log("Login bem-sucedido. Redirecionando...");
        $_SESSION['logged'] = true;

        // Verificar se a requisição veio do JavaScript
        $isJsAuth = isset($_GET['auth']) && $_GET['auth'] === 'attempt';

        if ($isJsAuth) {
            // Redirecionar de volta para a página de login com parâmetro de sucesso
            // O JavaScript irá interceptar isso e sincronizar os dados
            header('Location: index.php?auth=success');
        } else {
            // Redirecionamento tradicional
            header('Location: ../User/index.php');
        }
        exit;
    } else {
        error_log("Falha na autenticação.");
        $_SESSION['response'] = "Falha na autenticação.";

        // Se a requisição veio do JavaScript, redirecionar com erro
        if (isset($_GET['auth']) && $_GET['auth'] === 'attempt') {
            header('Location: index.php?auth=failed');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="../../CSS/IDX/style.css">
    <meta charset="UTF-8">
    <link rel="icon" href="../../Persistence/SystemPics/logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Login</title>
    <script src="../../JS/bootstrap.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</head>
<body>
<div class="container-userlogin">
    <form id="loginForm">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" name="nickname" id="nickname" placeholder=".">
            <label for="floatingInput">Usuário</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder=".">
            <label for="floatingPassword">Senha</label>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo $_SESSION['response'];
        }
        ?>

        <span id="response"></span>

        <div class="d-grid">
            <button type="submit">Login</button>
        </div>
        <a href="../Index/register.php" class="d-block mt-2 text-center">Ainda não é cadastrado?! Clique aqui!</a>
    </form>
</div>

<!-- JavaScript (IndexedDB + lógica offline) -->
<script type="module" src="../../JS/IDX/authController.js"></script>
</body>
</html>
