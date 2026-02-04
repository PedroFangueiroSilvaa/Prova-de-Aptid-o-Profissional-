<?php
// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Definir o caminho para o diretório de imagens
$target_dir = "imagens/marcas/";

// Verificar se foi passado um id de marca
if (isset($_GET['id_marca'])) {
    $id_marca = $_GET['id_marca'];
    // Consultar as informações da marca
    $queryMarca = "SELECT id_marca, nome, imagem FROM marcas WHERE id_marca = $id_marca";
    $resultMarca = mysqli_query($conn, $queryMarca);
    if (mysqli_num_rows($resultMarca) == 1) {
        $marca = mysqli_fetch_assoc($resultMarca);
    } else {
        echo "Marca não encontrada.";
        exit;
    }
}

// Atualizar as informações da marca após o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $imagem = $_FILES['imagem']['name'];

    // Verificar se o campo de imagem foi preenchido e fazer o upload
    if ($imagem) {
        // Debug: Mostrar informações do diretório
        echo "<script>console.log('Diretório: " . $target_dir . "');</script>";
        echo "<script>console.log('Existe: " . (file_exists($target_dir) ? 'Sim' : 'Não') . "');</script>";
        echo "<script>console.log('Permissões: " . substr(sprintf('%o', fileperms($target_dir)), -4) . "');</script>";
        
        // Verificar se o diretório existe
        if (!file_exists($target_dir)) {
            echo "<script>alert('Erro: O diretório " . $target_dir . " não existe. Por favor, crie-o manualmente.');</script>";
            exit;
        }

        // Verificar se o diretório tem permissões de escrita
        if (!is_writable($target_dir)) {
            echo "<script>alert('Erro: O diretório " . $target_dir . " não tem permissão de escrita.');</script>";
            exit;
        }

        $target_file = $target_dir . basename($_FILES["imagem"]["name"]);
        
        // Debug: Mostrar informações do arquivo
        echo "<script>console.log('Arquivo: " . $target_file . "');</script>";
        
        // Verificar se o upload foi bem sucedido
        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            // Atualizar os dados da marca na base de dados
            $queryUpdate = "UPDATE marcas SET nome = '$nome', imagem = '$target_file' WHERE id_marca = $id_marca";
            if (mysqli_query($conn, $queryUpdate)) {
                echo "<script>
                    alert('Marca atualizada com sucesso.');
                    window.location.href = 'listar_marcas_fornecedores.php';
                </script>";
            } else {
                echo "<script>alert('Erro ao atualizar marca: " . mysqli_error($conn) . "');</script>";
            }
        } else {
            echo "<script>alert('Erro ao fazer upload da imagem. Verifique as permissões do diretório.');</script>";
        }
    } else {
        // Se não foi carregada uma nova imagem, atualizar apenas o nome
        $queryUpdate = "UPDATE marcas SET nome = '$nome' WHERE id_marca = $id_marca";
        if (mysqli_query($conn, $queryUpdate)) {
            echo "<script>
                alert('Marca atualizada com sucesso.');
                window.location.href = 'listar_marcas_fornecedores.php';
            </script>";
        } else {
            echo "<script>alert('Erro ao atualizar marca: " . mysqli_error($conn) . "');</script>";
        }
    }
    exit;
}
?>
<main>
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
    <h2>Editar Marca</h2>
    <div class="form-container">
        <form action="editar_marca.php?id_marca=<?= $marca['id_marca'] ?>" method="POST" enctype="multipart/form-data">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($marca['nome']) ?>" required>
            
            <label for="imagem">Imagem atual:</label>
            <?php if (!empty($marca['imagem'])): ?>
                <img src="<?= $marca['imagem'] ?>" alt="Imagem da marca" style="max-width: 150px; height: auto; border-radius: 8px; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" id="imagem" name="imagem">
            
            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</main>
<?php
// Fechar a conexão com a base de dados
mysqli_close($conn);
?>