<?php 
include 'conexao.php';
include 'cabecalho2.php';
?>

<script>
function confirmarExclusao() {
    return confirm('Tem certeza que deseja excluir este post?');
}
</script>

<?php
// Excluir post
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Iniciar transação para garantir consistência
    mysqli_autocommit($conn, FALSE);
    
    try {
        // Primeiro excluir os comentários relacionados
        $query_comentarios = "DELETE FROM comentarios_blog WHERE id_post = $id";
        if (!mysqli_query($conn, $query_comentarios)) {
            throw new Exception("Erro ao excluir comentários: " . mysqli_error($conn));
        }
        
        // Depois excluir o post
        $query_post = "DELETE FROM blog WHERE id_post = $id";
        if (!mysqli_query($conn, $query_post)) {
            throw new Exception("Erro ao excluir post: " . mysqli_error($conn));
        }
        
        // Confirmar transação
        mysqli_commit($conn);
        echo "<script>alert('Post e comentários excluídos com sucesso!'); window.location='gerenciar_blog.php';</script>";
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        mysqli_rollback($conn);
        echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); window.location='gerenciar_blog.php';</script>";
    }
    
    // Restaurar autocommit
    mysqli_autocommit($conn, TRUE);
}

// Buscar todos os posts
$query = "SELECT * FROM blog ORDER BY data_publicacao DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-blog"></i> Gerenciar Posts do Blog</h4>
            <a href="criar_blog.php" class="btn btn-light"><i class="fas fa-plus"></i> Novo Post</a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Resumo</th>
                                <th>Data</th>
                                <th>Imagem</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($row['resumo']), 0, 100) . '...'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['data_publicacao'])); ?></td>
                                    <td>
                                        <img src="<?php echo $row['imagem']; ?>" alt="Imagem do post" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <?php
                                        echo '<a href="editar_blog.php?id=' . $row['id_post'] . '" class="btn btn-sm btn-warning">';
                                        echo '<i class="fas fa-edit"></i>';
                                        echo '</a>';
                                        
                                        echo '<a href="gerenciar_blog.php?delete=' . $row['id_post'] . '" class="btn btn-sm btn-danger" onclick="return confirmarExclusao();">';
                                        echo '<i class="fas fa-trash"></i>';
                                        echo '</a>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Nenhum post encontrado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

