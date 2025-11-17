<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Buscar todas as marcas
$queryMarcas = "SELECT id_marca, nome, imagem FROM marcas ORDER BY nome";
$resultMarcas = mysqli_query($conn, $queryMarcas);

// Buscar todos os fornecedores
$queryFornecedores = "SELECT id_fornecedor, nome, contato FROM fornecedores ORDER BY nome";
$resultFornecedores = mysqli_query($conn, $queryFornecedores);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Marcas e Fornecedores</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        table td {
            background-color: #f9f9f9;
            color: #333;
        }
        table tr:hover td {
            background-color: #f1f1f1;
        }
        img {
            max-width: 100px;
            height: auto;
            border-radius: 8px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .table-wrapper {
            margin-bottom: 40px;
        }
        footer {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        .btn-apagar, .btn-alterar {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 5px;
        }
        .btn-alterar {
            background-color: #007bff;
        }
        .btn-apagar:hover, .btn-alterar:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<main class="container">
    <!-- Mensagens de feedback -->
    <?php if (isset($_GET['msg'])): ?>
        <div style="margin: 20px 0; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold;
                    <?= $_GET['msg'] == 'sucesso' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' ?>">
            <?php if ($_GET['msg'] == 'sucesso'): ?>
                ✅ Operação realizada com sucesso!
            <?php else: ?>
                ❌ Ocorreu um erro durante a operação. Tente novamente.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Lista de Marcas -->
    <div class="table-wrapper">
        <h2>Lista de Marcas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Imagem</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($marca = mysqli_fetch_assoc($resultMarcas)): ?>
                    <?php
                    // Caminho absoluto para verificar a existência do arquivo
                    $caminho_absoluto = $_SERVER['DOCUMENT_ROOT'] . '/' . $marca['imagem'];

                    // Caminho relativo para exibir a imagem
                    $caminho_relativo = $marca['imagem'];
                    ?>
                    <tr>
                        <td><?= $marca['id_marca'] ?></td>
                        <td><?= $marca['nome'] ?></td>
                        <td>
            <?php if (!empty($marca['imagem'])): ?>
                <img src="<?= $marca['imagem'] ?>" alt="Imagem da marca" style="max-width: 150px; height: auto; border-radius: 8px; margin-bottom: 10px;">
            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Botão de Alterar -->
                            <a href="editar_marca.php?id_marca=<?= $marca['id_marca'] ?>" class="btn-alterar">Alterar</a>
                            <!-- Botão de Apagar -->
                            <a href="apagar_marca.php?id_marca=<?= $marca['id_marca'] ?>" class="btn-apagar" onclick="return confirm('Tem certeza que deseja apagar esta marca?')">Apagar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Lista de Fornecedores -->
    <div class="table-wrapper">
        <h2>Lista de Fornecedores</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Contato</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fornecedor = mysqli_fetch_assoc($resultFornecedores)): ?>
                    <tr>
                        <td><?= $fornecedor['id_fornecedor'] ?></td>
                        <td><?= $fornecedor['nome'] ?></td>
                        <td><?= $fornecedor['contato'] ?? 'Sem contato' ?></td>
                        <td>
                            <!-- Botão de Alterar -->
                            <a href="editar_fornecedor.php?id_fornecedor=<?= $fornecedor['id_fornecedor'] ?>" class="btn-alterar">Alterar</a>
                            <!-- Botão de Apagar -->
                            <a href="apagar_fornecedor.php?id_fornecedor=<?= $fornecedor['id_fornecedor'] ?>" class="btn-apagar" onclick="return confirm('Tem certeza que deseja apagar este fornecedor?')">Apagar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?>