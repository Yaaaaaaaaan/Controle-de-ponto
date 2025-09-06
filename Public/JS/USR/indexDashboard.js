// ========================
// 📁 indexDashboard.js
// ========================
import { getPointControlByUserId } from '../indexedDB/Model.js';


// --- Variáveis Globais ---
let presenceChart;
let daysData = {};
let currentLabels = [];
let currentDataPoints = [];

// --- DEFINIÇÃO DAS FUNÇÕES AUXILIARES (PRIMEIRO) ---

/**
 * Processa os registros brutos do IndexedDB e retorna os dados formatados.
 */
function processPointControlRecords(records) {
    const monthlyRecords = {}; // Para detalhes ao clicar
    const monthlyDaysCount = new Map(); // Para contar os dias únicos por mês

    // 1. Processa os dados recebidos e agrupa por mês
    records.forEach(record => {
        if (!record.dateIn) return;

        const parts = record.dateIn.split('-');
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const date = new Date(year, month, parseInt(parts[2], 10));
        const monthKey = `${year}-${String(month + 1).padStart(2, '0')}`;

        if (!monthlyRecords[monthKey]) {
            monthlyRecords[monthKey] = [];
            monthlyDaysCount.set(monthKey, new Set());
        }
        monthlyRecords[monthKey].push(record);
        monthlyDaysCount.get(monthKey).add(date.getDate());
    });

    // 2. Gera programaticamente os rótulos para os últimos 3 meses
    const labels = [];
    const today = new Date();
    for (let i = 2; i >= 0; i--) {
        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        labels.push(`${year}-${month}`);
    }

    // 3. Mapeia os dados para os rótulos gerados (usando 0 se não houver dados)
    const dataPoints = labels.map(label => {
        const daysSet = monthlyDaysCount.get(label);
        return daysSet ? daysSet.size : 0;
    });

    return { labels, dataPoints, daysData: monthlyRecords };
}

/**
 * Atualiza a instância do gráfico Chart.js com os novos dados.
 */
function updateChart(newLabels, newDataPoints) {
    currentLabels = newLabels;
    currentDataPoints = newDataPoints;

    if (presenceChart) {
        presenceChart.data.labels = newLabels;
        presenceChart.data.datasets[0].data = newDataPoints;
        presenceChart.update();
        console.log('4. Gráfico atualizado com sucesso com os novos dados.');
    }
}

/**
 * Exibe o card com os detalhes do mês clicado.
 */
function mostrarCardDetalhes(month, value) {
    document.getElementById('userCard').style.display = 'none';
    const detailCard = document.getElementById('detailCard');
    const monthNameElement = document.getElementById('monthName');
    const monthTotalElement = document.getElementById('monthTotal');

    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year, 10), parseInt(monthNum, 10) - 1, 1);
    const monthName = date.toLocaleString('pt-BR', { month: 'long', year: 'numeric' });

    monthNameElement.textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);
    monthTotalElement.textContent = `${value} dias`;
    detailCard.style.display = 'block';

    const detailContent = document.getElementById('detailContent');
    const recordsForMonth = daysData[month] || [];
    let tableRows = '';

    if (recordsForMonth.length > 0) {
        recordsForMonth.sort((a,b) => new Date(a.dateIn) - new Date(b.dateIn)).forEach(item => {
            // --- CORREÇÃO DE FUSO HORÁRIO APLICADA AQUI TAMBÉM ---
            const parts = item.dateIn.split('-');
            const itemDate = new Date(parts[0], parts[1] - 1, parts[2]);
            const formattedDate = itemDate.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            // ----------------------------------------------------
            tableRows += `<tr><td>${formattedDate}</td><td>${item.status}</td></tr>`;
        });
    } else {
        tableRows = `<tr><td colspan="2">Nenhum registro encontrado.</td></tr>`;
    }

    detailContent.innerHTML = `
        <table class="table table-striped table-hover">
            <thead><tr><th>Data</th><th>Status</th></tr></thead>
            <tbody>${tableRows}</tbody>
        </table>`;
}

// Anexa a função de voltar ao objeto window para ser acessível pelo HTML
window.voltarParaUsuario = function () {
    document.getElementById('detailCard').style.display = 'none';
    document.getElementById('userCard').style.display = 'block';
};

// --- FUNÇÕES DE ORQUESTRAÇÃO (SEGUNDO) ---

/**
 * Inicializa a instância do Chart.js com uma estrutura vazia.
 */
function initializeChart() {
    if (presenceChart) return;

    const ctx = document.getElementById('presence');
    if (!ctx) {
        console.error("Elemento 'presence' do canvas não encontrado.");
        return;
    }

    presenceChart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Dias presente', data: [], /* ...estilos... */ }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const dataIndex = elements[0].index;
                    mostrarCardDetalhes(currentLabels[dataIndex], currentDataPoints[dataIndex]);
                }
            }
        }
    });
}

/**
 * Orquestra o processo de carregar os dados e atualizar o gráfico.
 */
async function loadAndProcessData(userId) {
    try {
        console.log('1. Buscando dados para o userId:', userId);
        const records = await getPointControlByUserId(userId);
        console.log('2. Registros brutos retornados do IndexedDB:', records);

        if (!records || records.length === 0) {
            console.log("Nenhum registro de ponto encontrado. Limpando o gráfico.");
            updateChart([], []);
            return;
        }

        const chartData = processPointControlRecords(records);
        console.log('3. Dados processados para o gráfico:', chartData);

        daysData = chartData.daysData;
        updateChart(chartData.labels, chartData.dataPoints);

    } catch (error) {
        console.error("Erro fatal ao carregar e processar os dados do dashboard:", error);
    }
}

export function initializeDashboard() {
    document.addEventListener('userDataReady', async (event) => {
        const receivedUserId = event.detail.userId;
        console.log('indexDashboard.js: Event "userDataReady" detected. userId:', receivedUserId);

        if (!receivedUserId) {
            console.error("Dashboard could not start: User ID was not received in the event.");
            return;
        }

        const userId = parseInt(receivedUserId, 10);

        initializeChart();
        await loadAndProcessData(userId);
    });
}

// --- PONTO DE ENTRADA DA APLICAÇÃO (POR ÚLTIMO) ---

document.addEventListener('userDataReady', async (event) => {
    const receivedUserId = event.detail.userId;
    console.log('indexDashboard.js: Evento "userDataReady" detectado. userId:', receivedUserId);

    if (!receivedUserId) {
        console.error("Dashboard não pôde iniciar: ID do usuário não foi recebido no evento.");
        return;
    }

    const userId = parseInt(receivedUserId, 10);

    initializeChart();
    await loadAndProcessData(userId);
});