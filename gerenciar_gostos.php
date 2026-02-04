<?php
include 'conexao.php';

// Processar exclusão de gosto ANTES do cabeçalho
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM gostos WHERE id_gosto = $id";
    if (mysqli_query($conn, $sql)) {
        header('Location: gerenciar_gostos.php?msg=sucesso');
        exit;
    } else {
        header('Location: gerenciar_gostos.php?msg=erro');
        exit;
    }
}

include 'cabecalho2.php';

// Buscar todos os gostos com informações dos usuários e produtos
$sql = "SELECT g.*, u.nome as nome_utilizador, u.email, p.nome as nome_produto, p.preco, p.imagem 
        FROM gostos g 
        JOIN utilizadores u ON g.id_utilizador = u.id_utilizador 
        JOIN produtos p ON g.codigo_base = p.codigo_base 
        ORDER BY g.data_gosto DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Erro na query: ' . mysqli_error($conn));
}
?>

<style>
.alert {
    margin: 20px 0;
    padding: 15px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<div class="container mt-5">
    <!-- Mensagens de feedback -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert <?= $_GET['msg'] == 'sucesso' ? 'alert-success' : 'alert-danger' ?>">
            <?php if ($_GET['msg'] == 'sucesso'): ?>
                ✅ Gosto removido com sucesso!
            <?php else: ?>
                ❌ Erro ao remover o gosto. Tente novamente.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-heart"></i> Gerenciar Gostos</h4>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Data do Gosto</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle fa-2x mr-2 text-primary"></i>
                                            <?php echo htmlspecialchars($row['nome_utilizador']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td> 
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $row['imagem']; ?>" 
                                                 alt="<?php echo htmlspecialchars($row['nome_produto']); ?>"
                                                 class="img-thumbnail mr-2"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php echo htmlspecialchars($row['nome_produto']); ?>
                                        </div>
                                    </td>
                                    <td>€<?php echo number_format($row['preco'], 2); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['data_gosto'])); ?></td>
                                    <td>
                                        <a href="product-single.php?codigo_base=<?php echo $row['codigo_base']; ?>" 
                                           class="btn btn-sm btn-info"
                                           title="Ver produto">
                                            <i class="fas fa-eye"></i> Ver Produto
                                        </a>
                                        <a href="?delete=<?php echo $row['id_gosto']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Tem certeza que deseja remover este gosto?')">
                                            <i class="fas fa-trash"></i> Remover
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhum gosto encontrado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?>

