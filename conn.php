<?php
    $serv = "localhost";
    $user = "root";
    $senha = "";
    $banco = "breeze";

    $conn = new mysqli($serv,$user,$senha,$banco);

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }
    

?>