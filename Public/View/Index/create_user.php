<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <h2>Create User</h2>
    <form action="create_user.php" method="post">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name"><br><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email"><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br><br>
        <label for="rank">Rank:</label><br>
        <input type="number" id="rank" name="rank"><br><br>
        <input type="submit" value="Create">
    </form>

    <?php
    if ($_POST) {
        define('APP_RAN', true);
        include_once '../../../../Estudos/App/controller/UserController.php';

        $controller = new UserController();
        $controller->createUser($_POST['name'], $_POST['email'], $_POST['password'], $_POST['rank']);
    }
    ?>
</body>
</html>
