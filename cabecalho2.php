<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Inclui o arquivo de validação específico para administradores
include 'validar_admin.php';

// A verificação de sessão e nível de acesso já é feita no validar_admin.php
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxing for Life - Administração</title>
      
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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #343a40;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ff914d !important;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #ff914d !important;
        }
        .dropdown-menu {
            background-color: #343a40;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dropdown-item {
            color: #ffffff;
        }
        .dropdown-item:hover {
            background-color: #ff914d;
            color: #ffffff;
        }
        .main-content {
            padding: 20px;
            margin-top: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index2.php">Boxing for Life</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index2.php"><i class="fas fa-home"></i> Início</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="utilizadoresDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-users"></i> Utilizadores
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="ver_utilizador.php">Ver Utilizadores</a>
                        <a class="dropdown-item" href="registar_utilizador.php">Criar Utilizador</a>
                        <a class="dropdown-item" href="alterar_utilizador.php">Alterar Utilizador</a>
                        <a class="dropdown-item" href="eliminar_utilizador.php">Eliminar Utilizador</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="produtosDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="adicionar_produto.php">Adicionar Produto</a>
                        <a class="dropdown-item" href="listar_produtos.php">Ver Produtos</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="variacoesDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-layer-group"></i> Variações
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="listar_variacoes.php">Ver Todas as Variações</a>
                        <a class="dropdown-item" href="listar_produtos.php">Gerenciar por Produto</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="gerenciar_tamanhos.php">Gerenciar Tamanhos</a>
                        <a class="dropdown-item" href="gerenciar_cores.php">Gerenciar Cores</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="catalogoDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-tags"></i> Catálogo
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="adicionar_marca.php">Adicionar Marca</a>
                        <a class="dropdown-item" href="adicionar_fornecedor.php">Adicionar Fornecedor</a>
                        <a class="dropdown-item" href="listar_marcas_fornecedores.php">Listar Marcas e Fornecedores</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="gerenciar_categorias.php">Gerenciar Categorias</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-shopping-cart"></i> Encomendas
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="gerenciar_encomendas.php">Gerenciar Encomendas</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reviewsDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-star"></i> Reviews
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="gerenciar_reviews.php">Gerenciar Reviews</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-heart"></i> Gostos
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="gerenciar_gostos.php">Gerenciar Gostos</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="blogDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Blog
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="blogDropdown">
                        <li><a class="dropdown-item" href="gerenciar_blog.php">Gerir Posts</a></li>
                        <li><a class="dropdown-item" href="criar_post.php">Novo Post</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="comentarios_admin.php">Gerir Comentários</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php" target="_blank"><i class="fas fa-store"></i> Ver Loja</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="sair.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Painel de Administração</h5>
                        </div>
                        <div class="card-body">
                            <!-- O conteúdo específico de cada página será inserido aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>