const API_BASE = '/Public/Api';

export async function fetchAllUsers() {
    const response = await fetch(`${API_BASE}/getUsers.php`);
    if (!response.ok) throw new Error(`Erro API: ${response.status}`);
    const data = await response.json();
    if (data.error) throw new Error(data.error);
    return data;
}

export async function saveUser(formData) {
    const response = await fetch(`${API_BASE}/saveUser.php`, {
        method: 'POST',
        body: formData
    });
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const result = await response.json();
    if (!result.success) throw new Error(result.message);
    return result;
}

export async function deleteUser(userId) {
    const response = await fetch(`${API_BASE}/deleteUser.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
    });
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const result = await response.json();
    if (!result.success) throw new Error(result.message);
    return result;
}