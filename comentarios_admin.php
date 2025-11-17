<?php
include 'validar_admin.php';

include 'conexao.php';
include 'cabecalho2.php';

$search = $_GET['search'] ?? '';
$blog_id = $_GET['blog_id'] ?? '';

$where = [];
if (!empty($blog_id)) {
    $where[] = "c.id_post = '" . mysqli_real_escape_string($conn, $blog_id) . "'";
}
if (!empty($search)) {
    $where[] = "c.conteudo LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT c.*, u.nome as nome_usuario, b.titulo as titulo_blog 
        FROM comentarios_blog c 
        JOIN utilizadores u ON c.id_utilizador = u.id_utilizador 
        JOIN blog b ON c.id_post = b.id_post 
        $where_clause 
        ORDER BY c.data_comentario DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Comentários - Boxing for Life</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .comment-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .comment-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        .comment-body {
            padding: 20px;
        }
        .comment-actions {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Gestão de Comentários</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <form class="d-flex">
                    <input type="text" class="form-control me-2" placeholder="Pesquisar comentários..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-primary" type="submit">Pesquisar</button>
                </form>
            </div>
            <div class="col-md-6">
                <select class="form-select" id="filterBlog" onchange="window.location.href='?blog_id='+this.value">
                    <option value="">Todos os Posts</option>
                    <?php
                    $blogs = mysqli_query($conn, "SELECT id_post, titulo FROM blog ORDER BY titulo");
                    while ($blog = mysqli_fetch_assoc($blogs)) {
                        $selected = $blog['id_post'] == $blog_id ? 'selected' : '';
                        echo "<option value='{$blog['id_post']}' $selected>{$blog['titulo']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <?php while ($comentario = mysqli_fetch_assoc($result)): ?>
            <div class="card comment-card">
                <div class="comment-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($comentario['nome_usuario']); ?></h5>
                        <small class="text-muted">Comentou em: <?php echo htmlspecialchars($comentario['titulo_blog']); ?></small>
                    </div>
                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?></small>
                </div>
                <div class="comment-body">
                    <p><?php echo nl2br(htmlspecialchars($comentario['conteudo'])); ?></p>
                </div>
                <div class="comment-actions">
                    <button class="btn btn-sm btn-danger" onclick="deleteComment(<?php echo $comentario['id_comentario']; ?>)">Excluir Comentário</button>
                    <a href="blog-single.php?id=<?php echo $comentario['id_post']; ?>" class="btn btn-sm btn-primary" target="_blank">Ver Post</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
    function deleteComment(id) {
        if (confirm('Tem certeza que deseja excluir este comentário?')) {
            window.location.href = 'excluir_comentario.php?id=' + id;
        }
    }
    </script>
</body>
</html>