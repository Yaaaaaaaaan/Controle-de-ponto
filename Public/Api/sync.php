<?php
// Ficheiro: Public/Api/sync.php (VERSÃO COMPLETA E FUNCIONAL)

ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
date_default_timezone_set('America/Sao_Paulo'); // Boa prática de fuso horário
header('Content-Type: application/json');

// Dependências
require_once __DIR__ . '/../../App/Controller/UserController.php';
require_once __DIR__ . '/../../App/Controller/PointController.php';
require_once __DIR__ . '/../../App/Controller/HistoryController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

// 1. RECEBER E VALIDAR DADOS DO CLIENTE
$data = json_decode(file_get_contents('php://input'), true);
$userToken = $data['userToken'] ?? null;
$actions = $data['actions'] ?? [];

if (!$userToken) {
    sendJson(['success' => false, 'message' => 'Token de autenticação ausente.'], 400);
}

// 2. AUTENTICAR O UTILIZADOR VIA TOKEN
$userController = new UserController();
$userId = $userController->getUserIdByTokenForSync($userToken);
if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada.'], 401);
}

// 3. INSTANCIAR CONTROLLERS
$pointController = new PointController();
$historyController = new HistoryController();

// 4. PROCESSAR A FILA DE AÇÕES (A "IDA")
if (!empty($actions)) {
    foreach ($actions as $action) {
        $success = false;
        $historyDescription = '';

        // Lida com diferentes tipos de ações vindas do cliente
        switch ($action['type']) {
            case 'addPoint':
                $status = $action['payload']['status'] ?? 'Status não definido';
                $success = $pointController->insertPointControl($userId, $status);
                $historyDescription = "Ponto sincronizado do modo offline com status: {$status}";
                break;

            // Você pode adicionar outros 'case' aqui para outras ações, como 'updateTheme', etc.
        }

        // Se a ação foi bem-sucedida, regista-a no histórico.
        if ($success && !empty($historyDescription)) {
            $historyController->createHistoryEntry($userId, $historyDescription);
        } else if (!$success) {
            // Se uma ação falhar, para tudo e informa o cliente.
            sendJson(['success' => false, 'message' => 'Falha ao processar a ação: ' . ($action['type'] ?? 'desconhecida')], 500);
        }
    }
}

// 5. PREPARAR OS DADOS ATUALIZADOS (A "VOLTA")
// Após processar tudo, busca os dados mais recentes do banco de dados.
$updatedUserData = $userController->getUserById($userId);
$updatedPointControlData = $pointController->getPointControlByUserId($userId);

// 6. ENVIAR RESPOSTA DE SUCESSO COM DADOS FRESCOS
sendJson([
    'success' => true,
    'message' => 'Sincronização concluída com sucesso.',
    'userData' => $updatedUserData,
    'pointControlData' => $updatedPointControlData
]);

?>