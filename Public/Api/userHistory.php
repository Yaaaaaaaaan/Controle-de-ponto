<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Autenticação por Bearer Token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJson(['success' => false, 'message' => 'Token de autenticação ausente.'], 401);
}
$userToken = $matches[1];

$userController = new UserController();
$userId = $userController->getUserIdByToken($userToken);
if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido.'], 401);
}

// Pega o limite da query string (ex: ?limit=50)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

$historyEntries = $userController->getUserHistory($userId, $limit);

if ($historyEntries !== false) {
    sendJson(['success' => true, 'history' => $historyEntries]);
} else {
    sendJson(['success' => false, 'message' => 'Erro ao buscar histórico.'], 500);
}
?>