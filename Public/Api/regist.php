<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

/*function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
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

$userController = new UserController();
$result = $userController->createUser($name, $nickname, $email, $password);

sendJson($result);*/

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT ajuda na leitura
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}

$name = $_POST['name'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

try {
    $userController = new UserController();
    $result = $userController->createUser($name, $nickname, $email, $password);
    sendJson($result);

} catch (Exception $e) {
    sendJson([
        'success' => false,
        'message' => 'Erro crítico no servidor.',
        'error_details' => $e->getMessage(), // Envia a mensagem de erro real
        'trace' => $e->getTraceAsString() // Opcional: envia o stack trace
    ], 500);
}
?>