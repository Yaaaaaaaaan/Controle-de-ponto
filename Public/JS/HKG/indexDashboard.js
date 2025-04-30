document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pointControlUsersData').getContext('2d');

    const data = {
        labels: labels,
        datasets: [{
            label: " ",
            data: dataPoints,
            backgroundColor: [
                'rgb(173,181,189)',
                'rgb(32,201,151)',
                'rgb(253,126,20)'
            ],
            hoverOffset: 4
        }]
    };

    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                }
            },
            onClick: handleChartClick
        }
    };

    const chart = new Chart(ctx, config);

    function handleChartClick(event, elements) {
        if (!elements || elements.length === 0) return;
        const index = elements[0].index;
        const categoriaSelecionada = labels[index]; // Renomeado
        mostrarDetalhes(categoriaSelecionada);
    }

    function mostrarDetalhes(categoriaSelecionada) {
        const chartContainer = document.getElementById('chartContainer');
        const detailCard = document.getElementById('detailCard');
        const detailTitle = document.getElementById('detailTitle');
        const detailTotal = document.getElementById('detailTotal');
        const detailContent = document.getElementById('detailContent');

        // Atualiza dados
        detailTitle.textContent = categoriaSelecionada;
        detailTotal.textContent = dataPoints[labels.indexOf(categoriaSelecionada)];
        detailContent.innerHTML = '';

        const registros = detalhes[categoriaSelecionada] || [];
        const registrosPorId = {}; // Novo objeto para mapear IDs para registros

        registros.forEach(registro => {
            registrosPorId[registro.id] = registro; // Mapeia o ID para o registro

            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${registro.data}</td>
            <td>${categoriaSelecionada}</td>
            <td>${registro.nome}</td>
            <td>
                <button class="btn btn-primary btn-sm" onclick="editarRegistro(${registro.id}, ${registro.cod}, '${registro.data}', '${registro.nome}', '${registro.descricao}')">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </td>
        `;
            detailContent.appendChild(tr);
        });

        // Armazena o mapeamento para uso posterior
        detailCard.registrosPorId = registrosPorId;

        // Ativa animações
        chartContainer.classList.add('shrink');
        detailCard.classList.add('expand');
        setTimeout(() => {
            detailCard.style.display = 'block';
            detailCard.classList.add('expand');
        }, 480);
    }

    function atualizarTabela(registros, categoriaSelecionada) { // Renomeado
        tbody.innerHTML = '';
        registros.forEach(registro => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${registro.data}</td>
                <td>${categoriaSelecionada}</td>
                <td>${registro.nome}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarRegistro(${registro.id}, ${registro.cod}, '${registro.data}', '${registro.nome}', '${registro.descricao}')">
                        Editar
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    const estadoOrdenacao = {
        data: true,
        nome: true
    };

    function ordenarTabela(coluna) {
        const tbody = document.getElementById('detailContent');
        const linhas = Array.from(tbody.querySelectorAll('tr'));
        const crescente = estadoOrdenacao[coluna];

        linhas.sort((a, b) => {
            let valorA, valorB;

            if (coluna === 'data') {
                valorA = new Date(formatarDataISO(a.cells[0].textContent));
                valorB = new Date(formatarDataISO(b.cells[0].textContent));
            } else if (coluna === 'nome') {
                valorA = a.cells[2].textContent.trim().toLowerCase();
                valorB = b.cells[2].textContent.trim().toLowerCase();
            }

            if (valorA < valorB) return crescente ? -1 : 1;
            if (valorA > valorB) return crescente ? 1 : -1;
            return 0;
        });

        linhas.forEach(linha => tbody.appendChild(linha));
        estadoOrdenacao[coluna] = !estadoOrdenacao[coluna];
    }

    function formatarDataISO(dataString) {
        const partes = dataString.trim().split('/');
        return `${partes[2]}-${partes[1]}-${partes[0]}`;
    }

    function encontrarRegistroPorId(id) {
        for (const categoria in detalhes) {
            const lista = detalhes[categoria];
            const encontrado = lista.find(item => item.id === id);
            if (encontrado) {
                console.log("Registro encontrado:", encontrado); // Depuração!
                return encontrado;
            }
        }
        return null;
    }

    function formatarDataParaExibir(data) {
        if (!data) return '';
        if (!(data instanceof Date)) {
            console.error("Não é um objeto Date:", data);
            return 'Não é Data';
        }
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const ano = data.getFullYear();
        return `${dia}/${mes}/${ano}`;
    }

    window.ordenarTabela = ordenarTabela;
    window.editarRegistro = function(id, cod, data, nome, descricao) {
        const registro = encontrarRegistroPorId(id);
        if (!registro) return;

        let descricaoValue = null;
        if (registro.descricao === 'Verificação pendente') {
            descricaoValue = 1;
        } else if (registro.descricao === 'Já verificado') {
            descricaoValue = 2;
        } else if (registro.descricao === 'Recusado') {
            descricaoValue = 3;
        }

        console.log("editarRegistro - id:", id);
        console.log("editarRegistro - cod:", cod);
        console.log("editarRegistro - data:", data);
        console.log("editarRegistro - nome:", nome);
        console.log("editarRegistro - descricao:", registro.descricao);  // Original
        console.log("editarRegistro - descricaoValue:", descricaoValue);

        document.getElementById('inputId').value = id;
        document.getElementById('inputCod').value = cod;
        document.getElementById('inputNome').textContent = nome;
        document.getElementById('inputData').value = data;
        document.getElementById('inputDescricao').value = descricaoValue;

        const modal = new bootstrap.Modal(document.getElementById('editarModal'));
        modal.show();
    };

    //  ***ADICIONEI***
    const inputPesquisa = document.querySelector('#detailCard .input-group input');
    const botaoPesquisar = document.querySelector('#detailCard .input-group button');
    const tbody = document.getElementById('detailContent');
    let registrosExibidos = [];

    function filtrarRegistros() {
        const termoPesquisa = inputPesquisa.value.trim().toLowerCase();
        const registrosFiltrados = registrosExibidos.filter(registro => {
            const dataFormatada = formatarDataParaExibir(new Date(registro.data)).toLowerCase();
            const nome = registro.nome.toLowerCase();
            return dataFormatada.includes(termoPesquisa) || nome.includes(termoPesquisa);
        });
        atualizarTabela(registrosFiltrados, document.getElementById('detailTitle').textContent); // ***ADICIONEI***
    }

    botaoPesquisar.addEventListener('click', filtrarRegistros);
    inputPesquisa.addEventListener('input', filtrarRegistros);
});