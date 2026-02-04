<?php
// This line includes another file called 'conexao.php', which contains the code to connect to the database.
include 'conexao.php';

// This checks if the 'id_marca' (brand ID) was provided in the URL using the GET method.
if (isset($_GET['id_marca'])) {
    // If 'id_marca' exists, it is stored in a variable called $id_marca.
    $id_marca = $_GET['id_marca'];

    // A query (instruction for the database) is created to check if a brand with the given ID exists.
    $query = "SELECT imagem FROM marcas WHERE id_marca = $id_marca";
    // The query is sent to the database, and the result is stored in $result.
    $result = mysqli_query($conn, $query);
    // The result is converted into an associative array (a format we can work with in PHP).
    $marca = mysqli_fetch_assoc($result);    // This checks if the brand exists in the database.
    if ($marca) {
        // Verificar se existem produtos associados à marca antes de tentar excluí-la
        $queryProdutos = "SELECT COUNT(*) as total FROM produtos WHERE id_marca = $id_marca";
        $resultProdutos = mysqli_query($conn, $queryProdutos);
        $rowProdutos = mysqli_fetch_assoc($resultProdutos);
        
        if ($rowProdutos['total'] > 0) {
            // Existem produtos associados a esta marca, não podemos excluí-la
            $mensagem = "Não é possível excluir esta marca pois existem {$rowProdutos['total']} produtos associados a ela. 
                        Associe esses produtos a outra marca ou remova-os primeiro.";
            $tipoMensagem = "danger"; // This indicates the type of message (error).
        } else {
            // Não há produtos associados, podemos excluir a marca
            // If the brand has an associated image and the image file exists on the server, it is deleted.
            if (!empty($marca['imagem']) && file_exists($marca['imagem'])) {
                unlink($marca['imagem']); // Deletes the image file.
            }
            // A new query is created to delete the brand from the database.
            $queryDelete = "DELETE FROM marcas WHERE id_marca = $id_marca";
            // The query is sent to the database to delete the brand.
            if (mysqli_query($conn, $queryDelete)) {
                // If the deletion is successful, a success message is prepared.
                $mensagem = "Marca apagada com sucesso!";
                $tipoMensagem = "success"; // This indicates the type of message (success).
            } else {
                // If there is an error during deletion, an error message is prepared.
                $mensagem = "Erro ao apagar marca: " . mysqli_error($conn);
                $tipoMensagem = "danger"; // This indicates the type of message (error).
            }
        }
    } else {
        // If the brand does not exist, a warning message is prepared.
        $mensagem = "Marca não encontrada!";
        $tipoMensagem = "warning"; // This indicates the type of message (warning).
    }
} else {
    // If 'id_marca' was not provided in the URL, an error message is prepared.
    $mensagem = "ID da marca não especificado!";
    $tipoMensagem = "danger"; // This indicates the type of message (error).
}

// This closes the connection to the database to free up resources.
mysqli_close($conn);
?>

<body>
    <!-- This section includes a header from another file called 'cabecalho2.php'. -->
    <?php include 'cabecalho2.php'; ?>

    <!-- Main content of the page -->
    <main>
        <div class="container">
            <!-- This displays a feedback message to the user based on the operation performed. -->
            <div class="alert alert-<?php echo $tipoMensagem; ?>" role="alert">
                <?php echo $mensagem; ?> <!-- The message prepared earlier is displayed here. -->
            </div>
            <!-- This is a button that allows the user to go back to the list of brands. -->
            <a href="listar_marcas_fornecedores.php" class="btn-voltar">Voltar à Lista de Marcas</a>
        </div>
    </main>
</body>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f9f9f9;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 600px;
        margin: 40px auto;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 30px 30px 20px 30px;
    }
    .alert {
        margin-bottom: 20px;
    }
    .btn-voltar {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 18px;
        background: #ff914d;
        color: #fff;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s;
    }
    .btn-voltar:hover {
        background: #e65100;
    }
</style>
</html>