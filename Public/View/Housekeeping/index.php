    <?php
        require '../layout/menu_housekeep.php';

    ?>

<html>
    <head>

    </head>
    <body>
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-4 mt-3">
                <h3 class="mt-5">Estatísticas</h3>
                <p class="span">Total de presenças no sistema</p>
                <canvas id="myChart"></canvas>

                <!-- TODO: fazer gráfico de pizza com o total de presenças dos últimos 03 meses de todos os usuários-->
            </div>

            <div class="col-md-4">


            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            const data = {
                labels: [
                    'Recusado',
                    'Já verificado',
                    'Verificação pendente'
                ],
                datasets: [{
                    label: 'My First Dataset',
                    data: [300, 50, 100],
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
            };



        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, config);


            const responseTheme = document.getElementById('responseTheme');
            const themeValue = responseTheme ? parseInt(responseTheme.textContent, 10) : 0;
            document.body.dataset.bsTheme = themeValue === 1 ? 'dark' : 'light';
        });
        </script>
    </body>
</html>
