<!doctype html>
<?php

session_start();

include __DIR__ . "/../../conn/conn.php"; // conexão com o banco

$moeda_selecionada = $_SESSION['moeda_selecionada'] ?? 'BRL';
$simbolo_moeda = $_SESSION['simbolo_moeda'] ?? 'R$';
$taxa_conversao = $_SESSION['taxa_conversao'] ?? 1;

// Verifica se o usuário está logado
 if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php"); // Redireciona para o login se não estiver logado
    exit;
}

$query = "SELECT * FROM hoteis";
$result = $conn->query($query); //query pra mostras os hoteis

if ($result->rowCount() === 0) {
    echo "<p class='text-center'> Nenhum hotel disponível no momento. </p>";
    $result = false; // Prevent further processing
}

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

?>

<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="/breeze/codes/js/user/index.js?v=<?= time() ?>" defer></script>
  <link rel="icon" href="/breeze/images/logo.ico">
  <title> Breeze </title>
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
          <img src="/breeze/images/logob.png" height="60" width="120"/>
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
  <!-- Banner Principal -->
  <section class="jumbotron text-black text-center" style="background: url('../images/banner-hotel.jpg') no-repeat center center; background-size: cover;">
    <div class="container py-5">
      <h1 class="display-4 font-weight-bold"> Encontre o hotel perfeito para sua viagem </h1>
      <p class="lead"> Compare preços e reserve com confiança em poucos cliques </p>  
    </div>
  </section>

  <?php if ($result): ?>
  <!-- card de hoteis disponiveis -->
  <div class="container mt-5">
    <h2 class="mb-4"> Hotéis disponíveis </h2>
    <div class="row">
      <?php while ($hotel = $result->fetch(PDO::FETCH_ASSOC)) : ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><?php echo $hotel['nome']; ?></h5>
              <p class="card-text"><?php echo substr($hotel['cidade'], 0, 80) ?></p>
              <div class="mt-3 price-display" data-original-price="200">
                    <span class="text-success fw-bold">
                        <?= $simbolo_moeda ?> <?= number_format(200 * $taxa_conversao, 2) ?>
                    </span>
                    <p><?php echo $hotel['idhotel'] ?></p>
                    <small class="text-muted d-block"> diária </small>
                </div>
                 <a href="hotel.php?id=<?php echo $hotel['idhotel']; ?>" class="btn btn-primary" type="button">Ver mais</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
      <?php endif; ?>

            </div>
          </div>
        </div>
    </div>
  </div>
  <!-- Benefícios -->
  <section class="bg-light py-5">
    <div class="container">
      <h2 class="text-center mb-4">Por que reservar com o Breeze?</h2>
      <div class="row text-center">
        <div class="col-md-4 mb-4">
          <h5 class="mt-3">Melhores Preços</h5>
          <p>Compare tarifas e garanta o melhor valor para sua estadia.</p>
        </div>
        <div class="col-md-4 mb-4">
          <h5 class="mt-3">Suporte 24/7</h5>
          <p>Nossa equipe está sempre pronta para te ajudar em qualquer situação.</p>
        </div>
        <div class="col-md-4 mb-4">
          <h5 class="mt-3">Reserva Fácil</h5>
          <p>Interface intuitiva e processo de reserva simples e rápido.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Rodapé -->
  <footer class="bg-primary text-white text-center py-4 mt-5">
    <div class="container">
      <p class="mb-1">&copy; 2025 Breeze - Todos os direitos reservados</p>
      <small>Termos de uso | Política de privacidade</small>
    </div>
  </footer>
  </main>

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
  </body>
  
  <script>
    // Taxas de fallback atualizadas (BRL para outras moedas)
    const FALLBACK_RATES = {
        'USD': 0.18,   // 1 BRL = 0.19 USD
        'EUR': 0.17,
        'GBP': 0.15,
        'JPY': 25.50,
        'AUD': 0.29,
        'CAD': 0.25,
        'CHF': 0.17,
        'MXN': 3.33,
        'ARS': 270.27
    };

    // Função principal para conversão
    async function convertCurrency(fromCurrency, toCurrency, amount = 1) {
        // Se for a mesma moeda
        if (fromCurrency === toCurrency) return amount;
        
        try {
            // Caso 1: Ambas as moedas são suportadas pela API
            const moedas = <?= json_encode(getCurrenciesList()) ?>;
            const fromMoeda = moedas.find(m => m.code === fromCurrency);
            const toMoeda = moedas.find(m => m.code === toCurrency);
            
            if (fromMoeda.api_supported && toMoeda.api_supported) {
                const response = await fetch(`https://api.frankfurter.app/latest?from=${fromCurrency}&to=${toCurrency}`);
                const data = await response.json();
                return amount * data.rates[toCurrency];
            }
            
            // Caso 2: Conversão envolvendo BRL
            if (fromCurrency === 'BRL' && toMoeda.api_supported) {
                // Usar taxa inversa do fallback
                return amount * FALLBACK_RATES[toCurrency];
            }
            
            if (toCurrency === 'BRL' && fromMoeda.api_supported) {
                // Usar taxa direta do fallback
                return amount * FALLBACK_RATES[fromCurrency];
            }
            
            // Caso 3: Conversão entre moedas não suportadas (via BRL)
            if (fromCurrency !== 'BRL' && toCurrency !== 'BRL') {
                // Primeiro converte para BRL, depois para moeda destino
                const toBRL = amount / FALLBACK_RATES[fromCurrency];
                return toBRL / FALLBACK_RATES[toCurrency];
            }
            
            // Caso 4: Moeda não encontrada
            throw new Error('Par de moedas não suportado');
            
        } catch (error) {
            console.error('Erro na API, usando fallback:', error);
            // Fallback para taxas locais
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
            // Mostrar loading
            currencyDisplay.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            // Obter taxa de conversão (de BRL para a moeda selecionada)
            const rate = await convertCurrency('BRL', currencyCode, 1);
            
            // Atualizar sessão no servidor
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
            
            // Recarregar a página para aplicar as mudanças
            location.reload();
            
        } catch (error) {
            console.error('Erro na conversão:', error);
            alert('Erro ao alterar moeda: ' + error.message);
            currencyDisplay.textContent = '<?= $moeda_selecionada ?>';
        }
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar eventos aos itens de moeda
        document.querySelectorAll('.currency-item').forEach(item => {
            item.addEventListener('click', handleCurrencySelection);
        });
        
        // Atualizar todos os preços na página
        updateAllPrices();
    });

    // Função para atualizar todos os preços exibidos
    function updateAllPrices() {
    document.querySelectorAll('.price-display').forEach(priceElement => {
        const originalPrice = parseFloat(priceElement.dataset.originalPrice);
        const convertedPrice = originalPrice * <?= $taxa_conversao ?>;
        
        priceElement.innerHTML = `
            <span class="text-success fw-bold">
                <?= $simbolo_moeda ?> ${convertedPrice.toFixed(2)}
            </span>
            <small class="text-muted d-block">diária</small>
        `;
    });
}

