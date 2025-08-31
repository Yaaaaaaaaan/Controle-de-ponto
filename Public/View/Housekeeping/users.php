<?php
define('APP_RAN', true);

// 1. Define o ID único da página
$pageId = 'page-hkg-users'; // Exemplo para a página inicial do Admin

// 2. Inclui o cabeçalho
require_once __DIR__ . "/../Layout/header.php";

// 3. VALIDAÇÃO DE RANK ADICIONAL
if ($userRank != 1) {
    // Se o usuário não for rank 1, redireciona para o dashboard
    header('Location: /Public/View/User/index.php');
    exit;
}
?>

<div class="container-fluid">
    <h1>Olá mundo!</h1>
</div>


<?php
require_once __DIR__ . "/../Layout/footer.php";
?>


