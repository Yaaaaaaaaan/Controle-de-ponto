<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../Errors/erroUserdata.txt');

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

function sendJson($data, $httpCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($httpCode);

    function sanitizeStrings($value)
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value)) {
            return array_map('sanitizeStrings', $value);
        } else {
            return $value;
        }
    }

    $safeData = array_map('sanitizeStrings', $data);
    echo json_encode($safeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

function clearSession()
{
    unset($_SESSION['userData']);
    sendJson([
        'success' => true,
        'message' => 'Sessão limpa',
        'doNotUpdateLocalStorage' => true
    ]);
}

function checkUpdates()
{
    if (isset($_SESSION['userData_updated']) && $_SESSION['userData_updated'] === true) {
        unset($_SESSION['userData_updated']);
        sendJson([
            'success' => true,
            'dataUpdated' => true,
            'userData' => json_decode($_SESSION['userData'], true)
        ]);
    } else {
        sendJson(['success' => true, 'dataUpdated' => false]);
    }
}

function syncToSession()
{
    $userDataFromClient = $_POST['userData'] ?? null;

    if ($userDataFromClient) {
        $_SESSION['userData'] = json_encode($userDataFromClient); // Armazena como JSON
        sendJson(['success' => true, 'message' => 'Sessão sincronizada com IndexedDB']);
    } else {
        sendJson(['success' => false, 'message' => 'Dados inválidos']);
    }
}

function getUserDataAndPictures()
{
    error_log("userData.php: Chamada a getUserDataAndPictures");

    // Se o usuário não estiver logado, retornar erro
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        error_log("userData.php: Usuário não logado");
        sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        return;
    }

    $response = [
        'success' => true,
        'userData' => null
    ];

    // Obter dados do usuário da sessão
    if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
        $userData = json_decode($_SESSION['userData'], true);

        // Verificar se os dados do usuário são válidos
        if (is_array($userData) && !empty($userData)) {
            $response['userData'] = $userData;

            // Buscar dados atualizados do usuário se necessário
            if (!isset($_SESSION['userData_processed']) || $_SESSION['userData_processed'] !== true) {
                // Marcar dados como processados para evitar processamento repetido
                $_SESSION['userData_processed'] = true;

                // Registrar que os dados foram obtidos
                error_log("userData.php: Dados do usuário obtidos da sessão");
            }
        } else {
            error_log("userData.php: Dados do usuário inválidos na sessão");
        }
    } else {
        error_log("userData.php: Dados do usuário não encontrados na sessão");
    }

    // Registrar os dados que serão enviados
    error_log("userData.php: Enviando resposta: " . json_encode($response));

    sendJson($response);
}

function updateUserTheme(): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $theme = $data['theme'] ?? null;
    $userToken = $data['userToken'] ?? null;

    error_log("userData.php - updateUserTheme: Dados recebidos - theme: " . ($theme ?? 'null') . ", userToken: " . ($userToken ?? 'null'));

    if ($theme !== null && $userToken !== null) {
        include_once __DIR__ . '/../../controller/UserController.php';
        $userController = new UserController();
        $userId = $userController->getUserIdByToken($userToken);
        error_log("userData.php - updateUserTheme: ID do usuário obtido: " . ($userId ?? 'null'));

        if ($userId) {
            $success = $userController->updateUserTheme($userId, $theme);
            error_log("userData.php - updateUserTheme: Resultado da atualização: " . ($success ? 'true' : 'false'));

            if ($success) {
                $_SESSION['userData_updated'] = true;
                sendJson(['success' => true, 'message' => 'Tema atualizado']);
            } else {
                sendJson(['success' => false, 'message' => 'Falha ao atualizar tema']);
            }
        } else {
            sendJson(['success' => false, 'message' => 'Token de usuário inválido']);
        }
    } else {
        sendJson(['success' => false, 'message' => 'Tema ou token de usuário não especificados']);
    }
}

// Roteamento
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['clearSession']) && $_GET['clearSession'] === 'true') {
        clearSession();
    } elseif (isset($_GET['checkUpdates']) && $_GET['checkUpdates'] === 'true') {
        checkUpdates();
    } else {
        getUserDataAndPictures();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['syncToSession']) && $_POST['syncToSession'] === 'true') {
        syncToSession();
    } else {
        updateUserTheme();
    }
} else {
    sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
}
?>
