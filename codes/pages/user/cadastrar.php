<?php

session_start();

include __DIR__ . "/../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header('Content-Type: application/json');

    try {
        $nome = trim($_POST["nomeInput"] ?? '');
        $email = trim($_POST["emailInput"] ?? '');
        $senha = trim($_POST["senhaInput"] ?? '');
        $csenha = trim($_POST["senhaConf"] ?? '');

        // Verificação de senhas
        if ($senha !== $csenha) {
            echo json_encode(['status' => 'error', 'message' => 'As senhas não coincidem!']);
            exit;
        }

        // Verificação de força da senha
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha)) {
            echo json_encode(['status' => 'error', 'message' => 'A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial!']);
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Verificação detalhada de usuário existente

        $checkEmail = $conn->prepare("SELECT idusuarios FROM usuarios WHERE email = :email");
        $checkEmail->execute([':email' => $email]);
        $emailExiste = $checkEmail->rowCount() > 0;

        if ($emailExiste) {
            echo json_encode(['status' => 'error', 'message' => 'Já existe um usuário com este e-mail cadastrado!']);
            exit;
        } 

        // Inserção do novo usuário
        $insertStmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $insertStmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash]);

        echo json_encode(['status' => 'success', 'message' => 'Usuário cadastrado com sucesso!']);
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro no sistema: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/breeze/codes/js/user/cadastrar.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <style>
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css");
        
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out, opacity 0.3s ease;
        }
        
        #notificationModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        #notificationModal .modal-body {
            padding: 2rem;
            text-align: center;
        }
        
        #notificationModal .btn {
            padding: 8px 24px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        #modalIcon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        body {
            background: linear-gradient(170deg,rgb(129, 176, 222),rgb(255, 255, 255));
            margin-bottom: 105px;
        }
    </style>
    <title> Cadastrar </title>
</head>
<body class="d-flex align-items-center py-4">
    
    <!-- Formulário de Cadastro  -->
    <main class="w-100 m-auto form-container" style="max-width: 900px; padding: 1rem;" >
        <form id="formCadastro" method="post" action="cadastrar.php">
            <div class="row">
            <div class="col-sm"></div>
                <img src="/breeze/images/logo.png" class="m-4 col-md-auto" height="228" width="228"/>
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
                <button type="submit" class="btn btn-outline-primary w-100 py-2 align-self-center col-sm"> Cadastrar </button>
                <div class="col-sm"></div>
            </div>
        </form>
        <a href="login.php"> <button class="btn btn-outline-secondary"> Login </button> </a>
        
    </main>

    <!-- Modal de Mensagens -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="modalIcon"></div>
                    <h5 id="modalMessage" class="my-3"></h5>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"> Entendido </button>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['modal_message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showNotificationModal("<?= addslashes($_SESSION['modal_message']) ?>", "<?= $_SESSION['modal_type'] ?>");
                <?php 
                unset($_SESSION['modal_message']);
                unset($_SESSION['modal_type']);
                if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);
                ?>
            }); 
        </script>
    <?php endif; ?>

</body>
</html>