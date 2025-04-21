<?php
    $serv = "localhost";
    $user = "root";
    $pass = "";
    $bd = "breeze";

    $conn = new mysqli($serv,$user,$pass,$bd);

    try {
        $conn = new PDO("mysql:host=$serv;dbname=$bd;charset=utf8", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
?>
