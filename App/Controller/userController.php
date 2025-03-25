<?php

use Random\RandomException;

include_once '../../../App/Config/db.php';
include_once '../../../App/Model/user.php';
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    //TODO: REPARAR FUNÇÃO DE CRIAÇÃO DE USUÁRIO
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
                    $_SESSION['userdata'] = '';
                    $_SESSION['response'] = '<p>Preencha todos os dados.</p>';
            }
        }
    }

    //TODO: Apenas `authenticateUser` e, `createUser` , tanto em userController.php quanto em user.php serão apenas MVC com PHP e MYSQL.

    /**
     * @throws RandomException
     */
    public function authenticateUser($nickname, $password): void
    {
        $this->user->nickname = $nickname;
        $this->user->password = $password;
        if($this->user->authenticateUser()){
            header('Location: ../');
        }else{
            echo '<p>Usuário ou senha incorreto. </p>';
            }
    }
    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme): void
    {
        $this->user->name = $name;
        $this->user->id = $id;
        $this->user->email = $email;
        $this->user->nickname = $nickname;
        $this->user->oldPassword = $oldPassword;
        $this->user->newPassword = $newPassword;
        $this->user->confirmPassword = $confirmPassword;
        $this->user->defaultTheme = $defaultTheme ? 1 : 0;

        if ($this->user->updateUser()) {
            $_SESSION['response'] = '<p>Alterações feitas com sucesso!</p>';
        } else {
            if(empty($name)) {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira seu nome.</p>';
            } elseif(empty($email)) {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira um e-mail válido.</p>';
            } elseif(!empty($newPassword) && empty($confirmPassword)) {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor confirme sua nova senha.</p>';
            } elseif(!empty($newPassword) && empty($oldPassword)) {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira sua senha atual.</p>';
            } elseif(empty($nickname)) {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor insira seu nome de usuário.</p>';
            } else {
                $_SESSION['response'] = '<p>Usuário não pôde ser atualizado. Favor entre em contato com a administração.</p>';
            }
        }
    }




    public function updateProfilePicture($pictureId): void
    {
        if (isset($_SESSION['id'])) {
            if ($this->user->updateProfilePicture($_SESSION['id'], $pictureId)) {
                $_SESSION['response'] = '<p>Foto de perfil atualizada com sucesso.</p>';
            } else {
                $_SESSION['response'] = '<p>Erro ao atualizar a foto de perfil.</p>';
            }
        } else {
            $_SESSION['response'] = '<p>Usuário não autenticado.</p>';
        }
    }




    public function insertUserProfilePicture($profilePicture): void
    {
        if (isset($profilePicture) && $profilePicture['error'] == 0) {
            $targetDirectory = __DIR__ . '/../../App/Persistence/userProfileImages/'; // Caminho absoluto
            $imageFileType = strtolower(pathinfo($profilePicture['name'], PATHINFO_EXTENSION));
            $newFileName = time() . $_SESSION['id'] . '.' . $imageFileType;
            $targetFile = $targetDirectory . $newFileName;
    
            // Validar tipo de arquivo
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($imageFileType, $allowedTypes)) {
                $_SESSION['response'] = '<p>Tipo de arquivo inválido. Apenas imagens são permitidas.</p>';
                return;
            }
    
            // Mover o arquivo
            if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
                $this->user->profilePicture = $newFileName;
                $this->user->directory = '/App/Persistence/userProfileImages/' . $newFileName; // Caminho relativo ao root do site
    
                if ($this->user->insertUserProfilePicture($this->user->profilePicture, $this->user->directory)) {
                    $_SESSION['response'] = '<p>Foto de perfil atualizada com sucesso.</p>';
                } else {
                    $_SESSION['response'] = '<p>Erro ao atualizar a foto de perfil no banco de dados.</p>';
                }
            } else {
                $_SESSION['response'] = '<p>Erro ao fazer upload do arquivo.</p>';
            }
        } else {
            $_SESSION['response'] = '<p>Nenhum arquivo enviado ou erro no upload.</p>';
        }
    }

    public function unAuthenticateUser() {
        // Remove dados da sessão
        if (isset($_SESSION['UserData'])) {
            unset($_SESSION['UserData']);
        }

        if (isset($_SESSION['localUserData'])) {
            unset($_SESSION['localUserData']);
        }

        // Destrua a sessão completamente
        session_destroy();

        // Script com redirecionamento controlado
        echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Saindo...</title>
    </head>
    <body>
    <style>
    
    body {
    font-family: sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f0f0f0;
}

.container-user {
    width:280px;
    height: 230px;
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    justify-content: center;
    align-items: center;
    display: flex;
}

.container-user p {
    text-align: center;
    margin: 0;
    color:rgb(33, 37, 41);

}


</style>

<div class='container-user'>
    <p>Saindo...</p>
</div>

        
        <script>
            // Remove os dados do localStorage
            localStorage.removeItem('userData');
            console.log('localStorage limpo');
            
            // Redireciona após um curto delay
            setTimeout(function() {
                window.location.href = '../';
            }, 100);
        </script>
    </body>
    </html>
    ";
        exit();
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
