<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta</title>
    <link rel="stylesheet" href="../../CSS/IDX/style.css">
    <link rel="icon" href="../../Api/SystemPics/logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-userlogin">
    <h2>Registro</h2>
    <form id="registerForm">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" name="name" placeholder="Nome completo" required>
            <label>Nome completo</label>
        </div>
        <div class="form-floating mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email" required>
            <label>Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" name="nickname" placeholder="Usuário" required>
            <label>Usuário</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" placeholder="Senha" required>
            <label>Senha</label>
        </div>

        <p id="response" class="text-center mt-2"></p>

        <div class="d-grid">
            <button class="" type="submit">Registrar</button>
        </div>
        <a href="index.php" class="d-block mt-3 text-center">Já tem uma conta? Voltar para o login</a>
    </form>
</div>

<script type="module" src="../../JS/IDX/registerController.js"></script>
</body>
</html>