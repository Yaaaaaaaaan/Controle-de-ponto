<?php
// Public/Api/deleteUser.php
ini_set('display_errors', 0);
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/HousekeepingController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']); exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;

$controller = new HousekeepingController();
$response = $controller->deleteUser($id);

echo json_encode($response);