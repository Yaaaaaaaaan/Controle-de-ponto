<?php
if (!defined('APP_RAN')) {
    die('Acesso negado!');
}

class SystemController {

    public function healthcheck(): void
    {
        http_response_code(200);
    }

    public function phpinfo(): void
    {
        phpinfo();
    }

    // Outras funções do sistema podem ser adicionadas aqui
}
?>