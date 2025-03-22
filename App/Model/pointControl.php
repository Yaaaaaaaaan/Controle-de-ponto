<?php
    if (!defined('APP_RAN')) {
        die('Acesso não permitido');
    }

    class pointControl
    {
        private $conn;
        private $tableNames = [
            'ud' => 'userdata',
            'hs' => 'history',
            'ut' => 'usertoken',
            'pc' => 'pointControl'
        ];

        public $id;
        public $userToken;
        public $name;
        public $email;

        public function __construct($db){
            $this->conn = $db;
        }

        public function insertPointControl($id, $descricao) {
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

        public function getPointControl($id, $ano) {
            $sql = "SELECT DATE_FORMAT(data, '%Y-%m') AS mes, COUNT(*) AS presenca FROM presenca WHERE id_usuario = $id AND YEAR(data) = $ano GROUP BY mes ORDER BY mes";
            $result = $this->conn->query($sql);
            $data = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            return $data;
        }


        public function getAllAvailableMonths($id) {
            try {
                $query = "SELECT DISTINCT DATE_FORMAT(dateIn, '%Y-%m') as month FROM ".$this->tableNames['pc']." WHERE uidUserFK = :id ORDER BY month";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                error_log("Erro em getAllAvailableMonths: " . $e->getMessage());
                return []; // Retorna um array vazio em caso de erro
            }
        }

        public function getPointControlData($id): array
        {
            $query = "SELECT DATE_FORMAT(dateIn, '%Y-%m') as month, COUNT(*) as count 
              FROM pointControl 
              WHERE uidUserFK = :id 
              GROUP BY month 
              ORDER BY month DESC 
              LIMIT 3";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Inverte a ordem para mostrar do mais antigo para o mais recente
            return array_reverse($result);
        }

    }

?>