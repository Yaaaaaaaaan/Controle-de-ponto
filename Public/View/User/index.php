<?php
define('APP_RAN', true);
require "../Layout/menu.php";
?>

<html>
<head>
</head>

<body id="page-dashboard" data-user-type="usr">
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
                <div class="row mt-5 me-3">
                    <div id="userCard" class="badge-card mt-5">
                        <img id="pPicture" alt="Foto do perfil" class="profile-pic">
                        <h3 id="responseName" class="user-full-name">@nome completo.</h3>
                        <div id="responseNickname" class="user-nickname">@nickname</div>
                        <div id="responseEmail" class="user-email">usuario@email.com</div>
                        <button type="button" id="confirmPresenceBtn" class="btn btn-success badge-action-btn">Confirmar Presença</button>
                        <p id="responseAction" class="text-center mt-2"></p>
                    </div>

                    <div id="detailCard" class="detailCard mt-5">
                        <div class="headerDetailCard">
                            <button class="btn-close" onclick="voltarParaUsuario()"></button>
                            <div class="py-1 text-center">
                                <h3 id="monthName"></h3>
                                <h6>Total de registros: <text id="monthTotal">0</text></h6>
                            </div>
                        </div>
                        <div class="contentDetailCard" id="detailContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="/Public/JS/main.js"></script>

<img hidden id="pPictureModal">
</body>
</html>