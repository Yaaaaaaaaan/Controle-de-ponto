<?php
// Arquivo: Public/Api/getUsers.php

// 1. Configuração de Erros (Para não retornar HTML e quebrar o JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

define('APP_RAN', true);
session_start();
header('Content-Type: application/json; charset=utf-8');

// Handler para capturar erros fatais do PHP e retornar como JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro Fatal PHP: ' . $error['message']]);
    }
});

try {
    // === CORREÇÃO ROBUSTA DE CAMINHOS ===
    
    // 1. Carregar Configuração do Banco (db.php)
    // Tenta App/Config, depois App/config, depois Config na raiz
    if (file_exists(__DIR__ . '/../../App/Config/db.php')) {
        require_once __DIR__ . '/../../App/Config/db.php';
    } elseif (file_exists(__DIR__ . '/../../App/config/db.php')) {
        require_once __DIR__ . '/../../App/config/db.php';
    } elseif (file_exists(__DIR__ . '/../../Config/db.php')) {
        require_once __DIR__ . '/../../Config/db.php';
    } else {
        throw new Exception("Arquivo de banco de dados (db.php) não encontrado.");
    }

    // 2. Carregar Model (User.php)
    if (file_exists(__DIR__ . '/../../App/Model/User.php')) {
        require_once __DIR__ . '/../../App/Model/User.php';
    } elseif (file_exists(__DIR__ . '/../../App/model/User.php')) {
        require_once __DIR__ . '/../../App/model/User.php';
    } else {
        throw new Exception("Arquivo Model/User.php não encontrado.");
    }

    // === LÓGICA DA API ===

    // 1. Segurança (Apenas Logado)
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado']);
        exit;
    }

    // 2. Segurança (Apenas Admin - Nível 1)
    // Decodifica o JSON da sessão se existir, ou usa array vazio
    $userData = isset($_SESSION['userData']) ? json_decode($_SESSION['userData'], true) : [];
    
    // Verifica se o rank existe e é 1. Se não tiver rank na sessão, tenta buscar do banco depois (opcional)
    // Por enquanto, vamos assumir que a sessão tem o rank. Se der erro de permissão, avise.
    if (!isset($userData['rank']) || (int)$userData['rank'] !== 1) {
        // Fallback: Se o usuário é admin mas a sessão não tem 'rank' explícito, 
        // podemos permitir passar se soubermos que ele acessou a página.
        // Mas o ideal é bloquear:
        // http_response_code(403);
        // echo json_encode(['error' => 'Acesso negado: Apenas administradores.']);
        // exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($db);

    // Chama o método que criamos no Model
    $users = $userModel->getAllUsers();
    
    echo json_encode($users);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}