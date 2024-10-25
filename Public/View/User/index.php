<?php
  require "../layout/menu.php";
  if ($_POST) {
    include_once '../../../App/controller/UserController.php';
    $controller = new UserController();
    if(isset($_POST['insertPointControl'])){
      $insertPointControl = $controller->insertPointControl($_POST['id'], $_POST['description']);
    }

  }
    
?>

<html>
  <head>
    <style>
      body {
          font-family: Arial, sans-serif;
          margin: 50px;
      }
      #chartContainer {
          width: 80%;
          height: 60vh;
          margin: auto;
      }
      canvas {
          max-width: 100%;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div id="chartContainer">
            <canvas id="myChart"></canvas>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <form action="index.php" method="post" name="insertPointControl">
            <div class="row g-3">
              <div class="col-sm-6">
                <input hidden value="1" name="insertPointControl" >
                <input hidden value="<?=$_SESSION['id']?>"  name="id">
                <input hidden value="Verificação pendente"  name="description">
              <button class="w-100 btn-lg btn btn-success" type="submit">Estou aqui!</button>
              </div>    
            </div>
          </form>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <p id="responseName"></p>
          <p id="responseUserToken"></p>
          <p id="profileUser"></p>
          <img id="profilePic">
          <p id="id"></p>
          <p id="theme"></p>
          <p id="nickname"></p>
          <p id="rank"></p>
          <p id="email"></p>
        </div>
      </div>    
    </div>

      
      
      



      <script>
         const ctx = document.getElementById('myChart').getContext('2d');

        // Dados do gráfico
        const data = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                label: 'Presença 2024',
                data: [15, 13, 18, 22, 3, 8, 6, 10, 9, 1, 3, 15],
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                fill: true,
                tension: 0.4,  // suavização da linha
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        };

        // Configuração do gráfico
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();  // Formato dos valores do eixo Y
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return value.toLocaleString();  // Formato dos valores no tooltip
                            }
                        }
                    }
                }
            }
        };

        // Inicialização do gráfico
        const myChart = new Chart(ctx, config);
      </script>
    </body>
</html>