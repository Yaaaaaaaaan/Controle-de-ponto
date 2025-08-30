<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

// INÍCIO DA CORREÇÃO
// Adicione a inclusão do arquivo de banco de dados
require_once __DIR__ . '/../../App/Config/db.php';
// FIM DA CORREÇÃO

require_once __DIR__ . '/../../App/Controller/UserController.php';
require_once __DIR__ . '/../../App/Controller/pointControlController.php';

// A função sendJson permanece a mesma
function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

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

// INÍCIO DA CORREÇÃO
// 1. Crie a instância do banco de dados e obtenha a conexão
$database = new Database();
$db = $database->getConnection();

// 2. Passe a conexão ($db) para o construtor do PointController
$pointController = new PointController($db);
// FIM DA CORREÇÃO

// Chama a função SEM a data, para que a data do servidor seja usada.
$success = $pointController->insertPointControl($userId, $status);

if ($success) {
    sendJson(['success' => true, 'message' => 'Ponto registado com sucesso.']);
} else {
    sendJson(['success' => false, 'message' => 'Falha ao registar o ponto.'], 500);
}
?>