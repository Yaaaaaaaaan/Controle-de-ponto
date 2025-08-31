<?php
// Busca a lista de todas as fotos de um usuário
ini_set('display_errors', 1); error_reporting(E_ALL);
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';
function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
// Pega o token do cabeçalho de autorização
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJson(['success' => false, 'message' => 'Token de autenticação ausente ou malformado.'], 401);
}
$userToken = $matches[1];

// Autentica o usuário
$userController = new UserController();
$userId = $userController->getUserIdByToken($userToken); // Usamos o método normal aqui
if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada.'], 401);
}

$pictures = $userController->getAllUserPictures($userId);

sendJson(['success' => true, 'pictures' => $pictures]);
?>