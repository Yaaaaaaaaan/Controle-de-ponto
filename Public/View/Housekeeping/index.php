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
    <div class="row" style="width: 100%;">
        <div id="chartContainer" class="transition-card" style="height: 68vh;">
            <canvas id="pointControlUsersData" width="800" height="600"></canvas>
        </div>
        <div id="detailCard" class="transition-card detailCard mt-5" style="display: none;">
            <div class="headerDetailCard mt-4 mb-4 py-1 text-center">
                <h5 id="detailTitle"></h5>
                <h6>Total de registros: <span id="detailTotal">0</span></h6>
            </div>
            <div class="contentDetailCard">
                <table class="table-responsive table table-striped">
                    <thead>
                    <tr>
                        <th onclick="ordenarTabela('data')" style="cursor:pointer;">Data ▲▼</th>
                        <th>Descrição</th>
                        <th onclick="ordenarTabela('nome')" style="cursor:pointer;">Nome ▲▼</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody id="detailContent"></tbody>
                </table>
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
<script src="../../JS/HKG/indexDashboard.js"></script>
<p hidden id="responseTheme"></p>

<!-- Modal Bootstrap -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditar">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarModalLabel">Registro de <span id="inputNome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="inputId">
                    <input type="hidden" id="inputCod">

                    <div class="mb-3">
                        <label for="inputDescricao" class="form-label">Descrição</label>
                        <select class="form-select" id="inputDescricao" required>
                            <option value="">Selecione...</option>
                            <option value="1">Verificação pendente</option>
                            <option value="2">Já verificado</option>
                            <option value="3">Recusado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inputData" class="form-label">Data</label>
                        <input type="text" class="form-control" id="inputData" required>
                    </div>
                    <!-- Adicione mais campos se necessário -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>