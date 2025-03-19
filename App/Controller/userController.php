<?php
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
    public function updateUser($name, $id, $email, $nickname, $oldPassword, $newPassword, $confirmPassword, $defaultTheme): void  {
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



    public function updateProfilePicture(Request $request) {

        if ($request->ajax()) {
            if (Auth::check() && $request->has('selectedPicture')) {  // Usando Auth::check para verificar autenticação

                $selectedPictureId = $request->input('selectedPicture');

                if ($this->user->updateProfilePicture($_SESSION['id'], $selectedPictureId)) {
                    // Recupere o caminho da nova imagem de perfil
                    $picturePath = $this->user->getPathById($selectedPictureId);


                    return response()->json(['success' => true, 'message' => 'Foto de perfil atualizada com sucesso.', 'newSrc' => $picturePath]);

                } else {
                    return response()->json(['success' => false, 'message' => 'Erro ao atualizar a foto de perfil.'], 500);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado ou imagem não selecionada.'], 403); // Erro 403 - Acesso Negado
            }

        } else {

            // Se não for requisição AJAX, mantém o comportamento original (ou adapte conforme necessário)
            if (isset($_SESSION['id'])) {
                if ($this->user->updateProfilePicture($_SESSION['id'], $request->input('selectedPicture'))) { // Adaptado para usar Request
                    $_SESSION['response'] = '<p>Foto de perfil atualizada com sucesso.</p>';
                } else {
                    $_SESSION['response'] = '<p>Erro ao atualizar a foto de perfil.</p>';
                }
            } else {
                $_SESSION['response'] = '<p>Usuário não autenticado.</p>';
            }

            return redirect()->back(); // Redireciona de volta após o processamento não-AJAX
        }
    }


    public function insertUserProfilePicture($profilePicture) {
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
