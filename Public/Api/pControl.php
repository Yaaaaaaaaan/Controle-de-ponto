<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
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
$status = $data['status'] ?? 'Verificação pendente';
$obs = $data['obs'] ?? 'online'; // Assume 'online' se não especificado
$occurrenceDate = isset($data['timestamp']) ? date('Y-m-d', strtotime($data['timestamp'])) : date('Y-m-d');
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

$pointController = new PointController();

try {
    // A chamada ao controller já retorna tudo que precisamos
    $result = $pointController->insertPointControl($userId, $status, $obs, $occurrenceDate, $longitude, $latitude);

    // Usa a função sendJson com a mensagem e o código HTTP vindos do controller
    sendJson(['success' => $result['success'], 'message' => $result['message']], $result['http_code']);

} catch (Exception $e) {
    // Um catch genérico para erros inesperados na lógica
    error_log("Erro fatal na API pControl.php: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro interno no servidor.'], 500);
}
?>