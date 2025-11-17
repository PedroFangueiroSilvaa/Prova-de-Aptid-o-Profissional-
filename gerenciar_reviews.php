<?php
include 'conexao.php';
include 'cabecalho2.php';

// Processar exclusão de review
if (isset($_GET['apagar_encomenda'])) {
    $id_review = (int)$_GET['apagar_encomenda'];
    $sql_delete = "DELETE FROM reviews_encomendas WHERE id_review = $id_review";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Review de encomenda apagada com sucesso!'); window.location='gerenciar_reviews.php';</script>";
    } else {
        echo "<script>alert('Erro ao apagar review: " . mysqli_error($conn) . "');</script>";
    }
}

if (isset($_GET['apagar_produto'])) {
    $id_review = (int)$_GET['apagar_produto'];
    $sql_delete = "DELETE FROM reviews_produtos WHERE id_review = $id_review";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Review de produto apagada com sucesso!'); window.location='gerenciar_reviews.php';</script>";
    } else {
        echo "<script>alert('Erro ao apagar review: " . mysqli_error($conn) . "');</script>";
    }
}

// Buscar todas as reviews de encomendas
$sql_encomendas = "SELECT re.*, u.nome as nome_utilizador, e.id_encomenda, e.data_encomenda
                   FROM reviews_encomendas re 
                   JOIN utilizadores u ON re.id_utilizador = u.id_utilizador 
                   JOIN encomendas e ON re.id_encomenda = e.id_encomenda 
                   ORDER BY re.data_review DESC";
$result_encomendas = mysqli_query($conn, $sql_encomendas);

// Buscar todas as reviews de produtos
$sql_produtos = "SELECT rp.*, u.nome as nome_utilizador, p.nome as nome_produto, p.imagem, e.id_encomenda
                 FROM reviews_produtos rp 
                 JOIN utilizadores u ON rp.id_utilizador = u.id_utilizador 
                 JOIN produtos p ON rp.codigo_base = p.codigo_base 
                 JOIN encomendas e ON rp.id_encomenda = e.id_encomenda 
                 ORDER BY rp.data_review DESC";
$result_produtos = mysqli_query($conn, $sql_produtos);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4 text-gray-800">Gerenciar Reviews</h1>
        </div>
    </div>

    <!-- Reviews de Encomendas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Reviews de Encomendas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Review</th>
                            <th>ID Encomenda</th>
                            <th>Cliente</th>
                            <th>Classificação</th>
                            <th>Comentário</th>
                            <th>Data Review</th>
                            <th>Data Encomenda</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($review = mysqli_fetch_assoc($result_encomendas)): ?>
                            <tr>
                                <td><?php echo $review['id_review']; ?></td>
                                <td><?php echo $review['id_encomenda']; ?></td>
                                <td><?php echo htmlspecialchars($review['nome_utilizador']); ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['classificacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?php echo htmlspecialchars($review['comentario']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($review['data_review'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($review['data_encomenda'])); ?></td>
                                <td>
                                    <a href="?apagar_encomenda=<?php echo $review['id_review']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja apagar esta review?')" 
                                       title="Apagar Review">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Reviews de Produtos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Reviews de Produtos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable2" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Review</th>
                            <th>Produto</th>
                            <th>ID Encomenda</th>
                            <th>Cliente</th>
                            <th>Classificação</th>
                            <th>Comentário</th>
                            <th>Data Review</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($review = mysqli_fetch_assoc($result_produtos)): ?>
                            <tr>
                                <td><?php echo $review['id_review']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($review['imagem'])): ?>
                                            <img src="<?php echo htmlspecialchars($review['imagem']); ?>" 
                                                 alt="<?php echo htmlspecialchars($review['nome_produto']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($review['nome_produto']); ?>
                                    </div>
                                </td>
                                <td><?php echo $review['id_encomenda']; ?></td>
                                <td><?php echo htmlspecialchars($review['nome_utilizador']); ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['classificacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?php echo htmlspecialchars($review['comentario']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($review['data_review'])); ?></td>
                                <td>
                                    <a href="?apagar_produto=<?php echo $review['id_review']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja apagar esta review?')" 
                                       title="Apagar Review">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


</script>

