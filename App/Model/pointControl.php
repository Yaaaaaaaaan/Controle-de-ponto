<?php
    if (!defined('APP_RAN')) {
        die('Acesso não permitido');
    }

    /*#[AllowDynamicProperties] //para classes com propriedades dinâmicas*/ class pointControl
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
                    reg.status, reg.cod,
                    COUNT(*) as count,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'cod', reg.cod,
                            'data', DATE_FORMAT(reg.dateIn, '%d/%m/%Y'),
                            'nome', usr.uname,
                            'status', reg.status,
                            'id', reg.uidUserFK
                        )
                    ) as detalhes
                    FROM pointControl reg
                    INNER JOIN userdata usr ON reg.uidUserFK = usr.uid
                    WHERE reg.dateIn >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                    GROUP BY reg.status
                    ORDER BY count DESC";

                // Debug direto do resultado da query
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (PDOException $e) {
                echo "<pre>";
                echo "Erro na query: " . $e->getMessage();
                echo "</pre>";
                return [];
            }
        }
    }
?>