<?php
if (!defined('APP_RAN')) {
    die('Acesso negado!');
}

class SystemController {

    public function healthcheck() {
        http_response_code(200);
    }

    public function phpinfo() {
        phpinfo(); // Cuidado: veja as considerações de segurança abaixo
    }

    // Outras funções do sistema podem ser adicionadas aqui
}
?>