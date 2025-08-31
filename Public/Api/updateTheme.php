<?php
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendJson(['success' => false, 'message' => 'Método não permitido.'], 405);
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
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada.'], 401);
}

// Obter os dados (0 ou 1)
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['theme'])) {
    sendJson(['success' => false, 'message' => 'Dados de tema ausentes.'], 400);
}
$theme = (int)$data['theme']; // Garante que é um inteiro

// O seu UserController já tem o método 'updateUserTheme' que criamos
if ($userController->updateUserTheme($userId, $theme)) {
    sendJson(['success' => true, 'message' => 'Tema atualizado com sucesso.']);
} else {
    sendJson(['success' => false, 'message' => 'Falha ao atualizar o tema no banco de dados.'], 500);
}
?>