<?php

// --- Require do NOVO Controller ---
require_once __DIR__ . '/../../App/Controller/housekeepingController.php';

// Segurança (Login e Rank)
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Auth error']); exit;
}
$userData = isset($_SESSION['userData']) ? json_decode($_SESSION['userData'], true) : [];
    
    // Verifica se o rank existe e é 1. Se não tiver rank na sessão, tenta buscar do banco depois (opcional)
    // Por enquanto, vamos assumir que a sessão tem o rank. Se der erro de permissão, avise.
    if (!isset($userData['rank']) || (int)$userData['rank'] !== 1) {
        // Fallback: Se o usuário é admin mas a sessão não tem 'rank' explícito, 
        // podemos permitir passar se soubermos que ele acessou a página.
        // Mas o ideal é bloquear:
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado: Apenas administradores.']);
        exit;
    }

try {
    $controller = new HousekeepingController();
    $users = $controller->getUsersList();
    echo json_encode($users);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
}
?>