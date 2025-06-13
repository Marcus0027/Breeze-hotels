<?php

session_start();

include __DIR__ . "/../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Cache-Control: no-store");
    header("Content-Type: application/json");

    try {
        $email = trim($_POST["emailInput"] ?? '');
        $senha = trim($_POST["senhaInput"] ?? '');
        $lembrar = $_POST["lembrar"] ?? 'false';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de e-mail inválido.']);
            exit;
        }

        if (empty($email) || empty($senha)) {
            echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT idusuarios, senha FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() === 1) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $senhaHash = $usuario['senha'];

            if (password_verify($senha, $senhaHash)) {
                $_SESSION['usuario_id'] = $usuario['idusuarios'];
                $_SESSION['usuario_email'] = $email;
                $_SESSION['usuario_nome'] = $usuario['nome'] ?? '';
                $_SESSION['usuario_logado'] = true;


                if ($lembrar === "true") {
                    setcookie("email_lembrado", $email, time() + (86400 * 30), "/"); // 30 dias
                } else {
                    setcookie("email_lembrado", "", time() - 3600, "/");
                }

                echo json_encode(['status' => 'success', 'message' => 'Login realizado com sucesso!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Senha incorreta.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro no sistema: ' . $e->getMessage()]);
    }
    exit;
}

// Carrega o e-mail do cookie (se existir)
$emailSalvo = $_COOKIE["email_lembrado"] ?? "";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/breeze/codes/js/user/login.js?v=<?= time() ?>" defer></script>
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
            margin-bottom: 50px;
        }
    </style>
    <title> Login </title>
</head>
<body class="d-flex align-items-center py-4">

    <!-- Formulário de Cadastro  -->
    <main class="w-100 m-auto form-container" style="max-width: 300px; padding: 1rem;">
        <form method="POST" id="formLogin">
            <img src="/breeze/images/logo.png" class="m-4" height="228" width="228"/>
            <h1 class="h3 mb-3 fw-normal">Login</h1>

            <div class="form-floating mb-3">  
                <input type="email" name="emailInput" id="email" class="form-control" placeholder="seuemail@gmail.com" value="<?= htmlspecialchars($emailSalvo) ?>" required>
                <label for="email" >Email </label> 
            </div>

            <div class="form-floating mb-3">  
                <input type="password" name="senhaInput" id="senha" class="form-control" placeholder="senha" required>
                <label for="senha"> Senha </label> 
            </div>

            <div class="form-check text-start mb-3">
                <input type="checkbox" class="form-check-input" value="lembrar" id="lembrar" <?= $emailSalvo ? "checked" : "" ?>>
                <label class="form-check-label" for="lembrar"> Lembrar-me </label>
            </div>

            <button type="submit" class="btn btn-outline-primary w-100 py-2"> Logar-se </button>
        </form>
        <a href="cadastrar.php"> <button class="btn btn-outline-secondary my-3"> Criar conta </button> </a>
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
