<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$nickname = $data['nickname'] ?? null;
$password = $data['password'] ?? null;

if (!$nickname || !$password) {
    sendJson(['success' => false, 'message' => 'Nickname e senha são obrigatórios.'], 400);
}

$userController = new UserController();
$isAuthenticated = $userController->authenticateUser($nickname, $password);

if ($isAuthenticated) {
    $userData = $userController->getUserData(); // Pega os dados da sessão recém-criada
    if ($userData) {
        sendJson(['success' => true, 'userData' => $userData]);
    } else {
        sendJson(['success' => false, 'message' => 'Erro ao recuperar dados da sessão após o login.'], 500);
    }
} else {
    sendJson(['success' => false, 'message' => 'Usuário ou senha incorretos.'], 401);
}
?>