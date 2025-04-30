<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Breeze </title>

  </head>
  <body>
  <header>
  <!-- Navbar principal -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a href="#" class="navbar-brand">
        <img src="../images/inlinelogobranco.png" height="60" width="150" alt="Logo">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTopo" aria-controls="navbarTopo" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarTopo">
        <ul class="navbar-nav">
          <li class="nav-item mx-1">
            <a href="#" class="btn btn-outline-light">Login</a>
          </li>
          <li class="nav-item mx-1">
            <a href="#" class="btn btn-outline-light">Cadastrar</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Subnavbar com busca -->
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: rgb(0, 119, 255);">
    <div class="container">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarBusca" aria-controls="navbarBusca" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarBusca">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a href="#" class="btn btn-outline-light">Hospedagens</a>
          </li>
        </ul>
        <form class="form-inline my-2 my-lg-0">
          <input class="form-control mr-sm-2" type="search" placeholder="Para onde você vai?" aria-label="Pesquisar">
          <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Pesquisar</button>
        </form>
      </div>
    </div>
  </nav>
</header>

    <main>
  <!-- Banner Principal -->
  <section class="jumbotron text-black text-center" style="background: url('../images/banner-hotel.jpg') no-repeat center center; background-size: cover;">
    <div class="container py-5">
      <h1 class="display-4 font-weight-bold">Encontre o hotel perfeito para sua viagem</h1>
      <p class="lead">Compare preços e reserve com confiança em poucos cliques</p>
      <a href="#" class="btn btn-primary btn-lg">Começar agora</a> <!--Botão para abrir pagina de realizar reservas -->
    </div>
  </section>

  <!-- Hotéis em destaque -->
  <section class="container my-5">
    <h2 class="text-center mb-4">Hotéis em Destaque</h2>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="../images/hotel1.jpg" class="card-img-top" alt="Hotel 1">
          <div class="card-body">
            <h5 class="card-title">Hotel Mar Azul</h5>
            <p class="card-text">Localizado de frente para o mar, com café da manhã incluso.</p>
            <a href="#" class="btn btn-outline-primary btn-sm">Ver mais</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="../images/hotel2.jpg" class="card-img-top" alt="Hotel 2">
          <div class="card-body">
            <h5 class="card-title">Pousada das Flores</h5>
            <p class="card-text">Ambiente tranquilo e aconchegante no centro da cidade.</p>
            <a href="#" class="btn btn-outline-primary btn-sm">Ver mais</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="../images/hotel3.jpg" class="card-img-top" alt="Hotel 3">
          <div class="card-body">
            <h5 class="card-title">Resort Montanha Verde</h5>
            <p class="card-text">Ideal para quem busca lazer e natureza com todo conforto.</p>
            <a href="#" class="btn btn-outline-primary btn-sm">Ver mais</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Benefícios -->
  <section class="bg-light py-5">
    <div class="container">
      <h2 class="text-center mb-4">Por que reservar com o Breeze?</h2>
      <div class="row text-center">
        <div class="col-md-4 mb-4">
          <img src="../images/icon-preco.png" alt="Melhor Preço" width="60">
          <h5 class="mt-3">Melhores Preços</h5>
          <p>Compare tarifas e garanta o melhor valor para sua estadia.</p>
        </div>
        <div class="col-md-4 mb-4">
          <img src="../images/icon-suporte.png" alt="Suporte 24h" width="60">
          <h5 class="mt-3">Suporte 24/7</h5>
          <p>Nossa equipe está sempre pronta para te ajudar em qualquer situação.</p>
        </div>
        <div class="col-md-4 mb-4">
          <img src="../images/icon-facilidade.png" alt="Facilidade" width="60">
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

  </body>
</html>