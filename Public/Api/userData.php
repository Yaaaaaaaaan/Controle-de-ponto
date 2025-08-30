<?php
// ... (início do ficheiro)
$userController = new UserController();
// Apanha o ID da sessão para o GET
$userId = $_SESSION['id'] ?? null;
if (!$userId) { sendJson(['success' => false, 'message' => 'Sessão inválida'], 401); }

$responseData = $userController->getUserById($userId); // Isto agora retorna a estrutura correta

if ($responseData) {
    sendJson(['success' => true, 'data' => $responseData]); // Retorna a estrutura {userData, tokenData}
} else {
    sendJson(['success' => false, 'message' => 'Utilizador não encontrado.'], 404);
}
?>