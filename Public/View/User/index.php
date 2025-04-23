<?php
define('APP_RAN', true);
require "../layout/menu.php";
include_once __DIR__ . '/../../../App/controller/pointController.php';



if ($_POST) {
    include_once '../../../App/controller/UserController.php';
    $userController = new UserController();

    if (isset($_POST['insertPointControl'])) {
        $insertPointControl = $userController->insertPointControl($_POST['id'], $_POST['description']);
    }
}

// Buscar dados para o gráfico apenas se o controlador estiver inicializado
    $id = $_SESSION['id'];
// No arquivo que precisa usar estes dados
$pointController = new PointController();
$pointControlData = $pointController->getPointControl($id);


// Preparar dados para o gráfico
$labels = [];
$dataPoints = [];
$daysData = []; // Array para armazenar os dias de cada mês

foreach ($pointControlData as $row) {
    $month = $row['month'];
    $labels[] = $month;
    $dataPoints[] = $row['count'];

    // Se ainda não tem dados para este mês, cria um array vazio
    if (!isset($daysData[$month])) {
        $daysData[$month] = [];
    }

    // Adiciona todos os dias nos dados deste mês (da propriedade 'dias')
    if (isset($row['dias']) && is_array($row['dias'])) {
        foreach ($row['dias'] as $diaInfo) {
            if (isset($diaInfo['day'])) {
                // Adiciona o dia como chave e a contagem como valor
                $daysData[$month][] = [
                    'dia' => $diaInfo['day'],
                    'description' => $diaInfo['description'],
                    'contagem' => $diaInfo['day_count']
                ];
            }
        }
    }
}
echo "profileImagePath: (à fazer) <br>".$_SESSION['profileImagePath'];
echo "UserData: <br>".$_SESSION['userData'];
echo "LastProfiles: <br>".$_SESSION['lastProfilePictures'];

echo "<script>//console.log('Formato de daysData:', " . json_encode($daysData) . ");</script>";

?>

<html>
<head>
    <style>
        #chartContainer {
            width: auto;
            height: 60vh;
            margin: auto;
            position: relative;
        }

        canvas {
            max-width: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }

        #chartContainer {
            width: auto;
            height: 60vh;
            margin: auto;
        }

        canvas {
            max-width: 100%;
        }

        @media (max-width: 375px}) {
            #chartContainer {
                width: 100%;
                height: 75vh;
                padding: 0;
            }
            .mt-6{margin-top:2rem;}
        }

        /* Estilo personalizado para o crachá */
        .badge-card {
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 100%;
            margin: 0 auto;
            height: 350px;
        }

        .badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .badge-card .profile-pic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            margin: 0 auto 15px;
            display: block;
        }

        .badge-card .user-full-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 6px;
            text-align: center;
        }

        .badge-card .user-nickname {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 6px;
        }

        .badge-card .user-email {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            color: #495057;
            text-align: center;
            margin-bottom: 20px;
            word-break: break-all;
        }

        .badge-action-btn {
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }

        /* Estilos para o card de detalhes */
        #detailCard {
            display: none;
        }
        .detailCard{
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 0 20px 20px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 100%;
            margin: 0 auto;
            overflow-y: auto;
            height: 350px;
        }

        .detailCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-8">
            <div id="chartContainer">
                <canvas id="attendance"></canvas>
            </div>
        </div>

        <div class="mt-5 col-12 col-md-4">
            <!-- Card do usuário -->
            <div id="userCard" class="badge-card">
                <!-- Foto do perfil -->
                <img id="pPicture" alt="Foto do perfil" class="profile-pic">

                <!-- Informações do usuário -->
                <h3 id="responseName" class="user-full-name">@nome completo.</h3>
                <div id="responseNickname" class="user-nickname">@nickname</div>
                <div id="responseEmail" class="user-email">usuario@email.com</div>

                <!-- Botão de ação -->
                <form action="index.php" method="post" name="insertPointControl">
                    <input hidden value="1" name="insertPointControl">
                    <input hidden value="<?= $_SESSION['id'] ?>" name="id">
                    <input hidden value="Verificação pendente" name="description">
                    <button type="submit" class="btn btn-success badge-action-btn">Confirmar Presença</button>
                </form>
                <p hidden id="responseUserToken"></p>
                <p hidden id="responseId"></p>
                <p hidden id="theme"></p>
                <p hidden id="rank"></p>
            </div>

            <!-- Card de detalhes - mesma aparência que o badge-card -->
            <div id="detailCard" class="detailCard" style="position: relative;">
                <!-- Botão para voltar - posicionado fora da área de overflow -->
                <div style="position: sticky; top: 0; right: 0; text-align: right; z-index: 1000; background-color: #f8f9fa; padding: 10px;">
                    <button class="btn-close" onclick="voltarParaUsuario()"></button>
                    <!-- Título do mês -->
                    <div class="py-1 text-center">
                        <h3 id="monthName"></h3>
                        <!-- Total de registros do mês vigente -->
                        <h6>Total de registros: <text id="monthTotal">0</text></h6>
                    </div>
                </div>

                <!-- Área para informações detalhadas -->
                <div id="detailContent">
                    <!-- Aqui serão inseridos os detalhes do mês via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    //chama daysData por fora, pelo simples fato de se estar sendo feita a consulta independente do chart.js.
    const daysData = <?php echo json_encode($daysData); ?>;
    const labels = <?php echo json_encode($labels); ?>;
    const dataPoints = <?php echo json_encode($dataPoints); ?>;
    

</script>
<script src="../../JS/dashboard.js"></script> <!-- Dashboard de visualização de dados -->
<img hidden id="pPictureModal">
</body>
</html>