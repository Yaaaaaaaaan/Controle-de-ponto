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
$daysData = []; // Array para armazenar os dias de cada mês

foreach ($pointControlData as $row) {
    $month = $row['month'];
    $labels[] = $month;
    $dataPoints[] = $row['count'];

    // Se ainda não tem dados para este mês, cria um array vazio
    if (!isset($daysData[$month])) {
        $daysData[$month] = [];
    }

    // Adiciona todos os dias nos dados deste mês (da propriedade 'dias')
    if (isset($row['dias']) && is_array($row['dias'])) {
        foreach ($row['dias'] as $diaInfo) {
            if (isset($diaInfo['day'])) {
                // Adiciona o dia como chave e a contagem como valor
                $daysData[$month][] = [
                    'dia' => $diaInfo['day'],
                    'description' => $diaInfo['description'],
                    'contagem' => $diaInfo['day_count']
                ];
            }
        }
    }
}

echo "<script>console.log('Formato de daysData:', " . json_encode($daysData) . ");</script>";

?>

<html>
<head>
    <style>
        #chartContainer {
            width: auto;
            height: 60vh;
            margin: auto;
            position: relative;
        }

        canvas {
            max-width: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }

        #chartContainer {
            width: auto;
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
                padding: 0;
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
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 100%;
            margin: 0 auto;
            height: 350px;
        }

        .badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .badge-card .profile-pic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            margin: 0 auto 15px;
            display: block;
        }

        .badge-card .user-full-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 6px;
            text-align: center;
        }

        .badge-card .user-nickname {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 6px;
        }

        .badge-card .user-email {
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

        /* Estilos para o card de detalhes */
        #detailCard {
            display: none;
        }
        .detailCard{
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 100%;
            margin: 0 auto;
            overflow-y: auto;
            height: 350px;
        }

        .detailCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Novo estilo para o cabeçalho fixo */
        .detail-card-header {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            z-index: 10;
            width: 100%;
            font-weight: bold;
        }

        .month-title {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
        }

        .detail-icon {
            font-size: 50px;
            text-align: center;
            margin: 15px auto;
            display: block;
            color: #0d6efd;
        }

        .detail-summary {
            text-align: center;
            font-size: 18px;
            margin-bottom: 25px;
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
            <!-- Card do usuário -->
            <div id="userCard" class="badge-card">
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

            <!-- Card de detalhes - mesma aparência que o badge-card -->
            <div id="detailCard" class="detailCard">
                <!-- Botão para voltar -->
                <button class="btn-close" onclick="voltarParaUsuario()"></button>

                <!-- Título do mês -->
                <div class="py-4 text-center mt-4 pt-1">
                    <h2 id="monthName"></h2>
                </div>
                <!-- Total de registros do mês vigente -->
                <p class="lead">Total de registros: <text id="monthTotal">0</text></p>


                <!-- Área para informações detalhadas -->
                <div id="detailContent">
                    <!-- Aqui serão inseridos os detalhes do mês via JavaScript -->
                </div>

                <!-- Botão para ver mais detalhes -->
                <button id="detailButton" class="btn btn-outline-secondary badge-action-btn mt-3" onclick="">
                    Ver registros detalhados
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const daysData = <?php echo json_encode($daysData); ?>;
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

                mostrarCardDetalhes(month, value);
            }
        }
    });

    // Função para exibir o card de detalhes e ocultar o card do usuário
    function mostrarCardDetalhes(month, value) {
        // Ocultar o card do usuário
        document.getElementById('userCard').style.display = 'none';

        // Obter o card de detalhes
        const detailCard = document.getElementById('detailCard');
        const monthNameElement = document.getElementById('monthName');
        const monthTotalElement = document.getElementById('monthTotal');
        const detailButtonElement = document.getElementById('detailButton');

        // Converter número do mês para nome do mês, se aplicável
        let monthName = month;
        if (month) {
            // Extrair o mês de uma string no formato "YYYY-MM" ou similar
            let monthValue = month;
            let yearValue;
            // Verificar se está no formato YYYY-MM e extrair apenas o mês
            if (month.includes('-')) {
                monthValue = month.split('-')[1]; // Pega o que vem depois do hífen
                yearValue = month.split('-')[0]; // Pega o que vem antes do hífen
            }

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

            monthName = monthNames[monthValue]+' de '+yearValue || month;
        }



        // Definir o nome do mês e o total
        monthNameElement.textContent = monthName;
        monthTotalElement.textContent = value.toLocaleString();

        // Configurar o botão para buscar detalhes específicos deste mês
        detailButtonElement.onclick = function() {
            buscarDetalhesDoMes(month);
        };

        // Limpar qualquer conteúdo de detalhes anterior
        document.getElementById('detailContent').innerHTML = '';

        // Exibir o card de detalhes
        detailCard.style.display = 'block';
    }

    // Função para voltar ao card do usuário
    function voltarParaUsuario() {
        // Ocultar o card de detalhes
        document.getElementById('detailCard').style.display = 'none';

        // Exibir o card do usuário
        document.getElementById('userCard').style.display = 'block';
    }


    function atualizarDetalhesCard(monthYearObj) {
        console.log("Chamando atualizarDetalhesCard com:", monthYearObj);

        // Extrair a string do mês
        const monthYearStr = monthYearObj.mes;

        if (!monthYearStr || !monthYearStr.includes('-')) {
            console.error("Formato de mês inválido:", monthYearStr);
            return;
        }

        const [year, month] = monthYearStr.split('-');

        // Obter dados do mês do daysData
        const monthData = daysData[monthYearStr] || [];

        // Calcular total de registros
        let totalRegistros = 0;
        if (monthData.length > 0) {
            totalRegistros = monthData.reduce((total, item) => total + parseInt(item.day_count || item.contagem || 0), 0);
        }

        // Determinar o nome do mês e ano
        const date = new Date(parseInt(year), parseInt(month) - 1, 1);
        const monthName = date.toLocaleString('pt-BR', { month: 'long' });

        // Criar tabela com os dias e descrições
        let tableRows = '';

        if (monthData.length > 0) {
            monthData.forEach(item => {
                const dia = String(item.dia || item.day).padStart(2, '0');
                // Usar a descrição em vez da contagem
                const descricao = item.description;
                tableRows += `<tr>
                <td>${dia}/${month}/${year}</td>
                <td>${descricao}</td>
            </tr>`;
            });
        }

        // Atualizar o card de detalhes
        const detailCard = document.getElementById('detailCard');
        detailCard.innerHTML = `
    <div class="card-header">
        <h5 class="card-title">${monthName.charAt(0).toUpperCase() + monthName.slice(1)} de ${year}</h5>
        <h6>Total de registros: ${totalRegistros}</h6>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                ${tableRows}
            </tbody>
        </table>
    </div>
`;

        // Mostrar o card
        detailCard.style.display = 'block';
    }

    // Função para buscar detalhes do mês
    function buscarDetalhesDoMes(month) {
        // Obter o contêiner de detalhes
        const detailContent = document.getElementById('detailContent');

        // Adicionar indicador de carregamento
        detailContent.innerHTML = ``;

        // Desativar o botão enquanto carrega
        const detailButton = document.getElementById('detailButton');
        if (detailButton) {
            detailButton.disabled = true;
            detailButton.textContent = 'Carregando...';
        }

        // Processar os dados localmente
        setTimeout(() => {
            // Verificar se temos dados para este mês
            const diasDoMes = daysData[month] || [];
            console.log('Dados do mês:', month, diasDoMes); // Depuração

            // Criar estrutura de dados para exibição
            const dadosDetalhados = {
                mes: month,
                registrosDiarios: []
            };

            console.log(dadosDetalhados);

            // Processar os dados dos dias
            if (diasDoMes.length > 0) {
                // Verificar se os dias são objetos com propriedades 'dia' e 'contagem'
                if (diasDoMes[0] && (diasDoMes[0].dia !== undefined || diasDoMes[0].day !== undefined)) {
                    // Os dias são objetos estruturados
                    diasDoMes.forEach(diaInfo => {
                        // Obter o dia e descrição, considerando diferentes nomes de propriedades
                        const dia = diaInfo.dia || diaInfo.day || '';
                        // Usar a descrição em vez da contagem
                        const descricao = diaInfo.description;

                        // Formatar a data
                        const [ano, mes] = month.split('-');
                        const dataFormatada = dia + '/' + mes + '/' + ano;

                        dadosDetalhados.registrosDiarios.push({
                            data: dataFormatada,
                            descricao: descricao  // Adicionando a descrição aos dados
                        });
                    });
                } else {
                    // Formato antigo: array simples de dias
                    const contagem = {};
                    diasDoMes.forEach(dia => {
                        if (dia !== null) {
                            if (!contagem[dia]) {
                                contagem[dia] = 0;
                            }
                            contagem[dia]++;
                        }
                    });

                    // Transformar a contagem em registros diários
                    Object.keys(contagem).forEach(dia => {
                        const [ano, mes] = month.split('-');
                        const dataFormatada = dia + '/' + mes + '/' + ano;

                        dadosDetalhados.registrosDiarios.push({
                            data: dataFormatada,
                            registros: contagem[dia],
                            descricao: 'Sem descrição'  // Para o formato antigo, não temos descrição
                        });
                    });
                }
            }

            // Se não houver registros, adicionar uma mensagem
            if (dadosDetalhados.registrosDiarios.length === 0) {
                dadosDetalhados.registrosDiarios.push({
                    data: 'Sem registros',
                    registros: 0,
                    descricao: 'Sem descrição'
                });
            }

            // Ordenar por dia
            dadosDetalhados.registrosDiarios.sort((a, b) => {
                if (a.data === 'Sem registros') return -1;
                if (b.data === 'Sem registros') return 1;

                const diaA = parseInt(a.data.split('/')[0]);
                const diaB = parseInt(b.data.split('/')[0]);
                return diaA - diaB;
            });

            //console.log('Dados processados:', dadosDetalhados); // Depuração

            // Atualizar o card com os dados detalhados
            atualizarDetalhesCard(dadosDetalhados);

            // Reativar o botão
            if (detailButton) {
                detailButton.disabled = false;
                detailButton.textContent = 'Atualizar detalhes';
            }
        }, 500);
    }








</script>
<img hidden id="pPictureModal">
</body>
</html>