<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

// Dependências
require_once __DIR__ . '/../../App/Controller/UserController.php';
require_once __DIR__ . '/../../App/Controller/pointControlController.php';
// A dependência do HistoryController não é mais necessária aqui

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userToken = $data['userToken'] ?? null;
$actions = $data['actions'] ?? [];

if (!$userToken) {
    sendJson(['success' => false, 'message' => 'Token de autenticação ausente.'], 400);
}

// Autenticação
$userController = new UserController();
$userId = $userController->getUserIdByTokenForSync($userToken);
if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada.'], 401);
}

// Controllers
$pointController = new PointController();

// Processamento da Fila de Ações
if (!empty($actions)) {
    // Adicionado um try-catch para robustez
    try {
        foreach ($actions as $action) {
            if (!isset($action['type'])) continue;

            $success = false;

            switch ($action['type']) {
                case 'CREATE_POINT':
                    $status = $action['payload']['status'] ?? 'Status não definido';
                    $obs = $obs = $action['payload']['obs'] ?? null;
                    $timestamp = $action['timestamp'] ?? null;
                    $actionDate = $timestamp ? date('Y-m-d', strtotime($timestamp)) : null;

                    // A chamada para insertPointControl já cria o registro de histórico através do Model.
                    $success = $pointController->insertPointControl($userId, $status, $obs, $actionDate);
                    break;
                case 'UPDATE_PROFILE':
                    // Extraímos os dados do payload que o settingsController.js salvou
                    $payload = $action['payload'] ?? [];
                    $name = $payload['name'] ?? '';
                    $email = $payload['email'] ?? '';
                    $nickname = $payload['nickname'] ?? '';
                    $defaultTheme = $payload['defaultTheme'] ?? 0;

                    // A lógica de senha não é sincronizada offline por segurança,
                    // então passamos valores vazios para os campos de senha.
                    $oldPassword = '';
                    $newPassword = '';

                    // Reutilizamos o mesmo método que a API de tempo real usa!
                    $result = $userController->updateUser(
                        $name,
                        $userId,
                        $email,
                        $nickname,
                        $oldPassword,
                        $newPassword,
                        $newPassword,
                        $defaultTheme
                    );
                    $success = $result['success'];
                    break;

                case 'UPDATE_THEME':
                    $theme = $action['payload']['theme'] ?? 0;
                    // O método updateUserTheme já cria seu próprio histórico também.
                    $success = $userController->updateUserTheme($userId, $theme);
                    break;
            }

            if (!$success) {
                // Se uma ação falhar, interrompe o processo para evitar inconsistência de dados.
                throw new Exception('Falha ao processar a ação: ' . $action['type']);
            }
        }
    } catch (Exception $e) {
        // Se qualquer ação dentro do loop falhar, envia uma resposta de erro.
        sendJson(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// Preparar dados de retorno
$updatedPointControlData = $pointController->getPointControlByUserId($userId);
$updatedFullData = $userController->getUserByToken($userToken);

// Enviar resposta de sucesso
sendJson([
    'success' => true,
    'message' => 'Sincronização concluída com sucesso.',
    'userData' => $updatedFullData['userData'] ?? null,
    'tokenData' => $updatedFullData['tokenData'] ?? null,
    'pointControlData' => $updatedPointControlData
]);
?>