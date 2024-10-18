<?php
session_start();
 //validação de token e dados comuns de usuário
 if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
     $userData = $_SESSION['userData'];
 } else {
     $userData = false;
 }

// Verifica se os dados do usuário estão disponíveis
if ($userData !== false) {
    // Envia os dados para o JavaScript como JSON
    echo json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
} else {
    echo "[]"; // Retorna um array vazio se não houver dados
}

// Limpa a sessão, se necessário
unset($_SESSION['userData']);
?>