<?php

session_start();

include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header('Content-Type: application/json');
    
    try {
        // Obter e sanitizar dados
        $dados = [
            'idhotel' => filter_input(INPUT_POST, 'hotel', FILTER_VALIDATE_INT),
            'idtipo_quarto' => filter_input(INPUT_POST, 'tquarto', FILTER_VALIDATE_INT),
            'idocupacao' => filter_input(INPUT_POST, 'ocupacao', FILTER_VALIDATE_INT),
            'disponibilidade' => filter_input(INPUT_POST, 'disponibilidade', FILTER_VALIDATE_INT),
            'numero' => trim($_POST['numero'] ?? ''),
            'valor' => str_replace(['R$', '.', ','], ['', '', '.'], trim($_POST['valor'] ?? '')),
            'descricao' => trim($_POST['descricao'] ?? '')
        ];

        // Verificar se quarto já existe
        $checkStmt = $conn->prepare("SELECT idquarto FROM quartos 
                                   WHERE numero = :numero AND h_idhotel = :idhotel");
        $checkStmt->execute([
            ':numero' => $dados['numero'],
            ':idhotel' => $dados['idhotel']
        ]);

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Já existe um quarto com este número no hotel selecionado");
        }

        // Inserir novo quarto
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
    // Buscar hotéis
    $stmt_hotel = $conn->prepare("SELECT idhotel, nome FROM hoteis ORDER BY nome");
    $stmt_hotel->execute();
    $result_hotel = $stmt_hotel->fetchAll(PDO::FETCH_ASSOC);

    // Buscar tipos de quarto
    $stmt_tquarto = $conn->prepare("SELECT idtipo_quarto, tipoQuarto FROM tipo_quarto ORDER BY tipoQuarto");
    $stmt_tquarto->execute();
    $result_tquarto = $stmt_tquarto->fetchAll(PDO::FETCH_ASSOC);

    // Buscar ocupações
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
    <link href="/breeze/codes/css/hospedes.css" rel="stylesheet">
    <script src="/breeze/codes/js/c_quartos.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Quartos </title>
    <style>
        /* Importar ícones do Bootstrap */
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css");

        /* Estilo do modal */
        #notificationModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        #notificationModal .modal-body {
            padding: 2rem;
            text-align: center;
        }
        
        #modalIcon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        /* Estilo para campos inválidos */
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff3f3 !important;
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
            <form method="POST" id="quartosForm">
                <input type="hidden" id="editId" name="idQuartos">
                <div class="row">
                    <div class="col mb-3">
                        <label for="hotel" class="form-label"> Hotel </label>
                        <select class="form-control" name="hotel" required>
                            <option value="" selected> Escolha o Hotel... </option>
                            <?php foreach ($result_hotel as $hotel): ?>
                            <option value="<?= htmlspecialchars($hotel['idhotel']) ?>">
                                <?= htmlspecialchars($hotel['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col mb-3">
                        <label for="tquarto" class="form-label"> Tipo de Quarto </label>
                        <select class="form-control" name="tquarto" required>
                            <option value="" selected> Escolha o Tipo... </option>
                            <?php foreach ($result_tquarto as $tipo): ?>
                            <option value="<?= htmlspecialchars($tipo['idtipo_quarto']) ?>">
                                <?= htmlspecialchars($tipo['tipoQuarto']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label for="ocupacao" class="form-label"> Ocupação </label>
                        <select class="form-control" name="ocupacao" required>
                            <option value="" selected> Escolha a Ocupação... </option>
                            <?php foreach ($result_ocupacao as $ocupacao): ?>
                            <option value="<?= htmlspecialchars($ocupacao['idocupacao']) ?>">
                                <?= htmlspecialchars($ocupacao['ocupacao']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col mb-3">
                        <label for="disponibilidade" class="form-label"> Disponibilidade </label>
                        <select class="form-control" name="disponibilidade" required>
                            <option value="" selected> Escolha a Disponibilidade... </option>
                            <option value="1"> Disponível </option>
                            <option value="0"> Indisponível </option>
    
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label for="numero" class="form-label"> Nº do Quarto </label>
                        <input type="text" class="form-control" name="numero"  id="numero" maxlength="3" required>
                    </div>
                    <div class="col mb-3">
                        <label for="valor" class="form-label"> Valor </label>
                        <input type="text" class="form-control" name="valor" id="valor" required>
                    </div>
                </div>
                <div class="col mb-3">
                    <label for="descricao" class="form-label"> Descriçao </label>
                    <textarea class="form-control" name="descricao" id="desc" rows="3" required></textarea>
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