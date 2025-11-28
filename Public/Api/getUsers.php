<?php
// Public/Api/getUsers.php

// 1. Configuração de Erros para Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Define Constante de Segurança
define('APP_RAN', true);

// 3. Cabeçalhos JSON
header('Content-Type: application/json; charset=utf-8');
session_start();

// 4. Handler de Erros Fatais (Para pegar o erro 500 e mostrar JSON)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro Fatal PHP',
            'details' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
});

try {
    // === INCLUINDO ARQUIVOS COM CAMINHOS CHECADOS ===

    // A. Controller (Tenta caminho minúsculo que é o padrão do seu sistema)
    $pathController = __DIR__ . '/../../App/Controller/housekeepingController.php';
    if (!file_exists($pathController)) {
        throw new Exception("Arquivo de Controller não encontrado em: $pathController");
    }
    require_once $pathController;

    // === LÓGICA ===

    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        throw new Exception('Usuário não autenticado.');
    }

    // Instancia a classe (PHP é case-insensitive para classes, então HousekeepingController funciona)
    if (!class_exists('HousekeepingController')) {
        throw new Exception("A classe 'HousekeepingController' não foi encontrada dentro do arquivo incluído.");
    }

    $controller = new HousekeepingController();
    $data = $controller->getUsersList();
    
    echo json_encode($data);

} catch (Throwable $e) { // Captura Error e Exception
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na API getUsers',
        'details' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}