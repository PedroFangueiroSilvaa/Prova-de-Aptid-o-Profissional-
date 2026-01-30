<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Boxing for life</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Adicionando o favicon em vários tamanhos -->
    <link rel="icon" href="/PAP/imagens/11.png" type="image/x-icon" sizes="32x32">
    <link rel="shortcut icon" href="/PAP/imagens/11.png" type="image/x-icon" sizes="32x32">
    <link rel="apple-touch-icon" href="/PAP/imagens/11.png" sizes="180x180">
    <!-- Tamanhos adicionais para melhor suporte em diferentes dispositivos -->
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="16x16">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="48x48">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="96x96">
    <!-- Favicon para telas de alta resolução -->
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="192x192">
    
    <!-- Adicionando o link do Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,200;0,300;0,400;0,500;0,700;0,800;1,200;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Link do Bootstrap JS (bundle, com Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" defer></script>

    <style>
        .modal-content {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
        }
        /* Estilos para texto branco no cabeçalho */
        .wrap a, .wrap p, .wrap span, 
        .navbar a, .navbar span,
        .navbar-brand, .navbar-brand span,
        .nav-link, .dropdown-toggle,
        .btn-cart, .btn-cart small {
            color: #fff !important;
        }
        .dropdown-menu a {
            color: #000 !important;
        }
        .reg .btn {
            color: #fff !important;
            border-color: #fff;
        }
    </style>
</head>
<body>

<div class="wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-6 d-flex align-items-center">
                <p class="mb-0 phone pl-md-2">
                    <a href="#" class="mr-2"><span class="fa fa-phone mr-1"></span> +351 919 548 782</a> 
                    <a href="#"><span class="fa fa-paper-plane mr-1"></span> BoxingForLife@gmail.com</a>
                </p>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end">
                <div class="social-media mr-4">
                    <p class="mb-0 d-flex">
                        <a href="#" class="d-flex align-items-center justify-content-center"><span class="fab fa-facebook-f"></span></a>
                        <a href="#" class="d-flex align-items-center justify-content-center"><span class="fab fa-twitter"></span></a>
                        <a href="#" class="d-flex align-items-center justify-content-center"><span class="fab fa-instagram"></span></a>
                        <a href="#" class="d-flex align-items-center justify-content-center"><span class="fab fa-dribbble"></span></a>
                    </p>
                </div>
                <div class="reg d-flex align-items-center">
                    <span class="me-2">
                        <?php echo isset($_SESSION["nome_utilizador"]) ? $_SESSION["nome_utilizador"] : "Anónimo"; ?>
                    </span>
                    <p class="mb-0">
                        <?php if (!isset($_SESSION["nome_utilizador"])): ?>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#criarContaModal" class="mr-2">Sign Up</a>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Log In</a>
                        <?php else: ?>
                            <a href="conta_utilizador.php" class="btn btn-primary btn-sm rounded-pill px-3 py-1">Perfil</a>
                            <a href="sair.php" class="btn btn-danger btn-sm rounded-pill px-3 py-1">Sair</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">    <div class="container">        <a class="navbar-brand" href="index.php">Boxing <span>store</span></a>
        <div class="order-lg-last btn-group">
    <a href="cart.php" class="btn-cart">
        <span class="fas fa-shopping-cart"></span>
        <!-- Remover a div com o número -->
        <!-- <div class="d-flex justify-content-center align-items-center"><small>3</small></div> -->
    </a>
</div>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">Sobre a nossa loja</a></li>    
                <li class="nav-item"><a href="product.php" class="nav-link">Produtos</a></li>                      
                <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Iniciar Sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="processar_login.php" method="POST">
                    <div class="mb-3">
                        <label for="login-email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="login-password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="palavra_pass" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sessão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Criar Conta -->
<div class="modal fade" id="criarContaModal" tabindex="-1" aria-labelledby="criarContaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="criarContaModalLabel">Criar Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCriarConta" action="processar_criar_conta.php" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="palavra_passe" class="form-label">Password</label>
                        <input type="password" class="form-control" name="palavra_passe" required>
                    </div>
                    <div class="mb-3">
                        <label for="local_envio" class="form-label">Morada</label>
                        <input type="text" class="form-control" name="local_envio" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Criar Conta</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
