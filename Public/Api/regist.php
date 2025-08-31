<?php
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}

// Lemos os dados do $_POST, pois o JavaScript está enviando como URLSearchParams
$name = $_POST['name'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Usamos um try-catch para capturar qualquer erro inesperado
try {
    $userController = new UserController();
    $result = $userController->createUser($name, $nickname, $email, $password);

    // Se a criação falhou (ex: email duplicado), envia um status 400
    if (!$result['success']) {
        sendJson($result, 400);
    }

    sendJson($result);

} catch (Exception $e) {
    error_log("Erro crítico em regist.php: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Ocorreu um erro crítico no servidor.'], 500);
}
?>