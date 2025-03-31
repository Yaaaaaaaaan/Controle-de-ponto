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

// Use $pointControlData conforme necessário
;

    // Preparar dados para o gráfico
    $labels = [];
    $dataPoints = [];
    foreach ($pointControlData as $row) {
        $labels[] = $row['month'];
        $dataPoints[] = $row['count'];
    }
/* // Buscar dados para o gráfico apenas se o controlador estiver inicializado
 $id = $_SESSION['id'];
 $pointControlData = $pointController->getPointControlData($id, $months);

 // Preparar dados para o gráfico
 $allMonths = $pointController->getAllAvailableMonths($id);
 $selectedMonths = isset($_GET['months']) ? $_GET['months'] : $allMonths;
 $pointControlData = $pointController->getPointControlData($id, $selectedMonths);

 $labels = [];
 $dataPoints = [];
 foreach ($pointControlData as $row) {
     $labels[] = $pointController->converterMonthFromName($row['month']);
     $dataPoints[] = $row['count'];
 }*/

?>

<html>
<head>
<style>


        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }

        #chartContainer {
            width: auto; /* Garante que o contêiner ocupe toda a largura */
            height: 60vh;
            margin: auto;
        }

        canvas {
            max-width: 100%;
        }

        @media (max-width: 375px) {
        #chartContainer {
            width: 100%;
            height: 75vh; 
            padding: 0;  /* Ajuste para telas de 375px */
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
            transition: transform 0.3s ease;
            max-width: 100%;
            margin: 0 auto;
        }

        .badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-pic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            margin: 0 auto 15px;
            display: block;
        }

        .user-full-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 6px;
            text-align: center;
        }

        .user-nickname {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 6px;
        }

        .user-email {
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

                <div class="badge-card">
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

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('attendance').getContext('2d');

    const labels = <?php echo json_encode($labels); ?>;
    const dataPoints = <?php echo json_encode($dataPoints); ?>;

    const data = {
        labels: labels,
        datasets: [{
            label: 'Total Mensal',
            data: dataPoints,
            borderColor: 'rgba(0, 123, 255, 1)',
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false, // Permite ajustar a altura do gráfico
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let value = context.raw;
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    };

    const attendance = new Chart(ctx, config);


</script>
<img hidden id="pPictureModal">
</body>
</html>