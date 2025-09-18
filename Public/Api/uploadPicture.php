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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// 3. Verificar o arquivo enviado
if (!isset($_FILES['picture']) || $_FILES['picture']['error'] !== UPLOAD_ERR_OK) {
    sendJson(['success' => false, 'message' => 'Erro no upload do arquivo.'], 400);
}

$file = $_FILES['picture'];
$fileName = basename($file['name']); // Nome original do arquivo
$fileTmpName = $file['tmp_name']; // Caminho temporário
$fileSize = $file['size'];
$fileType = $file['type'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

// 4. Validações básicas do arquivo
$allowed = array('jpg', 'jpeg', 'png', 'gif');
if (!in_array($fileExt, $allowed)) {
    sendJson(['success' => false, 'message' => 'Tipo de arquivo não permitido.'], 400);
}
if ($fileSize > 5 * 1024 * 1024) { // Limite de 5MB
    sendJson(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB.'], 400);
}

// 5. Gerar um nome único para o arquivo e caminho de destino
$uploadDir = __DIR__ . '/../../Public/assets/uploads/profile_pictures/' . $userId . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newFileName = uniqid('profile_') . '.' . $fileExt;
$filePath = $uploadDir . $newFileName;
// CORREÇÃO: O caminho relativo salvo no banco de dados também é atualizado
$relativePath = '/Public/assets/uploads/profile_pictures/' . $userId . '/' . $newFileName;

// 6. Mover o arquivo para o diretório de destino
if (!move_uploaded_file($fileTmpName, $filePath)) {
    sendJson(['success' => false, 'message' => 'Falha ao mover o arquivo para o destino.'], 500);
}

// 7. Salvar as informações da foto no banco de dados
// O insertUserPicture agora retorna o ID da foto ou false
$photoId = $userController->insertUserPicture($userId, $relativePath, $fileName, $latitude, $longitude);

if ($photoId) {
    sendJson(['success' => true, 'message' => 'Foto enviada e salva com sucesso!', 'photoPath' => $relativePath, 'photoId' => $photoId], 200);
} else {
    // Se a foto foi movida mas não salva no DB, tentar remover o arquivo
    unlink($filePath);
    sendJson(['success' => false, 'message' => 'Erro ao registrar a foto no banco de dados.'], 500);
}
?>