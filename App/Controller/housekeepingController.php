<?php
// App/Controller/housekeepingController.php

if (!defined('APP_RAN')) { die('Acesso não permitido'); }

require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/housekeeping.php';

class HousekeepingController {
    private $hkgModel;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->hkgModel = new Housekeeping($this->db);
    }

    public function getUsersList() {
        $result = $this->hkgModel->getAllUsers();
        if ($result === false) {
            return ['error' => 'Erro interno ao buscar usuários.'];
        }
        return $result;
    }

    public function deleteUser($id) {
        if (empty($id)) return ['success' => false, 'message' => 'ID inválido.'];

        // Regra de negócio: Não pode se excluir (opcional, mas recomendado)
        if (isset($_SESSION['id']) && $_SESSION['id'] == $id) {
            return ['success' => false, 'message' => 'Você não pode inativar seu próprio usuário.'];
        }

        if ($this->hkgModel->softDeleteUser($id)) {
            return ['success' => true, 'message' => 'Usuário inativado com sucesso.'];
        }
        return ['success' => false, 'message' => 'Erro ao processar exclusão.'];
    }

    public function saveUser($input) {
        // 1. Sanitização e Validação Básica
        $id = $input['userId'] ?? null;
        $data = [
            'name'     => trim($input['name'] ?? ''),
            'nickname' => trim($input['nickname'] ?? ''),
            'email'    => filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'rank'     => (int)($input['rank'] ?? 2),
            'password' => $input['password'] ?? ''
        ];

        if (empty($data['name']) || empty($data['nickname']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'Preencha os campos obrigatórios.'];
        }

        if (!$data['email']) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }

        // 2. Lógica de Criação ou Edição
        if (empty($id)) {
            // --- CRIAR NOVO ---
            if (empty($data['password'])) {
                return ['success' => false, 'message' => 'Senha é obrigatória para novos usuários.'];
            }
            // Hash da senha AQUI no controller antes de mandar pro Model
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            if ($this->hkgModel->createUser($data)) {
                return ['success' => true, 'message' => 'Usuário criado com sucesso.'];
            }
        } else {
            // --- EDITAR EXISTENTE ---
            // Se senha foi fornecida, faz hash. Se não, manda vazia (o Model ignora)
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if ($this->hkgModel->updateUser($id, $data)) {
                return ['success' => true, 'message' => 'Usuário atualizado com sucesso.'];
            }
        }

        return ['success' => false, 'message' => 'Erro ao salvar no banco de dados.'];
    }
}
?>