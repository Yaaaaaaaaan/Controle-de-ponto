<?php
define('APP_RAN', true);
require '../layout/menu_housekeep.php';
include_once __DIR__ . '/../../../App/controller/pointController.php';

$pointController = new PointController();
$dados = $pointController->getPointControlUsers();

$labels = $dados['labels'];
$dataPoints = $dados['dataPoints'];

if ($_POST) {
    include_once __DIR__ . '/../../../App/controller/UserController.php';
    $userController = new UserController();

    if (isset($_POST['updatePointControl'])) {
        $insertPointControl = $userController->updatePresenceHousekeeping($_POST['inputId'], $_POST['inputCod'], $_POST['inputDescricao']);
    }
}
?>

<html>
<head></head>
<body>
<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mt-5">Estatísticas</h3>
            <p class="span">Total de presenças no sistema</p>
        </div>
    </div>
    <div class="row" style="width: 100%;">
        <div id="contentChart" class="transitionCard" style="height: 68vh;">
            <canvas id="pointControlUsersData" width="800" height="600"></canvas>
        </div>
        <div id="detailCard" class="transitionCard detailCard mt-5" style="display: none;">
            <div class="headerDetailCard mt-4 mb-4 py-1 d-flex justify-content-between align-items-center">
                <div class="text-center" style="flex-grow: 1;">
                    <h5 id="detailTitle" class="mb-0"></h5>
                    <h6>Total de registros: <span id="detailTotal">0</span></h6>
                </div>
                <div class="input-group mt-1" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Pesquisar..." aria-label="Pesquisar" aria-describedby="button-addon2">
                    <button class="btn btn-outline-secondary" type="button" id="button-addon2">Buscar</button>
                </div>
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

<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditar" method="post" name="updatePointControl">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarModalLabel">Registro de <span id="inputNome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inputId" id="inputId">
                    <input type="hidden" name="inputCod" id="inputCod">

                    <div class="mb-3">
                        <label for="inputDescricao" class="form-label">Descrição</label>
                        <select class="form-select" id="inputDescricao" name="inputDescricao" required>
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
                </div>
                <div class="modal-footer">
                    <button type="submit" name="updatePointControl" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>