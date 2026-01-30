<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se a conexão foi estabelecida
if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Consultar todas as variações com informações detalhadas
$queryVariacoes = "
    SELECT 
        vp.id_variacao,
        p.codigo_base,
        p.nome AS produto,
        m.nome AS marca,
        c.nome AS categoria,
        t.descricao AS tamanho,
        co.descricao AS cor,
        vp.stock,
        p.imagem
    FROM variacoes_produto vp
    INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
    INNER JOIN marcas m ON p.id_marca = m.id_marca
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria
    INNER JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    INNER JOIN cores co ON vp.codigo_cor = co.codigo_cor
    ORDER BY p.codigo_base, t.descricao, co.descricao
";

$resultVariacoes = mysqli_query($conn, $queryVariacoes);

// Verificar se a consulta foi bem sucedida
if (!$resultVariacoes) {
    die("Erro na consulta: " . mysqli_error($conn));
}

// Verificar se existem variações
$numVariacoes = mysqli_num_rows($resultVariacoes);
?>

<style>
.badge-stock {
    font-size: 14px !important;
    font-weight: bold !important;
    color: white !important;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
    min-width: 40px;
    text-align: center;
}
.badge-success {
    background-color: #28a745 !important;
}
.badge-danger {
    background-color: #dc3545 !important;
}
</style>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Todas as Variações</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Todas as Variações de Produtos</h2>
            <div>
                <a href="listar_produtos.php" class="btn btn-secondary mr-2">
                    <i class="fas fa-box"></i> Ver Produtos Base
                </a>
                <a href="gerenciar_tamanhos.php" class="btn btn-info mr-2">
                    <i class="fas fa-ruler"></i> Gerenciar Tamanhos
                </a>
                <a href="gerenciar_cores.php" class="btn btn-info">
                    <i class="fas fa-palette"></i> Gerenciar Cores
                </a>
            </div>
        </div>
    </div>

    <?php if ($numVariacoes == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Não existem variações cadastradas no sistema.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Marca</th>
                                <th>Categoria</th>
                                <th>Tamanho</th>
                                <th>Cor</th>
                                <th>Stock</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($variacao = mysqli_fetch_assoc($resultVariacoes)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($variacao['imagem'])): ?>
                                                <img src="<?= $variacao['imagem'] ?>" alt="<?= $variacao['produto'] ?>" class="img-thumbnail mr-2" style="max-width: 50px;">
                                            <?php else: ?>
                                                <div class="img-thumbnail mr-2" style="width: 50px; height: 50px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($variacao['produto']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($variacao['marca']) ?></td>
                                    <td><?= htmlspecialchars($variacao['categoria']) ?></td>
                                    <td><?= htmlspecialchars($variacao['tamanho']) ?></td>
                                    <td><?= htmlspecialchars($variacao['cor']) ?></td>
                                    <td>
                                        <span class="badge badge-stock <?= $variacao['stock'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $variacao['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="editar_variacao.php?id_variacao=<?= $variacao['id_variacao'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="apagar_variacao.php?id_variacao=<?= $variacao['id_variacao'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja apagar esta variação?')" title="Apagar">
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
    <?php endif; ?>
</div>

<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?>