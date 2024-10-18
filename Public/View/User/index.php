<?php
require "../layout/menu.php";
// Ainda não funciona a parafernalha do localStorage.js, já insere no localStorage, porém não consegue captar.

?>

<html>

<script>
  if(userDataString == null){
    // Verificando se os dados do usuário estão disponíveis
    if (<?= json_encode($userData !== false); ?>) {
            // Passando os dados PHP para o JavaScript
            userData = <?= json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            if (userData != null) {
                // Armazenando os dados no localStorage
                localStorage.setItem('userData', JSON.stringify(userData));

                // Verificando se os dados foram armazenados corretamente
                console.log('Dados do usuário armazenados no localStorage:', localStorage.getItem('userData'));

                // Limpa os dados da sessão no servidor, se necessário
                <?php unset($_SESSION['userData']); ?>
            } else {
                console.error('Nenhum dado de usuário encontrado no localStorage.');
            }
          }
  }   
          // Recuperando a string do localStorage
           userDataString = localStorage.getItem("userData");

          // Convertendo a string para um objeto JavaScript
         {UserData = JSON.parse(userDataString);}

          // Acessando o valor dentro do array
           userdata = UserData.split(",");
           name = (userdata[1]).slice(8, -1);
           userToken = (userdata[0]).slice(14, -1);

          //faz a manipulação detalhada da string
          nameCurto = name.substring(0, name.indexOf(" "));

       // document.body.dataset.bsTheme = <?php echo $_SESSION['defaultTheme'] == 1 ? "'dark'" : "'light'"; ?>;
       // const themeSwitch = document.getElementById('themeSwitch');
      //  themeSwitch.addEventListener('change', () => {
       //   const newTheme = themeSwitch.checked ? 'dark' : 'light';
       //   document.body.dataset.bsTheme = newTheme;
        //});
</script>
      <br><br>
      <p id="responseName"></p>
      <p id="responseUserToken"></p>
      <text></text>



<script>
        document.getElementById("responseName").textContent = name; 
        document.getElementById("responseUserToken").textContent = userToken; 
        document.getElementById("profileUser").textContent = profileUser;
        document.getElementById("id").textContent = id;
        document.getElementById("theme").textContent = theme;
        document.getElementById("nickname").textContent = nickname;
        document.getElementById("rank").textContent = rank;
        document.getElementById("email").textContent = email;
      </script>
    </body>
</html>
