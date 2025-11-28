<?php

use Random\RandomException;
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/User.php';
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    //TODO: REPARAR FUNÇÃO DE CRIAÇÃO DE USUÁRIO
    public function createUser($name, $nickname, $email, $password, $latitude, $longitude): array
    {
        // Validação de negócio (continua igual)
        if (empty($name) || empty($nickname) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'O formato do email é inválido.'];
        }

        $rank = 1;
        // Chama o Model para criar o utilizador
        $sessionData = $this->user->createUser($name, $nickname, $email, $password, $rank, $latitude, $longitude);
        if ($sessionData){
            // --- ESTA É A PARTE CRÍTICA ---
            // Garante que a resposta tenha a estrutura aninhada que o front-end espera,
            // envolvendo os dados retornados pelo Model ('userData' e 'tokenData') dentro da chave 'session'.
            return [
                'success' => true,
                'session' => $sessionData
            ];
        } else {
            return ['success' => false, 'message' => 'Não foi possível criar o utilizador. O email e/ou nome de utilizador já podem estar em uso.'];
        }
    }
    /**
     * @throws RandomException
     */

    public function authenticateUser($nickname, $password, $latitude, $longitude): ?array {
        $nicknamefil = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $this->user->authenticateUser($nicknamefil, $password, $latitude, $longitude);
    }

    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme, $occurrenceDate, $obs, $latitude, $longitude): array{
        $success = $this->user->updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme, $occurrenceDate, $obs, $latitude, $longitude);

        if ($success) {
            return ['success' => true, 'message' => 'Alterações efetuadas com sucesso.'];
        } else {
            return ['success' => false, 'message' => 'Alterações não efetuadas.'];
        }
    }

    public function getUserByToken(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        // 1. Usa o método do Model para obter o ID a partir do token
        $userId = $this->user->getIdByToken($token);

        if ($userId) {
            // 2. Se o ID for válido, usa o novo método para buscar os dados completos
            return $this->user->getUserById($userId);
        }

        return null;
    }

    public function getUserIdByTokenForSync(string $userToken): ?int {
        return $this->user->getUserIdByTokenForSync($userToken); // Apenas repassa a chamada
    }

    public function getUserIdByToken(string $userToken): ?int
    {
        error_log("UserController.php - getUserIdByToken: userToken recebido: " . $userToken);
        return $this->user->getIdByToken($userToken);
    }

     public function updateUserTheme(int $userId, int $theme): bool
    {
        error_log("UserController.php - updateUserTheme: userId recebido: " . $userId . ", theme recebido: " . $theme);
        return $this->user->updateTheme($userId, $theme);
    }

    public function getUserData(): ?array
    {
        if (isset($_SESSION['userData']) && $_SESSION['userData'] != null) {
            $userData = json_decode($_SESSION['userData'], true);
            if (is_array($userData) && !empty($userData)) {
                return $userData;
            }
        }
        return null;
    }

    public function getUserToken(): ?array{
        if (isset($_SESSION['userToken']) && $_SESSION['userToken'] != null) {
            $userToken = json_decode($_SESSION['userToken'], true);
            if (is_array($userToken) && !empty($userToken)) {
                return $userToken;
            }
        }
        return null;
    }

    public function deleteUserToken(int $userId): bool
    {
        if (empty($userId)) {
            return false;
        }
        // Apenas chama o método correspondente que já existe no Model (User.php)
        return $this->user->deleteTokenForUser($userId);
    }

    public function insertUserPicture(int $userId, string $filePath, string $originalFileName, ?float $latitude, ?float $longitude): ?int
    {
        return $this->user->insertNewUserPicture($userId, $filePath, $originalFileName, $latitude, $longitude);
    }

    public function setUserProfilePicture(int $userId, int $photoId, ?float $latitude, ?float $longitude): bool
    {
        return $this->user->setActiveProfilePicture($userId, $photoId, $latitude, $longitude);
    }

    public function getAllUserPictures(int $userId): array
    {
        return $this->user->getAllUserPictures($userId);
    }

    public function logUserLogout(int $userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        // A única ação é criar o registro de histórico.
        return $this->user->createUserHistory('Logout bem-sucedido', $userId);
    }

    public function getUserHistory(int $userId, int $limit = 20)
    {
        // Apenas repassa a chamada para o método que já corrigimos no Model.
        return $this->user->getUserHistory($userId, $limit);
    }
// Em App/Controller/UserController.php

public function updatePresenceHousekeeping($userId, $codRegistro, $statusId, $dateInput) {
    // --- DEBUG: Descobrir qual campo está vazio ---
    if (empty($codRegistro)) {
        return ['success' => false, 'message' => 'Erro: Código do registro (cod) não encontrado.'];
    }
    if (empty($statusId)) {
        return ['success' => false, 'message' => 'Erro: Status não selecionado.'];
    }
    if (empty($dateInput)) {
        return ['success' => false, 'message' => 'Erro: Data não informada.'];
    }

    // --- Lógica de Formatação da Data ---
    $dateFormatted = $dateInput;
    // Se vier DD/MM/YYYY, converte para YYYY-MM-DD
    if (strpos($dateInput, '/') !== false) {
        $parts = explode('/', $dateInput);
        if (count($parts) === 3) {
            $dateFormatted = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    // --- Chama o Model ---
    // Certifique-se que a função updateRegistryStatus existe no Model/User.php
    $updateResult = $this->user->updateRegistryStatus($codRegistro, $statusId, $dateFormatted);

    if ($updateResult) {
        return ['success' => true, 'message' => 'Registro atualizado com sucesso.'];
    } else {
        return ['success' => false, 'message' => 'Erro no Banco de Dados (SQL).'];
    }
}

}
?>
