<?php
// These lines ensure that any errors in the code are displayed, making it easier to identify and fix problems.
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// This line includes a file called 'conexao.php', which is responsible for connecting to the database.
include 'conexao.php';

// This checks if the page received a product ID. If not, it redirects the user to another page called 'listar_produtos.php'.
if (!isset($_GET['id_produto'])) {
    header('Location: listar_produtos.php');
    exit();
}

// The product ID is stored in a variable for later use.
$id_produto = $_GET['id_produto'];

// This section handles the form submission when the user adds a new variation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The selected size, color, and stock quantity are retrieved from the form.
    $codigo_tamanho = $_POST['codigo_tamanho'];
    $codigo_cor = $_POST['codigo_cor'];
    $stock = $_POST['stock'];

    // A unique SKU (Stock Keeping Unit) is generated using the product ID, size, and color.
    $sku = strtoupper($id_produto . '-' . $codigo_tamanho . '-' . $codigo_cor);

    // This query checks if a variation with the same size and color already exists for this product.
    $queryCheck = "SELECT id_variacao FROM variacoes_produto WHERE codigo_base = '$id_produto' AND codigo_tamanho = '$codigo_tamanho' AND codigo_cor = '$codigo_cor'";
    $resultCheck = mysqli_query($conn, $queryCheck);

    // If a variation already exists, an error message is displayed.
    if (mysqli_num_rows($resultCheck) > 0) {
        $erro = "Já existe uma variação com este tamanho e cor para este produto.";
    } else {
        // If no variation exists, a new variation is added to the database.
        $queryInsert = "INSERT INTO variacoes_produto (codigo_base, codigo_tamanho, codigo_cor, stock, sku) VALUES ('$id_produto', '$codigo_tamanho', '$codigo_cor', $stock, '$sku')";
        
        // If the insertion is successful, the user is redirected to the variation management page.
        if (mysqli_query($conn, $queryInsert)) {
            header("Location: gerenciar_variacoes.php?id_produto=" . $id_produto . "&msg=sucesso");
            exit();
        } else {
            // If there is an error during insertion, an error message is displayed.
            $erro = "Erro ao adicionar variação: " . mysqli_error($conn);
        }
    }
}

// This line includes a file called 'cabecalho2.php', which likely contains the header or top part of the webpage.
include 'cabecalho2.php';

// This checks if the page received a product ID. If not, it redirects the user to another page called 'listar_produtos.php'.
if (!isset($_GET['id_produto'])) {
    header('Location: listar_produtos.php');
    exit();
}

// The product ID is stored in a variable for later use.
$id_produto = $_GET['id_produto'];

// This query retrieves information about the product, such as its code, name, brand, and category, from the database.
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

// The query is executed, and the product information is stored in a variable.
$resultProduto = mysqli_query($conn, $queryProduto);
$produto = mysqli_fetch_assoc($resultProduto);

// If no product is found, the user is redirected to the product listing page.
if (!$produto) {
    header('Location: listar_produtos.php');
    exit();
}

// This query retrieves all available sizes from the database.
$queryTamanhos = "SELECT codigo_tamanho, descricao FROM tamanhos ORDER BY descricao";
$resultTamanhos = mysqli_query($conn, $queryTamanhos);

// This query retrieves all available colors from the database.
$queryCores = "SELECT codigo_cor, descricao FROM cores ORDER BY descricao";
$resultCores = mysqli_query($conn, $queryCores);
?>

<!-- HTML starts here. This section creates the structure of the webpage. -->

<div class="container">
    <!-- Verificar se há uma mensagem de sucesso -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success">
            <strong>Sucesso!</strong> A variação foi adicionada com sucesso!
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
        <div class="alert alert-danger">
            <strong>Erro!</strong> Ocorreu um erro ao adicionar a variação. Tente novamente.
        </div>
    <?php endif; ?>

    <!-- This section creates a breadcrumb navigation to help users understand where they are on the website. -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <!-- Links to navigate back to the product list or variation management page. -->
                    <li class="breadcrumb-item"><a href="listar_produtos.php">Produtos</a></li>
                    <li class="breadcrumb-item"><a href="gerenciar_variacoes.php?id_produto=<?= $id_produto ?>">Variações de <?= $produto['produto'] ?></a></li>
                    <li class="breadcrumb-item active">Adicionar Variação</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- This section contains the form for adding a new variation. -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Adicionar Nova Variação</h5>
                </div>
                <div class="card-body">
                    <!-- If there is an error, it is displayed here. -->
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <!-- The form starts here. -->
                    <form method="POST" action="">
                        <div class="row">
                            <!-- Dropdown to select a size. -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_tamanho">Tamanho</label>
                                    <select class="form-control" id="codigo_tamanho" name="codigo_tamanho" required>
                                        <option value="">Selecione um tamanho</option>
                                        <!-- Sizes are dynamically loaded from the database. -->
                                        <?php while ($tamanho = mysqli_fetch_assoc($resultTamanhos)): ?>
                                            <option value="<?= $tamanho['codigo_tamanho'] ?>"><?= $tamanho['descricao'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Dropdown to select a color. -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_cor">Cor</label>
                                    <select class="form-control" id="codigo_cor" name="codigo_cor" required>
                                        <option value="">Selecione uma cor</option>
                                        <!-- Colors are dynamically loaded from the database. -->
                                        <?php while ($cor = mysqli_fetch_assoc($resultCores)): ?>
                                            <option value="<?= $cor['codigo_cor'] ?>"><?= $cor['descricao'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Input field to specify the stock quantity. -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons to submit the form or cancel the operation. -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Adicionar Variação</button>
                            <a href="gerenciar_variacoes.php?id_produto=<?= $id_produto ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// This closes the connection to the database to free up resources.
mysqli_close($conn);
?>