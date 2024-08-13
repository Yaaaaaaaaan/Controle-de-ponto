<?php
include "../layout/menu.php";


?>

<html>
    <body>

    
      <script>
        document.body.dataset.bsTheme = <?php echo $_SESSION['defaultTheme'] == 1 ? "'dark'" : "'light'"; ?>;
        const themeSwitch = document.getElementById('themeSwitch');
        themeSwitch.addEventListener('change', () => {
          const newTheme = themeSwitch.checked ? 'dark' : 'light';
          document.body.dataset.bsTheme = newTheme;
        });
      </script>
    </body>
</html>
