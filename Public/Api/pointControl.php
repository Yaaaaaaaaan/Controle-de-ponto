<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/PointController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

// --- ROTEAMENTO ---
if (!isset($_SESSION['logged']) || !isset($_SESSION['id'])) {
    sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pointController = new PointController();
    $pointData = $pointController->getPointControl($_SESSION['id']);
    sendJson(['success' => true, 'pointControl' => $pointData]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    if ($action === 'addRecord') {
        $pointController = new PointController();
        $result = $pointController->insertPointControl($_SESSION['id'], $data['status'] ?? null);
        if ($result) {
            sendJson(['success' => true, 'message' => 'Registro de ponto adicionado com sucesso']);
        } else {
            sendJson(['success' => false, 'message' => 'Falha ao adicionar registro de ponto.'], 500);
        }
    } else {
        sendJson(['success' => false, 'message' => 'Ação não reconhecida'], 400);
    }
} else {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}
?>