<?php
// App/Model/housekeeping.php

if (!defined('APP_RAN')) { die('Acesso não permitido'); }

class Housekeeping {
    private $conn;
    private $table = 'registros_ponto';
    private $$tableUsers = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lista todos os usuários ativos (is_inactive = 0)
     * e junta com a foto de perfil atual.
     */
    public function getAllUsers() {
        $query = "SELECT 
                    u.id_usuario, 
                    u.nome_completo, 
                    u.nome_usuario, 
                    u.email, 
                    u.nivel_acesso, 
                    u.tema_padrao,
                    f.caminho_arquivo as foto_perfil
                  FROM " . $this->table . " u
                  LEFT JOIN " . $this->tablePics . " f ON u.id_usuario = f.id_usuario AND f.perfil = 1
                  WHERE u.is_inactive = 0 
                  ORDER BY u.nome_completo ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HKG Model Error (getAllUsers): " . $e->getMessage());
            return false;
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
}

?>