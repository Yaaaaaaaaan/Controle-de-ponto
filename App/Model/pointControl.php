<?php
    if (!defined('APP_RAN')) {
        die('Acesso não permitido');
    }

    /*#[AllowDynamicProperties] //para classes com propriedades dinâmicas*/ class PointControl
    {
        private $conn;
        private $tableNames = [
            'usr' => 'usuarios',
            'his' => 'historicos_acoes',
            'tok' => 'tokens_autenticacao',
            'reg' => 'registros_ponto'
        ];

        public $id;
        public $descricao;
        public $userToken;
        public $name;
        public $email;

        public function __construct($db)
        {
            $this->conn = $db;
        }

        public function insertPointControl($id, $status): bool
        {
            $query = "INSERT INTO " . $this->tableNames['reg'] . " (id_usuario, data_registro, status) VALUES (:id, CURDATE(), :status)";

            $stmt = $this->conn->prepare($query);

            // Associa os parâmetros recebidos
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);

            try {
                if ($stmt->execute()) {
                    return true;
                }
                return false;
            } catch (PDOException $e) {
                // Trata erro de chave duplicada (usuário já bateu ponto no dia)
                if ($e->getCode() == 23000) {
                    error_log("Tentativa de inserção de ponto duplicado para o usuário: " . $id);
                    return false;
                } else {
                    error_log("Erro PDO em insertPointControl: " . $e->getMessage());
                    throw $e;
                }
            }
        }

    public function getPointControl($id_usuario)
    {
        // A query agora seleciona e renomeia as colunas para corresponder ao que o JS espera
        $query = "SELECT
                registro_id AS cod,
                id_usuario AS userId,
                data_registro AS dateIn,
                status
              FROM " . $this->tableNames['reg'] . "
              WHERE id_usuario = :id_usuario
              ORDER BY data_registro ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro PDO em getPointControl: " . $e->getMessage());
            return [];
        }
    }


    public function getPointControlUsersData(): array
        {
            try{
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
                    FROM " . $this->tableNames['reg'] . " p
                    INNER JOIN " . $this->tableNames['usr'] . "u ON p.id_usuario = u.id_usuario
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

    //TODO: Criar função para um usuário validar a presença de outro usuário, mas, com a condição de; o usuário avaliador deverá estar com a presença confirmada no dia ao qual está sendo feita a validação do outro usuário e, tal ato deverá ocorrer no dia corrido.
    public function validatePresence($userId, $userIdToValidate, $description, $code, $currentDate, $descriptionToValidate): true{
        $this->userId = $userId;
        $this->userIdToValidate = $userIdToValidate;
        $this->description = $description;
        $this->code = $code;
        $this->currentDate = $currentDate;
        $this->descriptionToValidate = $descriptionToValidate;

        $query = "SELECT 
        *
        FROM {$this->tableNames['reg']} 
        WHERE uidUserFK = :userId
        AND dateIn = :currentDate
        AND status = :descriptionToValidate
        ";

        return true;
    }
//TODO: Criar função para atualizar dados no housekeeping
    public function updatePresenceHousekeeping($userIdToValidate, $code, $description) {
        $this->userIdToValidate = $userIdToValidate;
        $this->description = $description;
        $this->code = $code;

        $query = "UPDATE {$this->tableNames['reg']} SET status = :description
                    WHERE uidUserFK = :userIdToValidate AND cod = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':userIdToValidate', $this->userIdToValidate);
        $stmt->bindParam(':code', $this->code);
        $result = $stmt->execute();

        if ($result) {
            echo "1";
        } else {
            echo "0";
            print_r($stmt->errorInfo());
        }
        return $result;
    }
    }
?>