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
$message = '';

// Verificar se o ID do blog foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: meus_blogs.php");
    exit;
}

$id_post = (int)$_GET['id'];

// Verificar se o blog pertence ao utilizador
$check_query = "SELECT * FROM blog WHERE id_post = $id_post AND id_utilizador = $id_utilizador";
$check_result = mysqli_query($conn, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    $_SESSION['error_message'] = "Blog não encontrado ou não pertence ao seu utilizador.";
    header("Location: meus_blogs.php");
    exit;
}

$blog = mysqli_fetch_assoc($check_result);

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
    
    // Atualizar o blog no banco de dados
    $update_query = "UPDATE blog SET 
                     titulo = '$titulo', 
                     conteudo = '$conteudo'
                     WHERE id_post = $id_post AND id_utilizador = $id_utilizador";
    
    if (mysqli_query($conn, $update_query)) {
        $message = "<div class='alert alert-success'>Blog atualizado com sucesso!</div>";
        
        // Recarregar os dados do blog
        $check_result = mysqli_query($conn, $check_query);
        $blog = mysqli_fetch_assoc($check_result);
    } else {
        $message = "<div class='alert alert-danger'>Erro ao atualizar o blog: " . mysqli_error($conn) . "</div>";
    }
}
?>

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
<!-- Hero com vídeo de fundo -->
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
            <span class="mr-2"><a href="conta_utilizador.php">Minha Conta <i class="fa fa-chevron-right"></i></a></span>
            <span class="mr-2"><a href="meus_blogs.php">Meus Blogs <i class="fa fa-chevron-right"></i></a></span>
            <span>Editar Blog <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Editar Blog</h2>
      </div>
    </div>
  </div>
</div>

<!-- Formulário de Edição Simplificado -->
<section class="ftco-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <?php echo $message; ?>
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Editar Blog</h4>
          </div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($blog['titulo'], ENT_QUOTES, 'UTF-8'); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="conteudo">Conteúdo</label>
                <textarea class="form-control" id="conteudo" name="conteudo" rows="10" required><?php echo htmlspecialchars($blog['conteudo'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
              
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="meus_blogs.php" class="btn btn-secondary">Cancelar</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'rodape.php'; ?>

<!-- Scripts básicos -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
