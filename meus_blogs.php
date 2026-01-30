<?php
session_start();
include 'conexao.php';
include 'cabecalho.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];

// Verificar se existem mensagens de sucesso ou erro na sessão
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Consultar os blogs do utilizador
$query = "SELECT b.*, u.nome as autor_nome 
          FROM blog b 
          JOIN utilizadores u ON b.id_utilizador = u.id_utilizador 
          WHERE b.id_utilizador = $id_utilizador 
          ORDER BY b.data_publicacao DESC";
$resultado = mysqli_query($conn, $query);
?>

<!-- Banner -->
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
            <span>Meus Blogs <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Meus Blogs</h2>
      </div>
    </div>
  </div>
</div>

<!-- Lista de Blogs do Utilizador -->
<section class="ftco-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Meus Blogs Publicados</h2>
          <a href="criar_blog.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Criar Novo Blog
          </a>
        </div>
      </div>
    </div>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <div class="row">
      <?php
      if (mysqli_num_rows($resultado) > 0) {
          while ($blog = mysqli_fetch_assoc($resultado)) {
              $titulo = htmlspecialchars($blog['titulo'], ENT_QUOTES, 'UTF-8');
              $conteudo = substr(strip_tags($blog['conteudo']), 0, 150) . '...'; // Resumo do conteúdo
              $data = date('d/m/Y', strtotime($blog['data_publicacao']));
              $imagem = $blog['imagem'] ? $blog['imagem'] : 'imagens/blogs/default-blog.jpg';
              $blog_id = $blog['id_post'];
      ?>
          <div class="col-md-6 col-lg-4 mb-4">
              <div class="card h-100">
                  <img src="<?php echo $imagem; ?>" class="card-img-top" alt="<?php echo $titulo; ?>" style="height: 200px; object-fit: cover;">
                  <div class="card-body">
                      <h5 class="card-title"><?php echo $titulo; ?></h5>
                      <p class="card-text text-muted">Publicado em: <?php echo $data; ?></p>
                      <p class="card-text"><?php echo $conteudo; ?></p>
                  </div>
                  <div class="card-footer bg-transparent">
                      <div class="btn-group w-100">                          <a href="blog-single.php?id=<?php echo $blog_id; ?>" class="btn btn-info btn-sm">
                              <i class="fas fa-eye"></i> Ver
                          </a>
                          <a href="editar_blog_usuario.php?id=<?php echo $blog_id; ?>" class="btn btn-warning btn-sm">
                              <i class="fas fa-edit"></i> Editar
                          </a>
                          <a href="#" class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?php echo $blog_id; ?>)">
                              <i class="fas fa-trash-alt"></i> Excluir
                          </a>
                      </div>
                  </div>
              </div>
          </div>
      <?php
          }
      } else {
      ?>
          <div class="col-12">
              <div class="alert alert-info">
                  <p>Você ainda não publicou nenhum blog.</p>
                  <a href="criar_blog.php" class="btn btn-primary mt-3">Criar Meu Primeiro Blog</a>
              </div>
          </div>
      <?php
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

<script>
function confirmarExclusao(blogId) {
    if (confirm('Tem certeza que deseja excluir este blog?')) {
        window.location.href = 'apagar_blog_usuario.php?id=' + blogId;
    }
}
</script>
</body>
</html>
