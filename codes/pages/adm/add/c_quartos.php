<?php

session_start();

include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header('Content-Type: application/json');
    
    try {

        $dados = [
            'idhotel' => filter_input(INPUT_POST, 'hotel', FILTER_VALIDATE_INT),
            'idtipo_quarto' => filter_input(INPUT_POST, 'tquarto', FILTER_VALIDATE_INT),
            'idocupacao' => filter_input(INPUT_POST, 'ocupacao', FILTER_VALIDATE_INT),
            'disponibilidade' => filter_input(INPUT_POST, 'disponibilidade', FILTER_VALIDATE_INT),
            'numero' => trim($_POST['numero'] ?? ''),
            'valor' => str_replace(['R$', '.', ','], ['', '', '.'], trim($_POST['valor'] ?? '')),
            'descricao' => trim($_POST['descricao'] ?? '')
        ];

        // Validar duplicidade de quartos
        $checkStmt = $conn->prepare("SELECT idquarto FROM quartos 
                                   WHERE numero = :numero AND h_idhotel = :idhotel");
        $checkStmt->execute([
            ':numero' => $dados['numero'],
            ':idhotel' => $dados['idhotel']
        ]);

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Já existe um quarto com este número no hotel selecionado");
        }

        // Inserir os dados do novo quarto
        $insertStmt = $conn->prepare("INSERT INTO quartos 
                                    (h_idhotel, tq_idtipo, o_idocupacao, disponibilidade, numero, valor, descricao) 
                                    VALUES 
                                    (:idhotel, :idtipo_quarto, :idocupacao, :disponibilidade, :numero, :valor, :descricao)");

        if ($insertStmt->execute($dados)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Quarto cadastrado com sucesso!'
            ]);
        } else {
            throw new Exception("Erro ao cadastrar quarto");
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

try {
    // Preparar consultas para carregar os dados necessários

    // Carregar os hotéis
    $stmt_hotel = $conn->prepare("SELECT idhotel, nome FROM hoteis ORDER BY nome");
    $stmt_hotel->execute();
    $result_hotel = $stmt_hotel->fetchAll(PDO::FETCH_ASSOC);

    // Carregar os tipos de quartos
    $stmt_tquarto = $conn->prepare("SELECT idtipo_quarto, tipoQuarto FROM tipo_quarto ORDER BY tipoQuarto");
    $stmt_tquarto->execute();
    $result_tquarto = $stmt_tquarto->fetchAll(PDO::FETCH_ASSOC);

    // Carregar as ocupações
    $stmt_ocupacao = $conn->prepare("SELECT idocupacao, ocupacao FROM ocupacao ORDER BY ocupacao");
    $stmt_ocupacao->execute();
    $result_ocupacao = $stmt_ocupacao->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="/breeze/codes/js/adm/add/c_quartos.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Criar Quartos </title>
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
                <a class="navbar-brand navbar-brand-custom" href="#">
                    <img src="/breeze/images/logob.png" height="60" width="120" alt="Logo">
                </a>
                
                <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon navbar-toggler-icon-custom"></span>
                </button>
                
                <div class="collapse navbar-collapse navbar-collapse-custom" id="navbarSupportedContent">
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
                    
                    <form class="d-flex search-form-custom" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-light" type="submit"> Search </button>
                    </form>
                    
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
                <div class="form-card card mb-4 mt-5">
                    <!-- Cabeçalho -->
                    <div class="form-header card-header">
                        <a href="../../user/indexA.php" class="back-button" title="Voltar para a Tela Principal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                        </a>
                        <h2 class="text-center mb-0"><i class="bi bi-door-closed me-2"></i> Adicionar Quarto </h2>
                    </div>
                    
                    <!-- Corpo do Formulário -->
                    <div class="card-body p-4 p-md-3">
                        <form method="POST" id="quartosForm">
                            <input type="hidden" id="editId" name="idQuartos">
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="hotel" class="form-label">
                                        <i class="bi bi-building me-2"></i>Hotel
                                    </label>
                                    <select class="form-control" name="hotel" required>
                                        <option value="" selected>Escolha o Hotel...</option>
                                        <?php foreach ($result_hotel as $hotel): ?>
                                        <option value="<?= htmlspecialchars($hotel['idhotel']) ?>">
                                            <?= htmlspecialchars($hotel['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-2">
                                    <label for="tquarto" class="form-label">
                                        <i class="bi bi-grid-1x2 me-2"></i>Tipo de Quarto
                                    </label>
                                    <select class="form-control" name="tquarto" required>
                                        <option value="" selected>Escolha o Tipo...</option>
                                        <?php foreach ($result_tquarto as $tipo): ?>
                                        <option value="<?= htmlspecialchars($tipo['idtipo_quarto']) ?>">
                                            <?= htmlspecialchars($tipo['tipoQuarto']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="ocupacao" class="form-label">
                                        <i class="bi bi-people me-2"></i>Ocupação
                                    </label>
                                    <select class="form-control" name="ocupacao" required>
                                        <option value="" selected>Escolha a Ocupação...</option>
                                        <?php foreach ($result_ocupacao as $ocupacao): ?>
                                        <option value="<?= htmlspecialchars($ocupacao['idocupacao']) ?>">
                                            <?= htmlspecialchars($ocupacao['ocupacao']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-2">
                                    <label for="disponibilidade" class="form-label">
                                        <i class="bi bi-check-circle me-2"></i>Disponibilidade
                                    </label>
                                    <select class="form-control" name="disponibilidade" required>
                                        <option value="" selected>Escolha a Disponibilidade...</option>
                                        <option value="1">Disponível</option>
                                        <option value="0">Indisponível</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="numero" class="form-label">
                                        <i class="bi bi-123 me-2"></i>Nº do Quarto
                                    </label>
                                    <input type="text" class="form-control" name="numero" id="numero" maxlength="3" required
                                           placeholder="Digite o número do quarto">
                                </div>
                                
                                <div class="col-md-6 mb-2">
                                    <label for="valor" class="form-label">
                                        <i class="bi bi-currency-dollar me-2"></i>Valor
                                    </label>
                                    <input type="text" class="form-control" name="valor" id="valor" required
                                           placeholder="Digite o valor da diária">
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label for="descricao" class="form-label">
                                    <i class="bi bi-card-text me-2"></i>Descrição
                                </label>
                                <textarea class="form-control" name="descricao" id="desc" rows="3" required
                                          placeholder="Descreva as características do quarto"></textarea>
                            </div>
                            
                            <div class="d-grid mt-1">
                                <button type="submit" class="btn btn-submit text-white py-2">
                                    <i class="bi bi-check-circle me-2"></i>Salvar Quarto
                                </button>
                            </div>
                            
                            <div class="text-center mt-1 pt-3">
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
