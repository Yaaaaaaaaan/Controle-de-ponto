<?php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}

$name = $_POST['name'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$userController = new UserController();
// ATENÇÃO: Este passo depende que você refatore o método createUser
// em UserController.php para que ele retorne um array, como já discutimos.
$result = $userController->createUser($name, $nickname, $email, $password);

sendJson($result);
?>