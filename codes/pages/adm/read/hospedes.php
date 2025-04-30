<?php

include __DIR__ . "/../../../conn/conn.php";

// Verifica se o método da requisição é POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // AÇÃO 1: Buscar hóspede para edição
  if (isset($_POST['action']) && $_POST['action'] === 'buscar_hospede') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT u.nome, u.email, c.telefone 
                                FROM cliente c
                                JOIN usuarios u ON c.u_idusuarios = u.idusuarios
                                WHERE c.idcliente = ?");
        $stmt->execute([$id]);
        $hospede = $stmt->fetch();
        echo json_encode($hospede ?: ['error' => 'Hóspede não encontrado']);
    }
    exit;
}

  // AÇÃO 2: Editar hospede
  if (isset($_POST['action']) && $_POST['action'] === 'editar_hospede') {
      $id = $_POST['idHospede'] ?? null;
      $nome = $_POST['nome'] ?? '';
      $email = $_POST['email'] ?? '';
      $telefone = $_POST['telefone'] ?? '';

      if ($id && $nome && $email && $telefone) {
          try {
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
                              JOIN quartos q ON r.cq_idquarto = q.idquarto 
                              JOIN hoteis h ON r.cqh_idhotel = h.idhotel 
                              JOIN tipo_quarto t ON r.cqtq_idtipo = t.idtipo_quarto
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
          echo "<tr><td colspan='7'>Nenhuma reserva encontrada para este hóspede.</td></tr>";
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
  <link href="/breeze/codes/css/hospedes.css" rel="stylesheet">
  <script src="/breeze/codes/js/hospedes.js" defer></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title> Hóspedes </title>
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
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
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
                          <button class="btn btn-primary btn-sm w-100 ver-reservas-btn" data-idcliente="'.$row["idcliente"].'" data-bs-toggle="modal" data-bs-target="#modalReservas">
                            Ver Reservas
                          </button>
                        </td>
                        <td class="p-1">
                          <button class="btn btn-success btn-sm w-49 btn-editar" data-id="' . htmlspecialchars($row["idcliente"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                            Editar
                          </button> 
                          <button class="btn btn-danger btn-sm w-49" onclick="removerHospede('.$row["idcliente"].')">
                            Remover
                          </button>
                        </td>
                      </tr>';
            }
            } else {
                echo "<tr><td colspan='7' class='text-center'>Nenhum hóspede cadastrado.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='7' class='text-danger'>Erro ao buscar hóspedes: " . $e->getMessage() . "</td></tr>";
        }      
        ?>
      </tbody>
      <caption class="text-center"> Hóspedes </caption>
    </table>
  </main>

  <!-- Modal de Reservas -->
  <div class="modal fade" id="modalReservas" tabindex="-1" aria-labelledby="modalReservasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReservasLabel"> Reservas do Hóspede </h5>
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
              <tr><td colspan="7"> Selecione um hóspede para ver as reservas. </td></tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Editar -->
  <div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"> Editar Hóspede </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formEditarHospede" method="POST">
            <input type="hidden" name="action" value="editar_hospede">
              <input type="hidden" id="editId" name="idHospede">
            <div class="mb-3">
              <label for="editNome" class="form-label"> Nome do hóspede </label>
              <input type="text" class="form-control" id="editNome" name="nome" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label"> Email do hóspede </label>
              <input type="text" class="form-control" id="editEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="editTelefone" class="form-label"> Telefone do hóspede </label>
              <input type="text" class="form-control" id="editTelefone" name="telefone" required>
            </div>
            <button type="submit" class="btn btn-primary"> Salvar </button>
          </form>
          <div id="mensagemErro" class="text-danger mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
