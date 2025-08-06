<?php

define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';
require_once __DIR__ . '/../../App/Controller/PointController.php';

// ... (função sendJson) ...

$data = json_decode(file_get_contents('php://input'), true);
$userToken = $data['userToken'] ?? null;
$actions = $data['actions'] ?? [];

if (!$userToken || empty($actions)) {
    sendJson(['success' => false, 'message' => 'Token ou ações ausentes.'], 400);
}

// 1. VALIDAÇÃO DO TOKEN
$userController = new UserController();
$userId = $userController->getUserIdByTokenForSync($userToken);

if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada além do período de carência.'], 401);
}

// 2. PROCESSAMENTO DAS AÇÕES
$pointController = new PointController();
$allSuccess = true;

// Iniciar uma transação no banco de dados para garantir consistência
// $pdo->beginTransaction();

foreach ($actions as $action) {
    $success = false;
    if ($action['type'] === 'addPoint') {
        $status = $action['payload']['status'];
        $success = $pointController->insertPointControl($userId, $status);
    }
    // Outros tipos de ação podem ser adicionados aqui (ex: 'updateSettings')

    if (!$success) {
        $allSuccess = false;
        // $pdo->rollBack(); // Desfaz todas as operações se uma falhar
        break; // Para o loop
    }
}

if ($allSuccess) {
    // $pdo->commit(); // Confirma as operações
    sendJson(['success' => true, 'message' => 'Ações sincronizadas com sucesso.']);
} else {
    sendJson(['success' => false, 'message' => 'Uma ou mais ações offline falharam ao sincronizar.'], 500);
}
?>