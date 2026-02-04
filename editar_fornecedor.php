<?php
// Estabelecer ligação à base de dados PRIMEIRO
include 'conexao.php';
// Validação de administrador
include 'validar_admin.php';

// Verificar se foi passado um id de fornecedor
if (isset($_GET['id_fornecedor'])) {
    $id_fornecedor = intval($_GET['id_fornecedor']);
    // Consultar as informações do fornecedor
    $queryFornecedor = "SELECT id_fornecedor, nome, contato FROM fornecedores WHERE id_fornecedor = $id_fornecedor";
    $resultFornecedor = mysqli_query($conn, $queryFornecedor);
    if (mysqli_num_rows($resultFornecedor) == 1) {
        $fornecedor = mysqli_fetch_assoc($resultFornecedor);
    } else {
        header('Location: listar_marcas_fornecedores.php');
        exit;
    }
} else {
    header('Location: listar_marcas_fornecedores.php');
    exit;
}

// Atualizar as informações do fornecedor após o envio do formulário - ANTES DO CABEÇALHO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $contato = mysqli_real_escape_string($conn, $_POST['contato']);

    // Atualizar os dados do fornecedor na base de dados
    $queryUpdate = "UPDATE fornecedores SET nome = '$nome', contato = '$contato' WHERE id_fornecedor = $id_fornecedor";
    if (mysqli_query($conn, $queryUpdate)) {
        header('Location: listar_marcas_fornecedores.php?msg=sucesso');
        exit;
    } else {
        header('Location: editar_fornecedor.php?id_fornecedor=' . $id_fornecedor . '&msg=erro');
        exit;
    }
}

// Incluir o cabeçalho APENAS DEPOIS do processamento
include 'cabecalho2.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Fornecedor</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }
        main {
            margin: 20px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container input, .form-container button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
        }
        .form-container button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<main>
    <h2>Editar Fornecedor</h2>
    <div class="form-container">
        <form action="editar_fornecedor.php?id_fornecedor=<?= $fornecedor['id_fornecedor'] ?>" method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($fornecedor['nome']) ?>" required>

            <label for="contato">Contato:</label>
            <input type="text" id="contato" name="contato" value="<?= htmlspecialchars($fornecedor['contato']) ?>" required>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</main>
</body>
</html>
<?php
// Fechar a conexão com a base de dados
mysqli_close($conn);
?>