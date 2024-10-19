<?php
require "../layout/menu.php";
// Ainda não funciona a parafernalha do localStorage.js, já insere no localStorage, porém não consegue captar.

?>

<html>
</script>
      <br><br>
      <p id="responseName"></p>
      <p id="responseUserToken"></p>
      <p id="profileUser"></p>
      <p id="id"></p>
      <p id="theme"></p>
      <p id="nickname"></p>
      <p id="rank"></p>
      <p id="email"></p>



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