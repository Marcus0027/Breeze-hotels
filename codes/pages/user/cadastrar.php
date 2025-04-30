<?php

include __DIR__ . "/../../conn/conn.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $nome = $_POST["nomeInput"];
    $email = $_POST["emailInput"];
    $senha = $_POST["senhaInput"];
    $confi = $_POST["senhaConf"];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senhaHash);

    if ($stmt->execute()) {
        $sucesso = "Usuário cadastrado com sucesso!";
    } else {
        $erro = "Erro ao cadastrar: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Cadastrar </title>
</head>
<body class="d-flex align-items-center py-4">
<main class="w-100 m-auto form-container" style="max-width: 900px; padding: 1rem;" >
    <form id="formCadastro" method="post" action="cadastrar.php">
        <div class="row">
        <div class="col-sm"></div>
            <img src="logo.png" class="m-4 col-md-auto" height="228" width="228"/>
        <div class="col-sm"></div>
        </div>
            <h1 class="h3 mb-3 fw-normal align-self-center"> Cadastrar-se </h1>
        <div class="row">
            <div class="form-floating col-sm">  
                <input type="text" name="nomeInput" id="nome" class="form-control" placeholder="seunome" required><br/>
                <label for="nomeInput" style="padding-left: 5%;"> Nome </label> 
            </div>
            <div class="form-floating col-sm">  
                <input type="email" name="emailInput" id="email" class="form-control" placeholder="seuemail@gmail.com" required><br/>
                <label for="emailInput" style="padding-left: 5%;"> Email </label> 
            </div>
        </div>
        <div class="row">
            <div class="form-floating col-sm">  
                <input type="password" name="senhaInput" id="senha" class="form-control" placeholder="senha" required><br/>
                <label for="senhaInput" style="padding-left: 5%;"> Senha </label> 
            </div>
            <div class="form-floating col-sm">  
                <input type="password" name="senhaConf" id="senhaConf" class="form-control" placeholder="Confirme sua senha" required><br/>
                <label for="senhaConf" style="padding-left: 5%;"> Confirme sua senha </label> 
            </div>
        </div>
        <div class="row">
            <div class="col-sm"></div>
            <button class="btn btn-outline-primary w-100 py-2 align-self-center col-sm"> Cadastrar </button>
            <div class="col-sm"></div>
        </div>
        <div id="mensagem" style="text-align:center; color:red;"></div>
    </form>
    <button class="btn btn-outline-info"> <a href="login.php" style="color: blue;">Login</a> </button>
    <script>
        document.getElementById("formCadastro").addEventListener("submit", function(e) {
        e.preventDefault();

        const nome = document.getElementById("nome");
        const email = document.getElementById("email");
        const senha = document.getElementById("senha");
        const senhaConf = document.getElementById("senhaConf");
        const mensagem = document.getElementById("mensagem");

        const senhaForte = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        if (senha.value !== senhaConf.value) {
            mensagem.textContent = "As senhas não são iguais.";
            senha.value = "";
            senhaConf.value = "";
            return;
        }

        if (!senhaForte.test(senha.value)) {
            mensagem.textContent = "A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e caracteres especiais.";
            senha.value = "";
            senhaConf.value = "";
            return;
        }

        mensagem.textContent = "";
        this.submit();
        });
    </script>
</main>    
</body>
</html>