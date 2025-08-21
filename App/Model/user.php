<?php

use Random\RandomException;

if (!defined('APP_RAN')) {
  die('Acesso não permitido');
}

#[AllowDynamicProperties] class User{
    private $conn;
    private $tableNames = [
        'usr' => 'usuarios',
        'alb' => 'albuns',
        'fot' => 'fotos',
        'his' => 'historicos_acoes',
        'tok' => 'tokens_autenticacao',
        'reg' => 'registros_ponto'
    ];

    public $id;
    public $userToken;
    public $name;
    public $email;
    public $password;
    public $rank;
    public $nickname;

    public $newPassword;
    public $confirmPassword;
    public $oldPassword;

    public $defaultTheme;

    public $profilePicture;
    public $directory;
    public $verifyUpload;

    public $descricao;
    public $registro;

    public function __construct($db){
        $this->conn = $db;
    }

    public function createUser(): bool{
        // Define valor padrão para a imagem de perfil
        $this->profilePicture = 'Profile.png';
        $this->directory = '/controle-de-ponto/Public/Api/userProfileImages/Profile.png';
        $this->isProfile = '1';
        $this->albumName = 'Foto de perfil';
        $this->albumType = '1';

        $this->conn->beginTransaction();
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        try {
            // 1. Inserir usuário
            $queryUser = "INSERT INTO {$this->tableNames['usr']} (nome_completo, nome_usuario, email, senha_hash, nivel_acesso) VALUES (:name, :nickname, :email, :password, :rank)";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':name', $this->name);
            $stmtUser->bindParam(':nickname', $this->nickname);
            $stmtUser->bindParam(':email', $this->email);
            $stmtUser->bindParam(':password', $this->password);
            $stmtUser->bindParam(':rank', $this->rank);
            $stmtUser->execute();
            $newUserId = $this->conn->lastInsertId();

            // 2. Inserir álbum
            $queryAlbum = "INSERT INTO {$this->tableNames['alb']} (id_usuario, nome_album, tipo_album) VALUES (:newUserId, :albumName, :albumType)";
            $stmtAlbum = $this->conn->prepare($queryAlbum);
            $stmtAlbum->bindParam(':newUserId', $newUserId);
            $stmtAlbum->bindParam(':albumName', $this->albumName);
            $stmtAlbum->bindParam(':albumType', $this->albumType);
            $stmtAlbum->execute();
            $newAlbumId = $this->conn->lastInsertId();

            // 3. Inserir foto
            $queryProfilePic = "INSERT INTO {$this->tableNames['fot']} (album_id, id_usuario, caminho_arquivo, nome_foto, perfil) VALUES (:newAlbumId, :newUserId, :directory, :profilePicture, :isProfile)";
            $stmtProfilePic = $this->conn->prepare($queryProfilePic);
            $stmtProfilePic->bindParam(':newAlbumId', $newAlbumId);
            $stmtProfilePic->bindParam(':newUserId', $newUserId);
            $stmtProfilePic->bindParam(':directory', $this->directory);
            $stmtProfilePic->bindParam(':profilePicture', $this->profilePicture);
            $stmtProfilePic->bindParam(':isProfile', $this->isProfile);
            $stmtProfilePic->execute();

            // --- INÍCIO DA LÓGICA DO TOKEN (O PONTO PRINCIPAL DA CORREÇÃO) ---

            // 4. Gerar o token de autenticação
            $userToken = bin2hex(random_bytes(32)); // Gera um token seguro de 64 caracteres

            // 5. Calcular a data de expiração (7 dias a partir de agora)
            $expiryDate = (new DateTime())->add(new DateInterval('P7D'))->format('Y-m-d H:i:s');

            // 6. Inserir o token na tabela de tokens
            $queryToken = "INSERT INTO {$this->tableNames['tok']} (id_usuario, token, data_expiracao) VALUES (:id_usuario, :token, :data_expiracao)";
            $stmtToken = $this->conn->prepare($queryToken);
            $stmtToken->bindParam(':id_usuario', $newUserId);
            $stmtToken->bindParam(':token', $userToken);
            $stmtToken->bindParam(':data_expiracao', $expiryDate);
            $stmtToken->execute();

            // --- FIM DA LÓGICA DO TOKEN ---

            $this->conn->commit(); // Confirma todas as operações (usuário, album, foto e token)
            $this->createUserHistory('Criação de conta', $newUserId);
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            // Lembre-se de restaurar o código de log de erro aqui!
            error_log("Erro em User->createUser: " . $e->getMessage());
            return false;
        }
    }

    public function __destruct(){
        // Forma correta de fechar uma conexão PDO
        if ($this->conn) {
            $this->conn = null;
        }
    }

    public function authenticateUser(): ?array
    {
        try {

            $userId = $this->verifyCredentials();
            if (!$userId) return null;

            $fullUserData = $this->getUserById($userId);


            if (!$fullUserData) return null;

            $tokenData = $this->manageUserToken($userId, $fullUserData);

            // Monta o objeto final
            $finalResponseData = array_merge($fullUserData, $tokenData);

            // Popula a sessão (ainda útil para partes do PHP que usam a sessão)
            $this->populateSession($userId, $fullUserData, $tokenData);
            $this->createUserHistory('Login bem-sucedido', $userId);

            // RETORNA O OBJETO COMPLETO
            return $finalResponseData;

        } catch (Exception $e) {
            error_log("Erro no fluxo de autenticação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * [HELPER PRIVADO] Apenas verifica as credenciais.
     * @return int|null O ID do usuário ou null.
     */
    private function verifyCredentials(): ?int
    {
        if (empty($this->nickname) || empty($this->password)) {
            return null;
        }
        $query = "SELECT id_usuario, senha_hash FROM {$this->tableNames['usr']} WHERE nome_usuario = :nickname";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nickname', $this->nickname);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password, $row['senha_hash'])) {
            return (int)$row['id_usuario'];
        }
        return null;
    }

    /**
     * [HELPER PRIVADO] Gerencia o token para um usuário.
     * @param int $userId
     * @param array $userDataFromQuery Dados já buscados, incluindo token e data de expiração.
     * @return array Dados do token.
     */
    private function manageUserToken(int $userId, array $userDataFromQuery): array
    {
        $existingToken = $userDataFromQuery['userToken'] ?? null;
        $tokenExpiryStr = $userDataFromQuery['tokenExpiry'] ?? null;
        $tokenCreationStr = $userDataFromQuery['tokenDate'] ?? null;
        $tokenExpiry = $tokenExpiryStr ? new DateTime($tokenExpiryStr) : null;
        $now = new DateTime();

        $isExpired = $tokenExpiry ? $tokenExpiry < $now : true;

        if (!$existingToken || !$tokenExpiry || $isExpired) {
            // LOG ADICIONADO PARA DEPURAÇÃO
            $reason = ". Motivo: " .
                (!$existingToken ? 'Token não existia. ' : '') .
                (!$tokenExpiry ? 'Data de expiração não existia. ' : '') .
                ($isExpired ? 'Token expirado em ' . ($tokenExpiry ? $tokenExpiry->format('Y-m-d H:i:s') : 'N/A') . '. ' : '');
            error_log("DECISÃO: Gerando NOVO token para usuário ID {$userId}{$reason}");

            $userToken = bin2hex(random_bytes(32));
            $nowForDb = $now->format('Y-m-d H:i:s');
            $newExpiryDate = (clone $now)->add(new DateInterval('P7D'))->format('Y-m-d H:i:s');

            // (O resto da lógica de INSERT/UPDATE continua o mesmo)
            $tokenQuery = "INSERT INTO {$this->tableNames['tok']} (id_usuario, token, data_expiracao, data_criacao)
                   VALUES (:id, :token, :expiry, :created)
                   ON DUPLICATE KEY UPDATE token = VALUES(token), data_expiracao = VALUES(data_expiracao)";

            $tokenStmt = $this->conn->prepare($tokenQuery);
            $tokenStmt->execute([
                ':id' => $userId, ':token' => $userToken, ':expiry' => $newExpiryDate, ':created' => $nowForDb
            ]);

            return ['userToken' => $userToken, 'tokenDate' => $nowForDb, 'tokenExpiry' => $newExpiryDate];
        }

        // LOG ADICIONADO PARA DEPURAÇÃO
        error_log("DECISÃO: Reutilizando token existente para usuário ID {$userId}. Expira em: " . $tokenExpiry->format('Y-m-d H:i:s'));
        return ['userToken' => $existingToken, 'tokenDate' => $tokenCreationStr, 'tokenExpiry' => $tokenExpiryStr];
    }

    /**
     * [HELPER PRIVADO] Preenche as variáveis de sessão.
     * @param int $userId
     * @param array $profileData
     * @param array $tokenData
     */
    private function populateSession(int $userId, array $profileData, array $tokenData): void
    {
        $_SESSION['id'] = $userId;
        $_SESSION['logged'] = true;

        // A sessão 'userData' está correta.
        $_SESSION['userData'] = json_encode([
            'userId'      => $profileData['userId'],
            'name'        => $profileData['name'],
            'email'       => $profileData['email'],
            'rank'        => $profileData['rank'],
            'nickname'    => $profileData['nickname'],
            'theme'       => $profileData['theme'],
            'profileUser' => $profileData['profileUser'],
        ]);

        $_SESSION['tokenUserData'] = json_encode([
            'userToken'   => $tokenData['userToken'],
            'tokenDate'   => $tokenData['tokenDate'],
            'tokenExpiry' => $tokenData['tokenExpiry'],
            'userId'      => $userId,
        ]);
    }



    public function updateUser(): bool{
        // Verificar os campos obrigatórios
        if (empty($this->name) || empty($this->email) || empty($this->nickname)) {
            return false;
        }

        // Iniciar a consulta de atualização
        $query = "UPDATE " . $this->tableNames['usr'] . "
            SET uname = :name,
            uemail = :email,
            username = :nickname,
            udefaultTheme = :defaultTheme ";

        $passwordUpdated = false;
        if (!empty($this->newPassword) && !empty($this->confirmPassword) && !empty($this->oldPassword)) {
            // Verificar se a senha atual está correta antes de permitir a alteração
            $checkPasswordQuery = "SELECT upassword FROM " . $this->tableNames['usr'] . " WHERE id_usuario = :id";
            $checkStmt = $this->conn->prepare($checkPasswordQuery);
            $checkStmt->bindParam(':id', $this->id);
            $checkStmt->execute();
            $currentPasswordHash = $checkStmt->fetchColumn();
            var_dump($this->oldPassword);var_dump($this->newPassword);var_dump($this->confirmPassword);
            var_dump($currentPasswordHash);

            // Verificar se a senha antiga fornecida CORRESPONDE ao HASH armazenado
            if ($currentPasswordHash && password_verify($this->oldPassword, $currentPasswordHash) && $this->newPassword == $this->confirmPassword) {
                $query .= ", upassword = :newPassword";
                $passwordUpdated = true;
            } else {
                return false; // Falha na verificação da senha antiga ou nova senha não confere
            }
        }

        // Finaliza a consulta com a condição WHERE
        $query .= " WHERE id_usuario = :id";

        try {
            // Prepara e executa a consulta
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':nickname', $this->nickname);
            $stmt->bindParam(':defaultTheme', $this->defaultTheme);
            $stmt->bindParam(':id', $this->id);

            if ($passwordUpdated) {
                $stmt->bindParam(':newPassword', password_hash($this->newPassword, PASSWORD_DEFAULT));
            }

            if ($stmt->execute()) {
                // Atualiza os dados da sessão
                if (isset($_SESSION['userData'])) {
                    $userData = json_decode($_SESSION['userData'], true);
                    $userData['name'] = $this->name;
                    $userData['email'] = $this->email;
                    $userData['nickname'] = $this->nickname;
                    $userData['theme'] = $this->defaultTheme;
                    $_SESSION['userData'] = json_encode($userData);
                    $_SESSION['userData_updated'] = true;

                    //Inserir registro no histórico
                    $description = 'Alteração de informações, ';
                    $this->createUserHistory($description, $this->id);
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    //TODO: a fazer FUNCIONALIDADE DELETEACCOUNT.
    public function deleteAccount() {
        if (!empty($this->email) && !empty($this->password)) {
            $query = "SELECT id_usuario, uemail, upassword FROM " . $this->tableNames['usr'] . " 
            WHERE uemail = :email AND upassword = :upassword";
    
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $this->email);
                $stmt->bindParam(':upassword', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    //Inserir registro no histórico
                    $description = 'Exclusão de conta, ';
                    $this->createUserHistory($description, $this->id);
                    return true;
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
    
        return false;
    }

    public function createUserHistory($description, $userId): bool{
        try{
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $queryInsert = "INSERT INTO {$this->tableNames['his']} (descricao, id_usuario) VALUES (:description, :id)";
            $stmtHistory = $this->conn->prepare($queryInsert);
            $newDescription = $description . "Endereço IP: " . $ip;
            $stmtHistory->bindValue(':description', $newDescription);
            $stmtHistory->bindParam(':id', $userId);
            $result = $stmtHistory->execute();

            if (!$result) {
                error_log("Erro ao inserir no histórico: " . print_r($stmtHistory->errorInfo(), true));
                return false;
            }
            return true;

        }catch (PDOException $e) {
            error_log("Erro PDO ao inserir no histórico: " . $e->getMessage());
            return false;
        }
    }

    public function getUserHistory($userId, $registro) {

        $querySelect = "SELECT h.description, h.dateIn FROM " . $this->tableNames['usr'] .
            " u inner join ".$this->tableNames['his'].
            " h ON u.id_usuario = h.uidUserFK WHERE u.uid = :id ORDER BY h.cod desc LIMIT " . $registro . ";";

        try {
          $stmt = $this->conn->prepare($querySelect);
          $stmt->bindParam(':id', $userId);
          $stmt->bindParam('', $this->$registro);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if($result){
              //Inserir registro no histórico
              $description = 'Consulta de histórico ';
              $this->createUserHistory($description, $this->id);
          }
          return $result;
        } catch(PDOException $e) {
          echo "Error: " . $e->getMessage();
          return false;
        }
      }

      
      public function setPictures($picture, $directory): bool{ //insere imagens no banco de dados.
        $this->picture = $picture;
        $this->directory = $directory;
        $this->id = $_SESSION['id'];
        $queryInsert = "INSERT INTO" . $this->tableNames['fot'] . " SET path=:directory, namePic=:picture, idUserFK = :id ";
        $stmt = $this->conn->prepare($queryInsert);
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':directory', $this->directory);
        $stmt->bindValue(':picture', $this->picture);
    
        if ($stmt->execute()) {
            $_SESSION['lastImageProfileUser'] = $this->directory;
            return true;
        } else {
            return false;
        }
      }

      public function getUserPictures($userId) {
        $querySelect = "SELECT cod, path, description FROM " . $this->tableNames['fot'] . " WHERE uidUserFK = :userId ORDER BY dateload DESC LIMIT 3";
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['lastProfilePictures']= json_encode($pictures);
        return $pictures;
    }

    public function updateProfilePicture($userId, $pictureId): bool{
        try {
            // Inicia uma transação
            $this->conn->beginTransaction();

            //1. atualize a tabela
            $queryUpdate = "UPDATE " . $this->tableNames['alb'] . " 
                      SET uPictureFK = :pictureId 
                      WHERE uidUserFK = :userId";
            $updateStmt = $this->conn->prepare($queryUpdate);
            $updateStmt->bindParam(':userId', $userId);
            $updateStmt->bindParam(':pictureId', $pictureId);
            $updateStmt->execute();

            // 2. realize o SELECT
            $querySelect = "SELECT description 
                      FROM " . $this->tableNames['fot'] . " 
                      WHERE cod = :pictureId";
            $selectStmt = $this->conn->prepare($querySelect);
            $selectStmt->bindParam(':pictureId', $pictureId);
            $selectStmt->execute();
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

            // Confirme a transação
            $this->conn->commit();

            if ($row) {
                //Insere o registro no histórico
                $description = 'Alterou para a foto de perfil cod: '.$pictureId.' . ';
                $this->createUserHistory($description, $userId);
                // Atualize o profileUser na sessão
                $userData = json_decode($_SESSION['userData'], true);
                $userData['profileUser'] = $row['description'];
                $_SESSION['userData'] = json_encode($userData);
            }

            return true;
        } catch (PDOException $e) {
            // Reverte a transação em caso de erro
            $this->conn->rollBack();
            echo "Erro: " . $e->getMessage(); // Para depuração
            return false;
        }
    }

    public function insertUserProfilePicture($profilePicture, $directory, $verifyUpload): bool{
        $this->profilePicture = $profilePicture;
        $this->directory = $directory;
        $this->verifyUpload = $verifyUpload;
        $this->id = $_SESSION['id'] ?? null; // TODO: Precisa de atenção sobre SESSIONS, pois será alterado para JSON futuramente.

        if ($this->id === null) {
            error_log("Erro: ID do usuário não encontrado na sessão ao tentar inserir a foto de perfil.");
            return false;
        }

        try {
            $this->conn->beginTransaction();


            $queryInsertPic = "INSERT INTO {$this->tableNames['fot']} (path, description, uidUserFK)
                                VALUES (:directory, :profilePicture, :id)";
            $stmtInsertPic = $this->conn->prepare($queryInsertPic);
            $stmtInsertPic->bindValue(':directory', $this->directory);
            $stmtInsertPic->bindValue(':profilePicture', $this->profilePicture);
            $stmtInsertPic->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmtInsertPic->execute();
            $newPictureId = $this->conn->lastInsertId();
            $queryProfilePic = "INSERT INTO {$this->tableNames['alb']} (uidUserFK, uPictureFK)
                                VALUES (:id, :newPictureId)
                                ON DUPLICATE KEY UPDATE
                                uPictureFK = VALUES(uPictureFK)";
            $stmtProfilePic = $this->conn->prepare($queryProfilePic);
            $stmtProfilePic->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmtProfilePic->bindValue(':newPictureId', $newPictureId, PDO::PARAM_INT);
            $stmtProfilePic->execute();

            $this->conn->commit();
            if($stmtProfilePic->execute()){
                $_SESSION['lastImageProfileUser'] = $this->directory;
                //Inserir registro no histórico
                $description = 'Inseriu a imagem: '.$this->profilePicture.' ao sistema. ';
                $this->createUserHistory($description, $this->id);
            }

            return true;

        } catch (PDOException $e) {
            // Rollback em caso de erro
            $this->conn->rollBack();
            error_log("Erro ao inserir foto de perfil: " . $e->getMessage() . " (Código: " . $e->getCode() . ")");
            return false;
        }
    }

    //TODO: Verificar possibilidades de fazer o theme chegar ao banco de dados via menu. Mas, sem ser via AJAX. Precisa ser na padronização atual, e/ou via javascript.
    public function getIdByToken(string $userToken): ?int
    {
        error_log("Model/User.php - getIdByToken: userToken recebido: " . $userToken);
        try {
            $query = "SELECT id_usuario FROM {$this->tableNames['tok']} WHERE token = :userToken";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userToken', $userToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['id_usuario'])) {
                return $result['id_usuario'];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log("Model/User.php - getIdByToken: Erro PDO - " . $e->getMessage());
            return null;
        }
    }

    public function getUserIdByTokenForSync(string $userToken, int $gracePeriodHours = 24): ?int
    {
        try {
            $query = "SELECT id_usuario, data_expiracao FROM {$this->tableNames['tok']} WHERE token = :userToken";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userToken', $userToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $expiryDate = new DateTime($result['data_expiracao']);
                $now = new DateTime();

                // Calcula o fim do período de carência (data de expiração + X horas)
                $gracePeriodEndDate = (clone $expiryDate)->add(new DateInterval("PT{$gracePeriodHours}H"));

                // O token é válido se:
                // 1. A data atual for ANTES da data de expiração, OU
                // 2. A data atual for ANTES do FIM do período de carência.
                if ($now <= $expiryDate || $now <= $gracePeriodEndDate) {
                    return (int)$result['id_usuario'];
                }
            }
            // Se o token não existe ou está além do período de carência, retorna nulo.
            return null;

        } catch (Exception $e) {
            error_log("Model/User.php - getUserIdByTokenForSync: Erro - " . $e->getMessage());
            return null;
        }
    }

    public function updateTheme(int $userId, int $theme): bool
    {
        error_log("Model/User.php - updateTheme: userId recebido: " . $userId . ", theme recebido: " . $theme);
        try {
            $query = "UPDATE {$this->tableNames['usr']} SET udefaultTheme = :theme WHERE uid = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':theme', $theme, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $result = $stmt->execute();
            error_log("Model/User.php - updateTheme: Resultado da execução: " . ($result ? 'true' : 'false'));
            if (!$result) {
                error_log("Model/User.php - updateTheme: Erro SQL - " . print_r($stmt->errorInfo(), true));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Model/User.php - updateTheme: Erro PDO - " . $e->getMessage());
            return false;
        }
    }
    public function getUserById(int $userId): ?array
    {
        // Query que une as tabelas para pegar todos os dados necessários
        $query = "SELECT u.id_usuario, u.nome_completo, u.nome_usuario, u.nivel_acesso, u.email, u.tema_padrao,
                     f.nome_foto,
                     t.token AS userToken, t.data_criacao AS tokenDate, t.data_expiracao
              FROM {$this->tableNames['usr']} u
              LEFT JOIN {$this->tableNames['fot']} f ON u.id_usuario = f.id_usuario AND f.perfil = 1
              LEFT JOIN {$this->tableNames['tok']} t ON u.id_usuario = t.id_usuario
              LEFT JOIN {$this->tableNames['alb']} a ON f.album_id = a.album_id AND a.tipo_album = 1
              WHERE u.id_usuario = :userId";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Formata os dados para o padrão que o JavaScript espera
                return [
                    'userId' => (int)$row['id_usuario'],
                    'name' => $row['nome_completo'],
                    'email' => $row['email'],
                    'rank' => (int)$row['nivel_acesso'],
                    'nickname' => $row['nome_usuario'],
                    'theme' => (int)$row['tema_padrao'],
                    'profileUser' => $row['nome_foto'],
                    'userToken' => $row['userToken'],
                    'tokenDate' => $row['tokenDate'],
                    'tokenExpiry' => $row['data_expiracao']
                ];
            }
            return null;

        } catch (PDOException $e) {
            error_log("Erro em User->getUserById: " . $e->getMessage());
            return null;
        }
    }

    public function deleteTokenForUser(int $userId): bool
    {
        try {
            $query = "DELETE FROM {$this->tableNames['tok']} WHERE id_usuario = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar token: " . $e->getMessage());
            return false;
        }
    }
}
?>