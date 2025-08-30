<?php


if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}

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
    public function createUserHistory(string $description, int $userId): bool {
        try {
            // Delega a responsabilidade para o Model de Histórico
            $historyModel = new History($this->conn);
            return $historyModel->create($userId, $description);
        } catch (Exception $e) {
            // Adiciona contexto ao erro para facilitar a depuração
            error_log("Erro ao delegar criação de histórico a partir do PointControl->createUserHistory: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria um novo registo de ponto.
     * @param int $userId O ID do utilizador.
     * @param string $status O status do registo.
     * @param string|null $date A data do registo (formato Y-m-d). Se for nulo, usa a data atual.
     * @return bool Retorna true em caso de sucesso, false caso contrário.
     */
    public function insertPointControl(int $userId, string $status, ?string $date = null): bool
    {
        $data_registro = $date ?? date('Y-m-d');

        $query = "INSERT INTO {$this->tableName} (id_usuario, data_registro, status) 
                  VALUES (:id_usuario, :data_registro, :status)
                  ON DUPLICATE KEY UPDATE status = VALUES(status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $userId);
        $stmt->bindParam(':data_registro', $data_registro);
        $stmt->bindParam(':status', $status);

        try {
            if ($stmt->execute()) {
                // Se o ponto foi inserido, cria o registo de histórico usando a nova função.
                $historyDescription = "Registo de ponto bem-sucedido: {$status} | Data: {$data_registro}";
                $this->createUserHistory($historyDescription, $userId);
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erro em PointControl->insertPointControl(): " . $e->getMessage());
        }

        return false;
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
                WHERE p.data_registro >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
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