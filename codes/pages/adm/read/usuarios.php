<?php

include __DIR__ . "/../../../conn/conn.php";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <link href="/breeze/codes/css/hospedes.css" rel="stylesheet">
    <script src="/breeze/codes/js/usuarios.js" defer></script> 
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Usuários </title>
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
          <th> Ações </th>
        </tr>
      </thead>
      <tbody>
        <?php
          try {
            $sql = "SELECT u.idusuarios, u.nome, u.email
                    FROM usuarios u";
        
            /** @var PDOStatement $stmt */
            $stmt = $conn->prepare($sql);
        
            if ($stmt->execute()) {
                $usurios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $usurios = [];
            }
        
            if ($usurios) {
              foreach ($usurios as $row) {
            
                echo '<tr class="text-center align-middle">
                        <td>'.$row["idusuarios"].'</td>
                        <td>'.$row["nome"].'</td>
                        <td>'.$row["email"].'</td>
                        <td class="p-1">
                          <button class="btn btn-success btn-sm w-49 btn-editar" data-id="' . htmlspecialchars($row["idusuarios"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                            Editar
                          </button> 
                          <button class="btn btn-danger btn-sm w-49" onclick="removerHospede('.$row["idusuarios"].')">
                            Remover
                          </button>
                        </td>
                      </tr>';
            }
            } else {
                echo "<tr><td colspan='4' class='text-center'>Nenhum hóspede cadastrado.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='4' class='text-danger'>Erro ao buscar hóspedes: " . $e->getMessage() . "</td></tr>";
        }      
        ?>
      </tbody>
      <caption class="text-center"> Usuários </caption>
    </table>
  </main>
</body>
</html>