<?php
session_start();

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
} else {
    $response['userData'] = array();
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
