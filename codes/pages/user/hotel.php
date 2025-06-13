<?php

session_start();

include __DIR__ . "/../../conn/conn.php";

$email = $_SESSION['usuario_email'];
$moeda_selecionada = $_SESSION['moeda_selecionada'] ?? 'BRL';
$simbolo_moeda = $_SESSION['simbolo_moeda'] ?? 'R$';
$taxa_conversao = $_SESSION['taxa_conversao'] ?? 1;

if (!isset($email)) {
    echo "Você precisa estar logado para fazer uma reserva.";
    exit;
}

// Usando PDO para obter o ID do usuário
$sql_get_id = "SELECT idusuarios FROM usuarios WHERE email = :email";
$stmt = $conn->prepare($sql_get_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$idusuario = $usuario['idusuarios'];

if (!$idusuario) {
    echo "ID do usuário não encontrado." . $idusuario . $email;
    exit;
}

$id_hotel = intval($_GET['id']);
$sql_hotel = "SELECT * FROM hoteis WHERE idhotel = :idhotel";
$stmt_hotel = $conn->prepare($sql_hotel);
$stmt_hotel->bindParam(':idhotel', $id_hotel, PDO::PARAM_INT);
$stmt_hotel->execute();

$hotel = $stmt_hotel->fetch(PDO::FETCH_ASSOC);


if (!$hotel) {
    echo "<p>Hotel não encontrado.</p>";
    exit;
}

// Usando PDO para obter os tipos de quarto
$sql_tipos = "SELECT * FROM tipo_quarto";
$stmt_tipos = $conn->query($sql_tipos);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $idtipo = intval($_POST['tipo_quarto']);

    // Verifica se o cliente já existe
    $sql_verifica_cliente = "SELECT * FROM cliente WHERE u_idusuarios = :idusuario";
    $stmt_cliente = $conn->prepare($sql_verifica_cliente);
    $stmt_cliente->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmt_cliente->execute();

    echo ' num de linhas query: ' . $stmt_cliente->rowCount();
    if ($stmt_cliente->rowCount() == 0) {
        echo 'TRUE';
    } else {
        echo 'FALSE';
    }

    if (!isset($_POST['cpf']) || !isset($_POST['telefone'])) {
        echo 'TRUE';
    } else {
        echo 'FALSE';
    }

    if ($stmt_cliente->rowCount() == 0) {
        if (!isset($_POST['cpf']) || !isset($_POST['telefone'])) {
            echo 'oioioiosandjansd';
            echo "<script>document.addEventListener('DOMContentLoaded', function() { $('#clienteModal').modal('show'); });</script>";
        } else {
            $cpf = $_POST['cpf'];
            $telefone = $_POST['telefone'];

            // Verifica disponibilidade de quarto
            $sql_quarto_disponivel = "
                SELECT q.idquarto FROM quartos q
                WHERE q.h_idhotel = :id_hotel AND q.tq_idtipo = :idtipo AND q.idquarto NOT IN (
                    SELECT cq_idquarto FROM reserva
                    WHERE cqh_idhotel = :id_hotel
                      AND (
                            (check_in <= :checkin AND check_out > :checkin) OR
                            (check_in < :checkout AND check_out >= :checkout) OR
                            (:checkin <= check_in AND :checkout >= check_out)
                      )
                ) LIMIT 1
            ";
            $stmt_quarto = $conn->prepare($sql_quarto_disponivel);
            $stmt_quarto->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
            $stmt_quarto->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
            $stmt_quarto->bindParam(':checkin', $checkin);
            $stmt_quarto->bindParam(':checkout', $checkout);
            $stmt_quarto->execute();

            if ($stmt_quarto->rowCount() == 0) {
                echo "<div class='alert alert-danger'>Nenhum quarto disponível.</div>";
                echo "<a href='index.php' class='btn btn-secondary' type='button'>Voltar para a Página Principal</a>";
                exit;
            }

            $quarto = $stmt_quarto->fetch(PDO::FETCH_ASSOC);
            $idquarto = $quarto['idquarto'];

            // Inserir cliente no banco
            $sql_cliente = "INSERT INTO cliente (cpf, telefone, q_idquarto, qh_idhotel, qtq_idtipo, u_idusuarios)
                            VALUES (:cpf, :telefone, :idquarto, :id_hotel, :idtipo, :idusuario)";
            $stmt_cliente_insert = $conn->prepare($sql_cliente);
            $stmt_cliente_insert->bindParam(':cpf', $cpf);
            $stmt_cliente_insert->bindParam(':telefone', $telefone);
            $stmt_cliente_insert->bindParam(':idquarto', $idquarto, PDO::PARAM_INT);
            $stmt_cliente_insert->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
            $stmt_cliente_insert->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
            $stmt_cliente_insert->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
            $stmt_cliente_insert->execute();
        }
    } else {
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        $idquarto = null;

        // Verifica disponibilidade de quarto novamente
        $sql_quarto_disponivel = "
            SELECT q.idquarto FROM quartos q
            WHERE q.h_idhotel = :id_hotel AND q.tq_idtipo = :idtipo AND q.idquarto NOT IN (
                SELECT cq_idquarto FROM reserva
                WHERE cqh_idhotel = :id_hotel
                  AND (
                        (check_in <= :checkin AND check_out > :checkin) OR
                        (check_in < :checkout AND check_out >= :checkout) OR
                        (:checkin <= check_in AND :checkout >= check_out)
                  )
            ) LIMIT 1
        ";
        $stmt_quarto = $conn->prepare($sql_quarto_disponivel);
        $stmt_quarto->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
        $stmt_quarto->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
        $stmt_quarto->bindParam(':checkin', $checkin);
        $stmt_quarto->bindParam(':checkout', $checkout);
        $stmt_quarto->execute();

        if ($stmt_quarto->rowCount() == 0) {
            echo "<div class='alert alert-warning'>Não há reservas disponíveis para essa data.</div>";
            echo "<a href='index.php' class='btn btn-secondary' type='button'>Voltar para a Página Principal</a>";
            exit;
        }

        $quarto = $stmt_quarto->fetch(PDO::FETCH_ASSOC);
        $idquarto = $quarto['idquarto'];
    }

    // Verifica novamente o cliente
    $sql_get_cliente = "SELECT idcliente FROM cliente WHERE u_idusuarios = :idusuario";
    $stmt_cliente_get = $conn->prepare($sql_get_cliente);
    $stmt_cliente_get->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmt_cliente_get->execute();
    $cliente = $stmt_cliente_get->fetch(PDO::FETCH_ASSOC);
    $idcliente = $cliente['idcliente'];

    // Calculo de preços
    $precos = [1 => 200, 2 => 300, 3 => 500];
    $preco_noite = $precos[$idtipo] ?? 300;

    $timestamp_checkin = strtotime($checkin);
    $timestamp_checkout = strtotime($checkout);
    $dias = ceil(($timestamp_checkout - $timestamp_checkin) / (60 * 60 * 24));

    if ($dias <= 0) $dias = 1; // evita total 0 em caso de datas iguais ou erradas

    $total = $dias * $preco_noite;

    // Inserir reserva
    $sql_insert = "INSERT INTO reserva (check_in, check_out, preco, c_idcliente, cq_idquarto, cqh_idhotel, cqtq_idtipo)
                   VALUES (:checkin, :checkout, :total, :idcliente, :idquarto, :id_hotel, :idtipo)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bindParam(':checkin', $checkin);
    $stmt_insert->bindParam(':checkout', $checkout);
    $stmt_insert->bindParam(':total', $total);
    $stmt_insert->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
    $stmt_insert->bindParam(':idquarto', $idquarto, PDO::PARAM_INT);
    $stmt_insert->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
    $stmt_insert->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
    $stmt_insert->execute();

    echo "<div class='alert alert-success'>Reserva realizada com sucesso!</div>";

    
function getCurrenciesList() {
    return [
        // Moedas suportadas pela API Frankfurter (podem converter entre si)
        ['code' => 'USD', 'name' => 'Dólar Americano', 'symbol' => '$', 'country_code' => 'us', 'api_supported' => true],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'country_code' => 'eu', 'api_supported' => true],
        ['code' => 'GBP', 'name' => 'Libra Esterlina', 'symbol' => '£', 'country_code' => 'gb', 'api_supported' => true],
        ['code' => 'JPY', 'name' => 'Iene Japonês', 'symbol' => '¥', 'country_code' => 'jp', 'api_supported' => true],
        ['code' => 'AUD', 'name' => 'Dólar Australiano', 'symbol' => 'A$', 'country_code' => 'au', 'api_supported' => true],
        ['code' => 'CAD', 'name' => 'Dólar Canadense', 'symbol' => 'CA$', 'country_code' => 'ca', 'api_supported' => true],
        ['code' => 'CHF', 'name' => 'Franco Suíço', 'symbol' => 'CHF', 'country_code' => 'ch', 'api_supported' => true],
        
        // Real Brasileiro (BRL) - tratado de forma especial
        ['code' => 'BRL', 'name' => 'Real Brasileiro', 'symbol' => 'R$', 'country_code' => 'br', 'api_supported' => false],
    ];
}

