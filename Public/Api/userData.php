<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    // Sanitização de strings para segurança
    array_walk_recursive($data, function (&$item) {
        if (is_string($item)) {
            $item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
        }
    });
    echo json_encode($data);
    exit;
}

// --- ROTEAMENTO PADRONIZADO ---
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

// Instancia o controller que contém a lógica
$userController = new UserController();

// Roteamento baseado no método da requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userData = $userController->getUserData();
    if ($userData) {
        sendJson(['success' => true, 'userData' => $userData]);
    } else {
        sendJson(['success' => false, 'message' => 'Dados de usuário não encontrados na sessão.']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Aqui podemos adicionar lógica para diferentes ações POST no futuro.
    // Por enquanto, ele lida apenas com a atualização do tema.
    $theme = $data['theme'] ?? null;
    $userToken = $data['userToken'] ?? null;

    if ($theme !== null && $userToken !== null) {
        $userId = $userController->getUserIdByToken($userToken); // Assumindo que este método existe no controller
        if ($userId) {
            $success = $userController->updateUserTheme($userId, $theme);
            if ($success) {
                sendJson(['success' => true, 'message' => 'Tema atualizado com sucesso.']);
            } else {
                sendJson(['success' => false, 'message' => 'Falha ao atualizar o tema no banco de dados.'], 500);
            }
        } else {
            sendJson(['success' => false, 'message' => 'Token de usuário inválido.'], 401);
        }
    } else {
        // Se nenhuma ação POST conhecida for encontrada
        sendJson(['success' => false, 'message' => 'Ação POST não especificada ou dados ausentes.'], 400);
    }
} else {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}
?>