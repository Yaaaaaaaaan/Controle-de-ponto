<?php
    require '../layout/menu_housekeep.php';
         ?>

<html>
    <head>

    </head>
    <body>
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-12 mt-3">
                Olá mundo!
            </div>
        </div>
    </div>





        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const responseTheme = document.getElementById('responseTheme');
            const themeValue = responseTheme ? parseInt(responseTheme.textContent, 10) : 0;
            document.body.dataset.bsTheme = themeValue === 1 ? 'dark' : 'light';
        });
        </script>
    </body>
</html>
