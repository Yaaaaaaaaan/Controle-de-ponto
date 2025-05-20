<?php
define('APP_RAN', true);
include_once '../../App/Controller/SystemController.php';
$controller = new SystemController();
$controller->healthcheck();
?>