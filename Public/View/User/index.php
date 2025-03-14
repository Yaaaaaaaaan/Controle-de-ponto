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
            width: 100%;
            height: 60vh;
            margin: auto;
        }

        canvas {
            max-width: 100%;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div id="chartContainer">
                <canvas id="attendance"></canvas>
            </div>
        </div>
        <div class="col-md-4">
        <div class="card">
         <img id="profilePic" style="height: 35vh; object-fit: scale-down;" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Olá, <text id="responseName"></text>!</h5>
            Seu email: <text id="email"></text>
            <p>Seu nickname: <text id="nickname"></text></p>
            <form action="index.php" method="post" name="insertPointControl">
            <input hidden value="1" name="insertPointControl">
            <input hidden value="<?= $_SESSION['id'] ?>" name="id">
            <input hidden value="Verificação pendente" name="description">
            <button class="w-100 btn-lg btn btn-success" type="submit">Estou aqui!</button>
          </form>
          </div>
        </div>

          
      </div>
    </div>
    <div class="row">
        
    </div>
    <div class="row">
        <div class="col-md-12">
          
            <p id="responseUserToken"></p>
            <p id="profileUser"></p>
            <p id="id"></p>
            <p id="theme"></p>
            <p id="nickname"></p>
            <p id="rank"></p>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('attendance').getContext('2d');

    // Dados do gráfico obtidos do PHP
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