<?php

if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}

class History{
    private $conn;
    private $tableName = 'historicos_acoes';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Cria
    public function create(int $userId, string $description): bool
    {
        // A descrição completa é montada como antes
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $fullDescription = $description . " | Endereço IP: " . $ip;
        $fullDescription = strip_tags($fullDescription); // Limpa a string

        $query = "INSERT INTO {$this->tableName} (id_usuario, descricao) VALUES (:userId, :description)";

        try {
            // --- A LÓGICA DE TRANSAÇÃO ESTÁ AQUI ---
            $this->conn->beginTransaction(); // 1. Inicia a transação

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':description', $fullDescription);

            $success = $stmt->execute();

            if ($success) {
                $this->conn->commit(); // 2. Se a execução foi bem-sucedida, commita (salva permanentemente)
                return true;
            } else {
                $this->conn->rollBack(); // 3. Se falhou, reverte
                return false;
            }
            // --- FIM DA LÓGICA DE TRANSAÇÃO ---

        } catch (PDOException $e) {
            // 4. Se ocorrer um erro de SQL, garante que a transação seja revertida
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro em History->create(): " . $e->getMessage());
            return false;
        }
    }

    public function getHistoryByUserId(int $userId, int $limit = 20): array {
        $query = "SELECT descricao, data_ocorrencia 
              FROM {$this->tableName} 
              WHERE id_usuario = :userId 
              ORDER BY historico_id DESC 
              LIMIT :limitValue";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limitValue', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em History->getByUserId(): " . $e->getMessage());
            return [];
        }
    }

}

?>