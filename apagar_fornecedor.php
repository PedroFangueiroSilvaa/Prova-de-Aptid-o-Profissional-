<?php
// This line includes a separate file called 'conexao.php', which is responsible for connecting to the database.
// Without this, the script cannot interact with the database.
include 'conexao.php';

// This checks if the 'id_fornecedor' (supplier ID) was provided in the URL using the GET method.
// If it was provided, the code inside this block will execute.
if (isset($_GET['id_fornecedor'])) {
    // The supplier ID is retrieved from the URL and stored in the variable $id_fornecedor.
    $id_fornecedor = $_GET['id_fornecedor'];

    // This query checks if a supplier with the given ID exists in the database.
    $query = "SELECT * FROM fornecedores WHERE id_fornecedor = $id_fornecedor";
    // The query is sent to the database, and the result is stored in the variable $result.
    $result = mysqli_query($conn, $query);

    // This checks if the query found any supplier with the given ID.
    if (mysqli_num_rows($result) > 0) {
        // If the supplier exists, this query deletes the supplier from the database.
        $queryDelete = "DELETE FROM fornecedores WHERE id_fornecedor = $id_fornecedor";

        // This sends the delete query to the database and checks if it was successful.
        if (mysqli_query($conn, $queryDelete)) {
            // If the deletion was successful, a success message is prepared.
            $mensagem = "Fornecedor apagado com sucesso!";
            $tipoMensagem = "success"; // This indicates the type of message (success).
        } else {
            // If there was an error during deletion, an error message is prepared.
            $mensagem = "Erro ao apagar fornecedor: " . mysqli_error($conn);
            $tipoMensagem = "danger"; // This indicates the type of message (error).
        }
    } else {
        // If no supplier was found with the given ID, a warning message is prepared.
        $mensagem = "Fornecedor não encontrado!";
        $tipoMensagem = "warning"; // This indicates the type of message (warning).
    }
} else {
    // If the supplier ID was not provided in the URL, an error message is prepared.
    $mensagem = "ID do fornecedor não especificado!";
    $tipoMensagem = "danger"; // This indicates the type of message (error).
}

// This closes the connection to the database to free up resources.
mysqli_close($conn);
?>

<body>
    <!-- This section includes the header of the webpage from a separate file called 'cabecalho2.php'. -->
    <?php include 'cabecalho2.php'; ?>

    <!-- Main content of the webpage. -->
    <main>
        <div class="container">
            <!-- This displays a feedback message to the user based on the result of the PHP code above. -->
            <div class="alert alert-<?php echo $tipoMensagem; ?>" role="alert">
                <?php echo $mensagem; ?> <!-- The message prepared in the PHP code is displayed here. -->
            </div>
            <!-- This is a button that allows the user to go back to the list of suppliers. -->
            <a href="listar_marcas_fornecedores.php" class="btn-voltar">Voltar à Lista de Fornecedores</a>
        </div>
    </main>

    <!-- This section includes the footer of the webpage from a separate file called 'rodape2.php'. -->
</body>