// Script para ocultar o URL no status bar ao passar o mouse sobre links
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todos os elementos com links que não são vazios
    const allLinks = document.querySelectorAll('a[href]:not([href^="javascript:"])');
    
    allLinks.forEach(link => {
        // Armazena o href original como dataset
        if (!link.dataset.originalHref) {
            link.dataset.originalHref = link.href;
        }

        // Remove o href quando o mouse entra (previne status bar)
        link.addEventListener('mouseenter', function() {
            this.removeAttribute('href');
        });

        // Restaura o href quando o mouse sai
        link.addEventListener('mouseleave', function() {
            if (this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });

        // Garante que o clique funcione corretamente
        link.addEventListener('click', function(e) {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                e.preventDefault();
                
                // Trata diferentes tipos de links
                if (this.dataset.originalHref.startsWith('#')) {
                    // Scroll suave para âncoras
                    document.querySelector(this.dataset.originalHref)?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                } else if (this.target === '_blank') {
                    // Abre em nova aba se necessário
                    window.open(this.dataset.originalHref, '_blank');
                } else {
                    // Navegação normal
                    window.location.href = this.dataset.originalHref;
                }
            }
            // Se já tem href, o comportamento padrão é mantido
        });

        // Garante que o href seja restaurado se o elemento perder foco
        link.addEventListener('blur', function() {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });
    });
});
  </script>
</html>