<?php
        define('APP_RAN', true);
        require '../layout/menu_housekeep.php';
        include_once __DIR__ . '/../../../App/controller/pointController.php';
    // No arquivo que precisa usar estes dados
    $pointController = new PointController();
    $pointControlUsersData = $pointController->getPointControlUsers();

    // Inicializa as variáveis antes do loop
    $labels = [];
    $dataPoints = [];

    // Preparar dados para o gráfico
    foreach ($pointControlUsersData as $row) {
        $description = $row['description'];
        $count = $row['count'];

        // Adiciona a descrição aos rótulos se ainda não existir
        if (!in_array($description, $labels)) {
            $labels[] = $description;
        }

        // Soma os totais para cada descrição
        if (!isset($totalCounts[$description])) {
            $totalCounts[$description] = 0;
        }
        $totalCounts[$description] += $count;
    }

    // Converte o array associativo em um array simples mantendo a ordem das labels
    $dataPoints = array_map(function($label) use ($totalCounts) {
        return $totalCounts[$label];
    }, $labels);

?>

<html>
    <head>

    </head>
    <body>
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-4 mt-3">
                <h3 class="mt-5">Estatísticas</h3>
                <p class="span">Total de presenças no sistema</p>
                <canvas id="pointControlUsersData"></canvas>

                <!-- TODO: fazer gráfico de pizza com o total de presenças dos últimos 03 meses de todos os usuários-->
            </div>

            <div class="col-md-4">


            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        //chama daysData por fora, pelo simples fato de se estar sendo feita a consulta independente do chart.js.
        const labels = <?php echo json_encode($labels); ?>;
        const dataPoints = <?php echo json_encode($dataPoints); ?>;

    </script>
    <script src="../../JS/HKG/dashboard.js"></script> <!-- Dashboard de visualização de dados -->

        <script>



        document.addEventListener('DOMContentLoaded', function() {
            const responseTheme = document.getElementById('responseTheme');
            const themeValue = responseTheme ? parseInt(responseTheme.textContent, 10) : 0;
            document.body.dataset.bsTheme = themeValue === 1 ? 'dark' : 'light';
        });
        </script>
    </body>
</html>
