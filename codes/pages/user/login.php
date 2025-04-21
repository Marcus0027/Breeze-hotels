<?php
include 'conn.php';

header("Cache-Control: no-store");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajax"])) {
    $email = $_POST["emailInput"] ?? '';
    $senha = $_POST["senhaInput"] ?? '';
    $lembrar = $_POST["lembrar"] ?? 'false';

    $response = ["success" => false, "message" => ""];

    if (empty($email) || empty($senha)) {
        $response["message"] = "Preencha todos os campos.";
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT senha FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($senhaHash);
        $stmt->fetch();

        if (password_verify($senha, $senhaHash)) {
            $response["success"] = true;
            $response["message"] = "Login realizado com sucesso!";
            if ($lembrar === "true") {
                setcookie("email_lembrado", $email, time() + (86400 * 30), "/"); // 30 dias
            } else {
                setcookie("email_lembrado", "", time() - 3600, "/"); // apagar cookie
            }
        } else {
            $response["message"] = "Senha incorreta.";
        }
    } else {
        $response["message"] = "Usuário não encontrado.";
    }

    $stmt->close();
    echo json_encode($response);
    exit;
}

// Carrega o e-mail do cookie (se existir)
$emailSalvo = $_COOKIE["email_lembrado"] ?? "";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link href="login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Login </title>
</head>
<body class="d-flex align-items-center py-4">
<main class="w-100 m-auto form-container" style="max-width: 300px; padding: 1rem;">
    <form id="loginForm">
        <img src="logo.png" class="m-4" height="228" width="228"/>
        <h1 class="h3 mb-3 fw-normal">Login</h1>

        <div class="form-floating mb-3">  
            <input type="email" name="emailInput" id="email" class="form-control" placeholder="seuemail@gmail.com" value="<?= htmlspecialchars($emailSalvo) ?>" required>
            <label for="email">Email</label> 
        </div>

        <div class="form-floating mb-3">  
            <input type="password" name="senhaInput" id="senha" class="form-control" placeholder="senha" required>
            <label for="senha">Senha</label> 
        </div>

        <div class="form-check text-start mb-3">
            <input type="checkbox" class="form-check-input" id="lembrar" <?= $emailSalvo ? "checked" : "" ?>>
            <label class="form-check-label" for="lembrar">Lembrar-me</label>
        </div>

        <div id="mensagemErro" class="alert alert-danger d-none" role="alert"></div>
        <div id="mensagemSucesso" class="alert alert-success d-none" role="alert"></div>

        <button type="submit" class="btn btn-outline-primary w-100 py-2">Logar-se</button>
    </form>
    <button class="btn btn-outline-info"> <a href="cadastrar.php" style="color: blue;">Criar conta</a> </button>
</main>

<script>
document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const emailField = document.getElementById("email");
    const senhaField = document.getElementById("senha");
    const lembrarCheck = document.getElementById("lembrar");
    const erroDiv = document.getElementById("mensagemErro");
    const sucessoDiv = document.getElementById("mensagemSucesso");

    const formData = new FormData();
    formData.append("ajax", "1");
    formData.append("emailInput", emailField.value);
    formData.append("senhaInput", senhaField.value);
    formData.append("lembrar", lembrarCheck.checked);

    const response = await fetch("", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        erroDiv.classList.add("d-none");
        sucessoDiv.textContent = result.message;
        sucessoDiv.classList.remove("d-none");
    } else {
        sucessoDiv.classList.add("d-none");
        erroDiv.textContent = result.message;
        erroDiv.classList.remove("d-none");
        senhaField.value = "";
    }
});
</script>
</body>
</html>
