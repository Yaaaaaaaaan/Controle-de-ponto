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

// Roteamento
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['clearSession']) && $_GET['clearSession'] === 'true') {
        clearSession();
    } elseif (isset($_GET['checkUpdates']) && $_GET['checkUpdates'] === 'true') {
        checkUpdates();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['syncToSession']) && $_POST['syncToSession'] === 'true') {
        syncToSession();
    }
} else {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}
?>