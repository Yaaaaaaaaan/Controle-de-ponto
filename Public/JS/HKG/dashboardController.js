import * as Service from './dashboardService.js';
import * as UI from './dashboardInterface.js';

// Auto-inicialização
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

async function init() {
    await loadData();
    setupEventListeners();
}

async function loadData() {
    try {
        const data = await Service.fetchDashboardData();
        if (!data || data.length === 0) {
            UI.showMessage("Nenhum dado encontrado.", "info");
            return;
        }
        
        processData(data);
        
        // Dados processados, renderiza gráfico
        const labels = Object.keys(state.details).filter(k => state.details[k].length > 0);
        const points = labels.map(k => state.details[k].length);
        
        UI.renderChart(points, labels, handleCategoryClick);
        
        // Se tinha categoria aberta antes do reload, tenta reabrir
        if (state.activeCategory) {
            if (state.details[state.activeCategory]?.length > 0) {
                UI.showDetailsPanel(state.activeCategory, state.details[state.activeCategory], handleEditClick);
            } else {
                UI.closeDetailsPanel();
                state.activeCategory = null;
            }
        }

    } catch (error) {
        UI.showMessage("Erro ao carregar dashboard: " + error.message);
    }
}

function processData(records) {
    state.allRecords = records;
    state.details = { 'Verificação pendente': [], 'Já verificado': [], 'Recusado': [] };
    
    records.forEach(reg => {
        let status = reg.status || 'Verificação pendente';
        if (!state.details[status]) state.details[status] = [];
        state.details[status].push(reg);
    });
}

function handleCategoryClick(category) {
    const isPanelOpen = document.getElementById('contentChart').classList.contains('shrink');
    
    if (isPanelOpen && state.activeCategory === category) {
        UI.closeDetailsPanel();
        state.activeCategory = null;
    } else {
        state.activeCategory = category;
        UI.showDetailsPanel(category, state.details[category], handleEditClick);
    }
}

function handleEditClick(recordData) {
    UI.openEditModal(recordData);
}

function setupEventListeners() {
    // Form Submit
    const form = document.getElementById('formEditar');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.disabled = true; btn.innerText = "Salvando...";

            try {
                const formData = new FormData(form);
                const result = await Service.updateRegistry(formData);
                
                if (result.success) {
                    UI.closeEditModal();
                    await loadData(); // Recarrega tudo
                } else {
                    alert("Erro: " + result.message);
                }
            } catch (error) {
                alert("Erro ao salvar.");
            } finally {
                btn.disabled = false; btn.innerText = originalText;
            }
        });
    }

    // Pesquisa
    const searchInput = document.getElementById('searchDashboard');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            if (!state.activeCategory) return;
            const term = e.target.value.toLowerCase();
            const list = state.details[state.activeCategory];
            const filtered = list.filter(r => 
                r.nome.toLowerCase().includes(term) || 
                r.dateIn.includes(term)
            );
            UI.renderTable(filtered, handleEditClick);
        });
    }
}