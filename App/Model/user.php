<?php
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

    public function createUser()
    {
        if (!empty($this->name && $this->email && $this->password && $this->rank && $this->nickname)) {
            $userToken = bin2hex(random_bytes(32));
            $this->profilePicture = 'Profile.png';
            $this->directory = '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png';

            // Iniciar transação para garantir consistência
            $this->conn->beginTransaction();

            try {
                // Inserir usuário
                $query1 = "INSERT INTO " . $this->tableNames['ud'] . " 
                SET uname=:name, username=:nickname, uemail=:email, upassword=:password, urank=:rank";

                $stmt1 = $this->conn->prepare($query1);
                $stmt1->bindParam(':name', $this->name);
                $stmt1->bindParam(':nickname', $this->nickname);
                $stmt1->bindParam(':email', $this->email);
                $stmt1->bindParam(':password', $this->password);
                $stmt1->bindParam(':rank', $this->rank);
                $stmt1->execute();

                // Obter o ID do novo usuário
                $newUserId = $this->conn->lastInsertId();

                // Inserir imagem
                $query2 = "INSERT INTO " . $this->tableNames['pic'] . "(path, description, uidUserFK) 
                VALUES (:directory, :profilePicture, :newUserId)";

                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(':directory', $this->directory);
                $stmt2->bindParam(':profilePicture', $this->profilePicture);
                $stmt2->bindParam(':newUserId', $newUserId);
                $stmt2->execute();

                // Obter o ID da imagem
                $newPictureId = $this->conn->lastInsertId();

                // Inserir relação usuário-imagem
                $query3 = "INSERT INTO " . $this->tableNames['pps'] . "(uidUserFK, uimageFK) 
                VALUES (:newUserId, :newPictureId)";

                $stmt3 = $this->conn->prepare($query3);
                $stmt3->bindParam(':newUserId', $newUserId);
                $stmt3->bindParam(':newPictureId', $newPictureId);
                $stmt3->execute();

                // Verificar e criar o token do usuário
                $triggerExists = $this->checkTriggerExists('tr_insert_token');
                if (!$triggerExists) {
                    $triggerQuery = "CREATE TRIGGER tr_insert_token
                       AFTER INSERT ON " . $this->tableNames['ud'] . "
                       FOR EACH ROW
                       BEGIN
                         INSERT INTO " . $this->tableNames['ut'] . " (token, uidUserFK)
                         VALUES (UNHEX(?), NEW.uid);
                       END";

                    $stmt = $this->conn->prepare($triggerQuery);
                    $stmt->execute([str_replace('0x', '', bin2hex($userToken))]);
                }

                // Inserir token diretamente
                $query = "INSERT INTO " . $this->tableNames['ut'] . " (token, uidUserFK) 
                VALUES (:userToken, :newUserId)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':userToken', $userToken);
                $stmt->bindParam(':newUserId', $newUserId);
                $stmt->execute();

                // Confirmar todas as operações
                $this->conn->commit();
                return true;
            } catch (Exception $e) {
                $this->conn->rollBack();
                return false;
            }
        }
        return false;
    }

    // Função para checagem de gatilho
    private function checkTriggerExists($triggerName)
    {
        $query = "SELECT COUNT(*) AS trigger_exists
              FROM information_schema.triggers
              WHERE trigger_name = :triggerName;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':triggerName', $triggerName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['trigger_exists'] === 1;
    }

    public function authenticateUser()
    {
        if (!empty($this->nickname) && !empty($this->password)) {
            $userToken = bin2hex(random_bytes(32));
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
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


    public function __destruct() {
        $this->conn->close();
    }
}


?>