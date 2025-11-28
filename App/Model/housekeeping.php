<?php
// App/Model/housekeeping.php

if (!defined('APP_RAN')) { die('Acesso não permitido'); }

class Housekeeping { // Classe com H Maiúsculo (Padrão PSR, mas PHP aceita variações)
    private $conn;
    private $table = 'usuarios'; // Verifique se o nome da tabela no banco é 'usuarios' ou 'users'

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllUsers() {
        // Verifica se a coluna is_inactive existe. Se não existir, vai dar erro no execute().
        // query segura
        $query = "SELECT u.id_usuario, u.nome_completo, u.nome_usuario, u.email, u.nivel_acesso, 
                         f.caminho_arquivo as foto_perfil
                  FROM usuarios u
                  LEFT JOIN fotos f ON u.id_usuario = f.id_usuario AND f.perfil = 1
                  WHERE u.is_inactive = 0 
                  ORDER BY u.nome_completo ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Retorna o erro para ser capturado pelo controller
            throw new Exception("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * Soft Delete: Apenas marca como inativo.
     */
    public function softDeleteUser($id) {
        $query = "UPDATE " . $this->table . " SET is_inactive = 1 WHERE id_usuario = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("HKG Model Error (softDelete): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria um novo usuário administrativo.
     */
    public function createUser($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (nome_completo, nome_usuario, email, nivel_acesso, senha_hash) 
                  VALUES (:name, :nickname, :email, :rank, :password)";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Hash da senha é feito no Controller, aqui apenas salvamos
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':nickname', $data['nickname']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':rank', $data['rank']);
            $stmt->bindValue(':password', $data['password']); // Já vem hash
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("HKG Model Error (create): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza dados de um usuário existente.
     */
    public function updateUser($id, $data) {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table . " SET 
                        nome_completo = :name, 
                        nome_usuario = :nickname, 
                        email = :email, 
                        nivel_acesso = :rank";
            
            // Se tiver senha nova, atualiza. Se não, mantém a velha.
            if (!empty($data['password'])) {
                $query .= ", senha_hash = :password";
            }
            
            $query .= " WHERE id_usuario = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':nickname', $data['nickname']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':rank', $data['rank']);
            $stmt->bindValue(':id', $id);

            if (!empty($data['password'])) {
                $stmt->bindValue(':password', $data['password']);
            }

            $stmt->execute();
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("HKG Model Error (update): " . $e->getMessage());
            return false;
        }
    }
    /**
     * Busca dados para o Dashboard (Gráfico e Tabela Detalhada).
     * Junta registros de ponto com dados do usuário.
     */
    public function getDashboardData() {
        // Assume que a tabela de registros é 'registros_ponto'
        // Se for outra, ajuste aqui (ex: $this->tableRegistros)
        $query = "SELECT 
                    r.registro_id as cod, 
                    r.data_registro as dateIn, 
                    r.status as status, 
                    u.id_usuario as userId,
                    u.nome_completo as nome
                  FROM registros_ponto r
                  INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                  ORDER BY r.data_registro DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HKG Dashboard Error: " . $e->getMessage());
            return []; // Retorna array vazio em caso de erro para não quebrar o JS
        }
    }

    /**
     * Atualiza o status e a data de um registro de ponto específico.
     */
    public function updatePointStatus($cod, $statusText, $newDate) {
        $query = "UPDATE registros_ponto 
                  SET status = :status, data_registro = :data 
                  WHERE registro_id = :cod";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':status', $statusText);
            $stmt->bindValue(':data', $newDate);
            $stmt->bindValue(':cod', $cod);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("HKG Update Point Error: " . $e->getMessage());
            return false;
        }
    }
}

?>