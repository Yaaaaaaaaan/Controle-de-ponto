<?php
// Simulando dados do usuário
$userData = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john.doe@example.com'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
</head>
<body>
    <h1>Bem-vindo, <?php echo htmlspecialchars($userData['name']); ?>!</h1>

    <script>
        // Passando os dados PHP para o JavaScript
        let userData = <?php echo json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        
        // Armazenando os dados no localStorage
        localStorage.setItem('userData', JSON.stringify(userData));

        // Verificando se os dados foram armazenados corretamente
        console.log('Dados do usuário armazenados no localStorage:', localStorage.getItem('userData'));
    </script>
</body>
</html>
