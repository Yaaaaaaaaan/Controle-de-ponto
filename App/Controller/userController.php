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
            $_SESSION['response'] = '<p>Alterações feitas com sucesso!.</p>';
        } else {
                if(empty($name)){
                    $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira seu nome.</p>';
            }elseif(empty($email)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira um e-mail válido.</p>';
            }elseif(empty($newPassword) || empty($confirmPassword)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira confirme sua nova senha.</p>';
            }elseif(empty($oldPassword)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira sua senha atual.</p>';
            }elseif(empty($uname)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira seu username.</p>';
            }elseif(empty($CPF)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira seu CPF.</p>';
            }elseif(empty($location)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira sua localização.</p>';
            }elseif(empty($location) && empty($name) && empty($email) && empty($newPassword) && empty($confirmPassword) && empty($oldPassword) && empty($uname) && empty($CPF)){
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Você precisa inserir um ou mais campos para prosseguir com a atualização.</p>';
            }else{
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor entre em contato com a administração.</p>';
            }
        }
    }
    public function updateUserProfilePicture($profilePicture) {
        $targetDirectory = '/Controle-de-ponto/App/Persistence/userProfileImages/';
        $nameOld = $targetDirectory . basename($_FILES["profilepic"]["name"]);
        $uploadOk = 1;
        $fileTypeImage = strtolower(pathinfo($nameOld, PATHINFO_EXTENSION));
         // Gera um novo nome de arquivo baseado na data e hora atual
        $newFileName = date('YmdHis') .$_SESSION['id']. '.' . $fileTypeImage;
        $arch = $targetDirectory . $newFileName;
        // Caminho completo no servidor
        $targetFile = __DIR__ . '/../Persistence/userProfileImages/' . $newFileName;
        // Verifica se o arquivo é uma imagem
        $check = getimagesize($profilePicture['tmp_name']);
        if ($check === false) {
            $_SESSION['response'] = "O arquivo não é uma imagem.";
            $uploadOk = 0;
        }
        
        // Verifica se o arquivo já existe
        if (file_exists($arch)) {
            $_SESSION['response'] = "Arquivo já existente.";
            $uploadOk = 0;
        }
        
        // Verifica o tamanho do arquivo
        if ($profilePicture['size'] > 500000) { // Limite de 500KB
            $_SESSION['response'] = "Arquivo muito grande.";
            $uploadOk = 0;
        }
        
        // Permite apenas certos formatos de arquivo
        if (!in_array($fileTypeImage, ['jpg', 'png', 'jpeg', 'gif'])) {
            $_SESSION['response'] = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            $uploadOk = 0;
        }
        
        // Se estiver tudo ok, tenta fazer o upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
                if($this->user->updateUserProfilePicture($newFileName, $arch,  $uploadOk)){
                    $_SESSION['response'] = '<p>Imagem alterada com sucesso!.</p>';
                }
            } else {
                $_SESSION['response'] = '<p>Erro ao alterar imagem de perfil.</p>';
            }
        }
    }
    
    public function unAuthenticateUser($logout){
        $this->user->$logout = $logout;
        if($this->user->$logout != null){
            session_start();
            session_destroy();
            header('Location: ../');
        }
    }

    public function showUserHistory($registro) {
        $this->user->registro = $registro;
        $userHistory = $this->user->getUserHistory(userId: $_SESSION['id'], registro: $registro);
        return $userHistory;
    }
    public function insertPointControl($id, $descricao){
        $this->user->descricao = $descricao;
        $this->user->id = $id;
        $insertPointControl = $this->user->insertPointControl( $id, $descricao);
        return $insertPointControl;
       }
}
?>
