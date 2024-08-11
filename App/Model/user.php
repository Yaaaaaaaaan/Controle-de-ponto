<?php
if (!defined('APP_RAN')) {
    die('Direct access not permitted');
}
class User {
    private $conn;
    private $table_name = 'userdata';
    private $table_name2 = 'profilepictures';

    public $id;
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

    public function __construct($db) {
        $this->conn = $db;
    }
    public function createUser() {
        if(!empty($this->name && $this->email && $this->password && $this->rank && $this->nickname)){
            $query = 'INSERT INTO ' . $this->table_name . ' SET uname=:name, username=:nickname, uemail=:email, upassword=:password, urank=:rank';
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
            $query = "SELECT u.uid, u.uname, u.username, u.urank, u.uemail, u.upassword, u.username,  d.uimage, d.udefaultTheme FROM " . $this->table_name . " u INNER JOIN ". $this->table_name2 ." d ON u.uid = d.uidUserFK WHERE u.username = :nickname AND u.upassword = :password";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':nickname', $this->nickname);
                $stmt->bindParam(':password', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['name'] = $row['uname'];
                    $_SESSION['email'] = $row['uemail'];
                    $_SESSION['rank'] = $row['urank'];
                    $_SESSION['nickname'] = $row['unickname'];
                    $_SESSION['id'] = $row['uid'];
                    $_SESSION['lastImageProfileUser'] = $row['uimage'];
                    $_SESSION['defaultTheme'] = $row['defaultTheme'];
                    $_SESSION['logged'] = true;
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
            $updateFields['users'][] = "uname = :name";
            $params['users'][':name'] = $this->name;
        }
        if (isset($this->email)) {
            $updateFields['users'][] = "uemail = :email";
            $params['users'][':email'] = $this->email;
        }
        if (isset($this->nickname) && $this->nickname != $_SESSION['nickname']) {
            $updateFields['userdata'][] = "unickname = :nickname";
            $params['userdata'][':nickname'] = $this->nickname;
        }
        if (isset($this->defaultTheme) && $this->defaultTheme != $_SESSION['defaultTheme']) {
            $updateFields['userdata'][] = "defaultTheme = :defaultTheme";
            $params['userdata'][':defaultTheme'] = $this->defaultTheme;
        }
        foreach ($updateFields as $table => $fields) {
            $column = ($table == 'users') ? 'uid' : 'uidUserFK';
            $query = "UPDATE " . $table . " SET " . implode(", ", $fields) . " WHERE " . $column . " = :id";
            $params[$table][':id'] = $this->id;
            $queries[] = ['query' => $query, 'params' => $params[$table]];
            foreach ($fields as $field) {
                $fieldName = explode(' ', $field)[0];
                $updatedFields[$fieldName] = true;
            }
        }
        if (!empty($this->oldPassword) && !empty($this->newPassword) && !empty($this->confirmPassword) && $this->newPassword === $this->confirmPassword) {
            $query = "UPDATE users SET upassword = :newPassword WHERE uid = :id";
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
        if($id == $_SESSION['id']){
            if (isset($updatedFields['name'])) $_SESSION['name'] = $this->name;
            if (isset($updatedFields['email'])) $_SESSION['email'] = $this->email;
            if (isset($updatedFields['username'])) $_SESSION['nickname'] = $this->nickname;
            if (isset($updatedFields['defaultTheme'])) $_SESSION['defaultTheme'] = $this->defaultTheme;
        }
        return true;
    }
    
 // a fazer.
    public function deleteAccount() {
        if (!empty($this->email) && !empty($this->password)) {
            $query = "SELECT uname, urank, email, upassword FROM " . $this->table_name . " WHERE email = :email AND upassword = :upassword";
    
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
