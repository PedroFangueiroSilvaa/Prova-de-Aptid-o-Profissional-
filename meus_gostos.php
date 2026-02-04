<?php
session_start();
include 'conexao.php';

// Tratamento de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Verificar se o usuário está logado
if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}

$id_utilizador = $_SESSION['id_utilizador'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Os seus produtos Favoritos - Boxing for Life</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Adicionando o favicon -->
    <link rel="icon" href="/PAP/imagens/11.png" type="image/x-icon">
    <link rel="shortcut icon" href="/PAP/imagens/11.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="/PAP/imagens/11.png">
    <!-- Forçar atualização do favicon com parâmetro de versão -->
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/x-icon"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Spectral:wght@200;300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
    /* CSS ESPECÍFICO PARA ÍCONES DE FAVORITOS EM MEUS GOSTOS */
    .product .desc .meta-prod a i {
      color: #ff914d;
      transition: color 0.2s;
    }
    .product .desc .meta-prod a.favorite-btn.active i {
      color: #fff !important;
    }
    .product .desc .meta-prod a.favorite-btn.active:hover i {
      color: #fff !important;
      text-shadow: 0 0 6px #ff914d, 0 0 2px #fff;
    }
    .product .desc .meta-prod a:hover i {
      color: #fff !important;
    }
    .bg-video {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      object-fit: cover;
      z-index: 0;
      pointer-events: none;
    }
    .hero-wrap .overlay,
    .hero-wrap .container {
      position: relative;
      z-index: 1;
    }
    .hero-wrap {
      position: relative;
      overflow: hidden;
    }
    </style>
</head>
<body>
    <?php include 'cabecalho.php'; ?>

    <div class="hero-wrap position-relative hero-wrap-2" data-stellar-background-ratio="0.5">
      <video autoplay muted loop playsinline class="bg-video">
        <source src="imagens/INDEX.mp4" type="video/mp4">
        O seu navegador não suporta vídeo em HTML5.
      </video>
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-end justify-content-center">
          <div class="col-md-9 ftco-animate mb-5 text-center">
            <p class="breadcrumbs mb-0">
              <span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span>
              <span>Meus Gostos <i class="fa fa-chevron-right"></i></span>
            </p>
            <h2 class="mb-0 bread">Meus Gostos</h2>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section">
        <div class="container">
            <?php
            // Exibir Mensagens de Erro
            if (isset($_GET['error'])) {
                switch ($_GET['error']) {
                    case 'invalid_data':
                        echo '<div class="alert alert-danger">Erro: Dados inválidos.</div>';
                        break;
                    case 'product_not_found':
                        echo '<div class="alert alert-danger">Erro: Produto não encontrado.</div>';
                        break;
                    case 'user_not_identified':
                        echo '<div class="alert alert-danger">Erro: Usuário não identificado.</div>';
                        break;
                    case 'database_error':
                        echo '<div class="alert alert-danger">Erro: Problema ao acessar o banco de dados. Tente novamente mais tarde.</div>';
                        break;
                    case 'already_favorite':
                        echo '<div class="alert alert-warning">Este produto já está nos seus favoritos.</div>';
                        break;
                    case 'not_favorite':
                        echo '<div class="alert alert-warning">Este produto não está nos seus favoritos.</div>';
                        break;
                }
            }

            // Exibir Mensagens de Sucesso
            if (isset($_GET['success'])) {
                switch ($_GET['success']) {
                    case 'added':
                        echo '<div class="alert alert-success">Produto adicionado aos favoritos com sucesso!</div>';
                        break;
                    case 'removed':
                        echo '<div class="alert alert-success">Produto removido dos favoritos com sucesso!</div>';
                        break;
                }
            }
            ?>
            <div class="row">
                <?php
                // Buscar produtos favoritos do usuário
                $sql = "SELECT p.codigo_base, p.nome, p.preco, p.imagem, c.nome AS categoria_nome 
                        FROM produtos p 
                        JOIN categorias c ON p.id_categoria = c.id_categoria
                        JOIN gostos g ON p.codigo_base = g.codigo_base
                        WHERE g.id_utilizador = $id_utilizador";
                
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
                        $preco = number_format($row['preco'], 2, ',', '.');
                        $categoria_nome = htmlspecialchars($row['categoria_nome'], ENT_QUOTES, 'UTF-8');
                        $imagem = !empty($row['imagem']) ? htmlspecialchars($row['imagem'], ENT_QUOTES, 'UTF-8') : 'imagens/default.jpg';
                        $codigo_base = htmlspecialchars($row['codigo_base'], ENT_QUOTES, 'UTF-8');
                ?>
                        <div class="col-md-4 d-flex">
                            <div class="product ftco-animate">
                                <div class="img d-flex align-items-center justify-content-center" style="background-image: url('<?php echo $imagem; ?>');">
                                    <div class="desc">
                                        <p class="meta-prod d-flex">
                                        <a href="product-single.php?codigo_base=<?= $row['codigo_base'] ?>" class="d-flex align-items-center justify-content-center"><i class="fas fa-shopping-bag"></i></a>
                                        <a href="adicionar_gosto.php?codigo_base=<?php echo $codigo_base; ?>&acao=remover" class="d-flex align-items-center justify-content-center favorite-btn active"><i class="fas fa-heart"></i></a>
                                        </p>
                                    </div>
                                </div>
                                <div class="text text-center">
                                    <span class="category"><?php echo $categoria_nome; ?></span>
                                    <h2><a href="product-single.php?codigo_base=<?php echo $codigo_base; ?>"><?php echo $nome; ?></a></h2>
                                    <span class="price">€<?php echo $preco; ?></span>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<div class='col-12 text-center'><p>Você ainda não tem produtos favoritos.</p></div>";
                }
                ?>
            </div>
        </div>
    </section>

    <?php include 'rodape.php'; ?>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>