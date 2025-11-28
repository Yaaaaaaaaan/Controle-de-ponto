/*// Variáveis globais
let labels = [];
let dataPoints = [];
let detalhes = {};
let chartInstance = null;
let categoriaAtiva = null; 
let listaCategoriaAtual = []; 
let estadoOrdenacao = { coluna: 'data', direcao: 'desc' };

document.addEventListener('DOMContentLoaded', async function() {
    console.log("Dashboard Iniciado");
    await loadDataFromAPI();
    configurarPesquisa();
    
    // NOVO: Configura o envio do formulário de edição
    const form = document.getElementById('formEditar');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});

// === NOVA LÓGICA DE ENVIO DO FORMULÁRIO ===
async function handleFormSubmit(event) {
    event.preventDefault(); 
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Salvando...';

    const formData = new FormData(form);

    try {
        // --- CORREÇÃO AQUI ---
        // Removemos "/controle-de-ponto". 
        // A barra "/" no início diz: "comece da raiz do site (localhost:8080)"
        const response = await fetch('/Public/Api/updateHousekeep.php', {
            method: 'POST',
            body: formData
        });

        if (response.status === 404) {
            // Dica de depuração no console
            console.error("Caminho testado: /Public/Api/updateHousekeep.php");
            throw new Error('API não encontrada (404).');
        }
        
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            const modalEl = document.getElementById('editarModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            await loadDataFromAPI();

            if (categoriaAtiva) {
                if (!detalhes[categoriaAtiva] || detalhes[categoriaAtiva].length === 0) {
                    fecharDetalhes();
                } else {
                    mostrarDetalhes(categoriaAtiva);
                }
            }
        } else {
            alert('Erro ao salvar: ' + result.message);
        }

    } catch (error) {
        console.error('Erro detalhado:', error);
        alert('Erro ao processar: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// === RESTO DAS FUNÇÕES (IGUAL AO ANTERIOR) ===

async function loadDataFromAPI() {
    const url = '../../Api/housekeep.php';
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Erro na API');
        const registros = await response.json();
        
        // Se estiver vazio
        if (!registros || registros.length === 0) {
            // Se tinha gráfico, destroi
            if(chartInstance) chartInstance.destroy();
            document.getElementById('contentChart').innerHTML = '<h4 class="text-center mt-5">Nenhum dado.</h4>';
            return;
        }

        processarDados(registros);
        initChart();
    } catch (error) {
        console.error("Erro:", error);
    }
}

function processarDados(registros) {
    detalhes = { 'Verificação pendente': [], 'Já verificado': [], 'Recusado': [] };
    registros.forEach(reg => {
        let status = reg.status || 'Verificação pendente';
        if (!detalhes[status]) detalhes[status] = [];
        detalhes[status].push(reg);
    });
    labels = Object.keys(detalhes).filter(k => detalhes[k].length > 0);
    dataPoints = labels.map(k => detalhes[k].length);
}

function initChart() {
    const ctx = document.getElementById('pointControlUsersData');
    if (!ctx) return;

    const coresMap = {
        'Verificação pendente': 'rgb(173,181,189)',
        'Já verificado': 'rgb(32,201,151)',
        'Recusado': 'rgb(253,126,20)'
    };
    const cores = labels.map(l => coresMap[l] || '#333');

    const data = {
        labels: labels,
        datasets: [{
            data: dataPoints,
            backgroundColor: cores,
            hoverOffset: 10
        }]
    };

    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            onClick: handleChartClick
        }
    };

    if (chartInstance) {
        // Apenas atualiza os dados para ter animação suave
        chartInstance.data = data;
        chartInstance.update();
    } else {
        chartInstance = new Chart(ctx, config);
    }
}

function handleChartClick(evt, elements) {
    if (!elements || elements.length === 0) return;
    const index = elements[0].index;
    const categoriaClicada = labels[index];
    const contentChart = document.getElementById('contentChart');

    if (contentChart.classList.contains('shrink')) {
        if (categoriaAtiva === categoriaClicada) fecharDetalhes();
        else mostrarDetalhes(categoriaClicada);
    } else {
        mostrarDetalhes(categoriaClicada);
    }
}

function fecharDetalhes() {
    const contentChart = document.getElementById('contentChart');
    const detailCard = document.getElementById('detailCard');
    detailCard.classList.remove('expand');
    contentChart.classList.remove('shrink');
    categoriaAtiva = null;
    const searchInput = document.getElementById('searchDashboard');
    if(searchInput) searchInput.value = '';
}

function mostrarDetalhes(categoria) {
    categoriaAtiva = categoria;
    const contentChart = document.getElementById('contentChart');
    const detailCard = document.getElementById('detailCard');
    const searchInput = document.getElementById('searchDashboard');

    // Se não for uma atualização automática (o usuário está digitando ou trocando categoria), limpa pesquisa
    // Mas se for refresh pós-salvamento, mantém o estado seria ideal, mas por simplicidade limpamos ou re-filtramos
    // Aqui vamos apenas atualizar os dados base
    
    document.getElementById('detailTitle').textContent = categoria;
    document.getElementById('detailTotal').textContent = detalhes[categoria] ? detalhes[categoria].length : 0;
    
    listaCategoriaAtual = detalhes[categoria] || [];
    
    // Reseta ordenação apenas se mudou de categoria visualmente
    // (Poderíamos melhorar isso, mas ok para agora)
    // estadoOrdenacao = { coluna: 'data', direcao: 'desc' };
    
    aplicarFiltrosEOrdenacao();

    if (!contentChart.classList.contains('shrink')) {
        contentChart.classList.add('shrink');
        setTimeout(() => { detailCard.classList.add('expand'); }, 50);
    }
}

function aplicarFiltrosEOrdenacao() {
    const searchInput = document.getElementById('searchDashboard');
    const termo = searchInput ? searchInput.value.toLowerCase().trim() : '';

    let listaFiltrada = listaCategoriaAtual.filter(reg => {
        if (!termo) return true;
        return reg.nome.toLowerCase().includes(termo);
    });

    listaFiltrada.sort((a, b) => {
        let valA, valB;
        if (estadoOrdenacao.coluna === 'data') {
            valA = new Date(a.dateIn);
            valB = new Date(b.dateIn);
        } else if (estadoOrdenacao.coluna === 'nome') {
            valA = a.nome.toLowerCase();
            valB = b.nome.toLowerCase();
        }
        if (valA < valB) return estadoOrdenacao.direcao === 'asc' ? -1 : 1;
        if (valA > valB) return estadoOrdenacao.direcao === 'asc' ? 1 : -1;
        return 0;
    });

    renderizarTabela(listaFiltrada);
}

function renderizarTabela(lista) {
    const tbody = document.getElementById('detailContent');
    tbody.innerHTML = '';

    if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Nenhum registro encontrado.</td></tr>';
        return;
    }

    lista.forEach(reg => {
        let d = reg.dateIn;
        try { d = d.split('-').reverse().join('/'); } catch(e){}
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${d}</td>
            <td>${reg.status}</td>
            <td>${reg.nome}</td>
            <td><button class="btn btn-primary btn-sm" onclick="editarRegistro(${reg.cod}, '${reg.nome}', '${reg.dateIn}', '${reg.status}', ${reg.userId})">Editar</button></td>
        `;
        tbody.appendChild(tr);
    });
}

window.ordenarTabela = function(coluna) {
    if (estadoOrdenacao.coluna === coluna) {
        estadoOrdenacao.direcao = estadoOrdenacao.direcao === 'asc' ? 'desc' : 'asc';
    } else {
        estadoOrdenacao.coluna = coluna;
        estadoOrdenacao.direcao = 'asc';
    }
    // Atualiza ícones (simplificado)
    document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '⇵');
    const th = document.querySelector(`th[onclick="ordenarTabela('${coluna}')"] .sort-icon`);
    if(th) th.textContent = estadoOrdenacao.direcao === 'asc' ? '▲' : '▼';

    aplicarFiltrosEOrdenacao();
}

function configurarPesquisa() {
    const searchInput = document.getElementById('searchDashboard');
    if(searchInput) {
        searchInput.addEventListener('input', () => aplicarFiltrosEOrdenacao());
    }
}

window.editarRegistro = function(cod, nome, data, status, userId) {
    document.getElementById('inputId').value = userId;
    document.getElementById('inputCod').value = cod;
    document.getElementById('inputNome').textContent = nome;
    
    // Converte YYYY-MM-DD para DD/MM/YYYY para exibir no input
    if(data.includes('-')) {
        try { data = data.split('-').reverse().join('/'); } catch(e){}
    }
    document.getElementById('inputData').value = data;
    
    let val = "1";
    if(status == 'Já verificado') val = "2";
    if(status == 'Recusado') val = "3";
    document.getElementById('inputDescricao').value = val;

    const modal = new bootstrap.Modal(document.getElementById('editarModal'));
    modal.show();
}

window.fecharDetalhes = fecharDetalhes;*/