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
  
  // AÇÃO 1: Buscar cliente para edição
  if (isset($_POST['action']) && $_POST['action'] === 'buscar_cliente') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT u.nome, u.email, c.telefone 
                                FROM cliente c
                                JOIN usuarios u ON c.u_idusuarios = u.idusuarios
                                WHERE c.idcliente = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
        echo json_encode($cliente ?: ['error' => 'Hóspede não encontrado']);
    }
    exit;
}

  // AÇÃO 2: Editar cliente
  if (isset($_POST['action']) && $_POST['action'] === 'editar_cliente') {
      $id = $_POST['idHospede'] ?? null;
      $nome = $_POST['nome'] ?? '';
      $email = $_POST['email'] ?? '';
      $telefone = $_POST['telefone'] ?? '';

      if ($id && $nome && $email && $telefone) {
          try {
            $checkEmail = $conn->prepare("SELECT c.idcliente FROM cliente c JOIN usuarios u ON u.idusuarios = c.u_idusuarios WHERE u.email = :email AND c.idcliente != :id");
            $checkEmail->execute([':email' => $email, ':id' => $id]);
            $emailExiste = $checkEmail->rowCount() >= 1;

            if ($emailExiste) {
                echo json_encode(['error' => 'Já existe um cliente com este e-mail cadastrado!']);
                exit;
            }

            $checkTelefone = $conn->prepare("SELECT idcliente FROM cliente WHERE telefone = :telefone AND idcliente != :id");
            $checkTelefone->execute([':telefone' => $telefone, ':id' => $id]);
            $telefoneExiste = $checkTelefone->rowCount() >= 1;

            if ($telefoneExiste) {
              echo json_encode(['error' => 'Já existe um cliente com este telefone cadastrado!']);
              exit;
            }
            
            // Atualiza a tabela de usuários
            $stmtUser = $conn->prepare("UPDATE usuarios u
                                        JOIN cliente c ON u.idusuarios = c.u_idusuarios
                                        SET u.nome = ?, u.email = ?
                                        WHERE c.idcliente = ?");
            $stmtUser->execute([$nome, $email, $id]);

            // Atualiza a tabela cliente
            $stmtClient = $conn->prepare("UPDATE cliente SET telefone = ? WHERE idcliente = ?");
            $stmtClient->execute([$telefone, $id]);

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
  if (isset($input['idcliente'])) {
      $idCliente = (int)$input['idcliente'];

      /** @var PDOStatement $stmt */
      $stmt = $conn->prepare("SELECT r.idreserva, r.checkin, r.checkout, r.preco,
                                     q.numero, h.nome AS hotel, t.tipoQuarto
                              FROM reserva r
                              JOIN quartos q ON r.q_idquarto = q.idquarto 
                              JOIN hoteis h ON q.h_idhotel = h.idhotel 
                              JOIN tipo_quarto t ON q.tq_idtipo = t.idtipo_quarto
                              WHERE r.c_idcliente = :idcliente");

      $stmt->bindParam(':idcliente', $idCliente, PDO::PARAM_INT);
      $stmt->execute();
      $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($reservas) {
          foreach ($reservas as $reserva) {
              echo "<tr class='text-center align-middle'>
                      <td>{$reserva['idreserva']}</td>
                      <td>{$reserva['checkin']}</td>
                      <td>{$reserva['checkout']}</td>
                      <td>{$reserva['hotel']}</td>
                      <td>{$reserva['numero']}</td>
                      <td>{$reserva['tipoQuarto']}</td>
                      <td> R$ {$reserva['preco']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='7'> Nenhuma reserva encontrada para este hóspede. </td></tr>";
      }
      exit;
  }

  // AÇÃO 4: Verificar se há FK em quartos
  if (isset($_POST['action']) && $_POST['action'] === 'verificar_remocao') {
    $id = $_POST['id'] ?? null;
    header('Content-Type: application/json; charset=utf-8');
    if ($id) {
        // conta quantas reservas usam esse cliente
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reserva WHERE c_idcliente = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int)$row['cnt'];
        echo json_encode([
          'canDelete' => $count === 0,
          'count'     => $count,
          'table'     => 'reserva'
        ]);
    } else {
        echo json_encode(['error' => 'ID inválido.']);
    }
    exit;
  }

  // AÇÃO 5: Executar remoção
  if (isset($_POST['action']) && $_POST['action'] === 'remover_cliente') {
      $id = $_POST['id'] ?? null;
      header('Content-Type: application/json; charset=utf-8');
      if ($id) {
          try {
              $del = $conn->prepare("DELETE FROM cliente WHERE idcliente = ?");
              $del->execute([$id]);
              echo json_encode(['status' => 'success', 'message' => 'Cliente removido com sucesso!']);
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
  <link href="/breeze/codes/css/hospedes.css?v=<?= time() ?>" defer rel="stylesheet">
  <script src="/breeze/codes/js/adm/view/clientes.js?v=<?= time() ?>" defer></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title> Clientes </title>
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

  <!-- Tabela de Visualização -->
  <main class="w-100 m-auto form-container">
    <table class="table table-bordered align-middle caption-top mb-0">
      <thead class="table-dark text-center">
        <tr>
          <th class="sortable"> ID </th>
          <th class="sortable"> Nome </th>
          <th class="sortable"> Email </th>
          <th class="sortable"> CPF </th>
          <th class="sortable"> Telefone </th>
          <th> Reservas </th>
          <th> Ações </th>
        </tr>
      </thead>
      <tbody>
        <?php
          try {
            $sql = "SELECT c.idcliente, c.cpf, c.telefone, c.u_idusuarios, u.idusuarios, u.nome, u.email
                    FROM cliente c
                    JOIN usuarios u ON c.u_idusuarios = u.idusuarios";
        
            /** @var PDOStatement $stmt */
            $stmt = $conn->prepare($sql);
        
            if ($stmt->execute()) {
                $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $clientes = [];
            }
        
            if ($clientes) {
              foreach ($clientes as $row) {
                $cpf = $row['cpf'];
                $cpfFormat = '***.' . substr($cpf, 4, 3) . '.***-**';
            
                echo '<tr class="text-center align-middle">
                        <td>'.$row["idcliente"].'</td>
                        <td>'.$row["nome"].'</td>
                        <td>'.$row["email"].'</td>
                        <td>'.$cpfFormat.'</td>
                        <td>'.$row["telefone"].'</td>
                        <td class="p-1">
                          <button class="btn btn-outline-primary btn-sm w-100 ver-reservas-btn" reservas="'.$row["idcliente"].'" data-bs-toggle="modal" data-bs-target="#modalReservas">
                            Ver Reservas
                          </button>
                        </td>
                        <td class="p-1">
                          <button class="btn btn-outline-warning btn-sm w-49 btn-editar" editar="' . htmlspecialchars($row["idcliente"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                            Editar
                          </button> 
                          <button class="btn btn-outline-danger btn-sm w-49 btn-remover" remover="' . $row["idcliente"] . '" data-bs-toggle="modal" data-bs-target="#modalRemover">
                            Remover
                          </button>
                        </td>
                      </tr>';
            }
            } else {
                echo "<tr><td colspan='7' class='text-center'>Nenhum cliente cadastrado.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='7' class='text-danger'>Erro ao buscar hóspedes: " . $e->getMessage() . "</td></tr>";
        }      
        ?>
      </tbody>
      <caption class="text-center"> Clientes </caption>
    </table>
  </main>

  <!-- Modal de Reservas -->
  <div class="modal fade" id="modalReservas" tabindex="-1" aria-labelledby="modalReservasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReservasLabel"> Reservas do Cliente </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <table class="table table-hover text-center align-middle">
            <thead class="table-secondary">
              <tr>
                <th> ID </th>
                <th> Check-In </th>
                <th> Check-Out </th>
                <th> Hotel </th>
                <th> Nº do Quarto </th>
                <th> Tipo Quarto </th>
                <th> Preço da reserva </th>
              </tr>
            </thead>
            <tbody id="corpo-tabela-reservas">
              <tr><td colspan="7"> Selecione um cliente para ver as reservas. </td></tr>
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
          <h5 class="modal-title"> Editar Cliente </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formEditarCliente" method="POST">
            <input type="hidden" name="action" value="editar_cliente">
              <input type="hidden" id="editId" name="idCliente">
            <div class="mb-3">
              <label for="editNome" class="form-label"> Nome do Cliente </label>
              <input type="text" class="form-control" id="editNome" name="nome" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label"> Email do Cliente </label>
              <input type="text" class="form-control" id="editEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="editTelefone" class="form-label"> Telefone do Cliente </label>
              <input type="text" class="form-control" id="editTelefone" name="telefone" required>
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
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="removerMessage">
          <!-- Mensagem dinâmica será injetada aqui -->
        </div>
        <div class="modal-footer" id="removerFooter">
          <!-- Botões dinâmicos serão injetados aqui -->
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
