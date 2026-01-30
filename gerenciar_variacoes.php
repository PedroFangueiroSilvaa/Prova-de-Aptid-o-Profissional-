<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se foi fornecido um ID de produto
if (!isset($_GET['id_produto'])) {
    header('Location: listar_produtos.php');
    exit();
}

$id_produto = $_GET['id_produto'];

// Buscar informações do produto base
$queryProduto = "
    SELECT 
        p.codigo_base,
        p.nome AS produto,
        m.nome AS marca,
        c.nome AS categoria
    FROM produtos p
    INNER JOIN marcas m ON p.id_marca = m.id_marca
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria
    WHERE p.codigo_base = '$id_produto'
";

$resultProduto = mysqli_query($conn, $queryProduto);
$produto = mysqli_fetch_assoc($resultProduto);

if (!$produto) {
    header('Location: listar_produtos.php');
    exit();
}

// Buscar todas as variações do produto
$queryVariacoes = "
    SELECT 
        vp.id_variacao,
        vp.codigo_base,
        t.descricao AS tamanho,
        co.descricao AS cor,
        vp.stock,
        p.preco
    FROM variacoes_produto vp
    INNER JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    INNER JOIN cores co ON vp.codigo_cor = co.codigo_cor
    INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
    WHERE vp.codigo_base = '$id_produto'
    ORDER BY t.descricao, co.descricao
";

$resultVariacoes = mysqli_query($conn, $queryVariacoes);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="listar_produtos.php">Produtos</a></li>
                    <li class="breadcrumb-item active">Variações de <?= $produto['produto'] ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informações do Produto Base</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Nome:</strong> <?= $produto['produto'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Marca:</strong> <?= $produto['marca'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Categoria:</strong> <?= $produto['categoria'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Variações</h2>
            <a href="adicionar_variacao.php?id_produto=<?= $id_produto ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Nova Variação
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Tamanho</th>
                            <th>Cor</th>
                            <th>Stock</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($variacao = mysqli_fetch_assoc($resultVariacoes)): ?>
                            <tr>
                                <td><?= $variacao['tamanho'] ?></td>
                                <td><?= $variacao['cor'] ?></td>
                                <td><?= $variacao['stock'] ?></td>
                                <td><?= number_format($variacao['preco'], 2, ',', '.') ?> €</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar_variacao.php?id_variacao=<?= $variacao['id_variacao'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="apagar_variacao.php?id_variacao=<?= $variacao['id_variacao'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja apagar esta variação?')">
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