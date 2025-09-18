<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RAN', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

function sendJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Validar o método da requisição
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'PUT') {
    sendJson(['success' => false, 'message' => 'Método não permitido.'], 405);
}

// 2. Autenticar o usuário via Bearer Token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJson(['success' => false, 'message' => 'Token de autenticação ausente.'], 401);
}
$userToken = $matches[1];

$userController = new UserController();
$userId = $userController->getUserIdByToken($userToken);
if (!$userId) {
    sendJson(['success' => false, 'message' => 'Token inválido ou sessão expirada.'], 401);
}

// 3. Obter os dados enviados pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJson(['success' => false, 'message' => 'JSON inválido.'], 400);
}

// 4. Preparar os dados para o Controller
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$nickname = $data['nickname'] ?? '';
$defaultTheme = $data['defaultTheme'] ?? 0;

$oldPassword = $data['passwordChange']['oldPassword'] ?? '';
$newPassword = $data['passwordChange']['newPassword'] ?? '';

$obs = $data['obs'] ?? '';
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

// 5. Chamar o método do Controller para atualizar o usuário
// (Nota: updateUser no seu UserController já retorna um array ['success' => bool, 'message' => string])
$result = $userController->updateUser(
    $name,
    $userId, // Passamos o ID validado pelo token
    $email,
    $nickname,
    $oldPassword,
    $newPassword,
    $newPassword, // confirmPassword é igual a newPassword
    $defaultTheme,
    date('Y-m-d H:i:s'), // Parâmetro 9: A data de ocorrência (agora)
    $obs,             // Parâmetro 10: A observação de origem
    $latitude,
    $longitude
);

// 6. Retornar o resultado
if ($result['success']) {
    sendJson($result);
} else {
    sendJson($result, 400); // Bad Request se a atualização falhar
}
?>