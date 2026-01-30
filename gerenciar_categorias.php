<?php
include 'conexao.php';
include 'cabecalho2.php';

// Adicionar nova categoria
if (isset($_POST['adicionar'])) {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    
    $query = "INSERT INTO categorias (nome) VALUES ('$nome')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Categoria adicionada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao adicionar categoria.');</script>";
    }
}

// Excluir categoria
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Verificar se existem produtos nesta categoria
    $check_query = "SELECT COUNT(*) as count FROM produtos WHERE id_categoria = $id";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0) {
        echo "<script>alert('Não é possível excluir esta categoria pois existem produtos associados a ela.');</script>";
    } else {
        $query = "DELETE FROM categorias WHERE id_categoria = $id";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Categoria excluída com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao excluir categoria.');</script>";
        }
    }
}

// Atualizar categoria
if (isset($_POST['atualizar'])) {
    $id = $_POST['categoria_id'];
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    
    $query = "UPDATE categorias SET nome = '$nome' WHERE id_categoria = $id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Categoria atualizada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao atualizar categoria.');</script>";
    }
}

// Preencher formulário para edição
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_query = "SELECT * FROM categorias WHERE id_categoria = $id";
    $edit_result = mysqli_query($conn, $edit_query);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_result);
    }
}

// Verificar se a tabela produtos existe
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'produtos'");
if(mysqli_num_rows($check_table) > 0) {
    // Se a tabela existe, fazer a contagem de produtos
    $query = "SELECT c.id_categoria, c.nome, COUNT(p.codigo_base) as num_produtos 
              FROM categorias c 
              LEFT JOIN produtos p ON c.id_categoria = p.id_categoria 
              GROUP BY c.id_categoria, c.nome 
              ORDER BY c.nome";
} else {
    // Se a tabela não existe, mostrar apenas as categorias
    $query = "SELECT c.id_categoria, c.nome, 0 as num_produtos 
              FROM categorias c 
              ORDER BY c.nome";
}
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Erro na query: ' . mysqli_error($conn));
}
?>

<div class="container mt-5">
    <div class="row">
        <!-- Formulário de Adição/Edição -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <?php echo $edit_data ? '<i class="fas fa-edit"></i> Editar Categoria' : '<i class="fas fa-plus"></i> Nova Categoria'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="categoria_id" value="<?php echo $edit_data['id_categoria'] ?? ''; ?>">
                        <div class="form-group">
                            <label for="nome">Nome da Categoria</label>
                            <input type="text" class="form-control" id="nome" name="nome" required value="<?php echo htmlspecialchars($edit_data['nome'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <?php if ($edit_data): ?>
                                <button type="submit" name="atualizar" class="btn btn-success">
                                    <i class="fas fa-save"></i> Atualizar
                                </button>
                                <a href="gerenciar_categorias.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            <?php else: ?>
                                <button type="submit" name="adicionar" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Categorias -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Categorias Existentes</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Produtos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                            <td><?php echo $row['num_produtos']; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $row['id_categoria']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $row['id_categoria']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhuma categoria encontrada.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

