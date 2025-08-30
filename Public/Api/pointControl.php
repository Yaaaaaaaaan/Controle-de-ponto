<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';
require_once __DIR__ . '/../../App/Controller/PointController.php';

function sendJson($data, $httpCode = 200) { /* ... */ }

// Validação de Token para segurança
$userController = new UserController();
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
    sendJson(['success' => false, 'message' => 'Token não fornecido.'], 401);
}
$token = $matches[1];
$userId = $userController->getUserIdByTokenForSync($token);
if (!$userId) { sendJson(['success' => false, 'message' => 'Token inválido.'], 401); }

// Obter dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$status = $data['status'] ?? null;
if (!$status) { sendJson(['success' => false, 'message' => 'Status ausente.'], 400); }

$pointController = new PointController();
// Chama a função SEM a data, para que a data do servidor seja usada.
$success = $pointController->insertPointControl($userId, $status);

if ($success) {
    sendJson(['success' => true, 'message' => 'Ponto registado com sucesso.']);
} else {
    sendJson(['success' => false, 'message' => 'Falha ao registar o ponto.'], 500);
}
?>