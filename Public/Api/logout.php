<?php
    session_start();

    $_SESSION = [];

    session_destroy();

    header('Content-Type: Application/json');
    echo json_encode(['success' => true, 'message' => 'Sessão encerrada com sucesso.']);
    exit;

?>