<?php
if (!defined('APP_RAN')) {
    die('Direct access not permitted');
}
class User {
    private $conn;
    private $tableNames = [
        'userdata' => 'userdata',
        'profilepictures' => 'profilepictures',
        'history' => 'history',
        'usertoken' => 'usertoken' 
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

    public $descricao;

    public function __construct($db) {
        $this->conn = $db;
    }
    public function createUser() {
        if(!empty($this->name && $this->email && $this->password && $this->rank && $this->nickname)){
            $query = 'INSERT INTO ' . $this->tableNames['userdata'] . ' SET uname=:name, username=:nickname, uemail=:email, upassword=:password, urank=:rank';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':nickname', $this->nickname);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':rank', $this->rank);
            if ($stmt->execute()) {
                return true;
            }
        }else{
            return false;
        }
    }
    public function authenticateUser() {
        if (!empty($this->nickname) && !empty($this->password)) {
            $userToken = bin2hex(random_bytes(32));
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $query = "SELECT u.uid, t.token	, u.uname, u.username, u.urank, u.uemail, u.upassword, u.username, d.uimage, u.udefaultTheme FROM " . $this->tableNames['userdata'] . " u INNER JOIN ". $this->tableNames['profilepictures'] ." d ON u.uid = d.uidUserFK INNER JOIN ".$this->tableNames['usertoken']." t ON u.uid = t.uidUserFK WHERE u.username = :nickname AND u.upassword = :password;";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->bindParam(':password', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $query2 ="START TRANSACTION;
                        UPDATE ".$this->tableNames['usertoken']."
                        SET token = :userToken
                        WHERE uidUserFK = :id;

                        IF ROW_COUNT() = 0 THEN
                            INSERT INTO ".$this->tableNames['usertoken']." (token, uidUserFK)
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
                        //Remover as sessions e passar a usar LocalStorage (via Javascript)
                    $_SESSION['name'] = $row['uname'];
                    $_SESSION['email'] = $row['uemail'];
                    $_SESSION['rank'] = $row['urank'];
                    $_SESSION['nickname'] = $row['username'];
                    $_SESSION['defaultTheme'] = $row['udefaultTheme'];
                    $_SESSION['id'] = $row['uid'];
                    $_SESSION['lastImageProfileUser'] = $row['uimage'];
                    $_SESSION['logged'] = true;
                    $_SESSION['userData'] = json_encode([
                        'userToken' => $userToken,
                        'name' => $row['uname'],
                        'email' => $row['uemail'],
                        'rank' => $row['urank'],
                        'nickname' => $row['username'],
                        'theme' => $row['udefaultTheme'],
                        'id' => $row['uid'],
                        'profileUser' => $row['uimage'],
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
            $query = "UPDATE userdata SET upassword = :newPassword WHERE uid = :id";
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
    
    public static function updateSessionUserData($field, $value) {
        $_SESSION['userData'][$field] = $value;
    }
    
 // a fazer.
    public function deleteAccount() {
        if (!empty($this->email) && !empty($this->password)) {
            $query = "SELECT uname, urank, email, upassword FROM " . $this->tableNames[0] . " WHERE email = :email AND upassword = :upassword";
    
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

}
?>