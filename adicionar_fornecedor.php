<?php
// This line includes another file called 'cabecalho2.php'. 
// It is likely used to add a header or shared content to this page.
include 'cabecalho2.php';

// This line includes another file called 'conexao.php'. 
// This file is probably responsible for connecting to the database.
include 'conexao.php';

// This checks if the page was accessed through a form submission (POST method).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // These lines get the data entered by the user in the form fields.
    $nome = $_POST['nome']; // The name of the supplier.
    $contato = $_POST['contato']; // The contact information of the supplier.

    // Verificar se já existe um fornecedor com o mesmo nome
    $queryCheckNome = "SELECT id_fornecedor FROM fornecedores WHERE nome = '$nome'";
    $resultCheckNome = mysqli_query($conn, $queryCheckNome);
    
    if (mysqli_num_rows($resultCheckNome) > 0) {
        echo '<p style="text-align: center; color: red;">Erro: Já existe um fornecedor com este nome!</p>';
    } else {
        // Obter o próximo ID de fornecedor disponível
        $queryMaxId = "SELECT MAX(id_fornecedor) as max_id FROM fornecedores";
        $resultMaxId = mysqli_query($conn, $queryMaxId);
        $rowMaxId = mysqli_fetch_assoc($resultMaxId);
        $nextId = ($rowMaxId['max_id'] ? $rowMaxId['max_id'] + 1 : 1);

        // This creates a command (SQL query) to insert the supplier's data into the database with generated ID.
        $query = "INSERT INTO fornecedores (id_fornecedor, nome, contato) VALUES ($nextId, '$nome', '$contato')";

        // This checks if the command was successfully executed in the database.
        if (mysqli_query($conn, $query)) {
            // If successful, it shows a green message saying the supplier was added successfully.
            echo '<p style="text-align: center; color: green;">Fornecedor adicionado com sucesso!</p>';
        } else {
            // If there was an error, it shows a red message with the error details.
            echo '<p style="text-align: center; color: red;">Erro: ' . mysqli_error($conn) . '</p>';
        }
    }

    // This closes the connection to the database to free up resources.
    mysqli_close($conn);
}
?>

<style>
    /* Estilo personalizado para esta página */
    main form {
        background: #fffaf3; /* Fundo claro com tom laranja */
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        padding: 25px;
        width: 100%;
        max-width: 500px;
        margin: 20px auto;
        border: 2px solid #ff914d; /* Borda laranja */
    }

    main h2 {
        text-align: center;
        color: #ff914d; /* Título em laranja */
        margin-bottom: 20px;
    }

    main label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #b36b00; /* Cor escura de laranja para os rótulos */
    }

    main input, main button {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ff914d; /* Borda laranja */
        border-radius: 5px;
        font-size: 16px;
    }

    main input:focus {
        border-color: #ff914d;
        box-shadow: 0 0 5px rgba(255, 145, 77, 0.5); /* Efeito ao focar */
        outline: none;
    }

    main button {
        background-color: #ff914d; /* Botão laranja */
        color: white;
        border: none;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border-radius: 5px;
    }

    main button:hover {
        background-color: #e67e22; /* Efeito de hover */
    }
</style>

<!-- This is the main content of the page, where the form is displayed. -->
<main>
    <!-- This is a form that allows the user to add a new supplier. -->
    <form action="adicionar_fornecedor.php" method="post">
        <!-- The title of the form. -->
        <h2>Adicionar Fornecedor</h2>

        <!-- A label and input field for the supplier's name. -->
        <label for="nome">Nome do Fornecedor:</label>
        <input type="text" id="nome" name="nome" placeholder="Introduza o nome do fornecedor" required>

        <!-- A label and input field for the supplier's contact information. -->
        <label for="contato">Contato do Fornecedor:</label>
        <input type="text" id="contato" name="contato" placeholder="Introduza o contato do fornecedor" required>

        <!-- A button to submit the form. -->
        <button type="submit">Adicionar Fornecedor</button>
    </form>
</main>

