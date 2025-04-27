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
        const description = labels[index];
        mostrarDetalhes(description);
    }

    function mostrarDetalhes(description) {
        const detailCard = document.getElementById('detailCard');
        const detailTitle = document.getElementById('detailTitle');
        const detailTotal = document.getElementById('detailTotal');
        const detailContent = document.getElementById('detailContent');

        // Atualiza título e total
        detailTitle.textContent = description;
        detailTotal.textContent = dataPoints[labels.indexOf(description)];

        // Limpa conteúdo anterior
        detailContent.innerHTML = '';

        // Adiciona as linhas da tabela
        const registros = detalhes[description] || [];
        registros.forEach(registro => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${registro.data}</td>
                <td>${description}</td>
                <td>${registro.nome}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarRegistro(${registro.id})">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </td>
            `;
            detailContent.appendChild(tr);
        });

        // Mostra o card
        detailCard.style.display = 'block';
    }

    // Função global para editar registro
    window.editarRegistro = function(id) {
        // TODO: Implementar a lógica de edição futuramente.
        console.log('Editar registro:', id);
    };

});