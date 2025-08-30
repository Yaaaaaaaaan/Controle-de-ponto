<?php
header('Content-Type: application/json');
require __DIR__.'/_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Método não suportado']);
    exit;
}

$token = bearer_token_or_401();
$user  = user_from_token($token);
if (!$user) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Token inválido']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$actions = $input['actions'] ?? [];
if (!is_array($actions) || !count($actions)) {
    echo json_encode(['success'=>true,'message'=>'Nada para sincronizar']);
    exit;
}

$uid = (int)$user['userId'];

// Processa ações
foreach ($actions as $a) {
    $type = $a['type'] ?? '';
    $payload = $a['payload'] ?? [];

    switch ($type) {
        case 'CREATE_POINT':
            $status = $payload['status'] ?? null;
            if ($status) {
                // INSERT INTO point_control (userId, status, dateIn) VALUES ($uid, :status, NOW())
            }
            break;

        case 'UPDATE_PROFILE':
            $email = $payload['email'] ?? null;
            $nickname = $payload['nickname'] ?? null;
            $name = $payload['name'] ?? null;
            $defaultTheme = isset($payload['defaultTheme']) ? (int)$payload['defaultTheme'] : null;

            // UPDATE users SET email=?, nickname=?, name=?, defaultTheme=? WHERE userId=?
            break;

        case 'updateTheme':
            $theme = $payload['theme'] ?? null;
            if ($theme !== null) {
                // UPDATE users SET defaultTheme=? WHERE userId=?
            }
            break;

        default:
            // ignore
    }
}

// Resposta “de volta” (userData + pointControl) para o client atualizar IndexedDB
// $userData = SELECT ... FROM users WHERE userId = $uid
// $pointList = SELECT * FROM point_control WHERE userId = $uid ORDER BY dateIn DESC
echo json_encode([
    'success'=>true,
    'userData'=> /* $userData */ null,
    'pointControlData'=> /* $pointList */ []
]);
