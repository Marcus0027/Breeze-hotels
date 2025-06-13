<?php

include __DIR__ . "/../../../conn/conn.php";

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../../user/login.php"); // Redireciona para o login se não estiver logado
    exit;
}


// Verifica se o método da requisição é POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  header('Content-Type: application/json');
  
  // AÇÃO 1: Buscar hotel para edição
  if (isset($_POST['action']) && $_POST['action'] === 'buscar_hotel') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT idhotel, nome, regiao, estado, cidade, endereço
                                FROM hoteis
                                WHERE idhotel = ?");
        $stmt->execute([$id]);
        $hotel = $stmt->fetch();
        echo json_encode($hotel ?: ['error' => 'Hotel não encontrado']);
    }
    exit;
  }

  // AÇÃO 2: Editar hotel
  if (isset($_POST['action']) && $_POST['action'] === 'editar_hotel') {
    $id = $_POST['idhotel'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $regiao = $_POST['regiao'] ?? '';
    $estado = $_POST['estadoSigla'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $endereco = $_POST['endereco'] ?? '';

    if ($id && $nome && $regiao && $estado && $cidade && $endereco) {
      try {
        $checkStmt = $conn->prepare("SELECT idhotel FROM hoteis WHERE nome = :nome AND regiao = :regiao AND estado = :estado AND cidade = :cidade AND idhotel != :id");
        $checkStmt->execute([':nome' => $nome, ':regiao' => $regiao, ':estado' => $estado, ':cidade' => $cidade, ':id' => $id]);
        $checkStmt = $checkStmt->rowCount() >= 1;

        if ($checkStmt) {
            echo json_encode(['error' => 'Já existe um hotel com essas informações cadastrado!']);
            exit;
        }

        $checkStmt2 = $conn->prepare("SELECT idhotel FROM hoteis WHERE estado = :estado AND cidade = :cidade AND endereço = :endereco AND idhotel != :id");
        $checkStmt2->execute([':estado' => $estado, ':cidade' => $cidade, ':endereco' => $endereco, ':id' => $id]);
        $checkStmt2 = $checkStmt2->rowCount() >= 1;

        if ($checkStmt2) {
            echo json_encode(['error' => 'Já existe um hotel com esse endereço cadastrado!']);
            exit;
        }

        $stmtHotel = $conn->prepare("UPDATE hoteis
                                    SET nome = ?, regiao = ?, estado = ?, cidade = ?, endereço = ?
                                    WHERE idhotel = ?");
        $stmtHotel->execute([$nome , $regiao, $estado, $cidade, $endereco, $id]);

        echo json_encode(['success' => true]);
      } catch (PDOException $e) {
          echo json_encode(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
      }
    } else {
        echo json_encode(['error' => 'Campos obrigatórios ausentes.']);
    }
    exit;
  }

  // AÇÃO 3: Buscar reservas
  $input = json_decode(file_get_contents("php://input"), true);
  if (isset($input['idhotel'])) {
      $idHotel = (int)$input['idhotel'];

      /** @var PDOStatement $stmt */
      $stmt = $conn->prepare("SELECT r.idreserva, r.checkin, r.checkout, r.preco,
                                            q.numero, t.tipoQuarto, u.nome AS cliente
                                      FROM reserva r
                                      JOIN quartos q ON r.q_idquarto = q.idquarto 
                                      JOIN hoteis h ON q.h_idhotel = h.idhotel 
                                      JOIN tipo_quarto t ON q.tq_idtipo = t.idtipo_quarto
                                      JOIN cliente c ON r.c_idcliente = c.idcliente
                                      JOIN usuarios u ON c.u_idusuarios = u.idusuarios
                                      WHERE q.h_idhotel = :idHotel");

      $stmt->bindParam(':idHotel', $idHotel, PDO::PARAM_INT); 
      $stmt->execute();
      $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($reservas) {
          foreach ($reservas as $reserva) {
              echo "<tr class='text-center align-middle'>
                      <td>{$reserva['idreserva']}</td>
                      <td>{$reserva['checkin']}</td>
                      <td>{$reserva['checkout']}</td>
                      <td>{$reserva['cliente']}</td>
                      <td>{$reserva['numero']}</td>
                      <td>{$reserva['tipoQuarto']}</td>
                      <td> R$ {$reserva['preco']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='7'> Nenhuma reserva encontrada para este hotel. </td></tr>";
      }
      exit;
  }

  // AÇÃO 4: Verificar se há FK em quartos
  if (isset($_POST['action']) && $_POST['action'] === 'verificar_remocao') {
    $id = $_POST['id'] ?? null;
    header('Content-Type: application/json; charset=utf-8');
    if ($id) {
        // conta quantos quartos usam esse hotel
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM quartos WHERE h_idhotel = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int)$row['cnt'];
        echo json_encode([
          'canDelete' => $count === 0,
          'count'     => $count,
          'table'     => 'quartos'
        ]);
    } else {
        echo json_encode(['error' => 'ID inválido.']);
    }
    exit;
  }

  // AÇÃO 5: Executar remoção
  if (isset($_POST['action']) && $_POST['action'] === 'remover_hotel') {
      $id = $_POST['id'] ?? null;
      header('Content-Type: application/json; charset=utf-8');
      if ($id) {
          try {
              $del = $conn->prepare("DELETE FROM hoteis WHERE idhotel = ?");
              $del->execute([$id]);
              echo json_encode(['status' => 'success', 'message' => 'Hotel removido com sucesso!']);
          } catch (PDOException $e) {
              echo json_encode(['status' => 'error',   'message' => 'Erro ao remover: ' . $e->getMessage()]);
          }
      } else {
          echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
      }
      exit;
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="/breeze/codes/css/read.css?v=<?= time() ?>" defer rel="stylesheet">
  <script src="/breeze/codes/js/adm/view/hoteis.js?v=<?= time() ?>" defer></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title> Hotéis | Breeze </title>
  <style>
    .main-container {
      padding: 2px;
      max-width: 1450px;
      margin: 0 auto;
    }

    .filter-section {
      width: 80%;
      justify-self: center;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <header>
      <nav class="navbar navbar-expand-lg navbar-custom">
          <div class="container-fluid">
              <!-- Logo -->
              <a class="navbar-brand navbar-brand-custom" href="../../user/indexA.php">
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

  <!-- Main Content -->
 <main class="main-container">

    <!-- Page Header -->
    <div class="page-header">
      <button class="back-button" id="btnVoltar">
        <i class="bi bi-arrow-left"></i> Voltar
      </button>
      <h1 class="page-title">Hotéis</h1>
      <div></div>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
      <div class="filter-header">
        <h2 class="filter-title"> Filtrar Hotéis </h2>
      </div>
      
      <div class="filter-controls">
        <div class="filter-group">
          <label class="filter-label"> ID </label>
          <input type="text" class="filter-input" id="filterId" placeholder="ID do hotel">
        </div>

        <div class="filter-group">
          <label class="filter-label"> Nome </label>
          <input type="text" class="filter-input" id="filterNome" placeholder="Nome do hotel">
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Região </label>
          <select class="filter-input" id="filterRegiao">
            <option value=""> Todas </option>
            <option value="Norte"> Norte </option>
            <option value="Nordeste"> Nordeste </option>
            <option value="Centro-Oeste"> Centro-Oeste </option>
            <option value="Sudeste"> Sudeste </option>
            <option value="Sul"> Sul </option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Estado </label>
          <select class="filter-input" id="filterEstado">
            <option value=""> Todos </option>
            <option value="Norte"> Norte </option>
            <option value="Nordeste"> Nordeste </option>
            <option value="Centro-Oeste"> Centro-Oeste </option>
            <option value="Sudeste"> Sudeste </option>
            <option value="Sul"> Sul </option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Cidade </label>
          <input type="text" class="filter-input" id="filterCidade" placeholder="Cidade">
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Endereço </label>
          <input type="text" class="filter-input" id="filterEndereco" placeholder="Endereço">
        </div>
      </div>
      
      <div class="filter-actions">
        <button class="filter-btn filter-btn-primary" id="btnApplyFilters">
          <i class="bi bi-funnel"></i> Aplicar Filtros
        </button>
        <button class="filter-btn filter-btn-secondary" id="btnClearFilters">
          <i class="bi bi-x-circle"></i> Limpar Filtros
        </button>
      </div>
    </div>
    
    <!-- Tabela de Visualização -->
    <div class="table-container">
      <div class="table-title"> Lista de Hotéis </div>
      <div class="table-responsive">
        <table id="tabela-hoteis" class="table align-middle">
          <thead>
            <tr>
              <th class="sortable"> ID </th>
              <th class="sortable"> Nome </th>
              <th class="sortable"> Região </th>
              <th class="sortable"> Estado </th>
              <th class="sortable"> Cidade </th>
              <th class="sortable"> Endereço </th>
              <th> Reservas </th>
              <th> Ações </th>
            </tr>
          </thead>
          <tbody>
            <?php
              try {
                $sql = "SELECT h.idhotel, h.nome, h.regiao, h.estado, h.cidade, h.endereço FROM hoteis h";
            
                /** @var PDOStatement $stmt */
                $stmt = $conn->prepare($sql);
            
                if ($stmt->execute()) {
                    $hoteis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $hoteis = [];
                }
            
                if ($hoteis) {
                  foreach ($hoteis as $row) {
                    echo '<tr class="text-center align-middle">
                            <td>'.$row["idhotel"].'</td>
                            <td>'.$row["nome"].'</td>
                            <td>'.$row["regiao"].'</td>
                            <td>'.$row["estado"].'</td>
                            <td>'.$row["cidade"].'</td>
                            <td>'.$row["endereço"].'</td>
                            <td>
                              <button class="action-btn btn-view ver-reservas-btn" reservas="'.$row["idhotel"].'" data-bs-toggle="modal" data-bs-target="#modalReservas">
                                <i class="bi bi-calendar-check"></i> Reservas
                              </button>
                            </td>
                            <td>
                              <div class="action-buttons">
                                <button class="action-btn btn-edit btn-editar" editar="' . htmlspecialchars($row["idhotel"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                                  <i class="bi bi-pencil"></i> Editar
                                </button> 
                                <button class="action-btn btn-delete btn-remover" remover="' . $row["idhotel"] . '" data-bs-toggle="modal" data-bs-target="#modalRemover">
                                  <i class="bi bi-trash"></i> Remover
                                </button>
                              </div>
                            </td>
                          </tr>';
                  }
                } else {
                    echo "<tr><td colspan='8' class='text-center py-4'> Nenhum hotel cadastrado. </td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='8' class='text-center text-danger py-4'> Erro ao buscar hotéis: " . $e->getMessage() . "</td></tr>";
            }      
            ?>
            <tr id="noResultsRow" style="display: none;">
              <td colspan="8" style="text-align: center;"> Nenhum hotel encontrado para os filtros aplicados. </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Modal de Reservas -->
  <div class="modal fade" id="modalReservas" tabindex="-1" aria-labelledby="modalReservasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReservasLabel"> Reservas no Hotel </h5>
        </div>
        <div class="modal-body">
          <table class="table table-hover text-center align-middle">
            <thead class="table-secondary">
              <tr>
                <th> ID </th>
                <th> Check-In </th>
                <th> Check-Out </th>
                <th> Cliente </th>
                <th> Nº do Quarto </th>
                <th> Tipo de Quarto </th>
                <th> Preço da reserva </th>
              </tr>
            </thead>
            <tbody id="corpo-tabela-reservas">
              <tr><td colspan="7"> Selecione um hotel para ver as reservas. </td></tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"> Editar Hotel </h5>
        </div>
        <div class="modal-body">
          <form id="formEditarHotel" method="POST">
            <input type="hidden" name="action" value="editar_hotel">
            <input type="hidden" id="editId" name="idhotel">

            <div class="row">
              <div class="col mb-3">
                <label for="editNome" class="form-label"> Nome do Hotel </label>
                <input type="text" class="form-control" id="editNome" name="nome" required>
              </div>
              <div class="col mb-3">
                <label for="editRegiao" class="form-label"> Região </label>
                <select class="form-control" id="editRegiao" name="regiao" required>
                  <option value="" selected disabled> Escolha a Região... </option>
                  <option value="Norte"> Norte </option>
                  <option value="Nordeste"> Nordeste </option>
                  <option value="Centro-Oeste"> Centro-Oeste </option>
                  <option value="Sudeste"> Sudeste </option>
                  <option value="Sul"> Sul </option>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col mb-3">
                <label for="estado" class="form-label"> Estado </label>
                <select class="form-control" id="editEstado" name="estado" required>
                  <option value="" selected disabled> Escolha o Estado... </option>
                </select>
              </div>
              <div class="col mb-3">
                <label for="cidade" class="form-label"> Cidade </label>
                <select class="form-control" id="editCidade" name="cidade" required>
                  <option value="" selected disabled> Escolha a cidade... </option>
                </select>
              </div>
            </div>

            <div class="col mb-3">
              <label for="endereco" class="form-label"> Endereço </label>
              <div class="row g-2">
                <div class="col-md-3">
                  <label for="via" class="col-form-label-sm mt-1"> Tipo de Via </label>
                  <select class="form-control" name="via" id="editVia" required>
                    <option value="" selected disabled> Tipo </option>
                    <option value="Av."> Avenida </option>
                    <option value="R."> Rua </option>
                    <option value="Rod."> Rodovia </option>
                    <option value="Blv."> Bulevard </option>
                    <option value="Lg."> Largo </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="nvia" class="col-form-label-sm mt-1"> Nome da Via </label>
                  <input type="text" class="form-control" name="novia" id="editNVia" placeholder="Nome da via" required>
                </div>
                <div class="col-md-3">
                  <label for="Via" class="col-form-label-sm mt-1"> Número </label>
                  <input type="text" class="form-control" name="nuvia" id="editNumero" placeholder="Número" maxlength="3" required>
                </div>
              </div>
              <input type="hidden" name="endereco" id="editEndereco">
            </div>
            <button type="submit" class="btn btn-primary"> Salvar </button>
          </form>
          <div id="mensagemErro" class="text-danger mt-2"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Remoção -->
  <div class="modal fade" id="modalRemover" tabindex="-1" aria-labelledby="modalRemoverLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRemoverLabel"> Confirmar Remoção </h5>
        </div>
        <div class="modal-body" id="removerMessage">
        </div>
        <div class="modal-footer" id="removerFooter">
        </div>
      </div>
    </div>
  </div>

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