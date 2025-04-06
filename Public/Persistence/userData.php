<?php
session_start();

// Verificar se é uma solicitação para limpar a sessão
if (isset($_GET['clearSession']) && $_GET['clearSession'] === 'true') {
    // Limpar apenas a sessão, sem afetar o localStorage
    unset($_SESSION['userData']);

    // Indicar que a sessão foi limpa e não deve atualizar o localStorage
    header('Content-Type: application/json');
    echo json_encode([
        'sessionCleared' => true,
        'doNotUpdateLocalStorage' => true
    ]);
    exit; // Importante: interrompe a execução do script
}

// Nova funcionalidade: verificar atualizações
if (isset($_GET['checkUpdates']) && $_GET['checkUpdates'] === 'true') {
    $response = ['dataUpdated' => false];

    // Verifica se os dados foram atualizados na sessão
    if (isset($_SESSION['userData_updated']) && $_SESSION['userData_updated'] === true) {
        // Remove a flag para não processar novamente
        unset($_SESSION['userData_updated']);

        // Retorna os dados atualizados
        $response = [
            'dataUpdated' => true,
            'userData' => json_decode($_SESSION['userData'], true)
        ];
    }

    // Retorna a resposta em formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Verificar se é uma solicitação para sincronizar dados do localStorage para a sessão
if (isset($_POST['syncToSession']) && $_POST['syncToSession'] === 'true') {
    $userDataFromClient = $_POST['userData'];

    if ($userDataFromClient) {
        $_SESSION['userData'] = $userDataFromClient;
        echo json_encode(['success' => true, 'message' => 'Sessão sincronizada com localStorage']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    }
    exit;
}

// Criar um objeto de resposta
$response = array();

// Adicionar userData à resposta
if (isset($_SESSION['userData']) && $_SESSION['userData'] != null && !isset($_SESSION['userData_processed'])) {
    $response['userData'] = json_decode($_SESSION['userData'], true);
    $response['userDataAvailable'] = true;
} else {
    $response['userData'] = array();
    $response['userDataAvailable'] = false;
}

// Adicionar lastProfilePictures
if (isset($_SESSION['lastProfilePictures']) && $_SESSION['lastProfilePictures'] != null) {
    $response['lastProfilePictures'] = $_SESSION['lastProfilePictures'];
} else {
    $response['lastProfilePictures'] = array();
}

// Retornar tudo como um único JSON
header('Content-Type: application/json');
print json_encode($response, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>