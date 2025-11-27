<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../Config/db.php'; // Ajuste caminhos se necessário
require_once __DIR__ . '/../../App/Model/User.php';
require_once __DIR__ . '/../../App/Controller/UserController.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']); exit;
}

$id = $_POST['userId'] ?? '';
$name = $_POST['name'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$email = $_POST['email'] ?? '';
$rank = $_POST['rank'] ?? 2;
$password = $_POST['password'] ?? '';

$controller = new UserController();
$result = $controller->saveUserAdmin($id, $name, $nickname, $email, $rank, $password);

echo json_encode($result);
?>