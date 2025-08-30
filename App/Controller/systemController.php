<?php
if (!defined('APP_RAN')) {
    die('Acesso negado!');
}

class SystemController {

    public function healthcheck(): void
    {
        // Define o cabeçalho como JSON
        header('Content-Type: application/json');
        // Responde com um status de sucesso
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]);
    }

    public function phpinfo(): void
    {
        phpinfo();
    }

    // Outras funções do sistema podem ser adicionadas aqui
}
?>