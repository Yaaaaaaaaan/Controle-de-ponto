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
    public function createUser($name, $nickname, $email, $password): array
    {
        // --- VALIDAÇÃO CENTRALIZADA E DETALHADA ---
        if (empty($name)) {
            return ['success' => false, 'message' => 'O campo "Nome completo" é obrigatório.'];
        }
        if (empty($nickname)) {
            return ['success' => false, 'message' => 'O campo "Usuário" é obrigatório.'];
        }
        if (empty($email)) {
            return ['success' => false, 'message' => 'O campo "Email" é obrigatório.'];
        }
        if (empty($password)) {
            return ['success' => false, 'message' => 'O campo "Senha" é obrigatório.'];
        }
        // ---------------------------------------------

        $this->user->name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $this->user->password = $password;
        $this->user->rank = 1;

        // Tenta executar a criação no Model
        if ($this->user->createUser()){
            return ['success' => true, 'message' => 'Usuário criado com sucesso!'];
        } else {
            // Se o Model falhar, é provável que seja um erro de banco de dados (ex: email/nickname duplicado)
            return ['success' => false, 'message' => 'Não foi possível criar o usuário. O email ou nome de usuário já pode estar em uso.'];
        }
    }

    /**
     * @throws RandomException
     */

    public function authenticateUser($nickname, $password): array {
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->password = $password;

        if($this->user->authenticateUser()) {
            return ['success' => true, 'message' => 'Usuário autenticado com sucesso.'];

        } else {
            return ['success' => false, 'message' => 'Usuário não autenticado.'];
        }
    }

    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme): array{
        $this->user->name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
        $this->user->id = $id; /* preciso descobrir como recuperar diretamente o usertoken ao invés do ID */
        $this->user->email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
        $this->user->oldPassword = $oldPassword;
        $this->user->newPassword = $newPassword;
        $this->user->confirmPassword = $confirmPassword;
        $this->user->defaultTheme = $defaultTheme ? 1 : 0;

        if ($this->user->updateUser()) {
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

    public function updateProfilePicture($pictureId): array{
        if (isset($_SESSION['id'])) {
            if ($this->user->updateProfilePicture($_SESSION['id'], $pictureId)) {
                return ['success' => true, 'message' => 'Imagem alterada com sucesso.'];
            } else {
                return ['success' => false, 'message' => 'Imagem não alterada.'];
            }
        } else {
            return ['success' => false, 'message' => 'Usuário não autenticado.'];
        }
    }

    /*public function insertUserProfilePicture($userPicture): array
    {
        $targetDirectory = '/Controle-de-ponto/App/Persistence/userProfileImages/';
        $nameOld = $targetDirectory . basename($userPicture['name']);
        $uploadOk = 1;
        $fileTypeImage = strtolower(pathinfo($nameOld, PATHINFO_EXTENSION));
        // Gera um novo nome de arquivo baseado na data e hora atual
        $newFileName = date('YmdHis') . $_SESSION['id'] . '.' . $fileTypeImage;
        $arch = $targetDirectory . $newFileName;
        // Caminho completo no servidor
        $targetFile = __DIR__ . '/../Persistence/userProfileImages/' . $newFileName;
        // Verifica se o arquivo é uma imagem
        $check = getimagesize($userPicture['tmp_name']);
        if ($check === false) {
            return [''] = "O arquivo não é uma imagem.";
            $uploadOk = 0;
        }

        // Verifica se o arquivo já existe
        if (file_exists($arch)) {
            return [''] = "Arquivo já existente.";
            $uploadOk = 0;
        }

        // Verifica o tamanho do arquivo
        if ($userPicture['size'] > 500000) { // Limite de 500KB
            return [''] = "Arquivo muito grande.";
            $uploadOk = 0;
        }

        // Permite apenas certos formatos de arquivo
        if (!in_array($fileTypeImage, ['jpg', 'png', 'jpeg', 'gif'])) {
            return [''] = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            $uploadOk = 0;
        }

        // Se estiver tudo ok, tenta fazer o upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($userPicture['tmp_name'], $targetFile)) {
                if ($this->user->insertUserProfilePicture($newFileName, $arch, $uploadOk)) {
                    return [''] = 'Imagem carregada com sucesso.';
                }
            } else {
                return [''] = '<p>Erro ao alterar imagem de perfil.</p>';
            }
        }
    }*/

    public function showUserHistory($registro) {
        $this->user->registro = $registro;
        $userHistory = $this->user->getUserHistory(userId: $_SESSION['id'], registro: $registro);
        return $userHistory;
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



}
?>
