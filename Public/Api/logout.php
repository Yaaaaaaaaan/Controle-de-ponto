<?php
// /Public/Api/logout.php (Versão Final e Correta)

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

// 1. Pega o token do cabeçalho de autorização, se existir
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    // Se não houver token, o logout no lado do servidor não é estritamente necessário,
    // mas retornamos sucesso pois o cliente já se limpou.
    sendJson(['success' => true, 'message' => 'Nenhum token fornecido, logout no cliente já efetuado.'], 200);
}
$userToken = $matches[1];

// 2. Encontra o ID do usuário associado ao token
$userController = new UserController();
$userId = $userController->getUserIdByToken($userToken);

if ($userId) {
    // 3. Se encontrou, manda o Controller deletar o token do banco de dados
    if ($userController->deleteUserToken($userId)) {
        sendJson(['success' => true, 'message' => 'Logout efetuado com sucesso no servidor.'], 200);
    } else {
        sendJson(['success' => false, 'message' => 'Falha ao invalidar o token no servidor.'], 500);
    }
} else {
    // Se o token já for inválido, consideramos o logout um sucesso.
    sendJson(['success' => true, 'message' => 'Token já era inválido ou não encontrado.'], 200);
}
?>