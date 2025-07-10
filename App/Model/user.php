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
        // Verificar se todos os dados necessários foram fornecidos
        if (empty($this->name) || empty($this->email) || empty($this->password) ||
            empty($this->rank) || empty($this->nickname)) {
            return false;
        }

        // Define valor padrão para a imagem de perfil
        $this->profilePicture = 'Profile.png';
        $this->directory = '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png';

        // Define valore padrão para o álbum
        $this->albumName = 'Foto de perfil';
        $this->albumType = 'Foto de perfil';

        // Cria um hash para futuras transações
        $userToken = bin2hex(random_bytes(32));

        // Iniciar transação para garantir consistência dos dados
        $this->conn->beginTransaction();

        // Criptografa a senha
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        try {
            // 1. Inserir dados do usuário na tabela userdata
            $queryUser = "INSERT INTO {$this->tableNames['usr']} 
                  (nome_completo, nome_usuario, email, senha_hash, nivel_acesso) 
                  VALUES (:name, :nickname, :email, :password, :rank)";

            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':name', $this->name);
            $stmtUser->bindParam(':nickname', $this->nickname);
            $stmtUser->bindParam(':email', $this->email);
            $stmtUser->bindParam(':password', $this->password);
            $stmtUser->bindParam(':rank', $this->rank);
            $stmtUser->execute();

            // Obter o ID do usuário recém-inserido
            $newUserId = $this->conn->lastInsertId();

            // 2. Inserir dados da imagem na tabela albuns
            $queryPicture = "INSERT INTO {$this->tableNames['alb']}
                     (id_usuario, nome_album, tipo_album) 
                     VALUES (:newUserId, :albumName, :albumType)";

            $stmtPicture = $this->conn->prepare($queryPicture);
            $stmtPicture->bindParam(':newUserId', $newUserId);
            $stmtPicture->bindParam(':albumName', $this->albumName);
            $stmtPicture->bindParam(':albumType', $this->albumType);
            $stmtPicture->execute();

            // Obter o ID da imagem recém-inserida
            $newAlbumId = $this->conn->lastInsertId();

            // 3. Inserir dados da tabela imagem
            $queryProfilePic = "INSERT INTO {$this->tableNames['fot']}
                        (album_id, id_usuario, caminho_arquivo, nome_foto) 
                        VALUES (:newAlbumId, :newUserId, :directory, :profilePicture)";

            $stmtProfilePic = $this->conn->prepare($queryProfilePic);
            $stmtProfilePic->bindParam(':newAlbumId', $newAlbumId);
            $stmtProfilePic->bindParam(':newUserId', $newUserId);
            $stmtProfilePic->bindParam(':directory', $this->directory);
            $stmtProfilePic->bindParam(':profilePicture', $this->profilePicture);
            $stmtProfilePic->execute();

            // 4. Verificar se o trigger para a criação automática de tokens já existe
            $triggerExists = $this->checkTriggerExists('tr_insert_token');

            // Se o trigger não existir, criar um novo
            if (!$triggerExists) {
                $triggerQuery = "CREATE TRIGGER tr_insert_token
                       AFTER INSERT ON {$this->tableNames['usr']}
                       FOR EACH ROW
                       BEGIN
                         INSERT INTO {$this->tableNames['tok']} (token, id_usuario)
                         VALUES (UNHEX(?), NEW.uid);
                       END";

                $stmtTrigger = $this->conn->prepare($triggerQuery);
                $stmtTrigger->execute([str_replace('0x', '', bin2hex($userToken))]);
            }

            // 5. Inserir token do usuário na tabela usertoken
            $queryToken = "INSERT INTO {$this->tableNames['tok']} 
                   (token, id_usuario) 
                   VALUES (:userToken, :newUserId)";

            $stmtToken = $this->conn->prepare($queryToken);
            $stmtToken->bindParam(':userToken', $userToken);
            $stmtToken->bindParam(':newUserId', $newUserId);
            $stmtToken->execute();

            // Confirmar todas as operações
            $this->conn->commit();

            //6. Inserir registro no histórico TODO:Fazer funcionar aqui.
            $description = 'Criação de conta ';
            $this->createUserHistory($description, $newUserId);
            return true;

        } catch (Exception $e) {
            // Em caso de erro, reverter todas as alterações
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Verifica se um trigger específico já existe no banco de dados
     *
     * @param string $triggerName Nome do trigger a ser verificado
     * @return bool Retorna true se o trigger existir, false caso contrário
     */
    private function checkTriggerExists($triggerName): bool{
        $query = "SELECT COUNT(*) AS trigger_exists
          FROM information_schema.triggers
          WHERE trigger_name = :triggerName";

        $stmt = $this->conn->prepare($query);
        // Utiliza bindValue em vez de bindParam para valor literal
        $stmt->bindValue(':triggerName', $triggerName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['trigger_exists'] === 1;
    }

    public function __destruct(){
        // Forma correta de fechar uma conexão PDO
        if ($this->conn) {
            $this->conn = null;
        }
    }

    /**
     * @throws RandomException
     */
    public function authenticateUser(): bool{
        if (!empty($this->nickname) && !empty($this->password)) {
            $userToken = bin2hex(random_bytes(32));

            $query = "SELECT u.uid, t.token, u.uname, u.username, u.urank, u.uemail, u.upassword, d.namePic, p.dateload, u.udefaultTheme
                  FROM {$this->tableNames['usr']} u
                  INNER JOIN {$this->tableNames['fot']} d ON u.uid = d.uidUserFK
                  INNER JOIN {$this->tableNames['tok']} t ON u.uid = t.uidUserFK
                  INNER JOIN {$this->tableNames['alb']} p ON p.uPictureFK = d.cod
                  WHERE u.username = :nickname";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($this->password, $row['upassword'])) {
                        try {
                            $this->conn->beginTransaction();

                            // Atualiza o token
                            $update = $this->conn->prepare("UPDATE {$this->tableNames['tok']} SET token = :userToken WHERE uidUserFK = :id");
                            $update->bindValue(':userToken', $userToken);
                            $update->bindValue(':id', $row['uid']);
                            $update->execute();

                            // Se não atualizou nada, insere
                            if ($update->rowCount() === 0) {
                                $insert = $this->conn->prepare("INSERT INTO {$this->tableNames['tok']} (token, uidUserFK) VALUES (:userToken, :id)");
                                $insert->bindValue(':userToken', $userToken);
                                $insert->bindValue(':id', $row['uid']);
                                $insert->execute();
                            }

                            // Registro no histórico
                            $description = 'login e criação de hash em ';
                            $this->createUserHistory($description, $row['uid']);

                            $this->conn->commit();
                        } catch (PDOException $e) {
                            $this->conn->rollBack();
                            echo "Erro ao registrar o login: " . $e->getMessage();
                            return false;
                        }

                        // Sessão
                        $_SESSION['id'] = $row['uid'];
                        $_SESSION['logged'] = true;
                        $_SESSION['userData'] = json_encode([
                            'userToken' => $userToken,
                            'name' => $row['uname'],
                            'email' => $row['uemail'],
                            'rank' => $row['urank'],
                            'nickname' => $row['username'],
                            'theme' => $row['udefaultTheme'],
                            'id' => $row['uid'],
                            'profileUser' => $row['namePic'],
                        ]);
                        $_SESSION['pointControl'] = json_encode([
                            'name' => $row['uname'],
                            'status' => $row['status'],
                            'dateIn' => $row['dateIn'],
                        ]);
                        return true;
                    }
                }
            } catch (PDOException $e) {
                echo "Erro na autenticação: " . $e->getMessage();
            }
        }

        return false;
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
            $checkPasswordQuery = "SELECT upassword FROM " . $this->tableNames['usr'] . " WHERE uid = :id";
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
        $query .= " WHERE uid = :id";

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
            $query = "SELECT uid, uemail, upassword FROM " . $this->tableNames['usr'] . " 
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
            " h ON u.uid = h.uidUserFK WHERE u.uid = :id ORDER BY h.cod desc LIMIT " . $registro . ";";

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

     public function insertPointControl($id): bool{
        try {
            $this->descricao = 'Verificação pendente';
            $this->id = $id;
            $query = "INSERT INTO pointControl (description, uidUserFK) VALUES (:description, :id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':description', $this->descricao);
            $stmt->bindParam(':id', $this->id);
            if($stmt->execute()){
                //Inserir registro no histórico
                $description = 'Inserção de presença no controle-de-ponto. ';
                $this->createUserHistory($description, $id);
            }
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                return false; 
            } else {
                throw $e; 
            }
        }   
    }
    //TODO: Verificar possibilidades de fazer o theme chegar ao banco de dados via menu. Mas, sem ser via AJAX. Precisa ser na padronização atual, e/ou via javascript.
    public function getIdByToken(string $userToken): ?int
    {
        error_log("Model/User.php - getIdByToken: userToken recebido: " . $userToken);
        try {
            $query = "SELECT uid FROM {$this->tableNames['tok']} WHERE token = :userToken";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userToken', $userToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['uid'])) {
                return $result['uid'];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log("Model/User.php - getIdByToken: Erro PDO - " . $e->getMessage());
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