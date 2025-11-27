<?php
define('APP_RAN', true);

// 1. Define o ID único da página
$pageId = 'page-hkg-dashboard';

// 2. Inclui o cabeçalho
require_once __DIR__ . "/../Layout/header.php";

// 3. Validação de Rank
if ($userRank != 1) {
    header('Location: /Public/View/User/index.php');
    exit;
}
?>

<style>
    /* Container Flexível */
    .dashboard-container {
        display: flex;
        width: 100%;
        overflow: hidden; /* Importante para a animação */
        transition: all 0.5s ease;
        align-items: flex-start; /* Alinha no topo */
    }

    /* 1. O GRÁFICO (Estado Inicial: 100% de largura) */
    #contentChart {
        flex: 0 0 100%;      /* Flex-basis força o tamanho */
        max-width: 100%;     /* Garante que ocupe tudo */
        height: 70vh;
        
        display: flex;
        justify-content: center;
        align-items: center;
        
        /* A mágica da animação acontece aqui */
        transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* O GRÁFICO (Estado Encolhido: 40%) */
    #contentChart.shrink {
        flex: 0 0 40% !important;
        max-width: 40% !important;
    }

    /* 2. O CARD DE DETALHES (Estado Inicial: 0% de largura / Invisível) */
    #detailCard {
        display: block !important; /* Ele existe, mas está esmagado */
        flex: 0 0 0%;
        max-width: 0%;
        width: 0;
        
        height: 70vh;
        opacity: 0;
        padding: 0;
        overflow: hidden; /* Corta o conteúdo interno enquanto fechado */
        
        background-color: #f8f9fa;
        border-radius: 12px;
        box-shadow: -5px 0 15px rgba(0,0,0,0.05);
        border: none;
        
        transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* O CARD DE DETALHES (Estado Expandido: 60%) */
    #detailCard.expand {
        flex: 0 0 60% !important;
        max-width: 60% !important;
        opacity: 1;
        padding: 20px;
        border: 1px solid #ddd;
    }

    /* Ajustes internos */
    #pointControlUsersData {
        width: 100% !important;
        height: auto !important;
        max-height: 100%;
    }
    
    .contentDetailCard {
        height: calc(100% - 80px);
        overflow-y: auto;
    }

    /* Responsividade para Celular */
    @media (max-width: 768px) {
        .dashboard-container { flex-direction: column; }
        #contentChart, #contentChart.shrink { flex: 0 0 100% !important; max-width: 100% !important; height: 50vh; }
        #detailCard { flex: 0 0 100% !important; max-width: 100% !important; height: 0; }
        #detailCard.expand { height: auto; min-height: 50vh; margin-top: 20px; }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mt-2">Estatísticas</h3>
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
                            <th onclick="ordenarTabela('data')" style="cursor:pointer;" id="th-data">
                                Data <span class="sort-icon text-muted">⇵</span>
                            </th>            
                            <th>Status</th> <th onclick="ordenarTabela('nome')" style="cursor:pointer;" id="th-nome">
                                Nome <span class="sort-icon text-muted">⇵</span>
                            </th>
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
<script type="module" src="../../JS/HKG/indexDashboard.js?v=<?php echo time(); ?>"></script>
<p hidden id="responseTheme"></p>

<div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditar" method="post" name="updatePointControl">
                <div class="modal-header">
                    <h5 class="modal-title">Registro de <span id="inputNome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <input type="text" name="inputData" class="form-control" id="inputData" required>
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

<?php
require_once __DIR__ . "/../Layout/footer.php";
?>