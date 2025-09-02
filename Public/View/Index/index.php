<?php
error_reporting(0);
define('APP_RAN', true);
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="../../CSS/IDX/style.css">
    <meta charset="UTF-8">
    <link rel="icon" href='../../Assets/img/logo.png' type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Login</title>
    <script src="../../JS/bootstrap.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</head>
<body id="page-login">
<div class="container-userlogin">
    <form id="loginForm">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" name="nickname" id="nickname" placeholder="." autocomplete="username">
            <label for="floatingInput">Usuário</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="." autocomplete="current-password">
            <label for="floatingPassword">Senha</label>
        </div>

        <span id="responseAction"></span>

        <div class="d-grid">
            <button type="submit">Login</button>
        </div>
        <a href="../Index/register.php" class="d-block mt-2 text-center">Ainda não é cadastrado?! Clique aqui!</a>
    </form>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<!-- JavaScript (IndexedDB + lógica offline) -->
<script type="module" src="/Public/JS/main.js"></script>
</body>
</html>
