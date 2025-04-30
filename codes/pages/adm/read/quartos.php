<?php

include __DIR__ . "/../../../conn/conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents("php://input"), true);
  if (isset($input['idquarto'])) {
      $idQuarto = (int)$input['idquarto'];

      /** @var PDOStatement $stmt */
      $stmt = $conn->prepare("SELECT r.idreserva, r.checkin, r.checkout, r.preco,
                               q.numero, t.tipoQuarto, h.nome AS hotel, u.nome AS cliente
                        FROM reserva r
                        JOIN quartos q ON r.cq_idquarto = q.idquarto 
                        JOIN hoteis h ON r.cqh_idhotel = h.idhotel 
                        JOIN tipo_quarto t ON r.cqtq_idtipo = t.idtipo_quarto
                        JOIN cliente c ON r.c_idcliente = c.idcliente
                        JOIN usuarios u ON c.u_idusuarios = u.idusuarios
                        WHERE r.cq_idquarto = :idQuarto");

      $stmt->bindParam(':idQuarto', $idQuarto, PDO::PARAM_INT); 
      $stmt->execute();
      $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($reservas) {
          foreach ($reservas as $reserva) {
              echo "<tr class='text-center align-middle'>
                      <td>{$reserva['idreserva']}</td>
                      <td>{$reserva['checkin']}</td>
                      <td>{$reserva['checkout']}</td>
                      <td>{$reserva['cliente']}</td>
                      <td>{$reserva['hotel']}</td>
                      <td>{$reserva['tipoQuarto']}</td>
                      <td> R$ {$reserva['preco']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='7'> Nenhuma reserva encontrada para este quarto. </td></tr>";
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
    <script src="/breeze/codes/js/quartos.js" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Quartos </title>
    <style>
      main{
        max-width: 1300px;
        padding: 1rem;
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
    <table id="tabela-quartos" class="table table-bordered align-middle caption-top mb-0">
      <thead class="table-dark text-center">
        <tr>
          <th class="sortable"> ID </th>
          <th class="sortable"> Hotel </th>
          <th class="sortable"> Número </th>
          <th class="sortable"> Tipo </th>
          <th class="sortable"> Capacidade </th>
          <th class="sortable"> Descrição </th>
          <th class="sortable"> Disponibilidade </th>
          <th class="sortable"> Valor </th>
          <th> Reservas </th>
          <th> Ações </th>
        </tr>
      </thead>
      <tbody>
        <?php
          try {
            $sql = "SELECT q.idquarto, q.numero, q.descricao, q.disponibilidade, q.valor, q.h_idhotel, q.tq_idtipo, q.o_idocupacao, h.nome AS hotel, tq.tipoQuarto AS tipo, o.ocupacao
                    FROM quartos q
                    JOIN hoteis h ON q.h_idhotel = h.idhotel
                    JOIN tipo_quarto tq ON q.tq_idtipo = tq.idtipo_quarto
                    JOIN ocupacao o ON q.o_idocupacao = o.idocupacao";
        
            /** @var PDOStatement $stmt */
            $stmt = $conn->prepare($sql);
        
            if ($stmt->execute()) {
                $quartos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $quartos = [];
            }
        
            if ($quartos) {
              foreach ($quartos as $row) {
                if ($row["disponibilidade"] == "1") {
                  $row["disponibilidade"] = "Disponível";
                } else {
                  $row["disponibilidade"] = "Indisponível";
                }
            
                echo '<tr class="text-center align-middle">
                        <td>'.$row["idquarto"].'</td>
                        <td>'.$row["hotel"].'</td>
                        <td>'.$row["numero"].'</td>
                        <td>'.$row["tipo"].'</td>
                        <td>'.$row["ocupacao"].'</td>
                        <td style="max-width: 200px;">'.$row["descricao"].'</td>
                        <td>'.$row["disponibilidade"].'</td>
                        <td> R$ '.$row["valor"].'</td>
                        <td class="p-1">
                          <button class="btn btn-primary btn-sm w-100 ver-reservas-btn" data-idquarto="'.$row["idquarto"].'" data-bs-toggle="modal" data-bs-target="#modalReservas">
                            Ver Reservas
                          </button>
                        </td>
                        <td class="p-1">
                          <button class="btn btn-success btn-sm w-49 btn-editar" data-id="' . htmlspecialchars($row["idquarto"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                            Editar
                          </button> 
                          <button class="btn btn-danger btn-sm w-49" onclick="removerHospede('.$row["idquarto"].')">
                            Remover
                          </button>
                        </td>
                      </tr>';
            }
            } else {
                echo "<tr><td colspan='10' class='text-center'> Nenhum hotel cadastrado. </td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='10' class='text-danger'> Erro ao buscar hotéis: " . $e->getMessage() . "</td></tr>";
        }      
        ?>
      </tbody>
      <caption class="text-center"> Quartos </caption>
    </table>
  </main>

  <div class="modal fade" id="modalReservas" tabindex="-1" aria-labelledby="modalReservasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReservasLabel"> Reservas no Quarto </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <table class="table table-hover text-center align-middle">
            <thead class="table-secondary">
              <tr>
                <th> ID </th>
                <th> Check-In </th>
                <th> Check-Out </th>
                <th> Cliente </th>
                <th> Hotel </th>
                <th> Tipo Quarto </th>
                <th> Preço da reserva </th>
              </tr>
            </thead>
            <tbody id="corpo-tabela-reservas">
              <tr><td colspan="7"> Selecione um quarto para ver as reservas. </td></tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>