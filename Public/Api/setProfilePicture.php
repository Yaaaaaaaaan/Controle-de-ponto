<?php
// Define uma foto existente como a de perfil
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendJson(['success' => false, 'message' => 'Método não permitido.'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$photoId = $data['photoId'] ?? null;

if (!$photoId) {
    sendJson(['success' => false, 'message' => 'ID da foto ausente.'], 400);
}

if ($userController->setUserProfilePicture($userId, $photoId)) {
    sendJson(['success' => true, 'message' => 'Foto de perfil atualizada com sucesso!']);
} else {
    sendJson(['success' => false, 'message' => 'Falha ao definir a foto de perfil.'], 400);
}
?>