<?php
// Public/Api/saveUser.php
ini_set('display_errors', 0);
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/housekeepingController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']); exit;
}

// Passamos o $_POST inteiro para o Controller tratar
$controller = new HousekeepingController();
$response = $controller->saveUser($_POST);

echo json_encode($response);