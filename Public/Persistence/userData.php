<?php
    error_reporting(E_ALL); // Log todos os erros para depuração
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '../../App/Persistence/Errors/erroUserdata.txt'); // Substitua pelo caminho real

    session_start();

    // Configurações de erro (desative em produção)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //TODO: Trabalhar melhores possibilidades de implementação.
function sendJson($data, $httpCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpCode);

    // Recursive function to sanitize only strings
    function sanitizeStrings($value) {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value)) {
            return array_map('sanitizeStrings', $value); // Recursively handle arrays
        } else {
            return $value; // Leave other data types as they are
        }
    }

    $safeData = array_map('sanitizeStrings', $data); // Apply recursive sanitization
    echo json_encode($safeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

    // Função para limpar a sessão
    function clearSession() {
        unset($_SESSION['userData']);
        sendJson([
            'success' => true,
            'message' => 'Sessão limpa',
            'doNotUpdateLocalStorage' => true
        ]);
    }

    // Função para verificar atualizações
    function checkUpdates() {
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

    // Função para sincronizar dados com a sessão
    function syncToSession() {
        $userDataFromClient = $_POST['userData'] ?? null;

        if ($userDataFromClient) {
            $_SESSION['userData'] = $userDataFromClient;
            sendJson(['success' => true, 'message' => 'Sessão sincronizada com localStorage']);
        } else {
            sendJson(['success' => false, 'message' => 'Dados inválidos']);
        }
    }

    // Função para buscar dados do usuário e imagens de perfil
    function getUserDataAndPictures() {
        $response = [];

        $response['userData'] = (isset($_SESSION['userData']) && $_SESSION['userData'] != null && !isset($_SESSION['userData_processed']))
            ? json_decode($_SESSION['userData'], true)
            : [];
        $response['userDataAvailable'] = !empty($response['userData']);

        $response['lastProfilePictures'] = (isset($_SESSION['lastProfilePictures']) && $_SESSION['lastProfilePictures'] != null)
            ? $_SESSION['lastProfilePictures']
            : [];

        sendJson($response);
    }

    // Função para atualizar o tema do usuário
    function updateUserTheme(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $theme = $data['theme'] ?? null;

        if ($theme !== null) {
            include_once '../../App/controller/UserController.php';
            $userController = new UserController();
            $success = $userController->updateUserTheme($_SESSION['id'], $theme);

            if ($success) {
                $_SESSION['userData_updated'] = true; // Força atualização do userData
                sendJson(['success' => true, 'message' => 'Tema atualizado']);
            } else {
                sendJson(['success' => false, 'message' => 'Falha ao atualizar tema']);
            }
        } else {
            sendJson(['success' => false, 'message' => 'Tema não especificado']);
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