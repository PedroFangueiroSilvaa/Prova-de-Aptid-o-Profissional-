<?php
session_start();
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexao.php';

// Debugging: Check database connection
if (!$conn) {
    die("<div class='alert alert-danger text-center mt-3'>Erro ao conectar ao banco de dados: " . mysqli_connect_error() . "</div>");
}

// Variável para armazenar mensagens
$mensagem = "";

// Processar o formulário se foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publicar'])) {
    // Verificar se todos os campos obrigatórios estão preenchidos
    if (empty($_POST['titulo']) || empty($_POST['resumo']) || empty($_POST['conteudo'])) {
        $mensagem .= "<div class='alert alert-danger text-center mt-3'>Todos os campos são obrigatórios!</div>";
    } else {
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $resumo = mysqli_real_escape_string($conn, $_POST['resumo']);
        $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
        $id_utilizador = $_SESSION['id_utilizador'];
        $data_publicacao = date('Y-m-d H:i:s');

        // Verificar se a sessão do utilizador existe
        if (empty($id_utilizador)) {
            $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro: Utilizador não está logado!</div>";
        } else {
            // Verificar se foi enviado um ficheiro - usando a mesma lógica dos produtos
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $diretorio_imagens = 'imagens/blog/';
                
                // Criar diretório se não existir e definir permissões
                if (!is_dir($diretorio_imagens)) {
                    if (!mkdir($diretorio_imagens, 0755, true)) {
                        $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro: Não foi possível criar o diretório de imagens!</div>";
                    }
                }
                
                // Verificar se o diretório tem permissões de escrita
                if (!is_writable($diretorio_imagens)) {
                    $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro: Diretório de imagens sem permissões de escrita!</div>";
                } else {
                    $imagem_tmp = $_FILES['imagem']['tmp_name'];
                    $imagem_nome = basename($_FILES['imagem']['name']);
                    $caminho_imagem = $diretorio_imagens . time() . '_' . $imagem_nome;

                    if (move_uploaded_file($imagem_tmp, $caminho_imagem)) {
                        // Inserir os dados na base de dados
                        $query = "INSERT INTO blog (titulo, resumo, conteudo, imagem, data_publicacao, id_utilizador) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $query);
                        
                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, "sssssi", $titulo, $resumo, $conteudo, $caminho_imagem, $data_publicacao, $id_utilizador);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                echo "<script>alert('Blog publicado com sucesso!'); window.location='blog.php';</script>";
                                exit;
                            } else {
                                $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro ao publicar o blog: " . mysqli_error($conn) . "</div>";
                                // Remover a imagem se houve erro na base de dados
                                if (file_exists($caminho_imagem)) {
                                    unlink($caminho_imagem);
                                }
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro ao preparar a consulta: " . mysqli_error($conn) . "</div>";
                        }
                    } else {
                        $mensagem .= "<div class='alert alert-danger text-center mt-3'>Erro ao mover o ficheiro para o diretório. Verifique as permissões!</div>";
                    }
                }
            } else {
                // Verificar o erro específico do upload
                $error_message = "";
                switch ($_FILES['imagem']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = "Arquivo muito grande!";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = "Upload incompleto!";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = "Nenhum arquivo foi enviado!";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = "Diretório temporário não encontrado!";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = "Falha ao escrever arquivo no disco!";
                        break;
                    default:
                        $error_message = "Erro no upload da imagem: " . $_FILES['imagem']['error'];
                }
                $mensagem .= "<div class='alert alert-danger text-center mt-3'>" . $error_message . "</div>";
            }
        }
    }
}
?>
<?php include 'cabecalho.php'; ?>

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
        <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span> <span>Criar Blog <i class="fa fa-chevron-right"></i></span></p>
        <h2 class="mb-0 bread">Criar um Novo Blog</h2>
      </div>
    </div>
  </div>
</div>

<!-- Formulário de Criação de Blog -->
<div class="container mt-5 mb-5">
    <?php 
    // Exibir mensagens se existirem
    if (!empty($mensagem)) {
        echo $mensagem;
    }
    ?>
    
    <h2 class="text-center">Criar um Novo Blog</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" name="titulo" id="titulo" class="form-control" required maxlength="500" placeholder="Digite o título do blog" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="resumo">Resumo *</label>
            <textarea name="resumo" id="resumo" class="form-control" rows="3" required maxlength="1000" placeholder="Digite um resumo do blog"><?php echo isset($_POST['resumo']) ? htmlspecialchars($_POST['resumo']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="conteudo">Conteúdo *</label>
            <textarea name="conteudo" id="conteudo" class="form-control" rows="8" required placeholder="Digite o conteúdo completo do blog"><?php echo isset($_POST['conteudo']) ? htmlspecialchars($_POST['conteudo']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="imagem">Imagem *</label>
            <input type="file" name="imagem" id="imagem" class="form-control-file" required accept="image/*">
            <small class="form-text text-muted">Formatos aceites: JPG, PNG, GIF. Tamanho máximo: 5MB</small>
        </div>
        <div class="form-group text-center">
            <button type="submit" name="publicar" class="btn btn-primary btn-lg">Publicar Blog</button>
            <a href="blog.php" class="btn btn-secondary btn-lg ml-2">Cancelar</a>
        </div>
    </form>
</div>

<?php include 'rodape.php'; ?>

<!-- Scripts -->
<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script src="js/main.js"></script>