function getCountryCodeByCurrency($currency) {
  $map = [
      'USD' => 'us', 'BRL' => 'br', 'CAD' => 'ca',
  ];
  
  return $map[$currency] ?? 'us'; // Default para US caso não encontre
}

}
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title><?php echo $hotel['nome']; ?> - Reservar</title>
  <style>
    .color{
        color: white;
        border-radius: 10px;
    }
    .color:hover{
        color: white;
        background-color:rgba(255, 255, 255, 0.2);
    }
    .dropdown-toggle::after {
        display: none;
    }
    .currency-converter {
      display: flex;
      align-items: center;
      margin-right: 15px;
    }
    .currency-converter input {
      width: 80px;
      margin-right: 5px;
    }
    .currency-converter select {
      width: 70px;
      margin-right: 5px;
    }
    .currency-item {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .currency-item:hover {
      background-color: rgba(0, 0, 0, 0.1) !important;
      border-radius: 3px;
    }
    .bg {
      background-color: rgba(0, 0, 0, 0);
      border: rgba(0, 0, 0, 0);
    }

    .bg:hover {
      background-color: rgba(0, 0, 0, 0.1);
      border: rgba(0, 0, 0, 0);
      color: white;
    }

    #conversionResult {
      color: white;
      margin-left: 10px;
      font-size: 0.9rem;
    }
    @media (max-width: 992px) {
      .currency-converter {
        margin: 10px 0;
        width: 100%;
      }
    }
    .selected-currency {
      position: relative;
    }
    .selected-currency::after {
      content: "";
      position: absolute;
      right: 10px;
      width: 16px;
      height: 16px;
      background-color: #0d6efd;
      mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'/%3E%3C/svg%3E");
    }
    .hotel-img {
      max-height: 400px;
      object-fit: cover;
    }

    .navbar-collapse{
    display: flex;
    justify-content: right;
  }

  .fundo{
    background-color: #2c3e50 !important;
  }
  </style>
