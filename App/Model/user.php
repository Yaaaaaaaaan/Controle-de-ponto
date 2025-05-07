<?php

use Random\RandomException;

if (!defined('APP_RAN')) {
  die('Acesso não permitido');
}

#[AllowDynamicProperties] class User
{
    private $conn;
    private $tableNames = [
        'ud' => 'userdata',
        'pps' => 'profilepictures',
        'pic' => 'pictures',
        'hs' => 'history',
        'ut' => 'usertoken',
        'pc' => 'pointControl'
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

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createUser(): bool
    {
        // Verificar se todos os dados necessários foram fornecidos
        if (empty($this->name) || empty($this->email) || empty($this->password) ||
            empty($this->rank) || empty($this->nickname)) {
            return false;
        }

        // Definir valores padrão para a imagem de perfil
        $this->profilePicture = 'Profile.png';
        $this->directory = '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png';

        // Cria um hash para futuras transações
        $userToken = bin2hex(random_bytes(32));

        // Iniciar transação para garantir consistência dos dados
        $this->conn->beginTransaction();

        // Criptografa a senha
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        try {
            // 1. Inserir dados do usuário na tabela userdata
            $queryUser = "INSERT INTO {$this->tableNames['ud']} 
                  (uname, username, uemail, upassword, urank) 
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

            // 2. Inserir dados da imagem na tabela pictures
            $queryPicture = "INSERT INTO {$this->tableNames['pic']}
                     (path, description, uidUserFK) 
                     VALUES (:directory, :profilePicture, :newUserId)";

            $stmtPicture = $this->conn->prepare($queryPicture);
            $stmtPicture->bindParam(':directory', $this->directory);
            $stmtPicture->bindParam(':profilePicture', $this->profilePicture);
            $stmtPicture->bindParam(':newUserId', $newUserId);
            $stmtPicture->execute();

            // Obter o ID da imagem recém-inserida
            $newPictureId = $this->conn->lastInsertId();

            // 3. Inserir relação entre usuário e imagem de perfil na tabela profilepictures
            $queryProfilePic = "INSERT INTO {$this->tableNames['pps']}
                        (uidUserFK, uimageFK) 
                        VALUES (:newUserId, :newPictureId)";

            $stmtProfilePic = $this->conn->prepare($queryProfilePic);
            $stmtProfilePic->bindParam(':newUserId', $newUserId);
            $stmtProfilePic->bindParam(':newPictureId', $newPictureId);
            $stmtProfilePic->execute();

            // 4. Verificar se o trigger para a criação automática de tokens já existe
            $triggerExists = $this->checkTriggerExists('tr_insert_token');

            // Se o trigger não existir, criar um novo
            if (!$triggerExists) {
                $triggerQuery = "CREATE TRIGGER tr_insert_token
                       AFTER INSERT ON {$this->tableNames['ud']}
                       FOR EACH ROW
                       BEGIN
                         INSERT INTO {$this->tableNames['ut']} (token, uidUserFK)
                         VALUES (UNHEX(?), NEW.uid);
                       END";

                $stmtTrigger = $this->conn->prepare($triggerQuery);
                $stmtTrigger->execute([str_replace('0x', '', bin2hex($userToken))]);
            }

            // 5. Inserir token do usuário na tabela usertoken
            $queryToken = "INSERT INTO {$this->tableNames['ut']} 
                   (token, uidUserFK) 
                   VALUES (:userToken, :newUserId)";

            $stmtToken = $this->conn->prepare($queryToken);
            $stmtToken->bindParam(':userToken', $userToken);
            $stmtToken->bindParam(':newUserId', $newUserId);
            $stmtToken->execute();

            //6. Inserir registro no histórico
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $descricao = 'Criação de conta partir do ip: ' . $ip;
            $queryHistory = ("INSERT INTO {$this->tableNames['hs']} (description, uidUserFK) VALUES (:descricao, :id)");
            $stmtHistory = $this->conn->prepare($queryHistory);
            $stmtHistory->bindValue(':descricao', $descricao);
            $stmtHistory->bindParam(':id', $newUserId);
            $stmtHistory->execute();

            // Confirmar todas as operações
            $this->conn->commit();
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
    private function checkTriggerExists($triggerName): bool
    {
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

    public function __destruct()
    {
        // Forma correta de fechar uma conexão PDO
        if ($this->conn) {
            $this->conn = null;
        }
    }

    /**
     * @throws RandomException
     */
    public function authenticateUser(): bool
    {
        if (!empty($this->nickname) && !empty($this->password)) {
            $userToken = bin2hex(random_bytes(32));
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

            $query = "SELECT u.uid, t.token, u.uname, u.username, u.urank, u.uemail, u.upassword, d.description, p.dateload, u.udefaultTheme
                  FROM {$this->tableNames['ud']} u
                  INNER JOIN {$this->tableNames['pic']} d ON u.uid = d.uidUserFK
                  INNER JOIN {$this->tableNames['ut']} t ON u.uid = t.uidUserFK
                  INNER JOIN {$this->tableNames['pps']} p ON p.uimageFK = d.cod
                  WHERE u.username = :nickname";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Debug temporário (remova em produção)
                    echo "<pre>";
                    echo "Digitada: " . $this->password . "\n";
                    echo "No banco: " . $row['upassword'] . "\n";
                    var_dump(password_verify($this->password, $row['upassword']));
                    echo "</pre>";

                    if (password_verify($this->password, $row['upassword'])) {
                        try {
                            $this->conn->beginTransaction();

                            // Atualiza o token
                            $update = $this->conn->prepare("UPDATE {$this->tableNames['ut']} SET token = :userToken WHERE uidUserFK = :id");
                            $update->bindValue(':userToken', $userToken);
                            $update->bindValue(':id', $row['uid']);
                            $update->execute();

                            // Se não atualizou nada, insere
                            if ($update->rowCount() === 0) {
                                $insert = $this->conn->prepare("INSERT INTO {$this->tableNames['ut']} (token, uidUserFK) VALUES (:userToken, :id)");
                                $insert->bindValue(':userToken', $userToken);
                                $insert->bindValue(':id', $row['uid']);
                                $insert->execute();
                            }

                            // Registro no histórico
                            $descricao = 'login a partir do ip: ' . $ip . ' e criação do Hash para autenticação temporário: ' . $userToken;
                            $history = $this->conn->prepare("INSERT INTO {$this->tableNames['hs']} (description, uidUserFK) VALUES (:descricao, :id)");
                            $history->bindValue(':descricao', $descricao);
                            $history->bindValue(':id', $row['uid']);
                            $history->execute();

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
                            'profileUser' => $row['description'],
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
        $query = "UPDATE " . $this->tableNames['ud'] . "
            SET uname = :name,
            uemail = :email,
            username = :nickname,
            udefaultTheme = :defaultTheme ";

        $passwordUpdated = false;
        if (!empty($this->newPassword) && !empty($this->confirmPassword) && !empty($this->oldPassword)) {
            // Verificar se a senha atual está correta antes de permitir a alteração
            $checkPasswordQuery = "SELECT upassword FROM " . $this->tableNames['ud'] . " WHERE uid = :id";
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
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
                    $descricao = 'Alteração de informações partir do ip: ' . $ip;
                    $queryHistory = ("INSERT INTO {$this->tableNames['hs']} (description, uidUserFK) VALUES (:descricao, :id)");
                    $stmtHistory = $this->conn->prepare($queryHistory);
                    $stmtHistory->bindValue(':descricao', $descricao);
                    $stmtHistory->bindParam(':id', $this->id);
                    $stmtHistory->execute();
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
            $query = "SELECT uid, uemail, upassword FROM " . $this->tableNames['ud'] . " 
            WHERE uemail = :email AND upassword = :upassword";
    
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $this->email);
                $stmt->bindParam(':upassword', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    //Inserir registro no histórico
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
                    $descricao = 'Exclusão de conta partir do ip: ' . $ip;
                    $queryHistory = ("INSERT INTO {$this->tableNames['hs']} (description, uidUserFK) VALUES (:descricao, :id)");
                    $stmtHistory = $this->conn->prepare($queryHistory);
                    $stmtHistory->bindValue(':descricao', $descricao);
                    $stmtHistory->bindParam(':id', );
                    $stmtHistory->execute();
                    return true;
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
    
        return false;
    }
    public function getUserHistory($userId, $registro) {
        
        $query = "SELECT u.uname, u.username, h.description, h.dateIn FROM " . $this->tableNames['ud'] .
            " u inner join ".$this->tableNames['hs'].
            " h ON u.uid = h.uidUserFK WHERE u.uid = :id ORDER BY h.cod desc LIMIT " . $registro . ";";
        
        try {
          $stmt = $this->conn->prepare($query);
          $stmt->bindParam(':id', $userId);
          //$stmt->bindParam('', $this->$registro);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if($result){
              //Inserir registro no histórico
              $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
              $descricao = 'Consulta de histórico partir do ip: ' . $ip;
              $queryHistory = ("INSERT INTO {$this->tableNames['hs']} (description, uidUserFK) VALUES (:descricao, :id)");
              $stmtHistory = $this->conn->prepare($queryHistory);
              $stmtHistory->bindValue(':descricao', $descricao);
              $stmtHistory->bindParam(':id', $userId);
              $stmtHistory->execute();
          }
          return $result;
        } catch(PDOException $e) {
          echo "Error: " . $e->getMessage();
          return false;
        }
      }

      
      public function setPictures($picture, $directory): bool
      { //insere imagens no banco de dados.
        $this->picture = $picture;
        $this->directory = $directory;
        $this->id = $_SESSION['id'];
        $sql = "INSERT INTO" . $this->tableNames['pic'] . " SET path=:directory, description=:picture, idUserFK = :id ";
        $stmt = $this->conn->prepare($sql);
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
        $sql = "SELECT cod, path, description FROM " . $this->tableNames['pic'] . " WHERE uidUserFK = :userId ORDER BY dateload DESC LIMIT 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['lastProfilePictures']= json_encode($pictures);
        return $pictures;
    }

    public function updateProfilePicture($userId, $pictureId): bool
    {
        try {
            // Inicia uma transação
            $this->conn->beginTransaction();

            // Primeiro, atualize a tabela
            $updateSql = "UPDATE " . $this->tableNames['pps'] . " 
                      SET uimageFK = :pictureId 
                      WHERE uidUserFK = :userId";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindParam(':userId', $userId);
            $updateStmt->bindParam(':pictureId', $pictureId);
            $updateStmt->execute();

            // Em seguida, realize o SELECT
            $selectSql = "SELECT description 
                      FROM " . $this->tableNames['pic'] . " 
                      WHERE cod = :pictureId";
            $selectStmt = $this->conn->prepare($selectSql);
            $selectStmt->bindParam(':pictureId', $pictureId);
            $selectStmt->execute();
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

            // Confirme a transação
            $this->conn->commit();

            // Atualize o profileUser na sessão
            if ($row) {
                $userData = json_decode($_SESSION['userData'], true);
                $userData['profileUser'] = $row['description'];
                $_SESSION['userData'] = json_encode($userData);

                /* Depuração, se necessário
                echo '<pre>';
                print_r(json_decode($_SESSION['userData'], true));
                echo '</pre>';*/
            }

            return true;
        } catch (PDOException $e) {
            // Reverte a transação em caso de erro
            $this->conn->rollBack();
            echo "Erro: " . $e->getMessage(); // Para depuração
            return false;
        }
    }



    public function insertUserProfilePicture($profilePicture, $directory, $verifyUpload) {
        $this->profilePicture = $profilePicture;
        $this->directory = $directory;
        $this->verifyUpload = $verifyUpload;
        $this->id = $_SESSION['id'];
        $sql = "INSERT INTO pictures (path, description, uidUserFK) 
        VALUES (:directory, :profilePicture, :id);        
        SET @newPictureId = LAST_INSERT_ID();
        INSERT INTO profilepictures (uidUserFK, uimageFK) 
        VALUES (:id, @newPictureId)
        ON DUPLICATE KEY UPDATE
            uimageFK = VALUES(uimageFK);";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':profilePicture', $this->profilePicture);
        $stmt->bindValue(':directory', $this->directory);

        if ($stmt->execute()) {
            $_SESSION['lastImageProfileUser'] = $this->directory;
            return true;
        } else {
            return false;
        }
    }

     public function insertPointControl($id): bool
     {
        $this->descricao = 'Verificação pendente';
        $this->id = $id;

        $query = "INSERT INTO pointControl (description, uidUserFK) VALUES (:description, :id)";
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
    //TODO: Verificar possibilidades de fazer o theme chegar ao banco de dados via menu. Mas, sem ser via AJAX. Precisa ser na padronização atual, e/ou via javascript.
    public function updateTheme($userId, $theme) {
        try {
            $sql = "UPDATE userdata SET udefaultTheme = :theme WHERE uid = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':theme', $theme, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

//TODO: Criar função para um usuário validar a presença de outro usuário, mas, com a condição de; o usuário avaliador deverá estar com a presença confirmada no dia ao qual está sendo feita a validação do outro usuário e, tal ato deverá ocorrer no dia corrido.
    public function validatePresence($userId, $userIdToValidate, $description, $code, $currentDate, $descriptionToValidate) {
        $this->userId = $userId;
        $this->userIdToValidate = $userIdToValidate;
        $this->description = $description;
        $this->code = $code;
        $this->currentDate = $currentDate;
        $this->descriptionToValidate = $descriptionToValidate;

        $query = "SELECT 
        *
        FROM {$this->tableNames['pc']} 
        WHERE uidUserFK = :userId
        AND dateIn = :currentDate
        AND description = :descriptionToValidate
        ";

        return true;
    }
//TODO: Criar função para atualizar dados no housekeeping
    public function updatePresenceHousekeeping($userIdToValidate, $code, $description) {
        $this->userIdToValidate = $userIdToValidate;
        $this->description = $description;
        $this->code = $code;

        $query = "UPDATE {$this->tableNames['pc']} SET description = :description
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