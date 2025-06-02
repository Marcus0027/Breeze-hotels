<?php

session_start();

include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
      
    // Handle AJAX requests
    if (isset($_POST['ajax_request'])) {
        try {
            // Obter quartos por hotel
            if (isset($_POST['get_quartos'])) {
                $hotelId = $_POST['hotel_id'];
                $stmt = $conn->prepare("SELECT idquarto, numero, valor, tq_idtipo, o_idocupacao 
                                      FROM quartos 
                                      WHERE h_idhotel = ? AND disponibilidade = 1");
                $stmt->execute([$hotelId]);
                $quartos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($quartos)) {
                    echo json_encode(['error' => 'Nenhum quarto disponível no hotel']);
                } else {
                    echo json_encode($quartos);
                }
                exit;
            }

            // Obter detalhes do quarto
            if (isset($_POST['get_quarto_details'])) {
                $quartoId = $_POST['quarto_id'];
                $stmt = $conn->prepare("SELECT t.tipoQuarto, o.ocupacao, q.valor 
                                      FROM quartos q
                                      JOIN tipo_quarto t ON q.tq_idtipo = t.idtipo_quarto
                                      JOIN ocupacao o ON q.o_idocupacao = o.idocupacao
                                      WHERE q.idquarto = ?");
                $stmt->execute([$quartoId]);
                $quarto = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($quarto);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
            exit;
        }  
    }

    try {
        $checkin = trim($_POST["checkin"] ?? '');
        $checkout = trim($_POST["checkout"] ?? '');
        $idcliente = trim($_POST["cliente"] ?? '');
        $idquarto = trim($_POST["quarto"] ?? '');
        $preco = trim($_POST["preco"] ?? '');
        

        // Inserção do nova reserva
        $insertStmt = $conn->prepare("INSERT INTO reserva (checkin, checkout, preco, c_idcliente, q_idquarto) VALUES (:checkin, :checkout, :preco, :idcliente, :idquarto)");
        $insertStmt->execute([
            ':checkin' => $checkin,
            ':checkout' => $checkout,
            ':preco' => $preco,
            ':idcliente' => $idcliente,
            ':idquarto' => $idquarto
        ]);

        $alterDispStmt = $conn->prepare("UPDATE quartos q INNER JOIN reserva r ON q.idquarto = r.q_idquarto SET q.disponibilidade = 0 WHERE r.q_idquarto = :idquarto");
        $alterDispStmt->execute([
            ':idquarto' => $idquarto
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Reserva cadastrada com sucesso!'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro no sistema: ' . $e->getMessage()
        ]);
    }
    exit;
}

try {
    // Buscar Nome dos Clientes
    $stmt_usuario = $conn->prepare("SELECT idusuarios, nome, email FROM usuarios ORDER BY nome");
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->fetchAll(PDO::FETCH_ASSOC);

    // Buscar Hotéis
    $stmt_hotel = $conn->prepare("SELECT idhotel, nome FROM hoteis ORDER BY nome");
    $stmt_hotel->execute();
    $result_hotel = $stmt_hotel->fetchAll(PDO::FETCH_ASSOC);

    // Buscar Quartos
    foreach ($result_hotel as $row) {
        $hotel_id = $row["idhotel"];
        $stmt_quarto = $conn->prepare("SELECT idquarto, numero, tq_idtipo, o_idocupacao, valor FROM quartos WHERE h_idhotel = :idhotel AND disponibilidade = 1 ORDER BY numero");
        $stmt_quarto->bindParam(':idhotel', $hotel_id, PDO::PARAM_INT);
        $stmt_quarto->execute();
        $result_quarto = $stmt_quarto->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar Tipos de Quarto
    foreach ($result_quarto as $row) {
        $quarto_idtq = $row["tq_idtipo"];
        $stmt_tquarto = $conn->prepare("SELECT idtipo_quarto, tipoQuarto FROM tipos_quarto WHERE idtipo_quarto = :tq_idtipo");
        $stmt_tquarto->bindParam(':tq_idtipo', $quarto_idtq, PDO::PARAM_INT);
        $stmt_tquarto->execute();
        $result_tquarto = $stmt_tquarto->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar Ocupações
    foreach ($result_quarto as $row) {
        $quarto_ido = $row["o_idocupacao"];
        $stmt_ocupacao = $conn->prepare("SELECT idocupacao, ocupacao FROM ocupacoes WHERE idocupacao = :o_idocupacao");
        $stmt_ocupacao->bindParam(':o_idocupacao', $quarto_ido, PDO::PARAM_INT);
        $stmt_ocupacao->execute();
        $result_ocupacao = $stmt_ocupacao->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
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
    <script src="/breeze/codes/js/adm/add/c_reservas.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Criar Reservas </title>
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
                        <h2 class="text-center mb-0"><i class="bi bi-calendar-plus me-2"></i> Nova Reserva </h2>
                    </div>
                    
                    <!-- Corpo do Formulário -->
                    <div class="card-body p-4 p-md-3">
                        <form method="POST" id="reservaForm" novalidate>
                            <input type="hidden" id="editId" name="idReservas">
                            
                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="checkin" class="form-label">
                                        <i class="bi bi-calendar-check me-2"></i>Check-In
                                    </label>
                                    <input type="date" class="form-control" name="checkin" id="checkin" required min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="checkout" class="form-label">
                                        <i class="bi bi-calendar-x me-2"></i>Check-Out
                                    </label>
                                    <input type="date" class="form-control" name="checkout" id="checkout" required min="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="cliente" class="form-label">
                                        <i class="bi bi-person-fill me-2"></i> Cliente
                                    </label>
                                    <select class="form-control" name="cliente" id="cliente" required>
                                        <option value="" selected disabled>Selecione o cliente...</option>
                                        <?php foreach ($result_usuario as $usuario): ?>
                                            <?php
                                                // Buscar o idcliente correspondente ao idusuarios
                                                $stmt_cliente = $conn->prepare("SELECT idcliente FROM cliente WHERE u_idusuarios = :idusuarios");
                                                $stmt_cliente->bindParam(':idusuarios', $usuario['idusuarios'], PDO::PARAM_INT);
                                                $stmt_cliente->execute();
                                                $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <?php if ($cliente): ?>
                                                <option value="<?= htmlspecialchars($cliente['idcliente']) ?>"
                                                        data-email="<?= htmlspecialchars($usuario['email']) ?>">
                                                    <?= htmlspecialchars($usuario['nome']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope-fill me-2"></i>Email do Cliente
                                    </label>
                                    <input class="form-control" type="email" name="email" id="email" 
                                           placeholder="Email será preenchido sozinho" readonly required>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="hotel" class="form-label">
                                        <i class="bi bi-building me-2"></i>Hotel
                                    </label>
                                    <select class="form-control" name="hotel" id="hotel" required>
                                        <option value="" selected disabled>Selecione o hotel...</option>
                                        <?php foreach ($result_hotel as $hotel): ?>
                                        <option value="<?= htmlspecialchars($hotel['idhotel']) ?>">
                                            <?= htmlspecialchars($hotel['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="quarto" class="form-label">
                                        <i class="bi bi-door-open me-2"></i>Quarto
                                    </label>
                                    <select class="form-control" name="quarto" id="quarto" required>
                                        <option value="" selected disabled>Selecione o quarto...</option>
                                        <?php foreach ($result_quarto as $quarto): ?>
                                        <option value="<?= htmlspecialchars($quarto['idquarto']) ?>">
                                            <?= htmlspecialchars($quarto['numero']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-4 mb-2">
                                    <label for="tquarto" class="form-label">
                                        <i class="bi bi-house-door me-2"></i>Tipo do Quarto
                                    </label>
                                    <input class="form-control" type="text" name="tquarto" id="tquarto" 
                                           placeholder="Tipo do quarto" readonly required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label for="ocupacao" class="form-label">
                                        <i class="bi bi-people-fill me-2"></i>Ocupação
                                    </label>
                                    <input class="form-control" type="text" name="ocupacao" id="ocupacao" 
                                           placeholder="Ocupação do quarto" readonly required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label for="preco" class="form-label">
                                        <i class="bi bi-currency-dollar me-2"></i>Valor Total
                                    </label>
                                    <input class="form-control" type="text" id="precoDisplay" 
                                        placeholder="Valor calculado" readonly>
                                    <input type="hidden" name="preco" id="preco" value="">
                                </div>
                                <input type="hidden" id="valorDiaria" value="">
                            </div>
                            
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-submit text-white py-2">
                                    <i class="bi bi-check-circle me-2"></i>Confirmar Reserva
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