</head>
<body>

  <!-- Navbar -->
    <header class="fundo py-2">
        <nav class="navbar navbar-expand-lg navbar-dark fundo">
        <div class="container-fluid">
            <div class="d-flex justify-content-left align-items-left mx-3">
            <a href="index.php"> <img src="/breeze/images/logob.png" height="60" width="120"/> </a>
            </div>
            <div class="collapse navbar-collapse align-items-right" id="navbarSupportedContent">
            <div class="currency-selector btn btn-dark bg ms-3" data-bs-toggle="modal" data-bs-target="#currencyModal">
                <span class="currency-display"><?= $moeda_selecionada ?></span>
            </div>
            <form class="d-flex mx-5" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-light" type="submit"> Search </button>
            </form>
            <ul class="navbar-nav ms-5">
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="/breeze/images/login.png" width="40" height="40" class="rounded-circle" alt="User">
                </a>
                <?php if (isset($_SESSION['usuario_logado'])): ?>
                    <ul class="dropdown-menu">
                    <!-- <li><span class="dropdown-item-text"> <?= htmlspecialchars($_SESSION['usuario_nome']) ?> </span></li> -->
                    <li> <a href="logout.php" class="dropdown-item"> Logout </a> </li>
                    </ul>
                    <?php else: ?>
                    <ul class="dropdown-menu">
                    <li> <a class="dropdown-item" href="cadastrar.php"> Cadastrar </a> </li>
                    <li> <hr class="dropdown-divider"> </li>
                    <li> <a class="dropdown-item" href="login.php"> Login </a> </li>
                    </ul>
                <?php endif; ?>
                </li>
            </ul>
            </div>
        </div>     
        </nav>
    </header>

  <main>
    <div class="container mt-5">
      <h2><?php echo $hotel['nome']; ?></h2>
      <!--<img src="../imagens/<?php echo $hotel['imagem']; ?>" class="img-fluid mb-4 hotel-img"> nao tem image -->
      <p><strong>Endereço:</strong> <?php echo $hotel['endereço']; ?></p>
      <hr>
      <h4>Fazer Reserva</h4>
      <form method="POST">
        <div class="form-group">
          <label for="checkin">Data de Entrada:</label>
          <input type="date" class="form-control" name="checkin" required>
        </div>
        <div class="form-group">
          <label for="checkout">Data de Saída:</label>
          <input type="date" class="form-control" name="checkout" required>
        </div>
        <div class="form-group">
          <label for="tipo_quarto">Tipo de Quarto:</label>
          <select class="form-control" name="tipo_quarto" id="tipo_quarto" required>
            <option value="">Selecione</option>
            <?php
            $precos = [1 => 200, 2 => 300, 3 => 500]; // Preços por tipo
            while($tipo = $stmt_tipos->fetch(PDO::FETCH_ASSOC)):
              $id = $tipo['idtipo_quarto'];
              $valor = $precos[$id] ?? 300;
              $valor_convertido = $valor * $taxa_conversao;
              ?>
              <option value="<?php echo $id; ?>" data-preco="<?php echo $valor; ?>">
                <?php echo $tipo['tipoQuarto']; ?> - <?= $simbolo_moeda ?> <?= number_format($valor_convertido, 2) ?> / noite
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <p id="disponibilidade_msg" class="mt-2 font-weight-bold"></p>

        <h1 class="my-4">Total: <span id="total_reserva"><?= $simbolo_moeda ?> 0,00</span></h1>
        <button type="submit" class="btn btn-primary">Reservar</button>
        <a href="index.php" class="btn btn-secondary" type="button">Voltar para a Página Principal</a>
      </form>
    </div>

    <!-- Modal p completar cadastro -->
    <div class="modal fade" id="clienteModal" tabindex="-1" role="dialog" aria-labelledby="clienteModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form method="POST">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Complete seu cadastro</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="checkin" value="<?php echo $_POST['checkin'] ?? ''; ?>">
              <input type="hidden" name="checkout" value="<?php echo $_POST['checkout'] ?? ''; ?>">
              <input type="hidden" name="tipo_quarto" value="<?php echo $_POST['tipo_quarto'] ?? ''; ?>">

              <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" class="form-control" name="cpf" required>
              </div>
              <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="text" class="form-control" name="telefone" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Confirmar Reserva</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Seleção de Moeda -->
    <div class="modal fade" id="currencyModal" tabindex="-1" aria-labelledby="currencyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="currencyModalLabel"> Selecione sua moeda </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <?php
                $moedas = getCurrenciesList();
                foreach ($moedas as $moeda): 
                  $isSelected = ($moeda['code'] == $moeda_selecionada); ?>
                  <div class="col-lg-4">
                    <div class="currency-item mb-2 p-1 d-flex align-items-center rounded <?= $isSelected ? 'bg-primary bg-opacity-10 selected-currency' : '' ?>"
                      data-currency="<?= $moeda['code'] ?>"
                      data-symbol="<?= $moeda['symbol'] ?>">
                      <img src="https://flagcdn.com/w20/<?= $moeda['country_code'] ?>.png" class="currency-flag me-2" alt="<?= $moeda['code'] ?>">
                      <span class="pe-1"><?= $moeda['name'] ?> (<?= $moeda['code'] ?>)</span>
                      <span class="currency-symbol"><?= $moeda['symbol'] ?></span>
                    </div>
                  </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Taxas de fallback atualizadas (BRL para outras moedas)
    const FALLBACK_RATES = {
        'USD': 0.19,   // 1 BRL = 0.19 USD
        'EUR': 0.17,
        'GBP': 0.15,
        'JPY': 25.50,
        'AUD': 0.29,
        'CAD': 0.25,
        'CHF': 0.17,
        'MXN': 3.33,
        'ARS': 270.27
    };

    // Função para formatar o valor monetário
    function formatCurrency(value, currencySymbol) {
        return currencySymbol + ' ' + value.toFixed(2).replace('.', ',');
    }

    // Calcular preço
    document.addEventListener('DOMContentLoaded', function () {
        const checkin = document.querySelector('input[name="checkin"]');
        const checkout = document.querySelector('input[name="checkout"]');
        const tipoQuarto = document.getElementById('tipo_quarto');
        const totalSpan = document.getElementById('total_reserva');
        const currencySymbol = '<?= $simbolo_moeda ?>';
        const conversionRate = <?= $taxa_conversao ?>;

        function calcularTotal() {
            const dataCheckin = new Date(checkin.value);
            const dataCheckout = new Date(checkout.value);
            const precoNoite = parseFloat(tipoQuarto.options[tipoQuarto.selectedIndex]?.dataset.preco || 0) * conversionRate;

            if (checkin.value && checkout.value) {
                if (dataCheckin >= dataCheckout) {
                    alert('A data de entrada deve ser anterior à data de saída.');
                    checkout.value = '';
                    totalSpan.textContent = formatCurrency(0, currencySymbol);
                    return;
                }
            }

            if (checkin.value && checkout.value && precoNoite > 0) {
                const msPorDia = 1000 * 60 * 60 * 24;
                const dias = Math.floor((dataCheckout - dataCheckin) / msPorDia);
                
                if (dias > 0) {
                    const total = dias * precoNoite;
                    totalSpan.textContent = formatCurrency(total, currencySymbol);
                } else {
                    totalSpan.textContent = formatCurrency(precoNoite, currencySymbol);
                }
            } else {
                totalSpan.textContent = formatCurrency(0, currencySymbol);
            }
        }

        checkin.addEventListener('change', calcularTotal);
        checkout.addEventListener('change', calcularTotal);
        tipoQuarto.addEventListener('change', calcularTotal);
    });

    // Checar disponibilidade
    document.addEventListener('DOMContentLoaded', function () {
        const checkin = document.querySelector('input[name="checkin"]');
        const checkout = document.querySelector('input[name="checkout"]');
        const tipoQuarto = document.getElementById('tipo_quarto');
        const disponibilidadeMsg = document.getElementById('disponibilidade_msg');

        async function verificarDisponibilidade() {
            const dataCheckin = checkin.value;
            const dataCheckout = checkout.value;
            const tipo = tipoQuarto.value;
            const idHotel = <?php echo $id_hotel; ?>;

            if (dataCheckin && dataCheckout && tipo) {
                const response = await fetch(`verifica_disponibilidade.php?hotel=${idHotel}&tipo_quarto=${tipo}&checkin=${dataCheckin}&checkout=${dataCheckout}`);
                const status = await response.text();

                if (status === 'disponivel') {
                    disponibilidadeMsg.textContent = "Quarto disponível para essas datas.";
                    disponibilidadeMsg.className = "text-success font-weight-bold";
                } else {
                    disponibilidadeMsg.textContent = "Sem disponibilidade para esse tipo de quarto nas datas selecionadas.";
                    disponibilidadeMsg.className = "text-danger font-weight-bold";
                }
            } else {
                disponibilidadeMsg.textContent = "";
            }
        }

        checkin.addEventListener('change', verificarDisponibilidade);
        checkout.addEventListener('change', verificarDisponibilidade);
        tipoQuarto.addEventListener('change', verificarDisponibilidade);
    });

    // Função principal para conversão de moeda
    async function convertCurrency(fromCurrency, toCurrency, amount = 1) {
        if (fromCurrency === toCurrency) return amount;
        
        try {
            const moedas = <?= json_encode(getCurrenciesList()) ?>;
            const fromMoeda = moedas.find(m => m.code === fromCurrency);
            const toMoeda = moedas.find(m => m.code === toCurrency);
            
            if (fromMoeda.api_supported && toMoeda.api_supported) {
                const response = await fetch(`https://api.frankfurter.app/latest?from=${fromCurrency}&to=${toCurrency}`);
                const data = await response.json();
                return amount * data.rates[toCurrency];
            }
            
            if (fromCurrency === 'BRL' && toMoeda.api_supported) {
                return amount / FALLBACK_RATES[toCurrency];
            }
            
            if (toCurrency === 'BRL' && fromMoeda.api_supported) {
                return amount * FALLBACK_RATES[fromCurrency];
            }
            
            if (fromCurrency !== 'BRL' && toCurrency !== 'BRL') {
                const toBRL = amount / FALLBACK_RATES[fromCurrency];
                return toBRL / FALLBACK_RATES[toCurrency];
            }
            
            throw new Error('Par de moedas não suportado');
            
        } catch (error) {
            console.error('Erro na API, usando fallback:', error);
            if (fromCurrency === 'BRL' && FALLBACK_RATES[toCurrency]) {
                return amount / FALLBACK_RATES[toCurrency];
            }
            if (toCurrency === 'BRL' && FALLBACK_RATES[fromCurrency]) {
                return amount * FALLBACK_RATES[fromCurrency];
            }
            throw error;
        }
    }

    // Função para lidar com a seleção de moeda
    async function handleCurrencySelection(event) {
        const currencyItem = event.currentTarget;
        const currencyCode = currencyItem.dataset.currency;
        const currencySymbol = currencyItem.dataset.symbol;
        const currencyDisplay = document.querySelector('.currency-display');
        
        try {
            currencyDisplay.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            const rate = await convertCurrency('BRL', currencyCode, 1);
            
            const response = await fetch('conversao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    currency: currencyCode,
                    symbol: currencySymbol,
                    rate: rate
                })
            });
            
            if (!response.ok) throw new Error('Erro ao salvar no servidor');
            
            location.reload();
            
        } catch (error) {
            console.error('Erro na conversão:', error);
            alert('Erro ao alterar moeda: ' + error.message);
            currencyDisplay.textContent = '<?= $moeda_selecionada ?>';
        }
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.currency-item').forEach(item => {
            item.addEventListener('click', handleCurrencySelection);
        });
    });
  </script>
</body>
</html>