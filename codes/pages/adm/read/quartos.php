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

 // AÇÃO 1: Buscar quarto para edição
  if (isset($_POST['action']) && $_POST['action'] === 'buscar_quarto') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT idquarto, numero, descricao, disponibilidade, valor, h_idhotel, tq_idtipo, o_idocupacao
                                FROM quartos
                                WHERE idquarto = ?");
        $stmt->execute([$id]);
        $quarto = $stmt->fetch();
        echo json_encode($quarto ?: ['error' => 'Quarto não encontrado']);
    }
    exit;
  }

  // AÇÃO 2: Editar quarto
  if (isset($_POST['action']) && $_POST['action'] === 'editar_quarto') {
    $id = $_POST['idquarto'] ?? null;
    $numero = $_POST['numero'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $disponibilidade = $_POST['disponibilidade'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $tq_idtipo = $_POST['tquarto'] ?? '';
    $o_idocupacao = $_POST['ocupacao'] ?? '';

    if ($id && $numero && $descricao && $valor && $tq_idtipo && $o_idocupacao) {
        try {
            $stmt = $conn->prepare("UPDATE quartos 
                                    SET numero = ?, 
                                        descricao = ?, 
                                        disponibilidade = ?, 
                                        valor = ?,
                                        tq_idtipo = ?, 
                                        o_idocupacao = ?
                                    WHERE idquarto = ?");
            $stmt->execute([$numero, $descricao, $disponibilidade, $valor, $tq_idtipo, $o_idocupacao, $id]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
    }
    exit;
  }

  // AÇÃO 3: Buscar reservas
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['idquarto'])) {
        $idQuarto = (int)$input['idquarto'];

        /** @var PDOStatement $stmt */
        $stmt = $conn->prepare("SELECT r.idreserva, r.checkin, r.checkout, r.preco,
                                q.numero, t.tipoQuarto, h.nome AS hotel, u.nome AS cliente
                          FROM reserva r
                          JOIN quartos q ON r.q_idquarto = q.idquarto 
                          JOIN hoteis h ON q.h_idhotel = h.idhotel 
                          JOIN tipo_quarto t ON q.tq_idtipo = t.idtipo_quarto
                          JOIN cliente c ON r.c_idcliente = c.idcliente
                          JOIN usuarios u ON c.u_idusuarios = u.idusuarios
                          WHERE r.q_idquarto = :idQuarto");

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

  // AÇÃO 4: Verificar se há FK em reservas
  if (isset($_POST['action']) && $_POST['action'] === 'verificar_remocao') {
    $id = $_POST['id'] ?? null;
    header('Content-Type: application/json; charset=utf-8');
    if ($id) {
        // conta quantas reservas existem para este quarto
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reserva WHERE q_idquarto = ?");
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
  if (isset($_POST['action']) && $_POST['action'] === 'remover_quarto') {
    $id = $_POST['id'] ?? null;
    header('Content-Type: application/json; charset=utf-8');
    if ($id) {
        try {
            $del = $conn->prepare("DELETE FROM quartos WHERE idquarto = ?");
            $del->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Quarto removido com sucesso!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error',   'message' => 'Erro ao remover: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
    }
    exit;
  }

  // AÇÃO 6: Upload de imagens
  if (isset($_POST['action']) && $_POST['action'] === 'upload_imagens') {
    $idQuarto = $_POST['idquarto'] ?? null;
    $response = ['success' => false];
    
    try {
        if (!$idQuarto) {
            throw new Exception('ID do quarto inválido.');
        }
        
        if (empty($_FILES['imagens'])) {
            throw new Exception('Nenhum arquivo enviado.');
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/breeze/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadedFiles = [];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
            $fileName = $_FILES['imagens']['name'][$key];
            $fileType = $_FILES['imagens']['type'][$key];
            $fileSize = $_FILES['imagens']['size'][$key];
            $fileError = $_FILES['imagens']['error'][$key];
            
            // Verificação do tipo de arquivo
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Tipo de arquivo não permitido: $fileName");
            }
            
            // Verificação de erro no upload
            if ($fileError !== UPLOAD_ERR_OK) {
                throw new Exception("Erro no upload do arquivo: $fileName (Código: $fileError)");
            }
            
            // Verificação de tamanho (5MB máximo)
            if ($fileSize > 5 * 1024 * 1024) {
                throw new Exception("Arquivo muito grande: $fileName (Máximo: 5MB)");
            }
            
            // Gerar nome único para o arquivo
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'img_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpName, $destination)) {
                $stmt = $conn->prepare("INSERT INTO imagens (diretorio, q_idquarto) VALUES (?, ?)");
                $stmt->execute([$newFileName, $idQuarto]);
                $uploadedFiles[] = $fileName;
            } else {
                throw new Exception("Falha ao mover o arquivo: $fileName");
            }
        }
        
        $response = ['success' => true, 'message' => count($uploadedFiles) . " imagem(ns) carregada(s) com sucesso!", 'files' => $uploadedFiles];
        
    } catch (Exception $e){
        $response = ['success' => false, 'error' => $e->getMessage()];
      }
    
    echo json_encode($response);
    exit;
  }

  // AÇÃO 7: Listar imagens de um quarto
  if (isset($_POST['action']) && $_POST['action'] === 'listar_imagens') {
    $idQuarto = $_POST['idquarto'] ?? null;
    if ($idQuarto) {
        $stmt = $conn->prepare("SELECT idimagens, diretorio FROM imagens WHERE q_idquarto = ?");
        $stmt->execute([$idQuarto]);
        $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // CORREÇÃO: Usar caminho absoluto e garantir que o arquivo existe
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/breeze/images/';
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/breeze/images/';
        
        $html = '';
        foreach ($imagens as $imagem) {
            $filePath = $basePath . $imagem['diretorio'];
            
            // Verificar se o arquivo realmente existe
            if (file_exists($filePath)) {
                $html .= '<div class="col">';
                $html .= '<div class="count">';
                $html .= '<img src="' . $baseUrl . $imagem['diretorio'] . '" class="card-img-top img-fluid" style="height: 200px; object-fit: cover;">';
                $html .= '<div class="card-footer text-center p-2 px-3">';
                $html .= '<button class="btn btn-danger btn-sm btn-remover-imagem" data-idimagem="' . $imagem['idimagens'] . '">';
                $html .= '<i class="bi bi-trash"></i> Remover';
                $html .= '</button>';
                $html .= '</div></div></div>';
            }
        }

        if ($html === '') {
            $html = '<div class="col-12 text-center px-5"><i class="bi bi-images fs-1"></i><p class="mt-3">Nenhuma imagem cadastrada para este quarto.</p></div>';
        }

        echo $html;
    } else {
        echo '<div class="col-12 text-center px-5"><i class="bi bi-exclamation-triangle fs-1"></i><p class="mt-3">ID do quarto inválido.</p></div>';
    }
    exit;
  }

  // AÇÃO 8: Remover imagem
  if (isset($_POST['action']) && $_POST['action'] === 'remover_imagem') {
    $idImagem = $_POST['idimagem'] ?? null;
    $response = ['success' => false];

    if ($idImagem) {
        // Primeiro, buscamos o diretório da imagem para removê-lo do servidor
        $stmt = $conn->prepare("SELECT diretorio FROM imagens WHERE idimagens = ?");
        $stmt->execute([$idImagem]);
        $imagem = $stmt->fetch();

        if ($imagem) {
            $filePath = __DIR__ . '/../../images/quartos/' . $imagem['diretorio'];
            $del = $conn->prepare("DELETE FROM imagens WHERE idimagens = ?");
            if ($del->execute([$idImagem])) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $response['success'] = true;
                $response['message'] = 'Imagem removida com sucesso!';
            } else {
                $response['error'] = 'Erro ao remover do banco de dados.';
            }
        } else {
            $response['error'] = 'Imagem não encontrada.';
        }
    } else {
        $response['error'] = 'ID inválido.';
    }

    echo json_encode($response);
    exit;
  }
}

try {
    // Buscar hotéis
    $stmt_hotel = $conn->prepare("SELECT idhotel, nome FROM hoteis ORDER BY nome");
    $stmt_hotel->execute();
    $hoteis = $stmt_hotel->fetchAll(PDO::FETCH_ASSOC);

    // Buscar tipos de quarto
    $stmt_tipoQuarto = $conn->prepare("SELECT idtipo_quarto, tipoQuarto FROM tipo_quarto ORDER BY tipoQuarto");
    $stmt_tipoQuarto->execute();
    $tiposQuarto = $stmt_tipoQuarto->fetchAll(PDO::FETCH_ASSOC);

    // Buscar ocupações
    $stmt_ocupacao = $conn->prepare("SELECT idocupacao, ocupacao FROM ocupacao ORDER BY ocupacao");
    $stmt_ocupacao->execute();
    $ocupacoes = $stmt_ocupacao->fetchAll(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="/breeze/codes/css/read.css?v=<?= time() ?>" defer rel="stylesheet">
  <script src="/breeze/codes/js/adm/view/quartos.js?v=<?= time() ?>" defer></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title> Quartos | Breeze </title>
  <style>
    .main-container {
      padding: 2px;
      max-width: 1450px;
      margin: 0 auto;
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
      <h1 class="page-title">Quartos</h1>
      <div></div> <!-- Spacer for alignment -->
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
      <div class="filter-header">
        <h2 class="filter-title"> Filtrar Quartos </h2>
      </div>
      
      <div class="filter-controls">
        <div class="filter-group">
          <label class="filter-label"> ID </label>
          <input type="text" class="filter-input" id="filterId" placeholder="ID do quarto">
        </div>

        <div class="filter-group">
          <label class="filter-label"> Hotel </label>
          <select class="filter-input" id="filterHotel">
            <option value=""> Todos </option>
            <?php foreach ($hoteis as $hotel): ?>
              <option value="<?= htmlspecialchars($hotel['nome']) ?>">
                <?= htmlspecialchars($hotel['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Número </label>
          <input type="text" class="filter-input" id="filterNumber" maxlength="3" placeholder="N° do quarto">
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Tipo </label>
          <select class="filter-input" id="filterType">
            <option value=""> Todos </option>
            <?php foreach ($tiposQuarto as $tipo): ?>
              <option value="<?= htmlspecialchars($tipo['tipoQuarto']) ?>">
                <?= htmlspecialchars($tipo['tipoQuarto']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Capacidade </label>
          <select class="filter-input" id="filterCapacity">
            <option value=""> Todas </option>
            <?php foreach ($ocupacoes as $ocupacao): ?>
              <option value="<?= htmlspecialchars($ocupacao['ocupacao']) ?>">
                <?= htmlspecialchars($ocupacao['ocupacao']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Disponibilidade </label>
          <select class="filter-input" id="filterAvailability">
            <option value="">Todas</option>
            <option value="Disponível"> Disponível </option>
            <option value="Indisponível"> Indisponível </option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label"> Valor Mínimo </label>
          <input type="text" class="filter-input" id="filterMinValue" placeholder="R$ 0,00" min="0" step="0.01">
        </div>
        
        <div class="filter-group">
          <label class="filter-label"> Valor Máximo </label>
          <input type="text" class="filter-input" id="filterMaxValue" placeholder="R$ 0,00" min="0" step="0.01">
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
      <div class="table-title">Lista de Quartos</div>
      <div class="table-responsive">
        <table id="tabela-quartos" class="table align-middle">
          <thead>
            <tr>
              <th class="sortable"> ID </th>
              <th class="sortable"> Hotel </th>
              <th class="sortable"> Número </th>
              <th class="sortable"> Tipo </th>
              <th class="sortable"> Capacidade </th>
              <th class="sortable"> Descrição </th>
              <th class="sortable"> Disponibilidade </th>
              <th class="sortable"> Valor </th>
              <th> Imagens </th>
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
                    $disponibilidade = $row["disponibilidade"] == "1" 
                      ? '<span class="badge badge-available"> Disponível </span>' 
                      : '<span class="badge badge-unavailable"> Indisponível </span>';

                    $valor = 'R$ ' . number_format($row["valor"], 2, ',', '.');

                    // Descrição truncada
                    $descricao = $row["descricao"];
                    $descricaoTruncada = (strlen($descricao) > 15) 
                      ? substr($descricao, 0, 15) . '...' 
                      : $descricao;
                
                    echo '<tr class="text-center align-middle">
                            <td>'.$row["idquarto"].'</td>
                            <td>'.$row["hotel"].'</td>
                            <td style="max-width: 10px;">'.$row["numero"].'</td>
                            <td style="max-width: 100px;">'.$row["tipo"].'</td>
                            <td>'.$row["ocupacao"].'</td>
                            <td style="max-width: 100px;"> 
                              <div class="descricao-truncada" data-bs-toggle="modal" data-bs-target="#modalDescricao" data-descricao="'.htmlspecialchars($descricao).'">
                                '.htmlspecialchars($descricaoTruncada).'
                              </div> 
                            </td>
                            <td>'.$disponibilidade.'</td>
                            <td>'.$valor.'</td>
                            <td>
                              <div class="action-buttons">
                                <button class="action-btn btn-add" vimg="' . htmlspecialchars($row["idquarto"]) . '" data-bs-toggle="modal" data-bs-target="#modalAddImagem">
                                  <i class="bi bi-plus-lg"></i> Add
                                </button>
                                <button class="action-btn btn-view" vimg="' . htmlspecialchars($row["idquarto"]) . '" data-bs-toggle="modal" data-bs-target="#modalViewImagem">
                                  <i class="bi bi-eye"></i> Ver
                                </button> 
                              </div>
                            </td>
                            <td>
                              <button class="action-btn btn-view ver-reservas-btn" reservas="'.$row["idquarto"].'" data-bs-toggle="modal" data-bs-target="#modalReservas">
                                <i class="bi bi-calendar-check"></i> Reservas
                              </button>
                            </td>
                            <td>
                              <div class="action-buttons">
                                <button class="action-btn btn-edit btn-editar" editar="' . htmlspecialchars($row["idquarto"]) . '" data-bs-toggle="modal" data-bs-target="#modalEditar">
                                  <i class="bi bi-pencil"></i> Editar
                                </button> 
                                <button class="action-btn btn-delete btn-remover" remover="' . $row["idquarto"] . '" data-bs-toggle="modal" data-bs-target="#modalRemover">
                                  <i class="bi bi-trash"></i> Remover
                                </button>
                              </div>
                            </td>
                          </tr>';
                }
                } else {
                    echo "<tr><td colspan='11' class='text-center py-4'> Nenhum quarto cadastrado. </td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='11' class='text-center text-danger py-4'> Erro ao buscar quartos: " . $e->getMessage() . "</td></tr>";
            }      
            ?>
            <tr id="noResultsRow" style="display: none;">
              <td colspan="11" style="text-align: center;"> Nenhum quarto encontrado para os filtros aplicados. </td>
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
          <h5 class="modal-title" id="modalReservasLabel">Reservas no Quarto</h5>
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
              <tr><td colspan="7" class="py-4"> Selecione um quarto para ver as reservas. </td></tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Descrição -->
  <div class="modal fade" id="modalDescricao" tabindex="-1" aria-labelledby="modalDescricaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDescricaoLabel"> Descrição Completa </h5>
        </div>
        <div class="modal-body">
          <p id="descricaoCompleta" class="lead"></p>
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
          <h5 class="modal-title"> Editar Quarto </h5>
        </div>
        <div class="modal-body">
          <form id="formEditarQuarto" method="POST">
            <input type="hidden" name="action" value="editar_quarto">
            <input type="hidden" id="editId" name="idquarto">
            <div class="row">
              <div class="col mb-3">
                <label for="editNumero" class="form-label">Número do Quarto</label>
                <input type="text" class="form-control" id="editNumero" name="numero" maxlength="3" required>
              </div>
            
              <div class="col mb-3">
                <label for="editTipoQuarto" class="form-label">Tipo de Quarto</label>
                <select class="form-select" id="editTipoQuarto" name="tquarto" required>
                  <?php foreach ($tiposQuarto as $tipo): ?>
                    <option value="<?= $tipo['idtipo_quarto'] ?>">
                      <?= htmlspecialchars($tipo['tipoQuarto']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col mb-3">
                <label for="editOcupacao" class="form-label">Ocupação</label>
                <select class="form-select" id="editOcupacao" name="ocupacao" required>
                  <?php foreach ($ocupacoes as $ocupacao): ?>
                    <option value="<?= $ocupacao['idocupacao'] ?>">
                      <?= htmlspecialchars($ocupacao['ocupacao']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="editDescricao" class="form-label">Descrição</label>
              <textarea class="form-control" id="editDescricao" name="descricao" rows="3" required></textarea>
            </div>

            <div class="row">
              <div class="col mb-3">
                <label for="editDisponibilidade" class="form-label">Disponibilidade</label>
                <select class="form-select" id="editDisponibilidade" name="disponibilidade" required>
                  <option value="1">Disponível</option>
                  <option value="0">Indisponível</option>
                </select>
              </div>

              <div class="col mb-3">
                <label for="editValor" class="form-label">Valor</label>
                  <input type="text" class="form-control" id="editValor" name="valor" required>
              </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
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
          <h5 class="modal-title" id="modalRemoverLabel">Confirmar Remoção</h5>
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
        <div class="modal-body text-center p-4">
          <div id="modalIcon" class="notification-icon"></div>
          <h5 id="modalMessage" class="notification-message"></h5>
          <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Entendido</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para Adicionar Imagens -->
  <div class="modal fade" id="modalAddImagem" tabindex="-1" aria-labelledby="modalAddImagemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddImagemLabel">Adicionar Imagens</h5>
        </div>
        <div class="modal-body">
          <form id="formAddImagem" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_imagens">
            <input type="hidden" id="addImagemIdQuarto" name="idquarto">
            
            <div class="upload-area" id="uploadArea">
              <i class="bi bi-cloud-arrow-up"></i>
              <h5>Arraste e solte suas imagens aqui</h5>  
              <p>ou clique para selecionar arquivos</p>
              <p class="small text-muted mt-2">Formatos suportados: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB por imagem.</p>
            </div>
            
            <input class="d-none" type="file" id="imagens" name="imagens[]" multiple accept="image/*">
                    
            <div class="preview-container" id="previewContainer"></div>

            <div id="uploadFeedback" class="upload-feedback"></div>
    
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancelar </button>
              <button type="submit" class="btn btn-primary" id="btnSubmitImages"> Enviar Imagens </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para Visualizar Imagens -->
  <div class="modal fade" id="modalViewImagem" tabindex="-1" aria-labelledby="modalViewImagemLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalViewImagemLabel">Imagens do Quarto</h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <h6> Total de imagens: <span id="imageCount"> 0 </span></h6>
                <button class="btn btn-sm btn-outline-primary" id="btnRefreshImages">
                  <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
              </div>
            </div>
          </div>
          
          <div class="image-grid" id="containerImagens">
            <div class="no-images-placeholder">
              <i class="bi bi-images"></i>
              <h5> Nenhuma imagem encontrada </h5>
              <p class="mb-0"> Adicione imagens usando o botão "Adicionar Imagens" </p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
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