<?php
if (!defined('APP_RAN')) {
  die('Acesso não permitido');
}

class User {
  private $conn;
  private $tableNames = [
    'ud' => 'userdata',
    'pps' => 'profilepictures',
    'pic' => 'pictures',
    'hs' => 'history',
    'ut' => 'usertoken',
    'pc'=> 'pointControl'
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

  public function __construct($db) {
    $this->conn = $db;
  }

  public function createUser() {
    if (!empty($this->name && $this->email && $this->password && $this->rank && $this->nickname)) {
        $userToken = bin2hex(random_bytes(32));
        $this->profilePicture = 'Profile.png';
        $this->directory = '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png';

        $query1 = "INSERT INTO " . $this->tableNames['ud'] . " 
            SET uname=:name, username=:nickname, uemail=:email, upassword=:password, urank=:rank;
            SET @newUserId = LAST_INSERT_ID();";

        $stmt1 = $this->conn->prepare($query1);
        $stmt1->bindParam(':name', $this->name);
        $stmt1->bindParam(':nickname', $this->nickname);
        $stmt1->bindParam(':email', $this->email);
        $stmt1->bindParam(':password', $this->password);
        $stmt1->bindParam(':rank', $this->rank);
        $stmt1->execute();
        $stmt1->closeCursor();

        $query2 = "INSERT INTO " . $this->tableNames['pic'] . "(path, description, uidUserFK) 
                VALUES (:directory, :profilePicture, @newUserId);
                SET @newPictureId = LAST_INSERT_ID();";

        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(':directory', $this->directory);
        $stmt2->bindParam(':profilePicture', $this->profilePicture);
        $stmt2->execute();
        $stmt2->closeCursor();

        $query3 = "INSERT INTO " . $this->tableNames['pps'] . "(uidUserFK, uimageFK) 
                VALUES (@newUserId, @newPictureId)";

        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $stmt3->closeCursor();


      if ($stmt1->execute()) {   
        $triggerExists = $this->checkTriggerExists('tr_insert_token');
        if (!$triggerExists) {
          $triggerQuery = "CREATE TRIGGER tr_insert_token
                           AFTER INSERT ON " . $this->tableNames['ud'] . "
                           FOR EACH ROW
                           BEGIN
                             INSERT INTO " . $this->tableNames['ut'] . " (token, uidUserFK)
                             VALUES (:userToken, @newUserId);
                           END;";

          $stmt = $this->conn->prepare($triggerQuery);
          $stmt->bindParam(':userToken', $userToken);
          $stmt->execute();
        }
        //$query= "INSERT INTO " . $this->tableNames['profilepictures'] . " SET uimage = :image, uidUserFK = :id;"; precisa estudar a implementação dessa query para criação de linha de imagem. para que o login funcione corretamente.
        $query = "INSERT INTO " . $this->tableNames['ut'] . " (token, uidUserFK) 
                  VALUES (:userToken, @newUserId);";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userToken', $userToken);
        $stmt->execute();

        return true;
      }
    }
    return false;
  }

  // Função para checagem de gatilho
  private function checkTriggerExists($triggerName) {
    $query = "SELECT COUNT(*) AS trigger_exists
              FROM information_schema.triggers
              WHERE trigger_name = :triggerName;";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':triggerName', $triggerName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['trigger_exists'] === 1;
  }
    public function authenticateUser() {
        if (!empty($this->nickname) && !empty($this->password)) {
            $userToken = bin2hex(random_bytes(32));
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $query = "SELECT u.uid, t.token	, u.uname, u.username, u.urank, u.uemail, u.upassword, u.username, d.description, p.dateload, u.udefaultTheme 
            FROM " . $this->tableNames['ud'] . " u 
            INNER JOIN ". $this->tableNames['pic'] ." d ON u.uid = d.uidUserFK 
            INNER JOIN ".$this->tableNames['ut']." t ON u.uid = t.uidUserFK 
            inner join ".$this->tableNames['pps']." p ON p.uimageFK = d.cod
            WHERE u.username = :nickname AND u.upassword = :password;";
            
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->bindParam(':password', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $query2 ="START TRANSACTION;
                        UPDATE ".$this->tableNames['ut']."
                        SET token = :userToken
                        WHERE uidUserFK = :id;

                        IF ROW_COUNT() = 0 THEN
                            INSERT INTO ".$this->tableNames['ut']." (token, uidUserFK)
                            VALUES (:userToken, :id);
                        END IF;
                    COMMIT;
                    
                    INSERT INTO ". $this->tableNames['history'] . " SET description = :descricao, uidUserFK = :id";
                    try{
                        $this->descricao = 'login a partir do ip:'.$ip.' E criação do Hash para autenticação temporário: '.$userToken;
                        $this->userToken = $userToken;
                        $this->id = $row['uid'];
                        $stmt = $this->conn->prepare($query2);
                        $stmt->bindValue(':id', $this->id);
                        $stmt->bindValue(':userToken', $this->userToken);
                        $stmt->bindValue(':descricao', $this->descricao);
                        $stmt->execute();
                    }catch(PDOException $e){
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
    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme) {
        $this->name = $name;
        $this->id = $id;
        $this->email = $email;
        $this->nickname = $nickname;
        $this->oldPassword = $oldPassword;
        $this->newPassword = $newPassword;
        $this->confirmPassword = $confirmPassword;
        $this->defaultTheme = $defaultTheme;
        $updateFields = [];
        $params = [];
        $queries = [];
        if (isset($this->name)) {
            $updateFields['userdata'][] = "uname = :name";
            $params['userdata'][':name'] = $this->name;
        }
        if (isset($this->email)) {
            $updateFields['userdata'][] = "uemail = :email";
            $params['userdata'][':email'] = $this->email;
        }
        if (isset($this->nickname) && $this->nickname != $_SESSION['nickname']) {
            $updateFields['userdata'][] = "username = :nickname";
            $params['userdata'][':nickname'] = $this->nickname;
        }
        if (isset($this->defaultTheme) && $this->defaultTheme != $_SESSION['defaultTheme']) {
            $updateFields['userdata'][] = "udefaultTheme = :defaultTheme";
            $params['userdata'][':defaultTheme'] = $this->defaultTheme;
        }
        foreach ($updateFields as $table => $fields) {
            $column = ($table == 'userdata') ? 'uid' : 'uidUserFK';
            $query = "UPDATE " . $table . " SET " . implode(", ", $fields) . " WHERE " . $column . " = :id";
            $params[$table][':id'] = $this->id;
            $queries[] = ['query' => $query, 'params' => $params[$table]];
            foreach ($fields as $field) {
                $fieldName = explode(' ', $field)[0];
                $updatedFields[$fieldName] = true;
            }
        }
        if (!empty($this->oldPassword) && !empty($this->newPassword) && !empty($this->confirmPassword) && $this->newPassword === $this->confirmPassword) {
            $query = "UPDATE" . $this->tableNames['ud'] . "SET upassword = :newPassword WHERE uid = :id";
            $paramsPassword = [
                ':newPassword' => $this->newPassword,
                ':id' => $this->id
            ];
            $queries[] = ['query' => $query, 'params' => $paramsPassword];
            $updatedFields['upassword'] = true;
        }
        foreach ($queries as $q) {
            try {
                $stmt = $this->conn->prepare($q['query']);
                foreach ($q['params'] as $param => $value) {
                    $stmt->bindValue($param, $value);
                }
                $stmt->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
        //if($id == $_SESSION['id']){
            if (isset($updatedFields['uname'])) User::updateSessionUserData('name', $this->name);
            if (isset($updatedFields['uemail'])) User::updateSessionUserData('email', $this->email);
            if (isset($updatedFields['username'])) User::updateSessionUserData('nickname', $this->nickname);
            if (isset($updatedFields['udefaultTheme'])) User::updateSessionUserData('defaultTheme', $this->defaultTheme);
       // }
        
        return true;
    }
    //Ainda assim não está funcionando a parte 
    public static function updateSessionUserData($field, $value) {
        $_SESSION['userData'][$field] = $value;
    }
    
 // a fazer.
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