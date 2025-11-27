<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../App/Model/User.php';
require_once __DIR__ . '/../../App/Controller/UserController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']); exit;
}

// Lê o JSON enviado pelo fetch
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']); exit;
}

$controller = new UserController();
$result = $controller->deleteUserAdmin($id);

echo json_encode($result);