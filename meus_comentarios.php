<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';

// Verifica se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Meus Comentários - Boxing for Life</title>
    <!-- Adicionando o favicon em vários tamanhos -->
    <link rel="icon" href="/PAP/imagens/11.png" type="image/x-icon" sizes="32x32">
    <link rel="shortcut icon" href="/PAP/imagens/11.png" type="image/x-icon" sizes="32x32">
    <link rel="apple-touch-icon" href="/PAP/imagens/11.png" sizes="180x180">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="16x16">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="48x48">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="96x96">
    <link rel="icon" href="/PAP/imagens/11.png?v=2" type="image/png" sizes="192x192">
    <style>
        .comment-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .comment-date {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .comment-content {
            margin-bottom: 15px;
        }
        .blog-title {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .blog-title:hover {
            text-decoration: underline;
        }
        .delete-btn {
            color: #dc3545;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }
        .delete-btn:hover {
            text-decoration: underline;
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
              <span>Meus Comentários <i class="fa fa-chevron-right"></i></span>
            </p>
            <h2 class="mb-0 bread">Meus Comentários</h2>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $id_utilizador = $_SESSION['id_utilizador'];
                    $sql = "SELECT c.*, b.titulo, b.id_post 
                           FROM comentarios_blog c 
                           JOIN blog b ON c.id_post = b.id_post 
                           WHERE c.id_utilizador = $id_utilizador 
                           ORDER BY c.data_comentario DESC";
                    
                    $resultado = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($resultado) > 0) {
                        while ($comentario = mysqli_fetch_assoc($resultado)) {
                            echo '<div class="comment-card">';
                            echo '<h4><a href="blog-single.php?id=' . $comentario['id_post'] . '" class="blog-title">' . htmlspecialchars($comentario['titulo']) . '</a></h4>';
                            echo '<div class="comment-date">' . date('d/m/Y H:i', strtotime($comentario['data_comentario'])) . '</div>';
                            echo '<div class="comment-content">' . nl2br(htmlspecialchars($comentario['conteudo'])) . '</div>';
                            echo '<button onclick="apagarComentario(' . $comentario['id_comentario'] . ')" class="delete-btn">Apagar comentário</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center">';
                        echo '<p>Você ainda não fez nenhum comentário.</p>';
                        echo '<a href="blog.php" class="btn btn-primary">Ver Blog</a>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'rodape.php'; ?>

    <script>
    function apagarComentario(id_comentario) {
        if (confirm('Tem certeza que deseja apagar este comentário?')) {
            window.location.href = 'apagar_comentario.php?id=' + id_comentario;
        }
    }
    </script>

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
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>
</body>
</html>