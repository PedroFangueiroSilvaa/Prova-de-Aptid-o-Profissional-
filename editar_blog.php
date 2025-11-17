<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel_acesso"] != 9) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';
include 'cabecalho2.php';


// Buscar dados do post
if (isset($_GET['id'])) {
    $id_post = $_GET['id'];
    $sql = "SELECT * FROM blog WHERE id_post = $id_post";
    $result = mysqli_query($conn, $sql);
    $post = mysqli_fetch_assoc($result);

    if (!$post) {
        $_SESSION['erro'] = "Post não encontrado.";
        header("Location: gerenciar_blog.php");
        exit();
    }
} else {
    $_SESSION['erro'] = "ID do post não fornecido.";
    header("Location: gerenciar_blog.php");
    exit();
}

// Processar o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_post = $_POST['id_post'];
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
    $resumo = mysqli_real_escape_string($conn, $_POST['resumo']);
    $imagem = $_FILES['imagem']['name'];

    $target_dir = "imagens/blog/";
    $target_file = $target_dir . basename($imagem);
    $upload_ok = true;

    if (!empty($imagem)) {
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            $_SESSION['erro'] = "Erro ao fazer upload da imagem.";
            $upload_ok = false;
        }
    }

    if ($upload_ok && !empty($imagem)) {
        $sql = "UPDATE blog SET titulo = '$titulo', conteudo = '$conteudo', resumo = '$resumo', imagem = '$target_file' WHERE id_post = $id_post";
    } else {
        $sql = "UPDATE blog SET titulo = '$titulo', conteudo = '$conteudo', resumo = '$resumo' WHERE id_post = $id_post";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['sucesso'] = "Post atualizado com sucesso!";
        header("Location: gerenciar_blog.php");
        exit();
    } else {
        $_SESSION['erro'] = "Erro ao atualizar o post: " . mysqli_error($conn);
    }
}
?>

<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Editar Post</h2>

            <?php if (isset($_SESSION['erro'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
                </div>
            <?php endif; ?>

            <form action="editar_blog.php?id=<?php echo $id_post; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_post" value="<?php echo $post['id_post']; ?>">

                <div class="mb-3">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $post['titulo']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="resumo" class="form-label">Resumo</label>
                    <textarea class="form-control" id="resumo" name="resumo" rows="3" required><?php echo $post['resumo']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="conteudo" class="form-label">Conteúdo</label>
                    <textarea class="form-control" id="conteudo" name="conteudo" rows="6" required><?php echo $post['conteudo']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Imagem</label>
                    <?php if (!empty($post['imagem'])): ?>
                        <img src="<?php echo $post['imagem']; ?>" alt="Imagem atual" class="preview-image">
                    <?php endif; ?>
                    <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                    <small class="text-muted">Deixe em branco para manter a imagem atual.</small>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="gerenciar_blog.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>