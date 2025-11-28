<?php
// Arquivo: Public/Api/admin_dashboard_data.php
define('APP_RAN', true);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../App/Controller/UserController.php';

// 1. Verificação de Segurança (Apenas Admin)
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$userData = json_decode($_SESSION['userData'], true);
// Verifica se é Admin (Rank 1)
if (!isset($userData['rank']) || (int)$userData['rank'] !== 1) { 
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

// 2. Busca os dados
$userController = new UserController();
// Você precisará expor o método do Model no Controller ou chamar o model aqui.
// Exemplo instanciando o model (ou adicione o método no Controller):
// $data = $userController->getAllRegistersForAdmin(); <-- Idealmente via controller
// Para facilitar, vou instanciar o User aqui direto como exemplo, mas use o Controller se puder.
$db = new Database();
$userModel = new User($db->getConnection());
$registers = $userModel->getAllRegistersForAdmin();

echo json_encode($registers);

?>