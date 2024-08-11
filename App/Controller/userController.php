<?php
include_once '../../../App/Config/db.php';
include_once '../../../App/Model/user.php';
if (!defined('APP_RAN')) {
    die('Direct access not permitted');
}
class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }
    public function createUser($name, $nickname, $email, $password){
        $this->user->name = $name;
        $this->user->nickname = $nickname;
        $this->user->email = $email;
        $this->user->password = $password;
        $this->user->rank = 1;
        if ($this->user->createUser()){
            $_SESSION['response'] = '<p>Usuário criado com sucesso.</p>';
        } else {
            if(empty($name) || empty($email) || empty($password) || empty($nickname)){
                    $_SESSION['userdata'] = 
                    $_SESSION['response'] = '<p>Preencha todos os dados.</p>';
            }
        }
    }
    public function authenticateUser($nickname, $password){
        $this->user->nickname = $nickname;
        $this->user->password = $password;
        if($this->user->authenticateUser()){
            header('Location: ../');
        }else{
            echo '<p>Usuário ou senha incorreto. </p>';
            }
    }
    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme){
        $this->user->name = $name;
        $this->user->id = $id;
        $this->user->email = $email;
        $this->user->nickname = $nickname;
        $this->user->oldPassword = $oldPassword;
        $this->user->newPassword = $newPassword;
        $this->user->confirmPassword = $confirmPassword;
        $this->user->defaultTheme = $defaultTheme ? 1 : 0;
        if ($this->user->updateUser($name,$id,$email,$nickname,$oldPassword,$newPassword,$confirmPassword,$defaultTheme)){
            $_SESSION['response'] = '<p>Changes made successfully.</p>';
        } else {
                if(empty($name)){
                    $_SESSION['response'] = '<p>User could not be updated. Please insert your name.</p>';
            }elseif(empty($email)){
                $_SESSION['response'] = '<p>User could not be updated. Please insert a valid email.</p>';
            }elseif(empty($newPassword) || empty($confirmPassword)){
                $_SESSION['response'] = '<p>User could not be updated. Please confirm your new password.</p>';
            }elseif(empty($oldPassword)){
                $_SESSION['response'] = '<p>User could not be updated. Please insert your actual password.</p>';
            }elseif(empty($uname)){
                $_SESSION['response'] = '<p>User could not be updated. Please insert your username.</p>';
            }elseif(empty($CPF)){
                $_SESSION['response'] = '<p>User could not be updated. Please insert your CPF.</p>';
            }elseif(empty($location)){
                $_SESSION['response'] = '<p>User could not be updated. Please insert your location.</p>';
            }elseif(empty($location) && empty($name) && empty($email) && empty($newPassword) && empty($confirmPassword) && empty($oldPassword) && empty($uname) && empty($CPF)){
                $_SESSION['response'] = '<p>User could not be updated. You need to enter at least one field to change.</p>';
            }else{
                $_SESSION['response'] = '<p>User could not be updated. Please contact a admin.</p>';
            }
        }
    }
    public function updateUserProfilePicture(){
        
    }
    public function unAuthenticateUser($logout){
        $this->user->$logout = $logout;
        if($this->user->$logout != null){
            session_start();
            session_destroy();
            header('Location: ../');
        }
    }
}
?>
