<?php

echo "";



?>
<div class="container-info">
    <h1>Acesso negado</h1>
    <h3>Acesso negado a esta página.</h3>
    <h6>Para retornar a página inicial, clique no botão abaixo.</h6>
    <a href='../index.php'><button>clique aqui</button></a>
</div>
<style>
    body {
        font-family: sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f0f0f0;
    }
    .container-info {
        width:380px;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
    }
    h3{margin-bottom: -25px;text-align: center;}
    h6{margin-bottom: 30px;text-align: center;
    font-size: small;}
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
    }

    button:hover {
        background-color: #45a049;
    }
</style>