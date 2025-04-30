<?php
session_start();
include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');

    try {
        $nome = trim($_POST["nome"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $senha = trim($_POST["senha"] ?? '');
        $csenha = trim($_POST["csenha"] ?? '');

        // Verificação de senhas
        if ($senha !== $csenha) {
            echo json_encode([
                'status' => 'error',
                'message' => 'As senhas não coincidem!'
            ]);
            exit;
        }

        // Verificação de força da senha
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial!'
            ]);
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Verificação detalhada de usuário existente
        $checkNome = $conn->prepare("SELECT idusuarios FROM usuarios WHERE nome = :nome");
        $checkNome->execute([':nome' => $nome]);
        $nomeExiste = $checkNome->rowCount() > 0;

        $checkEmail = $conn->prepare("SELECT idusuarios FROM usuarios WHERE email = :email");
        $checkEmail->execute([':email' => $email]);
        $emailExiste = $checkEmail->rowCount() > 0;

        if ($nomeExiste && $emailExiste) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Já existe um usuário com este nome e e-mail cadastrados!'
            ]);
            exit;
        } elseif ($nomeExiste) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Já existe um usuário com este nome cadastrado!'
            ]);
            exit;
        } elseif ($emailExiste) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Já existe um usuário com este e-mail cadastrado!'
            ]);
            exit;
        }

        // Inserção do novo usuário
        $insertStmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $insertStmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Usuário cadastrado com sucesso!'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro no sistema: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <link href="/breeze/codes/css/hospedes.css" rel="stylesheet">
    <script src="/breeze/codes/js/c_usuarios.js" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Usuários </title>
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
    </style>
</head>
<body>
    <header class="bg-primary py-2">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <div class="d-flex justify-content-left align-items-left mx-3">
                    <img src="/breeze/images/logob.png" height="60" width="120"/>
                </div>
                <div class="collapse navbar-collapse align-items-center ms-5" id="navbarSupportedContent">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Hotéis </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_hoteis.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/hoteis.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Quartos </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_quartos.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/quartos.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Ocupações </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_ocupacoes.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/ocupacoes.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Tipos de Quartos </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_tquartos.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/tquartos.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Reservas </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_reservas.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/reservas.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Usuários </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_usuarios.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/usuarios.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle color mx-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Clientes </a>
                            <ul class="dropdown-menu">
                                <li> <a class="dropdown-item" href="../add/c_hospedes.php"> Adicionar </a> </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li> <a class="dropdown-item" href="../read/hospedes.php"> Visualizar </a> </li>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex mx-5" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                    </form>
                    <ul class="navbar-nav ms-5">
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="/breeze/images/login.png" width="40" height="40" class="rounded-circle" alt="User">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a href="logout.php" class="dropdown-item"> Logout </a>
                        </div>
                        </li>
                    </ul>
                </div>
            </div>     
        </nav>
    </header>

    <main class="w-100 m-auto form-container">
        <div class="container m-auto mt-3 p-4 rounded shadow form">
            <form method="POST" id="usuariosForm">
                <input type="hidden" id="editId" name="idUsuarios">
                <div class="col mb-3">
                    <label for="nome" class="form-label"> Check' </label>
                    <input type="text" class="form-control" name="nome" id="nome" required>
                </div>
                <div class="col mb-3">
                    <label for="email" class="form-label"> Email </label>
                    <input type="email" class="form-control" name="email"  id="email" required>
                </div>
                <div class="col mb-3">
                    <label for="senha" class="form-label"> Senha </label>
                    <input type="password" class="form-control" name="senha" id="senha" required>
                </div>
                <div class="col mb-3">
                    <label for="csenha" class="form-label"> Confirmar a senha </label>
                    <input type="password" class="form-control" name="csenha" id="csenha" required>
                </div>
                <button type="submit" class="btn btn-primary"> Salvar </button>
            </form>
            <div id="mensagem" style="text-align:center; color:red;"></div>
        </div>
    </main>

    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="modalIcon"></div>
                    <h5 id="modalMessage" class="my-3"></h5>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
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