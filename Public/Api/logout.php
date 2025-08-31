<?php
// /Public/Api/logout.php (Versão Final e Completa)

ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Pega o token que o userController.js enviou no cabeçalho
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    // Se não há token, não há como registrar o histórico.
    sendJson(['success' => true, 'message' => 'Nenhum token fornecido.'], 200);
}
$userToken = $matches[1];

// 2. Cria uma instância do Controller
$userController = new UserController();

// 3. USA o token para DESCOBRIR o ID do usuário
$userId = $userController->getUserIdByToken($userToken);

// 4. Se encontrou um usuário válido para o token...
if ($userId) {
    // 5. ...chama a função que PASSA o userId para criar o histórico.
    if ($userController->logUserLogout($userId)) {
        sendJson(['success' => true, 'message' => 'Logout registrado no histórico.'], 200);
    } else {
        // Isso aconteceria se a inserção no banco de históricos falhasse.
        sendJson(['success' => false, 'message' => 'Falha ao registrar o logout no histórico.'], 500);
    }
} else {
    // Se o token já for inválido, não há usuário para associar o log.
    sendJson(['success' => true, 'message' => 'Token já era inválido, nenhum log criado.'], 200);
}
?>