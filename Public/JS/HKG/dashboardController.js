import * as Service from './dashboardService.js';
import * as UI from './dashboardInterface.js';

// 1. Estado Global do Módulo (Declarado no topo para evitar erros)
let state = {
    allRecords: [],
    details: {},
    activeCategory: null
};

// 2. EXPORTAR a função init para o Main.js conseguir chamá-la
export async function init() {
    console.log("HKG Dashboard Controller: Inicializando...");
    await loadData();
    setupEventListeners();
}

// 3. Funções Internas
async function loadData() {
    try {
        const data = await Service.fetchDashboardData();
        
        if (!data || data.length === 0) {
            UI.showMessage("Nenhum dado encontrado.", "info");
            return;
        }
        
        // Processa os dados para atualizar a variável 'state'
        processData(data);
        
        // Agora usa 'state' para renderizar o gráfico
        // A validação abaixo previne o erro "state is not defined" caso algo estranho aconteça
        if (!state.details) throw new Error("Erro de estado interno (details)");

        const labels = Object.keys(state.details).filter(k => state.details[k].length > 0);
        const points = labels.map(k => state.details[k].length);
        
        UI.renderChart(points, labels, handleCategoryClick);
        
        // Restaura painel se necessário (ex: após salvar edição)
        if (state.activeCategory) {
            const list = state.details[state.activeCategory];
            if (list && list.length > 0) {
                UI.showDetailsPanel(state.activeCategory, list, handleEditClick);
            } else {
                UI.closeDetailsPanel();
                state.activeCategory = null;
            }
        }

    } catch (error) {
        console.error("Erro no loadData:", error);
        UI.showMessage("Erro ao carregar dashboard: " + error.message);
    }
}

function processData(records) {
    state.allRecords = records;
    // Reinicia os detalhes
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
    // Form Submit (Edição)
    const form = document.getElementById('formEditar');
    if (form) {
        // Remove listener anterior se houver (para evitar duplicação em reloads parciais)
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);
        
        newForm.addEventListener('submit', handleFormSubmit);
    }

    // Pesquisa
    const searchInput = document.getElementById('searchDashboard');
    if (searchInput) {
        // Remove listener anterior
        const newInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newInput, searchInput);

        newInput.addEventListener('input', (e) => {
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

async function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerText;
    
    btn.disabled = true; 
    btn.innerText = "Salvando...";

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
        alert("Erro ao salvar: " + error.message);
    } finally {
        btn.disabled = false; 
        btn.innerText = originalText;
    }
}