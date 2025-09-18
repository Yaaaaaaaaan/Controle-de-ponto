<?php
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/history.php';

class PointControl
{
    private $conn;
    private $tableNames = [
        'usr' => 'usuarios',
        'his' => 'historicos_acoes',
        'reg' => 'registros_ponto'
    ];
    private $tableName  = 'registros_ponto';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Adicionada para padronizar a criação de histórico, assim como na classe User.
     * @param string $description A descrição da ação.
     * @param int $userId O ID do usuário que realizou a ação.
     * @return bool
     */
    public function createUserHistory(string $description, int $userId, string $occurrenceDate, string $obs, $longitude, $latitude): bool {
        try {
            $historyModel = new History($this->conn);
            // Repassa todos os 4 parâmetros para a criação real do histórico
            return $historyModel->create($userId, $description, $occurrenceDate, $obs, $longitude, $latitude);
        } catch (Exception $e) {
            error_log("Erro ao delegar criação de histórico a partir do PointControl: " . $e->getMessage());
            return false;
        }
    }

    private function recordExists(int $userId, string $date): bool
    {
        $query = "SELECT 1 FROM {$this->tableName} WHERE id_usuario = :id_usuario AND data_registro = :data_registro LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $userId);
        $stmt->bindParam(':data_registro', $date);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Cria um novo registro de ponto, após verificar se ele já existe.
     * @return array Retorna um array indicando o sucesso ou o tipo de erro.
     */
    public function insertPointControl(int $userId, string $status, ?string $obs = null, $occurrenceDate, $longitude, $latitude): array
    {

        // 1. VERIFICAÇÃO ANTES DE INSERIR
        if ($this->recordExists($userId, $occurrenceDate)) {
            return ['success' => false, 'error' => 'duplicate_entry'];
        }

        // 2. QUERY DE INSERÇÃO SIMPLES
        $query = "INSERT INTO {$this->tableName} (id_usuario, data_registro, status, observacao) 
                  VALUES (:id_usuario, :data_registro, :status, :obs)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $userId);
        $stmt->bindParam(':data_registro', $occurrenceDate);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':obs', $obs);

        try {
            if ($stmt->execute()) {
                $historyDescription = "Regisro de ponto bem-sucedido: {$status}";
                $this->createUserHistory($historyDescription, $userId, $occurrenceDate, $obs, $longitude, $latitude);
                return ['success' => true];
            }
        } catch (PDOException $e) {
            error_log("Erro em PointControl->insertPointControl(): " . $e->getMessage());
            return ['success' => false, 'error' => 'db_error'];
        }

        return ['success' => false, 'error' => 'unknown_failure'];
    }

    public function getByUserId(int $userId): array {
        $query = "SELECT registro_id as cod, id_usuario as userId, data_registro as dateIn, status 
                  FROM {$this->tableName} 
                  WHERE id_usuario = :id_usuario 
                  ORDER BY data_registro ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPointControlUsersData(): array
    {
        try{
            // CORRIGIDO: Adicionado espaço em branco antes do alias 'u'.
            $query = "SELECT 
                p.status,
                COUNT(*) as count,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'cod', p.registro_id,
                        'data', DATE_FORMAT(p.data_registro, '%d/%m/%Y'),
                        'nome', u.nome_completo,
                        'status', p.status,
                        'id', p.id_usuario
                    )
                ) as detalhes
                FROM {$this->tableNames['reg']} p
                INNER JOIN {$this->tableNames['usr']} u ON p.id_usuario = u.id_usuario
                WHERE p.data_registro >= DATE_FORMAT(CURDATE() - INTERVAL 2 MONTH, '%Y-%m-01')
            GROUP BY p.status
            ORDER BY count DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro na query getPointControlUsersData: " . $e->getMessage());
            return [];
        }
    }

    public function updatePresenceHousekeeping($userIdToValidate, $code, $description) {
        $query = "UPDATE {$this->tableNames['reg']} SET status = :description
                    WHERE id_usuario = :userIdToValidate AND registro_id = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':userIdToValidate', $userIdToValidate);
        $stmt->bindParam(':code', $code);

        $success = $stmt->execute();

        // Adiciona um registro de histórico se a atualização for bem-sucedida
        if ($success) {
            $historyDescription = "Status do registro de ponto cód: {$code} atualizado para '{$description}'";
            // O ID do usuário para o histórico é aquele que foi validado.
            $this->createUserHistory($historyDescription, $userIdToValidate);
        }

        return $success;
    }
}
?>