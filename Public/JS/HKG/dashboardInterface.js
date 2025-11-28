// Public/JS/HKG/hkgDashboardInterface.js

let chartInstance = null;

export function renderChart(dataPoints, labels, onClickCallback) {
    const ctx = document.getElementById('pointControlUsersData');
    if (!ctx) return;

    // Cores padronizadas
    const coresMap = {
        'Verificação pendente': 'rgb(173,181,189)',
        'Já verificado': 'rgb(32,201,151)',
        'Recusado': 'rgb(253,126,20)'
    };
    const cores = labels.map(l => coresMap[l] || '#333');

    const config = {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: dataPoints,
                backgroundColor: cores,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            onClick: (evt, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    onClickCallback(labels[index]);
                }
            }
        }
    };

    if (chartInstance) chartInstance.destroy();
    chartInstance = new Chart(ctx, config);
}

export function showDetailsPanel(category, records, onEditClick) {
    const contentChart = document.getElementById('contentChart');
    const detailCard = document.getElementById('detailCard');
    
    // Atualiza Textos
    document.getElementById('detailTitle').textContent = category;
    document.getElementById('detailTotal').textContent = records.length;
    
    // Renderiza Tabela
    renderTable(records, onEditClick);

    // Animação CSS
    if (!contentChart.classList.contains('shrink')) {
        contentChart.classList.add('shrink');
        setTimeout(() => {
            detailCard.classList.add('expand');
        }, 50);
    }
}

export function closeDetailsPanel() {
    const contentChart = document.getElementById('contentChart');
    const detailCard = document.getElementById('detailCard');
    const searchInput = document.getElementById('searchDashboard');

    detailCard.classList.remove('expand');
    contentChart.classList.remove('shrink');
    if (searchInput) searchInput.value = '';
}

export function renderTable(records, onEditClick) {
    const tbody = document.getElementById('detailContent');
    tbody.innerHTML = '';

    if (!records || records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum registro.</td></tr>';
        return;
    }

    records.forEach(reg => {
        let d = reg.dateIn;
        try { d = d.split('-').reverse().join('/'); } catch(e){}
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${d}</td>
            <td>${reg.status}</td>
            <td>${reg.nome}</td>
            <td><button class="btn btn-primary btn-sm btn-edit-action">Editar</button></td>
        `;
        
        // Bind do evento de clique no botão
        const btn = tr.querySelector('.btn-edit-action');
        btn.addEventListener('click', () => onEditClick(reg));
        
        tbody.appendChild(tr);
    });
}

export function openEditModal(data) {
    document.getElementById('inputId').value = data.userId;
    document.getElementById('inputCod').value = data.cod;
    document.getElementById('inputNome').textContent = data.nome;
    
    // Formatar data para input (YYYY-MM-DD para HTML5 date ou DD/MM/YYYY para texto)
    let dateVal = data.dateIn; 
    if(dateVal.includes('-')) {
        try { dateVal = dateVal.split('-').reverse().join('/'); } catch(e){}
    }
    document.getElementById('inputData').value = dateVal;
    
    let statusVal = "1";
    if (data.status === 'Já verificado') statusVal = "2";
    if (data.status === 'Recusado') statusVal = "3";
    document.getElementById('inputDescricao').value = statusVal;

    const modal = new bootstrap.Modal(document.getElementById('editarModal'));
    modal.show();
}

export function closeEditModal() {
    const el = document.getElementById('editarModal');
    const modal = bootstrap.Modal.getInstance(el);
    if (modal) modal.hide();
}

export function showMessage(msg, type='danger') {
    const container = document.getElementById('contentChart');
    container.innerHTML = `<div class="alert alert-${type} m-5">${msg}</div>`;
}