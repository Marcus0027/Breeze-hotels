<?php
$serv = "localhost";
$user = "root";
$pass = "";
$bd = "breeze";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $conn = new PDO("mysql:host=$serv;dbname=$bd;charset=utf8mb4", $user, $pass, $options);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
