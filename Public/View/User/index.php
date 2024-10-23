<?php
  require "../layout/menu.php";
  if ($_POST) {
    include_once '../../../App/controller/UserController.php';
    $controller = new UserController();

  }
?>

<html>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
            <div class="row g-3">
              <div class="col-sm-6">
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
          <p id="id"></p>
          <p id="theme"></p>
          <p id="nickname"></p>
          <p id="rank"></p>
          <p id="email"></p>
        </div>
      </div>    
    </div>

      
      
      



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