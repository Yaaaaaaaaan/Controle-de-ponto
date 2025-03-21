<?php
error_reporting(0);
define('APP_RAN', true);
include_once '../../../App/Controller/UserController.php';
session_start();
if($_SESSION['logged'] != null){
    header('Location:../User/index.php');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <link rel="stylesheet" href="../../CSS/style.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <title>Login</title>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>    
    </head>
        <body>
            <div class="container-userlogin">
                <h1></h1>
                <form action="index.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="nickname" id="floatingInput" placeholder=".">
                        <label for="floatingInput">Usuário</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="floatingPassword" placeholder=".">
                        <label for="floatingPassword">Senha</label>
                    </div>
        
        
                    <?php
                        if ($_POST) {
                            define('APP_RAN', true);
                            include_once '../../../App/Controller/UserController.php';

                            $controller = new UserController();
                            $controller->AuthenticateUser($_POST['nickname'], $_POST['password']);

                            echo $_SESSION['response'];
                        }
                    ?>
                    <div class="d-grid"><button class="" type="submit">Login</button></div> <a href="../Index/register.php">Ainda não é cadastrado?! Clique aqui!</a>
        
                </form>
            </div>
        </body>
    </html>