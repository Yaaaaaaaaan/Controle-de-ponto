<?php
define('APP_RAN', true);
$pageId = 'page-hkg-dashboard';
require_once __DIR__ . "/../Layout/header.php";

if ($userRank != 1) {
    header('Location: /Public/View/User/index.php');
    exit;
}
?>

<style>
    .dashboard-container {
        display: flex; width: 100%; overflow: hidden; transition: all 0.5s ease; align-items: flex-start;
    }
    #contentChart {
        flex: 0 0 100%; max-width: 100%; height: 70vh; display: flex; justify-content: center; align-items: center;
        transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    #contentChart.shrink { flex: 0 0 40% !important; max-width: 40% !important; }
    #detailCard {
        display: block !important; flex: 0 0 0%; max-width: 0%; width: 0; height: 70vh; opacity: 0; padding: 0;
        overflow: hidden; background-color: #f8f9fa; border-radius: 12px; box-shadow: -5px 0 15px rgba(0,0,0,0.05);
        transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    #detailCard.expand { flex: 0 0 60% !important; max-width: 60% !important; opacity: 1; padding: 20px; }
    .contentDetailCard { height: calc(100% - 80px); overflow-y: auto; }
    #pointControlUsersData { width: 100% !important; height: auto !important; max-height: 100%; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mt-5">Estatísticas</h3>
            <p class="span">Total de presenças no sistema</p>
        </div>
    </div>

    <div class="dashboard-container">
        <div id="contentChart">
            <canvas id="pointControlUsersData"></canvas>
        </div>

        <div id="detailCard">
            <div class="headerDetailCard mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 id="detailTitle" class="mb-0">Detalhes</h5>
                    <small>Total: <span id="detailTotal">0</span></small>
                </div>
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Pesquisar..." id="searchDashboard">
                    <button class="btn btn-outline-secondary" type="button">🔍</button>
                </div>
            </div>
            
            <div class="contentDetailCard">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="detailContent"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditar">
                <div class="modal-header">
                    <h5 class="modal-title">Registro de <span id="inputNome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inputId" id="inputId">
                    <input type="hidden" name="inputCod" id="inputCod">
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <select class="form-select" id="inputDescricao" name="inputDescricao" required>
                            <option value="1">Verificação pendente</option>
                            <option value="2">Já verificado</option>
                            <option value="3">Recusado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="text" class="form-control" id="inputData" name="inputData" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="../../JS/HKG/dashboardController.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . "/../Layout/footer.php"; ?>