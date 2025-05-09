<?php

use Random\RandomException;
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/User.php';
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
        $this->user->name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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

    /**
     * @throws RandomException
     */

    public function authenticateUser($nickname, $password): void
    {
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->user->password = $password;
        if($this->user->authenticateUser()){
            header('Location: ../');
        }else{
            $_SESSION['response'] = '<p>Usuário ou senha incorretos. </p>';
            }
    }

    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme): void
    {
        $this->user->name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
        $this->user->id = $id; /* preciso descobrir como recuperar diretamente o usertoken ao invés do ID */
        $this->user->email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
        $this->user->nickname = filter_var($nickname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);;
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

    public function insertUserProfilePicture($userPicture): void
    {
        $targetDirectory = '/Controle-de-ponto/App/Persistence/userProfileImages/';
        $nameOld = $targetDirectory . basename($userPicture['name']);
        $uploadOk = 1;
        $fileTypeImage = strtolower(pathinfo($nameOld, PATHINFO_EXTENSION));
        // Gera um novo nome de arquivo baseado na data e hora atual
        $newFileName = date('YmdHis') .$_SESSION['id']. '.' . $fileTypeImage;
        $arch = $targetDirectory . $newFileName;
        // Caminho completo no servidor
        $targetFile = __DIR__ . '/../Persistence/userProfileImages/' . $newFileName;
        // Verifica se o arquivo é uma imagem
        $check = getimagesize($userPicture['tmp_name']);
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
        if ($userPicture['size'] > 500000) { // Limite de 500KB
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
            if (move_uploaded_file($userPicture['tmp_name'], $targetFile)) {
                if($this->user->insertUserProfilePicture($newFileName, $arch,  $uploadOk)){
                    $_SESSION['response'] = 'Imagem carregada com sucesso.';
                }
            } else {
                $_SESSION['response'] = '<p>Erro ao alterar imagem de perfil.</p>';
            }
        }
    }

    public function unAuthenticateUser(/*$userToken*/) {
        /* // Esse código fará a implementação inicial do logout com token do localStorage
        (tudo será passado por formulário ou requisições HTTP)
        $description = 'Logout. ';
        $this->createUserHistory($description, );
        */
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

    public function insertPointControl($id){
        $this->user->id = $id;
        $insertPointControl = $this->user->insertPointControl( $id);
        return $insertPointControl;
       }

    public function getUserIdByToken(string $userToken): ?int {
        return $this->user->getIdByToken($userToken);
    }

    public function updateUserTheme(int $userId, int $theme): bool {
        return $this->user->updateTheme($userId, $theme);
    }

    public function validatePresence($userId, $userIdToValidate, $description, $code){ //Tudo aqui é transformação
        $currentDate = date("Y-m-d");
        $descriptionToValidate = "Já verificado";
        $this->user->validatePresence($userId);
        $this->user->validatePresence($userIdToValidate);
        $this->user->validatePresence($description);
        $this->user->validatePresence($code);
        $this->user->validatePresence($currentDate);
        $this->user->validatePresence($descriptionToValidate);


        return true;
    }

    public function updatePresenceHousekeeping($userIdToValidate, $code, $description)
    {
        if ($description == 1) {
            $descriptionTranslated = "Verificação pendente";
        } else if ($description == 2) {
            $descriptionTranslated = "Já verificado";
        } else if ($description == 3) {
            $descriptionTranslated = "Recusado";
        } else {
            error_log("Valor inválido para descrição: " . $description);
            return false;
        }
        $result = $this->user->updatePresenceHousekeeping($userIdToValidate, $code, $descriptionTranslated);
        if ($result) {
            echo "200 OK.";
        } else {
            echo "400 Bad Request.";
        }
        return $result;
    }




}
?>
