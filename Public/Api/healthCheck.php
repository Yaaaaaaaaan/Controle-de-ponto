<?php
    define('APP_RAN', true);

    // Define o fuso horário para consistência no timestamp
    date_default_timezone_set('America/Sao_Paulo');

    require_once __DIR__ . '/../../App/Controller/SystemController.php';

    $controller = new SystemController();
    $controller->healthcheck();
?>