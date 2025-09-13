<?php
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}

// Lê o corpo da requisição JSON
$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? null;
$nickname = $data['nickname'] ?? null;
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

// Usamos um try-catch para capturar qualquer erro inesperado
try {
    $userController = new UserController();
    $result = $userController->createUser($name, $nickname, $email, $password, $latitude, $longitude);

    if ($result['success']) {
        sendJson($result, 201);
    }else{
        sendJson($result, 400);
    }



} catch (Exception $e) {
    error_log("Erro crítico em regist.php: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Ocorreu um erro crítico no servidor.'], 500);
}
?>