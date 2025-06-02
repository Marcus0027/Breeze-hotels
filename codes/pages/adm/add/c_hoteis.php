<?php   

session_start();

include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Obter dados do formulário
        $nome = trim($_POST["nome"] ?? '');
        $regiao = trim($_POST["regiao"] ?? '');
        $estado = trim($_POST["estadoSigla"] ?? '');
        $cidade = trim($_POST["cidade"] ?? '');
        $endereco = trim($_POST["endereco"] ?? '');

        // Verificar se hotel já existe
        $checkStmt = $conn->prepare("SELECT idhotel FROM hoteis WHERE nome = :nome AND regiao = :regiao AND estado = :estado AND cidade = :cidade");
        $checkStmt->execute([
            ':nome' => $nome,
            ':regiao' => $regiao,
            ':estado' => $estado,
            ':cidade' => $cidade
        ]);

        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Esse hotel já existe!'
            ]);
            exit;
        }

        // Inserir novo hotel
        $insertStmt = $conn->prepare("INSERT INTO hoteis (nome, regiao, estado, cidade, endereço) VALUES (:nome, :regiao, :estado, :cidade, :endereco)");
        
        $insertStmt->execute([
            ':nome' => $nome,
            ':regiao' => $regiao,
            ':estado' => $estado,
            ':cidade' => $cidade,
            ':endereco' => $endereco
        ]);
            echo json_encode([
                'status' => 'success',
                'message' => 'Hotel cadastrado com sucesso!'
            ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar: ' . $e->getMessage()
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
    <script src="/breeze/codes/js/adm/add/c_hoteis.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Criar Hotéis </title>
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
        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="form-card card mb-4">
                    <!-- Cabeçalho -->
                    <div class="form-header card-header">
                        <a href="../../user/indexA.php" class="back-button" title="Voltar para a Tela Principal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                        </a>
                        <h2 class="text-center mb-0"><i class="bi bi-building me-2"></i> Adicionar Hotel </h2>
                    </div>
                    
                    <!-- Corpo do Formulário -->
                    <div class="card-body p-3 p-md-4">
                        <form method="POST" id="hotelForm">
                            <input type="hidden" id="editId" name="idHotel">
                            
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="nome" class="form-label">
                                        <i class="bi bi-building me-2"></i>Nome do Hotel
                                    </label>
                                    <input type="text" class="form-control" name="nome" required
                                        placeholder="Digite o nome do hotel">
                                </div>
                                
                                <div class="col mb-3">
                                    <label for="regiao" class="form-label">
                                        <i class="bi bi-globe-americas me-2"></i>Região
                                    </label>
                                    <select class="form-control" name="regiao" required>
                                        <option value="" selected disabled>Selecione a região...</option>
                                        <option value="Norte">Norte</option>
                                        <option value="Nordeste">Nordeste</option>
                                        <option value="Centro-Oeste">Centro-Oeste</option>
                                        <option value="Sudeste">Sudeste</option>
                                        <option value="Sul">Sul</option>
                                    </select>
                                </div>
                            </div>
                            
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">
                                        <i class="bi bi-geo-alt me-2"></i>Estado
                                    </label>
                                    <select class="form-control" name="estado" required>
                                        <option value="" selected disabled>Selecione o estado...</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="cidade" class="form-label">
                                        <i class="bi bi-geo me-2"></i>Cidade
                                    </label>
                                    <select class="form-control" name="cidade" required>
                                        <option value="" selected disabled>Selecione a cidade...</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-house-door me-2"></i>Endereço
                                </label>
                                <div class="row g-2 address-row">
                                    <div class="col-md-3">
                                        <label for="via" class="address-label">Tipo</label>
                                        <select class="form-control" name="via" id="tipoEndereco" required>
                                            <option value="" selected disabled>Tipo...</option>
                                            <option value="Av.">Avenida</option>
                                            <option value="R.">Rua</option>
                                            <option value="Rod.">Rodovia</option>
                                            <option value="Blv.">Bulevard</option>
                                            <option value="Lg.">Largo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nvia" class="address-label">Nome da Via</label>
                                        <input type="text" class="form-control" name="nvia" id="nomeEndereco" 
                                               placeholder="Nome da via" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="numero" class="address-label">Número</label>
                                        <input type="text" class="form-control" id="numeroEndereco" maxlength="5"
                                               placeholder="Nº" required>
                                    </div>
                                </div>
                                <input type="hidden" name="endereco" id="enderecoCompleto">
                            </div>
                            
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-submit text-white py-2">
                                    <i class="bi bi-check-circle me-2"></i>Cadastrar Hotel
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
