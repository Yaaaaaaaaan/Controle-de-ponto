async function processUserData() {
    try {
      const response = await fetch('../../Persistence/userData.php');
      const userData = await response.json();
  
      if (userData.length > 0) {
        localStorage.setItem('userData', JSON.stringify(userData));
        console.log('Dados armazenados no localStorage:', localStorage.getItem('userData'));
  
        // Assumindo que a estrutura dos dados é um array de objetos
        const userData = JSON.parse(localStorage.getItem('userData'));
        
  
        // Verificar se userData é um array antes de usar forEach
        if (Array.isArray(userData)) {
          userData.forEach(user => {
            console.log('Nome:', user.name);
            console.log('Token:', user.token);
            console.log('Nome curto:', user.name.split(' ')[0]);
            
          });
        } else {
          console.error('userData não é um array');
          // Aqui você pode implementar uma lógica alternativa para lidar com outros tipos de dados
        }
      } else {
        console.error('Nenhum dado de usuário encontrado');
      }
    } catch (error) {
      console.error('Erro ao processar dados:', error);
    }
  }