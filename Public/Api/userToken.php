<?php
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

// O padrão para enviar tokens é através do cabeçalho "Authorization"
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    sendJson(['success' => false, 'message' => 'Cabeçalho de autorização ausente.'], 401);
}

// O formato padrão é "Bearer seu-token-aqui"
$token = null;
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    sendJson(['success' => false, 'message' => 'Token mal formatado ou ausente.'], 401);
}

// Usa o Controller para buscar os dados do usuário
$userController = new UserController();
$userData = $userController->getUserByToken($token);

if ($userData) {
    // Se encontrou, retorna sucesso com os dados do usuário
    sendJson(['success' => true, 'userData' => $userData]);
} else {
    // Se o token não correspondeu a nenhum usuário, retorna não autorizado
    sendJson(['success' => false, 'message' => 'Token inválido ou expirado.'], 401);
}
?>