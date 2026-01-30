<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Consultar apenas os produtos base
$queryProdutos = "
    SELECT 
        p.codigo_base AS id_produto,
        p.nome AS produto,
        m.nome AS marca,
        f.nome AS fornecedor,
        c.nome AS categoria,
        p.preco,
        p.descricao,
        p.imagem
    FROM produtos p
    INNER JOIN marcas m ON p.id_marca = m.id_marca
    INNER JOIN fornecedores f ON p.id_fornecedor = f.id_fornecedor
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria
    GROUP BY p.codigo_base
    ORDER BY p.codigo_base
";

$resultProdutos = mysqli_query($conn, $queryProdutos);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Produtos Base</h2>
            <a href="adicionar_produto.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Novo Produto
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Marca</th>
                            <th>Fornecedor</th>
                            <th>Categoria</th>
                            <th>Preço Base</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($produto = mysqli_fetch_assoc($resultProdutos)): ?>
                            <tr>
                                <td><?= $produto['id_produto'] ?></td>
                                <td>
                                    <?php if (!empty($produto['imagem'])): ?>
                                        <img src="<?= $produto['imagem'] ?>" alt="<?= $produto['produto'] ?>" class="img-thumbnail" style="max-width: 80px;">
                                    <?php else: ?>
                                        <span class="text-muted">Sem imagem</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $produto['produto'] ?></td>
                                <td><?= $produto['marca'] ?></td>
                                <td><?= $produto['fornecedor'] ?></td>
                                <td><?= $produto['categoria'] ?></td>
                                <td><?= number_format($produto['preco'], 2, ',', '.') ?> €</td>
                                <td><?= substr($produto['descricao'], 0, 50) . (strlen($produto['descricao']) > 50 ? '...' : '') ?></td>
                                <td>
                                    <div class="btn-group">
                                        <!-- Botão de Editar -->
                                        <a href="editar_produto.php?id_produto=<?= $produto['id_produto'] ?>" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botão do meio (Visualizar ou Gerenciar Variações) -->
                                        <a href="gerenciar_variacoes.php?id_produto=<?= $produto['id_produto'] ?>" class="btn btn-sm btn-info" data-toggle="tooltip" title="Gerenciar Variações">
                                            <i class="fas fa-eye"></i> <!-- Substituído por um ícone válido -->
                                        </a>

                                        <!-- Botão de Apagar -->
                                        <a href="apagar_produto.php?codigo_base=<?= $produto['id_produto'] ?>" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Apagar" onclick="return confirm('Tem certeza que deseja apagar este produto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?>