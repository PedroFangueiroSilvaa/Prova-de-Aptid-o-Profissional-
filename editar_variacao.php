<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Estabelecer ligação à base de dados PRIMEIRO
include 'conexao.php';
// Validação de administrador
include 'validar_admin.php';

// Verificar se foi fornecido um ID de variação
if (!isset($_GET['id_variacao'])) {
    header('Location: listar_produtos.php');
    exit();
}

$id_variacao = $_GET['id_variacao'];

// Buscar informações da variação
$queryVariacao = "
    SELECT 
        vp.*,
        p.nome AS produto,
        m.nome AS marca,
        c.nome AS categoria,
        t.descricao AS tamanho,
        co.descricao AS cor
    FROM variacoes_produto vp
    INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
    INNER JOIN marcas m ON p.id_marca = m.id_marca
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria
    INNER JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    INNER JOIN cores co ON vp.codigo_cor = co.codigo_cor
    WHERE vp.id_variacao = ?
";

$stmt = mysqli_prepare($conn, $queryVariacao);
mysqli_stmt_bind_param($stmt, "i", $id_variacao);
mysqli_stmt_execute($stmt);
$resultVariacao = mysqli_stmt_get_result($stmt);
$variacao = mysqli_fetch_assoc($resultVariacao);

if (!$variacao) {
    header('Location: listar_produtos.php');
    exit();
}

// Buscar todos os tamanhos disponíveis
$queryTamanhos = "SELECT codigo_tamanho, descricao FROM tamanhos ORDER BY descricao";
$resultTamanhos = mysqli_query($conn, $queryTamanhos);

// Buscar todas as cores disponíveis
$queryCores = "SELECT codigo_cor, descricao FROM cores ORDER BY descricao";
$resultCores = mysqli_query($conn, $queryCores);

// Processar o formulário quando enviado - ANTES DE INCLUIR O CABEÇALHO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_tamanho = $_POST['codigo_tamanho'];
    $codigo_cor = $_POST['codigo_cor'];
    $stock = $_POST['stock'];

    // Verificar se já existe uma variação com o mesmo tamanho e cor (exceto a atual)
    $queryCheck = "SELECT id_variacao FROM variacoes_produto WHERE codigo_base = ? AND codigo_tamanho = ? AND codigo_cor = ? AND id_variacao != ?";
    $stmt = mysqli_prepare($conn, $queryCheck);
    mysqli_stmt_bind_param($stmt, "sssi", $variacao['codigo_base'], $codigo_tamanho, $codigo_cor, $id_variacao);
    mysqli_stmt_execute($stmt);
    $resultCheck = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultCheck) > 0) {
        $erro = "Já existe uma variação com este tamanho e cor para este produto.";
    } else {
        // Atualizar a variação
        $queryUpdate = "UPDATE variacoes_produto SET codigo_tamanho = ?, codigo_cor = ?, stock = ? WHERE id_variacao = ?";
        $stmt = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmt, "ssii", $codigo_tamanho, $codigo_cor, $stock, $id_variacao);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: gerenciar_variacoes.php?id_produto=" . $variacao['codigo_base']);
            exit();
        } else {
            $erro = "Erro ao atualizar a variação: " . mysqli_error($conn);
        }
    }
}

// Incluir o cabeçalho APENAS DEPOIS do processamento
include 'cabecalho2.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="listar_produtos.php">Produtos</a></li>
                    <li class="breadcrumb-item"><a href="gerenciar_variacoes.php?id_produto=<?= $variacao['codigo_base'] ?>">Variações de <?= $variacao['produto'] ?></a></li>
                    <li class="breadcrumb-item active">Editar Variação</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editar Variação</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_tamanho">Tamanho</label>
                                    <select class="form-control" id="codigo_tamanho" name="codigo_tamanho" required>
                                        <?php while ($tamanho = mysqli_fetch_assoc($resultTamanhos)): ?>
                                            <option value="<?= $tamanho['codigo_tamanho'] ?>" <?= $tamanho['codigo_tamanho'] === $variacao['codigo_tamanho'] ? 'selected' : '' ?>>
                                                <?= $tamanho['descricao'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_cor">Cor</label>
                                    <select class="form-control" id="codigo_cor" name="codigo_cor" required>
                                        <?php while ($cor = mysqli_fetch_assoc($resultCores)): ?>
                                            <option value="<?= $cor['codigo_cor'] ?>" <?= $cor['codigo_cor'] === $variacao['codigo_cor'] ? 'selected' : '' ?>>
                                                <?= $cor['descricao'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $variacao['stock'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Atualizar Variação</button>
                            <a href="gerenciar_variacoes.php?id_produto=<?= $variacao['codigo_base'] ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?>