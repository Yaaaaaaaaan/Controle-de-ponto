<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

if (!$nickname || !$password) {
    sendJson(['success' => false, 'message' => 'Nickname e senha são obrigatórios.'], 400);
}
$userController = new UserController();
// A função agora retorna os dados diretamente ou null
$responseData = $userController->authenticateUser($nickname, $password, $latitude, $longitude);

if ($responseData) {
    // Se recebeu os dados, envia-os na resposta
    sendJson(['success' => true, 'session' => $responseData]);
} else {
    // Se recebeu null, a autenticação falhou
    sendJson(['success' => false, 'message' => 'Usuário ou senha incorretos.'], 401);
}
?>