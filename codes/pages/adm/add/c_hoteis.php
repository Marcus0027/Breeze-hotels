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
    <link href="/breeze/codes/css/hospedes.css" rel="stylesheet">
    <script src="/breeze/codes/js/c_hoteis.js" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Criar Hotel </title>
    <style>
        /* Ícones do Bootstrap Icons */
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css");
        
        /* Animação suave para o modal */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out, opacity 0.3s ease;
        }
        
        /* Estilo personalizado para o modal */
        #notificationModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        #notificationModal .modal-body {
            padding: 2rem;
        }
        
        #notificationModal .btn {
            padding: 8px 24px;
            border-radius: 8px;
            font-weight: 500;
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
            <form method="POST" id="hotelForm">
                <input type="hidden" id="editId" name="idHotel">
                <div class="col mb-3">
                    <label for="nome" class="form-label"> Nome </label>
                    <input type="text" class="form-control" name="nome" required>
                </div>
                <div class="col mb-3">
                    <label for="regiao" class="form-label"> Região </label>
                    <select class="form-control" name="regiao" required>
                        <option value="" selected> Escolha a Região... </option>
                        <option value="Norte"> Norte </option>
                        <option value="Nordeste"> Nordeste </option>
                        <option value="Centro-Oeste"> Centro-Oeste </option>
                        <option value="Sudeste"> Sudeste </option>
                        <option value="Sul"> Sul </option>
                    </select>
                </div>
                <div class="col mb-3">
                    <label for="estado" class="form-label"> Estado </label>
                    <select class="form-control" name="estado" required>
                        <option value="" selected> Escolha o Estado... </option>
                    </select>
                </div>
                <div class="col mb-3">
                    <label for="cidade" class="form-label"> Cidade </label>
                    <select class="form-control" name="cidade" required>
                        <option value="" selected> Escolha a cidade... </option>
                    </select>
                </div>
                <div class="col mb-3">
                    <label for="endereco" class="form-label"> Endereço </label>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label for="via" class="col-form-label-sm mt-1"> Tipo de Via </label>
                            <select class="form-control" name="via" id="tipoEndereco" required>
                                <option value="" selected disabled> Selecione o tipo... </option>
                                <option value="Av."> Avenida </option>
                                <option value="R."> Rua </option>
                                <option value="Rod."> Rodovia </option>
                                <option value="Blv."> Bulevard </option>
                                <option value="Lg."> Largo </option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="nvia" class="col-form-label-sm mt-1"> Nome da Via </label>
                            <input type="text" class="form-control" name="nvia" id="nomeEndereco" placeholder="Nome da via" required>
                        </div>
                        <div class="col-md-3">
                            <label for="Via" class="col-form-label-sm mt-1"> Número </label>
                            <input type="text" class="form-control" id="numeroEndereco" placeholder="Número" required>
                        </div>
                    </div>
                    <input type="hidden" name="endereco" id="enderecoCompleto">
                </div>
                <button type="submit" class="btn btn-primary"> Salvar </button>
            </form>
        </div>
    </main>

    <!-- Modal de Notificação -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div id="modalIcon" style="font-size: 3rem;"></div>
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
            });
        </script>
    <?php 
        unset($_SESSION['modal_message']);
        unset($_SESSION['modal_type']);
        endif; 
    ?>
</body>
</html>
