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
        $checkEmail = $conn->prepare("SELECT idusuarios FROM usuarios WHERE email = :email");
        $checkEmail->execute([':email' => $email]);
        $emailExiste = $checkEmail->rowCount() > 0;

        if ($emailExiste) {
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
    <link href="/breeze/codes/css/hospedes.css?v=<?= time() ?>" defer rel="stylesheet">
    <script src="/breeze/codes/js/adm/add/c_usuarios.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Criar Usuários </title>
    <style>
        body {
            max-height: 1vh;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand navbar-brand-custom" href="#">
                    <img src="/breeze/images/logob.png" height="60" width="120" alt="Logo">
                </a>
                
                <!-- Botão Toggle para mobile -->
                <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon navbar-toggler-icon-custom"></span>
                </button>
                
                <!-- Conteúdo do Navbar -->
                <div class="collapse navbar-collapse navbar-collapse-custom" id="navbarSupportedContent">
                    <!-- Menu Principal -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Hotéis </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_hoteis.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/hoteis.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Quartos </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_quartos.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/quartos.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Ocupações </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_ocupacoes.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/ocupacoes.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Tipos de Quartos </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_tquartos.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/tquartos.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Reservas </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_reservas.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/reservas.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Usuários </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_usuarios.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/usuarios.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Clientes </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../add/c_clientes.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../read/clientes.php"> Visualizar </a></li>
                            </ul>
                        </li>
                    </ul>
                    
                    <!-- Barra de Pesquisa -->
                    <form class="d-flex search-form-custom" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-light" type="submit"> Search </button>
                    </form>
                    
                    <!-- Menu do Usuário -->
                    <ul class="navbar-nav user-menu">
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/breeze/images/login.png" class="user-avatar rounded-circle" alt="User">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-custom dropdown-menu-end">
                                <li><a class="dropdown-item dropdown-item-custom" href="../../user/logout.php"> Logout </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Formulário de Adição -->
    <main class="container d-flex flex-column justify-content-center min-vh-100 py-5" style="margin-top: -72px; padding-top: 72px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="form-card card mb-4">
                    <!-- Cabeçalho -->
                    <div class="form-header card-header">
                        <a href="../../user/indexA.php" class="back-button" title="Voltar para a Tela Principal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                        </a>
                        <h2 class="text-center mb-0"><i class="bi bi-person-plus me-2"></i> Adicionar Usuário </h2>
                    </div>
                    
                    <!-- Corpo do Formulário -->
                    <div class="card-body p-4 p-md-4">
                        <form method="POST" id="usuariosForm">
                            <input type="hidden" id="editId" name="idUsuarios">

                            <div class="row">
                                <div class="col mb-4">
                                    <label for="nome" class="form-label">
                                        <i class="bi bi-person-fill me-2"></i>Nome
                                    </label>
                                    <input type="text" class="form-control" name="nome" id="nome" required
                                        placeholder="Digite o nome do usuário">
                                </div>
                                
                                <div class="col mb-4">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope-fill me-2"></i>E-mail
                                    </label>
                                    <input type="email" class="form-control" name="email" id="email" required
                                        placeholder="Digite o e-mail do usuário">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col mb-4">
                                    <label for="senha" class="form-label">
                                        <i class="bi bi-lock-fill me-2"></i>Senha
                                    </label>
                                    <input type="password" class="form-control" name="senha" id="senha" required
                                        placeholder="Crie uma senha segura">
                                </div>
                                
                                <div class="col mb-4">
                                    <label for="csenha" class="form-label">
                                        <i class="bi bi-lock-fill me-2"></i>Confirmar Senha
                                    </label>
                                    <input type="password" class="form-control" name="csenha" id="csenha" required
                                        placeholder="Confirme a senha digitada">
                                </div>
                                <small class="password-hint">A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial</small>
                            </div>
                            
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-submit text-white py-2">
                                    <i class="bi bi-check-circle me-2"></i>Cadastrar Usuário
                                </button>
                            </div>
                            
                            <div class="text-center mt-2 pt-3">
                                <a href="../../user/indexA.php" class="text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>Voltar para a Tela Principal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Mensagens -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div id="modalIcon" class="my-3"></div>
                    <h4 id="modalMessage" class="mb-4"></h4>
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-2"></i>Entendido
                    </button>
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
