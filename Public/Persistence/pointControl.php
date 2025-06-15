<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../Errors/pointControl.txt');

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

function sendJson($data, $httpCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($httpCode);

    function sanitizeStrings($value)
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value)) {
            return array_map('sanitizeStrings', $value);
        } else {
            return $value;
        }
    }

    $safeData = array_map('sanitizeStrings', $data);
    echo json_encode($safeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

function clearSession()
{
    unset($_SESSION['pointControl']);
    sendJson([
        'success' => true,
        'message' => 'Sessão limpa',
        'doNotUpdateLocalStorage' => true
    ]);
}

function checkUpdates()
{
    if (isset($_SESSION['pointControl_updated']) && $_SESSION['pointControl_updated'] === true) {
        unset($_SESSION['pointControl_updated']);
        sendJson([
            'success' => true,
            'dataUpdated' => true,
            'pointControl' => json_decode($_SESSION['pointControl'], true)
        ]);
    } else {
        sendJson(['success' => true, 'dataUpdated' => false]);
    }
}

function syncToSession()
{
    $pointControlFromClient = $_POST['pointControl'] ?? null;

    if ($pointControlFromClient) {
        $_SESSION['pointControl'] = json_encode($pointControlFromClient); // Armazena como JSON
        sendJson(['success' => true, 'message' => 'Sessão sincronizada com IndexedDB']);
    } else {
        sendJson(['success' => false, 'message' => 'Dados inválidos']);
    }
}

// Obter dados de controle de ponto
function getPointControlData()
{
    error_log("pointControl.php: Chamada a getPointControlData");

    // Se o usuário não estiver logado, retornar erro
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        error_log("pointControl.php: Usuário não logado");
        sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        return;
    }

    // Se não houver ID de usuário na sessão, retornar erro
    if (!isset($_SESSION['id'])) {
        error_log("pointControl.php: ID de usuário não encontrado na sessão");
        sendJson(['success' => false, 'message' => 'ID de usuário não encontrado'], 400);
        return;
    }

    // Buscar dados de controle de ponto do usuário
    include_once __DIR__ . '/../../App/Controller/pointController.php';
    $pointController = new PointController();
    $pointControlData = $pointController->getPointControl($_SESSION['id']);

    // Armazenar dados na sessão
    $_SESSION['pointControl'] = json_encode($pointControlData);

    // Retornar dados
    sendJson([
        'success' => true,
        'pointControl' => $pointControlData
    ]);
}

// Adicionar novo registro de ponto
function addPointControlRecord()
{
    error_log("pointControl.php: Chamada a addPointControlRecord");

    // Se o usuário não estiver logado, retornar erro
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        error_log("pointControl.php: Usuário não logado");
        sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        return;
    }

    // Se não houver ID de usuário na sessão, retornar erro
    if (!isset($_SESSION['id'])) {
        error_log("pointControl.php: ID de usuário não encontrado na sessão");
        sendJson(['success' => false, 'message' => 'ID de usuário não encontrado'], 400);
        return;
    }

    // Obter dados do corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? 1; // Status padrão: 1

    // Adicionar registro de ponto
    include_once __DIR__ . '/../../App/Controller/UserController.php';
    $userController = new UserController();
    $result = $userController->insertPointControl($_SESSION['id']);

    if ($result) {
        // Marcar sessão como atualizada
        $_SESSION['pointControl_updated'] = true;

        // Retornar sucesso
        sendJson([
            'success' => true,
            'message' => 'Registro de ponto adicionado com sucesso'
        ]);
    } else {
        // Retornar erro
        sendJson([
            'success' => false,
            'message' => 'Falha ao adicionar registro de ponto'
        ], 500);
    }
}

// Roteamento
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['clearSession']) && $_GET['clearSession'] === 'true') {
        clearSession();
    } elseif (isset($_GET['checkUpdates']) && $_GET['checkUpdates'] === 'true') {
        checkUpdates();
    } else {
        // Se não houver parâmetros específicos, retornar dados de controle de ponto
        getPointControlData();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['syncToSession']) && $_POST['syncToSession'] === 'true') {
        syncToSession();
    } elseif (isset($_POST['addRecord']) && $_POST['addRecord'] === 'true') {
        addPointControlRecord();
    } else {
        // Se não houver parâmetros específicos, tentar adicionar registro de ponto
        addPointControlRecord();
    }
} else {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}
?>
