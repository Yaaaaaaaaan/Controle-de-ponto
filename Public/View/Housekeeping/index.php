<?php
define('APP_RAN', true);
require '../layout/menu_housekeep.php';
include_once __DIR__ . '/../../../App/controller/pointController.php';

$pointController = new PointController();
$dados = $pointController->getPointControlUsers();

$labels = $dados['labels'];
$dataPoints = $dados['dataPoints'];
?>

<html>
<head></head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <h3 class="mt-5">Estatísticas</h3>
        <p class="span">Total de presenças no sistema</p>
        </div>
    </div>
    <div class="row">
        <!-- Coluna do Gráfico -->
        <div class="col-md-4">
            <canvas id="pointControlUsersData"></canvas>
        </div>

        <!-- Coluna dos Detalhes -->
        <div class="col-md-8">
            <div id="detailCard" style="display:none;">
                <div class="detailCard mt-5">
                    <div class="headerDetailCard mt-4 mb-4 py-1 text-center">
                        <h4 id="detailTitle" class=""></h4>
                        <small>Total de registros: <span id="detailTotal">0</span></small>
                    </div>
                    <div class="contentDetailCard">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Nome</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody id="detailContent">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?php echo json_encode($labels); ?>;
    const dataPoints = <?php echo json_encode($dataPoints); ?>;
    const detalhes = <?php echo json_encode($dados['detalhes']); ?>;
</script>
<script src="../../JS/HKG/dashboard.js"></script>
<p hidden id="responseTheme"></p>
</body>
</html>