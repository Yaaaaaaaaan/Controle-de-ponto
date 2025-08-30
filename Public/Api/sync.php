<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

// Dependências
// INÍCIO DA CORREÇÃO
// Adicione a inclusão do arquivo de banco de dados
require_once __DIR__ . '/../../App/Config/db.php';
// FIM DA CORREÇÃO
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
// INÍCIO DA CORREÇÃO
// 1. Crie a instância do banco de dados e obtenha a conexão
$database = new Database();
$db = $database->getConnection();

// 2. Passe a conexão ($db) para os construtores
$pointController = new PointController($db);
$historyController = new HistoryController\HistoryController(); // O seu HistoryController usa namespace
// FIM DA CORREÇÃO


// 4. PROCESSAR A FILA DE AÇÕES (A "IDA")
if (!empty($actions)) {
    foreach ($actions as $action) {
        $success = false;
        $historyDescription = '';

        switch ($action['type']) {
            case 'CREATE_POINT':
                // --- INÍCIO DA MUDANÇA ---
                $status = $action['payload']['status'] ?? 'Status não definido';

                // 1. Extrai o timestamp da ação offline.
                $timestamp = $action['timestamp'] ?? null;

                // 2. Converte o timestamp para o formato de data (Y-m-d).
                $actionDate = $timestamp ? date('Y-m-d', strtotime($timestamp)) : null;

                // 3. Passa a data extraída para o controller.
                $success = $pointController->insertPointControl($userId, $status, $actionDate);
                // --- FIM DA MUDANÇA ---

                $historyDescription = "Ponto sincronizado do modo offline com status: {$status} para a data {$actionDate}";
                break;

            // Exemplo de como seria para outra ação:
            /*
            case 'UPDATE_USER_NAME':
                $newName = $action['payload']['name'] ?? '';
                $success = $userController->updateUserName($userId, $newName); // Supondo que este método existe
                $historyDescription = "Nome de utilizador atualizado offline para: {$newName}";
                break;
            */
        }

        if ($success && !empty($historyDescription)) {
            $historyController->createHistoryEntry($userId, $historyDescription);
        } else if (!$success) {
            sendJson(['success' => false, 'message' => 'Falha ao processar a ação: ' . ($action['type'] ?? 'desconhecida')], 500);
        }
    }
}

// 5. PREPARAR OS DADOS ATUALIZADOS (A "VOLTA")
$updatedPointControlData = $pointController->getByUserId($userId);
// Assumindo que você tenha um método em UserController para buscar os dados completos por ID
$updatedFullData = $userController->getUserByToken($userId);

// 6. ENVIAR RESPOSTA DE SUCESSO COM DADOS FRESCOS
sendJson([
    'success' => true,
    'message' => 'Sincronização concluída com sucesso.',
    'userData' => $updatedFullData['userData'],
    'tokenData' => $updatedFullData['tokenData'],
    'pointControlData' => $updatedPointControlData
]);
?>