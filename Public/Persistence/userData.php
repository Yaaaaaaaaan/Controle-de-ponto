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

// Criar um objeto de resposta
$response = array();

// Adicionar userData
if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
    $userData = $_SESSION['userData'];

    if (isset($_SESSION['profileImagePath'])) {
        $userData['profileUser'] = $_SESSION['profileImagePath'];
        unset($_SESSION['profileImagePath']);
    }

    $response['userData'] = $userData;
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