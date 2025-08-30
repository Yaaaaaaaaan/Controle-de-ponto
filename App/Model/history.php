<?php

namespace History;

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
        // Captura o endereço IP do utilizador, tal como a função antiga fazia.
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $fullDescription = $description . " | Endereço IP: " . $ip;

        $query = "INSERT INTO {$this->tableName} (id_usuario, descricao) VALUES (:userId, :description)";

        try {
            $stmt = $this->conn->prepare($query);

            // Limpa os dados
            $userId = htmlspecialchars(strip_tags($userId));
            $fullDescription = htmlspecialchars(strip_tags($fullDescription));

            // Vincula os dados
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':description', $fullDescription);

            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erro em History->create(): " . $e->getMessage());
        }

        return false;
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