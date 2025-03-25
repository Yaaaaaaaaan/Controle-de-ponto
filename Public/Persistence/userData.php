<?php
session_start();

$userData = false;
if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
    $userData = $_SESSION['userData'];
}

if (isset($_SESSION['profileImagePath'])) {
    if ($userData !== false) {
        $userData['profileUser'] = $_SESSION['profileImagePath'];
    }
    unset($_SESSION['profileImagePath']); // Limpa a variável de sessão
}

if(isset($_SESSION['name'])){
    if ($userData !== false) {
        $userData['name'] = $_SESSION['uname'];
    }
    unset($_SESSION['profileImagePath']); // Limpa a variável de sessão
}

if ($userData !== false) {
    echo json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
} else {
    echo "[]";
}
?>