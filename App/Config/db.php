<?php
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
class Database {
    // 1. O host agora é o nome do serviço do banco de dados no docker-compose.yml
    private $host = 'db';
    private $db_name = 'controle_ponto_db';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->exec('set names utf8');
        } catch(PDOException $exception) {
            echo 'Connection error: ' . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
