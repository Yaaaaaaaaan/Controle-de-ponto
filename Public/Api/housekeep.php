<?php
// Public/Api/housekeep.php
ini_set('display_errors', 0);
define('APP_RAN', true);
session_start();
header('Content-Type: application/json; charset=utf-8');

// --- Require do NOVO Controller (minúsculo para Linux) ---
require_once __DIR__ . '/../../App/Controller/housekeepingController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Auth error']); exit;
}

try {
    $controller = new HousekeepingController();
    $data = $controller->getDashboardStats();
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
}