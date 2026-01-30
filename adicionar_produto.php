<?php
// Validação de administrador
include 'validar_admin.php';

// This line includes another file called 'conexao.php'.
// That file is responsible for connecting to the database, which is where all the product information is stored.
include 'conexao.php';

// The next three blocks of code are used to get lists of brands, suppliers, and categories from the database.
// These lists will be shown in dropdown menus on the webpage.

// This query asks the database for all the brand IDs and names.
$queryMarcas = "SELECT id_marca, nome FROM marcas";
// This sends the query to the database and stores the result in a variable.
$resultMarcas = mysqli_query($conn, $queryMarcas);

// This query asks the database for all the supplier IDs and names.
$queryFornecedores = "SELECT id_fornecedor, nome FROM fornecedores";
// This sends the query to the database and stores the result in a variable.
$resultFornecedores = mysqli_query($conn, $queryFornecedores);

// This query asks the database for all the category IDs and names.
$queryCategorias = "SELECT id_categoria, nome FROM categorias";
// This sends the query to the database and stores the result in a variable.
$resultCategorias = mysqli_query($conn, $queryCategorias);
?>

<!-- This line includes another file called 'cabecalho2.php'. -->
<!-- That file likely contains the header of the webpage, which is the top part of the page that stays the same across multiple pages. -->
<?php include 'cabecalho2.php'; ?>

<!-- Adicionado para garantir o estilo visual consistente em todos os ficheiros -->
<style>
    body {
        font-family: 'Montserrat', Arial, sans-serif;
        background: linear-gradient(120deg, #fff3e0 0%, #ffe0b2 100%);
        color: #333;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 700px;
        margin: 40px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        padding: 40px 40px 30px 40px;
    }
    h1 {
        color: #e65100;
        text-align: center;
        margin-bottom: 30px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    label {
        font-weight: 500;
        color: #e65100;
    }
    input, select, textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0 20px 0;
        border: 1px solid #fb8c00;
        border-radius: 6px;
        font-size: 1rem;
        background: #fff8f0;
        transition: border-color 0.3s;
    }
    input:focus, select:focus, textarea:focus {
        border-color: #e65100;
        outline: none;
    }
    button, .btn-voltar {
        background: #fb8c00;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 12px 28px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    button:hover, .btn-voltar:hover {
        background: #e65100;
    }
    .alert {
        margin-bottom: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        font-weight: 500;
        text-align: center;
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
    
    .text-center {
        text-align: center;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
</style>

<!-- Main content of the page -->
<main>
    <div class="container">
        <!-- Verificar se há uma mensagem de sucesso -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="alert alert-success">
                <strong> Sucesso!</strong> O produto foi adicionado com sucesso!
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
            <div class="alert alert-danger">
                <strong> Erro!</strong> Ocorreu um erro ao adicionar o produto. Tente novamente.
            </div>
        <?php endif; ?>

        <!-- This is a form where the user can fill in details about a new product they want to add. -->
        <!-- The form sends the information to another file called 'processar_adicionar_produto.php' when the user clicks the submit button. -->
        <!-- The 'enctype="multipart/form-data"' part allows the form to send files, like images, along with the other information. -->
        <form action="processar_adicionar_produto.php" method="post" enctype="multipart/form-data">
            <h2>Adicionar Novo Produto</h2>

        <!-- This is a text field where the user can type the name of the product. -->
        <label for="nome">Nome do Produto:</label>
        <input type="text" id="nome" name="nome" placeholder="Introduza o nome do produto" required>

        <!-- This is a dropdown menu where the user can select a brand for the product. -->
        <label for="marca">Marca:</label>
        <select id="marca" name="id_marca" required>
            <!-- The first option is a placeholder that tells the user to select a brand. -->
            <option value="" disabled selected>Selecione uma marca</option>
            <!-- This PHP code creates an option for each brand retrieved from the database. -->
            <?php while ($marca = mysqli_fetch_assoc($resultMarcas)): ?>
                <option value="<?= $marca['id_marca'] ?>"><?= $marca['nome'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- This is a dropdown menu where the user can select a supplier for the product. -->
        <label for="fornecedor">Fornecedor:</label>
        <select id="fornecedor" name="id_fornecedor" required>
            <!-- The first option is a placeholder that tells the user to select a supplier. -->
            <option value="" disabled selected>Selecione um fornecedor</option>
            <!-- This PHP code creates an option for each supplier retrieved from the database. -->
            <?php while ($fornecedor = mysqli_fetch_assoc($resultFornecedores)): ?>
                <option value="<?= $fornecedor['id_fornecedor'] ?>"><?= $fornecedor['nome'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- This is a dropdown menu where the user can select a category for the product. -->
        <label for="categoria">Categoria:</label>
        <select id="categoria" name="id_categoria" required>
            <!-- The first option is a placeholder that tells the user to select a category. -->
            <option value="" disabled selected>Selecione uma categoria</option>
            <!-- This PHP code creates an option for each category retrieved from the database. -->
            <?php while ($categoria = mysqli_fetch_assoc($resultCategorias)): ?>
                <option value="<?= $categoria['id_categoria'] ?>"><?= $categoria['nome'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- This is a field where the user can type the price of the product. -->
        <label for="preco">Preço (€):</label>
        <input type="number" id="preco" name="preco" step="0.01" placeholder="Exemplo: 49.99" required>


        <!-- This is a text area where the user can write a description of the product. -->
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva o produto"></textarea>

        <!-- This is a field where the user can upload an image of the product. -->
        <label for="imagem">Imagem do Produto:</label>
        <div class="file-input">
            <input type="file" id="imagem" name="imagem" accept="image/*" required>
        </div>

        <!-- This is the button the user clicks to submit the form. -->
        <button type="submit">Adicionar Produto</button>
    </form>
    
    <!-- Botão Voltar -->
    <div class="text-center mt-3">
        <a href="index2.php" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar à entrada
        </a>
    </div>
</div>
</main>
<?php
// This line closes the connection to the database to free up resources.
mysqli_close($conn);
?>