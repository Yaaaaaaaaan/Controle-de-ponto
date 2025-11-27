<?php
// Arquivo: Public/Api/updateHousekeep.php

// Configurações de erro para JSON limpo
ini_set('display_errors', 0);
error_reporting(E_ALL);

define('APP_RAN', true);
session_start();
header('Content-Type: application/json; charset=utf-8');

// Handler para erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro Fatal PHP: ' . $error['message']]);
    }
});

try {
    // === CORREÇÃO DE CAMINHOS ===
    
    // 1. Configuração do Banco (Tenta App/Config e depois Config raiz)
    if (file_exists(__DIR__ . '/../../App/Config/db.php')) {
        require_once __DIR__ . '/../../App/Config/db.php';
    } elseif (file_exists(__DIR__ . '/../../Config/db.php')) {
        require_once __DIR__ . '/../../Config/db.php';
    } else {
        // Tenta minúsculo (Linux/Podman é case sensitive)
        if (file_exists(__DIR__ . '/../../App/config/db.php')) {
            require_once __DIR__ . '/../../App/config/db.php';
        } else {
            throw new Exception("Arquivo de banco de dados (db.php) não encontrado em App/Config.");
        }
    }

    // 2. Model User
    if (file_exists(__DIR__ . '/../../App/Model/User.php')) {
        require_once __DIR__ . '/../../App/Model/User.php';
    } elseif (file_exists(__DIR__ . '/../../App/model/User.php')) {
        require_once __DIR__ . '/../../App/model/User.php';
    } else {
        throw new Exception("Arquivo Model/User.php não encontrado.");
    }

    // 3. Controller User
    if (file_exists(__DIR__ . '/../../App/Controller/UserController.php')) {
        require_once __DIR__ . '/../../App/Controller/UserController.php';
    } elseif (file_exists(__DIR__ . '/../../App/controller/UserController.php')) {
        require_once __DIR__ . '/../../App/controller/UserController.php';
    } else {
        throw new Exception("Arquivo Controller/UserController.php não encontrado.");
    }

    // === LÓGICA ===

    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        throw new Exception('Usuário não autenticado ou sessão expirada.');
    }

    // Recebe dados
    $inputId = $_POST['inputId'] ?? null;
    $inputCod = $_POST['inputCod'] ?? null;
    $inputDescricao = $_POST['inputDescricao'] ?? null;
    $inputData = $_POST['inputData'] ?? null;

    // Instancia e executa
    $controller = new UserController();
    
    // Chama o método do controller
    $response = $controller->updatePresenceHousekeeping($inputId, $inputCod, $inputDescricao, $inputData);
    
    echo json_encode($response);

} catch (Exception $e) {
    // Retorna erro 500 mas com JSON legível
    // http_response_code(500); // Opcional: pode manter 200 se quiser tratar no front
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}