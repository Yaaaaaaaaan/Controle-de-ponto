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
    foreach ($pointControlData as $row) {
        $labels[] = $row['month'];
        $dataPoints[] = $row['count'];
    }

// Obter dados dos últimos 3 meses
//$monthlyData = $pointController->getDetailedPointControlData($id);

// Converter os dados para JSON para usar no JavaScript
$monthlyDataJSON = json_encode($monthlyData);

// Mapeamento de meses em português
$monthNames = [
    '01' => 'Janeiro',
    '02' => 'Fevereiro',
    '03' => 'Março',
    '04' => 'Abril',
    '05' => 'Maio',
    '06' => 'Junho',
    '07' => 'Julho',
    '08' => 'Agosto',
    '09' => 'Setembro',
    '10' => 'Outubro',
    '11' => 'Novembro',
    '12' => 'Dezembro'
];


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
    #chartContainer {
        width: auto;
        height: 60vh;
        margin: auto;
        position: relative; /* Importante para posicionamento relativo */
    }

    canvas {
        max-width: 100%;
    }

    /* Estilo para o popup de detalhes */
    .detail-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 1000;
        width: 80%;
        max-width: 500px;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #333;
    }

    .detail-body {
        max-height: 400px;
        overflow-y: auto;
    }

    @media (max-width: 375px) {
        #chartContainer {
            width: 100%;
            height: 75vh;
            padding: 0;
        }

        .detail-popup {
            width: 90%;
            padding: 15px;
        }
    }


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

        <!-- Adicione esta div para o popup de detalhes -->
        <div id="detailContainer" class="detail-popup" style="display: none;">
            <div class="detail-content">
                <div class="detail-header">
                    <h4 id="detailTitle">Detalhes do Mês</h4>
                    <button type="button" class="close-btn" onclick="closeDetailPopup()">&times;</button>
                </div>
                <div id="detailContent" class="detail-body">
                    <!-- Conteúdo detalhado será inserido aqui via JavaScript -->
                </div>
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
    document.addEventListener('DOMContentLoaded', function() {
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
                maintainAspectRatio: false,
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
                },
                onClick: handleChartClick
            }
        };

        const attendance = new Chart(ctx, config);

        // Função para lidar com o clique no gráfico
        function handleChartClick(event, elements, chart) {
            if (elements.length > 0) {
                const clickedElement = elements[0];
                const dataIndex = clickedElement.index;
                const month = labels[dataIndex];
                const value = dataPoints[dataIndex];

                showDetailPopup(month, value, dataIndex);
            }
        }
    });

    // Função para exibir o popup com detalhes
    function showDetailPopup(month, value, index) {
        const detailContainer = document.getElementById('detailContainer');
        const detailTitle = document.getElementById('detailTitle');
        const detailContent = document.getElementById('detailContent');

        // Definir o título com o mês clicado
        detailTitle.textContent = `Detalhes de ${month}`;

        // Converter número do mês para nome do mês, se aplicável
        let monthName = month;
        if (/^\d{2}$/.test(month)) {
            const monthNames = {
                '01': 'Janeiro',
                '02': 'Fevereiro',
                '03': 'Março',
                '04': 'Abril',
                '05': 'Maio',
                '06': 'Junho',
                '07': 'Julho',
                '08': 'Agosto',
                '09': 'Setembro',
                '10': 'Outubro',
                '11': 'Novembro',
                '12': 'Dezembro'
            };
            monthName = monthNames[month] || month;
            detailTitle.textContent = `Detalhes de ${monthName}`;
        }

        // Construir o conteúdo detalhado
        let contentHTML = `
        <div class="month-summary">
            <h5>Resumo</h5>
            <p>Total de registros em ${monthName}: <strong>${value.toLocaleString()}</strong></p>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" onclick="buscarDetalhesDoMes('${month}')">
                Ver registros detalhados
            </button>
        </div>
    `;

        // Inserir o conteúdo no popup
        detailContent.innerHTML = contentHTML;

        // Exibir o popup
        detailContainer.style.display = 'block';
    }

    // Função para fechar o popup
    function closeDetailPopup() {
        document.getElementById('detailContainer').style.display = 'none';
    }

    // Função para buscar detalhes adicionais via AJAX
    function buscarDetalhesDoMes(month) {
        // Aqui você implementaria sua chamada AJAX real
        const detailContent = document.getElementById('detailContent');

        // Adicionar indicador de carregamento
        detailContent.innerHTML += `
        <div class="mt-3 loading-indicator">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Buscando detalhes para o mês...</p>
        </div>
    `;

        // Exemplo: você pode usar AJAX para buscar dados detalhados
        // Aqui está uma simulação:
        setTimeout(() => {
            // Esta é apenas uma simulação - substitua por seus dados reais
            const dadosDetalhados = {
                mes: month,
                registrosDiarios: [
                    { data: '05/' + month, registros: Math.floor(Math.random() * 10) + 1 },
                    { data: '12/' + month, registros: Math.floor(Math.random() * 10) + 1 },
                    { data: '19/' + month, registros: Math.floor(Math.random() * 10) + 1 },
                    { data: '26/' + month, registros: Math.floor(Math.random() * 10) + 1 }
                ]
            };

            // Atualizar o popup com os dados detalhados
            atualizarDetalhesPopup(dadosDetalhados);
        }, 1000);
    }

    function atualizarDetalhesPopup(dados) {
        const detailContent = document.getElementById('detailContent');

        // Remover o indicador de carregamento
        const loadingIndicator = detailContent.querySelector('.loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }

        // Criar tabela com os dados diários
        let htmlDetalhes = `
        <div class="mt-4">
            <h5>Registros diários</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Registros</th>
                    </tr>
                </thead>
                <tbody>
    `;

        dados.registrosDiarios.forEach(dia => {
            htmlDetalhes += `
            <tr>
                <td>${dia.data}</td>
                <td>${dia.registros}</td>
            </tr>
        `;
        });

        htmlDetalhes += `
                </tbody>
            </table>
        </div>
    `;

        // Adicionar a tabela ao conteúdo
        detailContent.innerHTML += htmlDetalhes;
    }

</script>
<img hidden id="pPictureModal">
</body>
</html>