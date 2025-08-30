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

    public function __construct($db)
    {
        $this->conn = $db;
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
        // CORRIGIDO: A data agora é tratada corretamente.
        $data_registro = $date ?? date('Y-m-d');

        // CORRIGIDO: O nome da tabela agora é o correto do array.
        $query = "INSERT INTO {$this->tableNames['reg']} (id_usuario, data_registro, status) 
                  VALUES (:id_usuario, :data_registro, :status)
                  ON DUPLICATE KEY UPDATE status = VALUES(status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $userId);
        $stmt->bindParam(':data_registro', $data_registro);
        $stmt->bindParam(':status', $status);

        try {
            if ($stmt->execute()) {
                // Se o ponto foi inserido, cria o registo de histórico.
                try {
                    $historyModel = new History($this->conn);
                    $historyDescription = "Registo de ponto criado com status: '{$status}' para a data {$data_registro}";
                    $historyModel->create($userId, $historyDescription);
                } catch (Exception $e) {
                    error_log("Falha ao criar entrada de histórico em PointControl->insertPointControl(): " . $e->getMessage());
                }
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erro em PointControl->insertPointControl(): " . $e->getMessage());
        }

        return false;
    }

    /**
     * Busca todos os registos de ponto de um utilizador.
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array {
        // CORRIGIDO: Renomeado de getPointControl para getByUserId para padronização.
        $query = "SELECT registro_id as cod, id_usuario as userId, data_registro as dateIn, status 
                  FROM {$this->tableNames['reg']} 
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
        // CORRIGIDO: Nomes das colunas atualizados (id_usuario, registro_id)
        $query = "UPDATE {$this->tableNames['reg']} SET status = :description
                    WHERE id_usuario = :userIdToValidate AND registro_id = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':userIdToValidate', $userIdToValidate);
        $stmt->bindParam(':code', $code);

        return $stmt->execute();
    }
}
?>