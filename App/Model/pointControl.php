<?php
    if (!defined('APP_RAN')) {
        die('Acesso não permitido');
    }

    /*#[AllowDynamicProperties] //para classes com propriedades dinâmicas*/ class pointControl
    {
        private $conn;
        private $tableNames = [
            'ud' => 'userdata',
            'hs' => 'history',
            'ut' => 'usertoken',
            'pc' => 'pointControl'
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

        public function insertPointControl($id, $descricao): bool
        {
            $this->descricao = $descricao;
            $this->id = $id;

            $query = "INSERT INTO " . $this->tableNames['pc'] . "  (description, uidUserFK) VALUES (:description, :id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':description', $this->descricao);
            $stmt->bindParam(':id', $this->id);

            try {
                $stmt->execute();
                return true;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    return false;
                } else {
                    throw $e;
                }
            }
        }

        public function getPointControlData($id): array
        {
            // Consulta para obter os meses (para o gráfico)
            $queryMeses = "SELECT DATE_FORMAT(dateIn, '%Y-%m') as month, COUNT(*) as count 
                FROM pointControl 
                WHERE uidUserFK = :id 
                GROUP BY month 
                ORDER BY month DESC 
                LIMIT 3";

            $stmt = $this->conn->prepare($queryMeses);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $resultMeses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Inverte a ordem para mostrar do mais antigo para o mais recente
            $resultMeses = array_reverse($resultMeses);

            // Obter detalhes dos dias para cada mês
            $diasPorMes = [];
            foreach ($resultMeses as $mes) {
                $monthStr = $mes['month'];

                // Consulta modificada para incluir a descrição para cada dia
                $queryDias = "SELECT DATE_FORMAT(dateIn, '%d') as day, COUNT(*) as day_count, description
                    FROM pointControl 
                    WHERE uidUserFK = :id AND DATE_FORMAT(dateIn, '%Y-%m') = :month
                    GROUP BY day, description
                    ORDER BY day";

                $stmtDias = $this->conn->prepare($queryDias);
                $stmtDias->bindParam(':id', $id);
                $stmtDias->bindParam(':month', $monthStr);
                $stmtDias->execute();
                $diasPorMes[$monthStr] = $stmtDias->fetchAll(PDO::FETCH_ASSOC);
            }

            // Adicionar informações de dias ao resultado
            foreach ($resultMeses as &$mes) {
                $mes['dias'] = $diasPorMes[$mes['month']] ?? [];
            }
            $_SESSION['pointControl'] = json_encode([$resultMeses]);
            return $resultMeses;
        }

    public function getPointControlUsersData(): array
    {
        try{
        $query = "SELECT 
        pc.description, pc.cod,
        COUNT(*) as count,
        GROUP_CONCAT(
            JSON_OBJECT(
                'cod', pc.cod,
                'data', DATE_FORMAT(pc.dateIn, '%d/%m/%Y'),
                'nome', ud.uname,
                'descricao', pc.description,
                'id', pc.uidUserFK
            )
        ) as detalhes
    FROM pointControl pc
    INNER JOIN userdata ud ON pc.uidUserFK = ud.uid
    WHERE pc.dateIn >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY pc.description
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