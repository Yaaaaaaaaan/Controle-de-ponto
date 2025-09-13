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

    /**
     * Cria um novo registro de histórico com mais detalhes.
     * @param int $userId O ID do usuário.
     * @param string $description A descrição base da ação.
     * @param string $occurrenceDate A data/hora em que a ação realmente ocorreu (formato 'Y-m-d H:i:s').
     * @param string $obs Observação sobre o ambiente (ex: 'online', 'offline').
     * @return bool
     */
    public function create(int $userId, string $description, string $occurrenceDate, string $obs, ?float $latitude, ?float $longitude): bool {
        // Adiciona a observação à descrição
        $fullDescription = $description . " | Origem: " . $obs;
        // Adiciona o IP do usuário que está fazendo a requisição ao servidor
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'IP não detectado';
        $fullDescription .= " | Endereço IP: " . $ipAddress;

        $query = "INSERT INTO {$this->tableName} (id_usuario, descricao, data_ocorrencia, longitude, latitude) VALUES (:userId, :description, :occurrenceDate, :longitude, :latitude)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':description', $fullDescription);
            $stmt->bindParam(':occurrenceDate', $occurrenceDate);
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':longitude', $longitude);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro em History->create: " . $e->getMessage());
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