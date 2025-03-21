<?php
define('APP_RAN', true);
require "../layout/menu.php";
include_once '../../../App/controller/pointController.php';
// Inicialização do controlador fora do bloco POST
$pointController = new PointController();

if ($_POST) {
    include_once '../../../App/controller/UserController.php';
    $userController = new UserController();

    if (isset($_POST['insertPointControl'])) {
        $insertPointControl = $userController->insertPointControl($_POST['id'], $_POST['description']);
    }
}

// Buscar dados para o gráfico apenas se o controlador estiver inicializado
    $id = $_SESSION['id'];
    $pointControlData = $pointController->getPointControlData($id);

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
        <div class="mt-6 col-12 col-md-4">
            <div class="col-12">
                <div class="text-center border rounded py-2 mb-3">
                    <h4 class="d-flex justify-content-between align-items-center mb-3 ms-2">
                        <span class="text-primary">Olá, <text id="responseName"></text>!</span>
                    </h4>
                    <?php // echo '<img src="' . $_SESSION['lastImageProfileUser'] . '" alt="Imagem do usuário" style="width:226px;" >'; ?>
                    <div class="index"><img id="pPicture"></div>
                    <small class="text-body-secondary"><span class="nav-link">
                        Seu email: <text id="responseEmail"></text>
                        <p>Seu nickname: <text id="responseNickname"></text></p>
                        <p hidden id="responseUserToken"></p>
                        <p hidden id="responseId"></p>
                        <p hidden id="theme"></p>
                        <p hidden id="rank"></p>
                    </span></small>
                    <form action="index.php" method="post" name="insertPointControl">
                        <input hidden value="1" name="insertPointControl">
                        <input hidden value="<?= $_SESSION['id'] ?>" name="id">
                        <input hidden value="Verificação pendente" name="description">
                        <button class="btn-lg btn btn-success" style="width:90%;" type="submit">Estou aqui!</button>
                    </form>
                </div>
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
</body>
</html>