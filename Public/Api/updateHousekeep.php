<?php
// Public/Api/updateHousekeep.php
ini_set('display_errors', 0);
define('APP_RAN', true);
session_start();
header('Content-Type: application/json; charset=utf-8');

// --- Require do NOVO Controller ---
require_once __DIR__ . '/../../App/Controller/housekeepingController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(401); echo json_encode(['success' => false, 'message' => 'Auth error']); exit;
}

// Coleta dados
$inputCod = $_POST['inputCod'] ?? null;
$inputDescricao = $_POST['inputDescricao'] ?? null;
$inputData = $_POST['inputData'] ?? null;

try {
    $controller = new HousekeepingController();
    $response = $controller->updatePoint($inputCod, $inputDescricao, $inputData);
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}