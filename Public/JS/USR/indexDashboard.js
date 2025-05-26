document.addEventListener('DOMContentLoaded', function() {
    // Agora as variáveis daysData, labels e dataPoints já foram definidas
    const ctx = document.getElementById('presence').getContext('2d');
 //ttttttttttttttggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggate fez isso
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

    const presence = new Chart(ctx, config);

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

    // Função para exibir o card de detalhes e ocultar o card do usuário
    window.mostrarCardDetalhes = function (month, value) {
        // Ocultar o card do usuário
        document.getElementById('userCard').style.display = 'none';

        // Obter o card de detalhes
        const detailCard = document.getElementById('detailCard');
        const monthNameElement = document.getElementById('monthName');
        const monthTotalElement = document.getElementById('monthTotal');

        let monthName = month;
        if (month) {
            let monthValue = month;
            let yearValue;
            if (month.includes('-')) {
                monthValue = month.split('-')[1];
                yearValue = month.split('-')[0];
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

            monthName = monthNames[monthValue] + ' de ' + yearValue || month;
        }

        monthNameElement.textContent = monthName;
        monthTotalElement.textContent = value.toLocaleString();

        document.getElementById('detailContent').innerHTML = '';
        detailCard.style.display = 'block';
        buscarDetalhesDoMes(month);
    };

    // Função para voltar ao card do usuário
    window.voltarParaUsuario = function () {
        document.getElementById('detailCard').style.display = 'none';
        document.getElementById('userCard').style.display = 'block';
    };


    function atualizarDetalhesCard(monthYearObj) {
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
                const descricao = item.description;
                tableRows += `<tr>
                <td>${dia}/${month}/${year}</td>
                <td>${descricao}</td>
            </tr>`;
            });
        }

        // Atualizar apenas o conteúdo do detailContent em vez do detailCard completo.
        const detailContent = document.getElementById('detailContent');
        detailContent.innerHTML = `
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

        // Atualizar o nome do mês e total no card existente
        document.getElementById('monthName').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1) + ' de ' + year;
        document.getElementById('monthTotal').textContent = totalRegistros;
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
            //console.log('Dados do mês:', month, diasDoMes); // Depuração

            // Criar estrutura de dados para exibição
            const dadosDetalhados = {
                mes: month,
                registrosDiarios: []
            };

            //console.log(dadosDetalhados);

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
        }, 0);
    }

});