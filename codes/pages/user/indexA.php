<?php 

include __DIR__ . "/../../conn/conn.php";

session_start();

// Verifica se o usuário está logado
 if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php"); // Redireciona para o login se não estiver logado
    exit;
}

try {
    // Buscar número de hoteis
    $stmt_hoteis = $conn->prepare("SELECT COUNT(idhotel) FROM hoteis");
    $stmt_hoteis->execute();
    $count_hoteis = $stmt_hoteis->fetchColumn();

    // Buscar número de quartos
    $stmt_quartos = $conn->prepare("SELECT COUNT(idquarto) FROM quartos");
    $stmt_quartos->execute();
    $count_quartos = $stmt_quartos->fetchColumn();

    // Buscar número de usuários
    $stmt_usuarios = $conn->prepare("SELECT COUNT(idusuarios) FROM usuarios");
    $stmt_usuarios->execute();
    $count_usuarios = $stmt_usuarios->fetchColumn();

    // Buscar número de clientes
    $stmt_clientes = $conn->prepare("SELECT COUNT(idcliente) FROM cliente");
    $stmt_clientes->execute();
    $count_clientes = $stmt_clientes->fetchColumn();

    // Buscar número de reservas
    $stmt_reservas = $conn->prepare("SELECT COUNT(idreserva) FROM reserva");
    $stmt_reservas->execute();
    $count_reservas = $stmt_reservas->fetchColumn();

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
    <script src="/breeze/codes/js/user/indexA.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="/breeze/images/logo.ico">
    <title> Tela de Adm </title>
    <style>
        :root {
            --card-1: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
            --card-2: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
            --card-3: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --card-4: linear-gradient(135deg, #f46b45 0%, #eea849 100%);
            --card-5: linear-gradient(135deg, #c31432 0%, #240b36 100%);
            
            --text-light: rgba(255, 255, 255, 0.9);
            --text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --nav-primary: #2c3e50;
        }

        /* Estilos do Navbar */
        .navbar-custom {
            background: var(--nav-primary) !important;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand-custom {
            padding: 0.5rem 0;
        }
        
        .navbar-toggler-custom {
            border: none;
            padding: 0.5rem;
        }
        
        .navbar-toggler-custom:focus {
            box-shadow: none;
        }
        
        .navbar-toggler-icon-custom {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            width: 1.5em;
            height: 1.5em;
        }
        
        .nav-link-custom {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        
        .nav-link-custom:hover, 
        .nav-link-custom:focus {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .dropdown-menu-custom {
            background: var(--nav-primary);
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-item-custom {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1.5rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            cursor: pointer;
        }
        
        .search-form-custom {
            margin: 0.5rem 0;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            object-fit: cover;
        }
        
        @media (max-width: 1199.98px) {
            .navbar-collapse-custom {
                background: var(--nav-primary);
                padding: 1rem;
                margin-top: 0.5rem;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
            
            .nav-item-custom {
                margin: 0.25rem 0;
            }
            
            .dropdown-menu-custom {
                margin-left: 1rem;
                background: rgba(0, 0, 0, 0.2);
            }
        }
        
        @media (max-width: 767.98px) {
            .navbar-brand-custom img {
                height: 50px;
                width: auto;
            }
            
            .search-form-custom {
                margin: 1rem 0;
                width: 100%;
            }
            
            .user-menu {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        /* Estilos do Banner e Cards */
        .compact-banner {
            min-height: 40vh !important;
            padding: 2rem 0;
        }
        
        .cards-container {
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        
        .admin-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 0;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            color: var(--text-light);
            height: 100%;
        }
        
        .admin-card:hover {
            transform: translateY(-8px);
            cursor: pointer;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .admin-card .card-body {
            padding: 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .admin-card .card-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            text-shadow: var(--text-shadow);
        }
        
        .admin-card .card-text {
            font-size: 1.75rem;
            font-weight: 700;
            margin-top: 0.5rem;
            text-shadow: var(--text-shadow);
        }
        
        .card-hoteis {
            background: var(--card-1);
        }
        
        .card-quartos {
            background: var(--card-2);
        }
        
        .card-usuarios {
            background: var(--card-3);
        }
        
        .card-clientes {
            background: var(--card-4);
        }
        
        .card-reservas {
            background: var(--card-5);
        }
        
        @media (max-width: 768px) {
            .admin-card .card-title {
                font-size: 1rem;
            }
            
            .admin-card .card-text {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand navbar-brand-custom" href="#">
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
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_hoteis.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/hoteis.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Quartos </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_quartos.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/quartos.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Ocupações </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_ocupacoes.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/ocupacoes.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Tipos de Quartos </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_tquartos.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/tquartos.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Reservas </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_reservas.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/reservas.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Usuários </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_usuarios.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/usuarios.php"> Visualizar </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item nav-item-custom dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Clientes </a>
                            <ul class="dropdown-menu dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/add/c_clientes.php"> Adicionar </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="../adm/read/clientes.php"> Visualizar </a></li>
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
                                <li><a class="dropdown-item dropdown-item-custom" href="logout.php"> Logout </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container-fluid p-0" style="overflow-x: hidden;">
        <!-- Banner Principal -->
        <section class="d-flex justify-content-center align-items-center text-black text-center compact-banner" style="background: url('../images/banner-hotel.jpg') no-repeat center center; background-size: cover;">
            <div>
                <h1 class="display-4 fw-bold"> Seja bem-vindo, Adm </h1>
                <p class="lead"> Selecione as telas acima no navbar </p>
            </div>
        </section>

        <!-- Painel de Informações Administrativas -->
        <section class="container cards-container">
            <div class="row g-2 justify-content-center">

                <!-- Card de Hotéis -->
                <div class="col-md-2 col-6">
                    <a href="../adm/read/hoteis.php" class="text-decoration-none text-light">
                        <div class="admin-card card-hoteis">
                            <div class="card-body text-center">
                                <h5 class="card-title"> Hotéis </h5>
                                <p class="card-text fs-4"> <?= htmlspecialchars($count_hoteis) ?> </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card de Quartos -->
                <div class="col-md-2 col-6">
                    <a href="../adm/read/quartos.php" class="text-decoration-none text-light">
                        <div class="admin-card card-quartos">
                            <div class="card-body text-center">
                                <h5 class="card-title"> Quartos </h5>
                                <p class="card-text fs-4"> <?= htmlspecialchars($count_quartos) ?> </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card de Usuários -->
                <div class="col-md-2 col-6">
                    <a href="../adm/read/usuarios.php" class="text-decoration-none text-light">
                        <div class="admin-card card-usuarios">
                            <div class="card-body text-center">
                                <h5 class="card-title"> Usuários </h5>
                                <p class="card-text fs-4"> <?= htmlspecialchars($count_usuarios) ?> </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card de Clientes -->
                <div class="col-md-2 col-6">
                    <a href="../adm/read/clientes.php" class="text-decoration-none text-light">
                        <div class="admin-card card-clientes">
                            <div class="card-body text-center">
                                <h5 class="card-title"> Clientes </h5>
                                <p class="card-text fs-4"> <?= htmlspecialchars($count_clientes) ?> </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card de Reservas -->
                <div class="col-md-2 col-6">
                    <a href="../adm/read/reservas.php" class="text-decoration-none text-light">
                        <div class="admin-card card-reservas">
                            <div class="card-body text-center">
                                <h5 class="card-title"> Reservas </h5>
                                <p class="card-text fs-4"> <?= htmlspecialchars($count_reservas) ?> </p>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </section>
    </main>

</body>
</html>