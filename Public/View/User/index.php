<?php
    define('APP_RAN', true);
    require "../layout/menu.php";
    include_once __DIR__ . '/../../../App/controller/pointController.php';



    if ($_POST) {
        include_once '../../../App/controller/UserController.php';
        $userController = new UserController();

        if (isset($_POST['insertPointControl'])) {
            $insertPointControl = $userController->insertPointControl($_POST['id']);
        }
    }

    // ID do usuário é necessário para o JavaScript
    $id = $_SESSION['id'] ?? null;

    echo '<br><br><br><br>'.$_SESSION['userData'];
?>

<html>
    <head>

    </head>

    <body>
        <div class="dashboardWrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-8">

                        <h2 class="mt-1">Meu histórico</h2>
                        <p class="lead">Minhas visitas</p>
                        <div id="ChartContainer">
                            <canvas id="presence"></canvas>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 mt-5">
                        <div class="row mt-5">
                            <!-- Card do usuário -->
                            <div id="userCard" class="badge-card mt-5">
                                <!-- Foto do perfil -->
                                <img id="pPicture" alt="Foto do perfil" class="profile-pic">

                                <!-- Informações do usuário -->
                                <h3 id="responseName" class="user-full-name">@nome completo.</h3>
                                <div id="responseNickname" class="user-nickname">@nickname</div>
                                <div id="responseEmail" class="user-email">usuario@email.com</div>

                                <!-- Botão de ação -->
                                <form action="index.php" method="post" name="insertPointControl">
                                    <input hidden value="1" name="insertPointControl"> <!-- Otimizar esse valor do formulário -->
                                    <input hidden name="id" id="responseIdInput" value="">
                                    <button type="submit" class="btn btn-success badge-action-btn">Confirmar Presença</button>
                                </form>
                                <p hidden id="responseUserToken"></p>
                                <p hidden id="responseTheme"></p>
                                <p hidden id="rank"></p>
                            </div>

                            <!-- Card de detalhes - mesma aparência que o badge-card -->
                            <div id="detailCard" class="detailCard mt-5">
                                <!-- Botão para voltar - posicionado fora da área de overflow -->
                                <div class="headerDetailCard">
                                    <button class="btn-close" onclick="voltarParaUsuario()"></button>
                                    <!-- Título do mês -->
                                    <div class="py-1 text-center">
                                        <h3 id="monthName"></h3>
                                        <!-- Total de registros do mês vigente -->
                                        <h6>Total de registros: <text id="monthTotal">0</text></h6>
                                    </div>
                                </div>

                                <!-- Área para informações detalhadas -->
                                <div class="contentDetailCard" id="detailContent">
                                    <!-- Aqui serão inseridos os detalhes do mês via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="module" src="../../JS/indexedDB/Model.js"></script>
        <script type="module" src="../../JS/USR/userInterface.js"></script>
            <script type="module">
                import { processUserData, syncServerToIndexedDB } from '../../JS/indexedDB/Model.js';
                import { updateUIElements } from '../../JS/USR/userInterface.js';

                document.addEventListener('DOMContentLoaded', async () => {
                    const userData = await processUserData();
                    if (userData) {
                        updateUIElements(userData);
                    }
                });
            </script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="module" src="../../JS/USR/indexDashboard.js"></script> <!-- Dashboard de visualização de dados -->
        <img hidden id="pPictureModal">
    </body>
</html>
