<?php
    define('APP_RAN', true);
    session_start();

    // Se a sessão existir, remove o token do banco de dados.  ---   Para outra função: desconectar todos os dispositivos
    /*if (isset($_SESSION['id'])) {
        require_once __DIR__ . '/../../App/Controller/UserController.php';
        $userController = new UserController();
        // Você precisará adicionar um método no UserController que chama o deleteTokenForUser
        $userController->deleteUserToken($_SESSION['id']);
    }*/

    $_SESSION = [];
    session_destroy();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Sessão encerrada com sucesso.']);
    exit;
?>