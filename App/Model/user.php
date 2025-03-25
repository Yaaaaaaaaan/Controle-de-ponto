<?php

use Random\RandomException;

if (!defined('APP_RAN')) {
  die('Acesso não permitido');
}

class User
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
        $userToken = bin2hex(random_bytes(32));
        $this->profilePicture = 'Profile.png';
        $this->directory = '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png';

        // Iniciar transação para garantir consistência dos dados
        $this->conn->beginTransaction();

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

    /**
     * Método destrutor para limpar a conexão com o banco de dados
     */
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
            $query = "SELECT u.uid, t.token	, u.uname, u.username, u.urank, u.uemail, u.upassword, u.username, d.description, p.dateload, u.udefaultTheme 
            FROM " . $this->tableNames['ud'] . " u 
            INNER JOIN " . $this->tableNames['pic'] . " d ON u.uid = d.uidUserFK 
            INNER JOIN " . $this->tableNames['ut'] . " t ON u.uid = t.uidUserFK 
            inner join " . $this->tableNames['pps'] . " p ON p.uimageFK = d.cod
            WHERE u.username = :nickname AND u.upassword = :password;";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->bindParam(':password', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $query2 = "START TRANSACTION;
                        UPDATE " . $this->tableNames['ut'] . "
                        SET token = :userToken
                        WHERE uidUserFK = :id;

                        IF ROW_COUNT() = 0 THEN
                            INSERT INTO " . $this->tableNames['ut'] . " (token, uidUserFK)
                            VALUES (:userToken, :id);
                        END IF;
                    COMMIT;
                    
                    INSERT INTO " . $this->tableNames['history'] . " SET description = :descricao, uidUserFK = :id";

                    try {
                        $this->descricao = 'login a partir do ip:' . $ip . ' E criação do Hash para autenticação temporário: ' . $userToken;
                        $this->userToken = $userToken;
                        $this->id = $row['uid'];
                        $stmt = $this->conn->prepare($query2);
                        $stmt->bindValue(':id', $this->id);
                        $stmt->bindValue(':userToken', $this->userToken);
                        $stmt->bindValue(':descricao', $this->descricao);
                        $stmt->execute();
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
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
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
        return false;
    }


    public function updateUser(): bool
    {
        // Verificar os campos obrigatórios
        if (empty($this->name) || empty($this->email) || empty($this->nickname)) {
            return false;
        }

        // Iniciar a consulta de atualização
        $query = "UPDATE " . $this->tableNames['ud'] . " 
          SET uname = :name, 
              uemail = :email,
              username = :nickname,
              udefaultTheme = :defaultTheme ";  // Corrigido para udefaultTheme com parâmetro

        // Adicionar alteração de senha à consulta, se aplicável
        $passwordUpdated = false;
        if (!empty($this->newPassword) && !empty($this->confirmPassword) && !empty($this->oldPassword)) {
            // Verificar se a senha atual está correta antes de permitir a alteração
            $checkPasswordQuery = "SELECT upassword FROM " . $this->tableNames['ud'] . " WHERE uid = :id";
            $checkStmt = $this->conn->prepare($checkPasswordQuery);
            $checkStmt->bindParam(':id', $this->id);
            $checkStmt->execute();
            $currentPassword = $checkStmt->fetchColumn();

            if ($currentPassword == $this->oldPassword && $this->newPassword == $this->confirmPassword) {
                $query .= ", upassword = :newPassword";
                $passwordUpdated = true;
            } else {
                return false; // Senha atual incorreta ou as novas senhas não coincidem
            }
        }

        // Finalizar a consulta com a condição WHERE
        $query .= " WHERE uid = :id";

        try {
            // Preparar e executar a consulta
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':nickname', $this->nickname);
            $stmt->bindParam(':defaultTheme', $this->defaultTheme);
            $stmt->bindParam(':id', $this->id);

            if ($passwordUpdated) {
                $stmt->bindParam(':newPassword', $this->newPassword);
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
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Opcional: registre o erro em algum lugar
            // error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    //TODO: a fazer FUNCIONALIDADE DELETEACCOUNT.
    public function deleteAccount() {
        if (!empty($this->email) && !empty($this->password)) {
            $query = "SELECT uname, urank, email, upassword FROM " . $this->tableNames[0] . " 
            WHERE email = :email AND upassword = :upassword";
    
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $this->email);
                $stmt->bindParam(':upassword', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
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
        
        $query = "SELECT u.uname, u.username, h.description, h.dateIn FROM " . $this->tableNames['ud'] . " u inner join ".$this->tableNames['hs']." h ON u.uid = h.uidUserFK WHERE u.uid = :id ORDER BY h.cod desc LIMIT " . $registro . ";";
        
        try {
          $stmt = $this->conn->prepare($query);
          $stmt->bindParam(':id', $userId);
          //$stmt->bindParam('', $this->$registro);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          return $result;
        } catch(PDOException $e) {
          echo "Error: " . $e->getMessage();
          return false;
        }
      }

      
      /*public function setPictures($picture, $directory){ //insere imagens no banco de dados.
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
      }*/

      public function getUserPictures($userId) {
        $sql = "SELECT cod, path, description FROM " . $this->tableNames['pic'] . " WHERE uidUserFK = :userId ORDER BY dateload DESC LIMIT 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfilePicture($userId, $pictureId) {
        try {
            // Inicie uma transação
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



    /*public function insertUserProfilePicture($profilePicture, $directory) {
      $this->profilePicture = $profilePicture;
      $this->directory = $directory;
      $this->id = $_SESSION['id'];

      $sql = "UPDATE " . $this->tableNames['pps'] . " SET uimage = :profilePicture WHERE uidUserFK = :id";
      $stmt = $this->conn->prepare($sql);
      $stmt->bindValue(':id', $this->id);
      $stmt->bindValue(':profilePicture', $this->directory);

      if ($stmt->execute()) {
          $_SESSION['lastImageProfileUser'] = $this->directory;
          return true;
      } else {
          return false;
      }
  }*/
     public function insertPointControl($id, $descricao) {
        $this->descricao = $descricao;
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

}


?>