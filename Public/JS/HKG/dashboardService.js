// Public/JS/HKG/hkgDashboardService.js

const API_URL = '/Public/Api/housekeep.php';
const API_UPDATE = '/Public/Api/updateHousekeep.php';

export async function fetchDashboardData() {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error(`Erro API: ${response.status}`);
        const data = await response.json();
        return data || [];
    } catch (error) {
        console.error("Dashboard Service Error:", error);
        throw error;
    }
}

export async function updateRegistry(formData) {
    try {
        const response = await fetch(API_UPDATE, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) throw new Error(`Erro API: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error("Update Service Error:", error);
        throw error;
    }
}