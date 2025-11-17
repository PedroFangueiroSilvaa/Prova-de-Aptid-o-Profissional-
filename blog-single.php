<?php 
// This starts a session, which is a way to store information (like user login) across different pages.
session_start(); 

// This includes a file called "conexao.php" that likely contains the database connection details.
include 'conexao.php'; 

// Verificar se o id do blog foi passado e é válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger text-center mt-3">ID do blog inválido ou não fornecido.</div>';
    include 'rodape.php';
    exit;
}
$id_post = (int)$_GET['id'];

// Buscar o blog na base de dados
$sql_blog = "SELECT * FROM blog WHERE id_post = '$id_post'";
$result_blog = mysqli_query($conn, $sql_blog);
$blog = mysqli_fetch_assoc($result_blog);
if (!$blog) {
    echo '<div class="alert alert-danger text-center mt-3">Blog não encontrado.</div>';
    include 'rodape.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <!-- This is the title of the page that appears in the browser tab. -->
    <title>Detalhes do Blog - Boxing for Life</title>
    
    <!-- This sets the character encoding for the page to support special characters like accents. -->
    <meta charset="utf-8">
    
    <!-- This makes the page responsive, meaning it adjusts to different screen sizes like mobile or desktop. -->
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
   
</head>
<body>
    <!-- This includes a file called "cabecalho.php", which likely contains the header of the website (like a menu or logo). -->
    <?php include 'cabecalho.php'; ?>

    <!-- Seção hero atualizada com o novo layout -->
    <style>
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
              <span>Blog <i class="fa fa-chevron-right"></i></span>
            </p>
            <h2 class="mb-0 bread">Blog</h2>
          </div>
        </div>
      </div>
    </div>

    <!-- This section displays the main content of the blog post. -->
    <section class="container my-5">
        <div class="row justify-content-center">
            <?php
            // If a blog post was found earlier, it formats and displays the details.
            if (!empty($blog)) {
                // This array translates English month names to Portuguese.
                $meses = [
                    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
                    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
                    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
                    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
                ];
                
                // This converts the blog's publication date into a readable format in Portuguese.
                $data = strtotime($blog['data_publicacao']);
                $dataFormatada = date('d', $data) . ' de ' . $meses[date('F', $data)] . ' de ' . date('Y', $data);

                // This displays the blog post's image, title, content, and a button to go back to the blog list.
                echo '
                <div class="col-lg-8">
                    <div class="card">
                        <img src="' . $blog['imagem'] . '" class="card-img-top" alt="' . $blog['titulo'] . '">
                        <div class="card-body">
                            <p class="text-muted"><i class="fa fa-calendar"></i> ' . $dataFormatada . '</p>
                            <h3 class="card-title">' . $blog['titulo'] . '</h3>
                            <p class="card-text">' . nl2br($blog['conteudo']) . '</p>
                            <a href="blog.php" class="btn btn-primary">Voltar ao Blog</a>
                        </div>
                    </div>
                </div>';
            } else {
                // If no blog post was found, it shows a message.
                echo '<p class="text-center">Blog não encontrado.</p>';
            }
            ?>
        </div>
    </section>

    <!-- This section displays the comments for the blog post and a form to add new comments. -->
    <section class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="pt-5 mt-5">
                    <?php
                    // This counts the total number of comments for the blog post.
                    $id_post = $_GET['id'];
                    $sql_total = "SELECT COUNT(*) as total FROM comentarios_blog WHERE id_post = '$id_post'";
                    $result_total = mysqli_query($conn, $sql_total);
                    $total = mysqli_fetch_assoc($result_total)['total'];
                    ?>
                    
                    <!-- This displays the total number of comments. -->
                    <h3 class="mb-5"><?php echo $total; ?> Comentários</h3>
                    
                    <ul class="list-unstyled">
                        <?php
                        // This retrieves all comments for the blog post, along with the names of the users who posted them.
                        $sql_comentarios = "SELECT c.*, u.nome 
                                          FROM comentarios_blog c 
                                          JOIN utilizadores u ON c.id_utilizador = u.id_utilizador 
                                          WHERE c.id_post = '$id_post' 
                                          ORDER BY c.data_comentario DESC";
                        $result_comentarios = mysqli_query($conn, $sql_comentarios);

                        // This loops through each comment and displays it.
                        while ($comentario = mysqli_fetch_assoc($result_comentarios)):
                        ?>
                        <li class="mb-4">
                            <div class="bg-light p-3 rounded">
                                <!-- This shows the user's name, the date of the comment, and the comment content. -->
                                <h5><?php echo htmlspecialchars($comentario['nome']); ?></h5>
                                <small class="text-muted"><?php echo date('d M Y', strtotime($comentario['data_comentario'])); ?></small>
                                <p><?php echo nl2br(htmlspecialchars($comentario['conteudo'])); ?></p>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>

                    <div class="pt-5">
                        <h3>Deixe um comentário</h3>
                        <?php 
                        // If the user is logged in, it shows a form to add a comment.
                        if (isset($_SESSION['id_utilizador'])): ?>
                            <form action="processar_comentario_blog.php" method="POST">
                                <!-- This hidden field stores the blog post ID for the comment. -->
                                <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
                                <div class="mb-3">
                                    <label for="conteudo" class="form-label">Mensagem</label>
                                    <!-- This is a text area where the user can write their comment. -->
                                    <textarea name="conteudo" id="conteudo" class="form-control" rows="5" required></textarea>
                                </div>
                                <!-- This button submits the comment. -->
                                <button type="submit" class="btn btn-primary">Publicar Comentário</button>
                            </form>
                        <?php else: ?>
                            <!-- If the user is not logged in, it shows a message asking them to log in. -->
                            <div class="alert alert-info text-center">
                                <p>Por favor, <a href="login.php" class="alert-link">faça login</a> para deixar um comentário.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- This includes a file called "rodape.php", which likely contains the footer of the website. -->
    <?php include 'rodape.php'; ?>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="js/google-map.js"></script>
<script src="js/main.js"></script>
</body>
</html>
